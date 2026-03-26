<?php
/**
 * functions.php
 * Aspirian.pk Online Test System
 * Reusable helper functions used across the application
 */

require_once __DIR__ . '/config.php';

// ─────────────────────────────────────────────
// Authentication helpers
// ─────────────────────────────────────────────

/**
 * Check if a student is logged in; redirect to login if not.
 */
function requireStudent(): void {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
        header('Location: ' . SITE_URL . '/index.php?error=Please+login+first');
        exit;
    }
}

/**
 * Check if an admin is logged in; redirect to admin login if not.
 */
function requireAdmin(): void {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header('Location: ' . SITE_URL . '/admin/login.php?error=Admin+access+required');
        exit;
    }
}

/**
 * Return true if ANY user is logged in.
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

// ─────────────────────────────────────────────
// Security helpers
// ─────────────────────────────────────────────

/**
 * Sanitise a string for output (prevent XSS).
 */
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Hash a password using bcrypt.
 */
function hashPassword(string $password): string {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify a plain password against a bcrypt hash.
 */
function verifyPassword(string $password, string $hash): bool {
    return password_verify($password, $hash);
}

/**
 * Generate a CSRF token and store it in the session.
 */
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate submitted CSRF token; die on failure.
 */
function csrfVerify(): void {
    if (
        empty($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])
    ) {
        http_response_code(403);
        die('Invalid CSRF token. Please go back and try again.');
    }
}

// ─────────────────────────────────────────────
// Database helpers
// ─────────────────────────────────────────────

/**
 * Fetch a single row as associative array.
 * @param string $sql  Parameterised query
 * @param string $types  MySQLi bind_param types string
 * @param mixed  ...$params  Values to bind
 */
function fetchOne(string $sql, string $types = '', ...$params) {
    global $conn;
    $stmt = $conn->prepare($sql);
    if (!$stmt) return null;
    if ($types && $params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $row    = $result->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

/**
 * Fetch all rows as an array of associative arrays.
 */
function fetchAll(string $sql, string $types = '', ...$params) {
    global $conn;
    $stmt = $conn->prepare($sql);
    if (!$stmt) return [];
    if ($types && $params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $rows   = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

/**
 * Execute an INSERT/UPDATE/DELETE query.
 * Returns insert ID for INSERT, affected rows otherwise, or false on error.
 */
function execute(string $sql, string $types = '', ...$params) {
    global $conn;
    $stmt = $conn->prepare($sql);
    if (!$stmt) return false;
    if ($types && $params) {
        $stmt->bind_param($types, ...$params);
    }
    $ok = $stmt->execute();
    if (!$ok) {
        $stmt->close();
        return false;
    }
    $id = $stmt->insert_id ?: $stmt->affected_rows;
    $stmt->close();
    return $id;
}

// ─────────────────────────────────────────────
// MCQ / Test helpers
// ─────────────────────────────────────────────

/**
 * Get a random set of MCQs for a given topic.
 */
function getMCQs(string $topic, int $limit = MCQS_PER_TEST): array {
    return fetchAll(
        'SELECT * FROM mcqs WHERE topic = ? ORDER BY RAND() LIMIT ?',
        'si',
        $topic,
        $limit
    );
}

/**
 * Get all distinct topics that have at least one MCQ.
 */
function getTopicsWithMCQs(): array {
    return fetchAll('SELECT DISTINCT topic FROM mcqs ORDER BY topic ASC');
}

/**
 * Grade a submitted test.
 * $answers = ['mcq_id' => 'a'|'b'|'c'|'d', ...]
 * Returns ['score' => int, 'total' => int, 'details' => [...]]
 */
function gradeTest(array $answers): array {
    if (empty($answers)) {
        return ['score' => 0, 'total' => 0, 'details' => []];
    }

    $ids     = array_map('intval', array_keys($answers));
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types   = str_repeat('i', count($ids));

    global $conn;
    $stmt = $conn->prepare(
        "SELECT id, question, option_a, option_b, option_c, option_d, correct_option
         FROM mcqs
         WHERE id IN ($placeholders)"
    );
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $score   = 0;
    $details = [];

    foreach ($rows as $row) {
        $mcqId     = $row['id'];
        $submitted = strtolower(trim($answers[$mcqId] ?? ''));
        $correct   = strtolower(trim($row['correct_option']));
        $isCorrect = ($submitted === $correct);

        if ($isCorrect) $score++;

        // Map letter to full option text
        $optionMap = [
            'a' => $row['option_a'],
            'b' => $row['option_b'],
            'c' => $row['option_c'],
            'd' => $row['option_d'],
        ];

        $details[] = [
            'question'       => $row['question'],
            'submitted'      => $submitted,
            'submitted_text' => $optionMap[$submitted] ?? 'Not answered',
            'correct'        => $correct,
            'correct_text'   => $optionMap[$correct],
            'is_correct'     => $isCorrect,
        ];
    }

    return [
        'score'   => $score,
        'total'   => count($rows),
        'details' => $details,
    ];
}

/**
 * Save a test result in the database.
 */
function saveResult(int $userId, string $topic, int $score, int $total) {
    return execute(
        'INSERT INTO results (user_id, topic, score, total) VALUES (?, ?, ?, ?)',
        'isii',
        $userId, $topic, $score, $total
    );
}

// ─────────────────────────────────────────────
// Flash message helpers
// ─────────────────────────────────────────────

/**
 * Store a flash message in the session.
 */
function flash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Render and clear the flash message (returns HTML string).
 */
function renderFlash(): string {
    if (empty($_SESSION['flash'])) return '';
    $f    = $_SESSION['flash'];
    unset($_SESSION['flash']);
    $type = e($f['type']);   // 'success' | 'error' | 'info'
    $msg  = e($f['message']);
    return "<div class=\"alert alert-{$type}\">{$msg}</div>";
}

// ─────────────────────────────────────────────
// CSV Upload helper (Admin)
// ─────────────────────────────────────────────

/**
 * Parse and import an MCQ CSV file.
 * Expected CSV columns (header row required):
 * topic, question, option_a, option_b, option_c, option_d, correct_option
 *
 * Returns ['inserted' => int, 'errors' => string[]]
 */
function importMCQsFromCSV(string $filePath): array {
    $handle = fopen($filePath, 'r');
    if (!$handle) {
        return ['inserted' => 0, 'errors' => ['Could not open file.']];
    }

    $inserted = 0;
    $errors   = [];
    $lineNum  = 0;

    // Skip header row
    fgetcsv($handle);

    while (($row = fgetcsv($handle)) !== false) {
        $lineNum++;
        if (count($row) < 7) {
            $errors[] = "Line $lineNum: insufficient columns (expected 7).";
            continue;
        }

        [$topic, $question, $optA, $optB, $optC, $optD, $correct] = array_map('trim', $row);
        $correct = strtolower($correct);

        if (!in_array($correct, ['a','b','c','d'], true)) {
            $errors[] = "Line $lineNum: invalid correct_option '$correct' (must be a/b/c/d).";
            continue;
        }

        $result = execute(
            'INSERT INTO mcqs (topic, question, option_a, option_b, option_c, option_d, correct_option)
             VALUES (?, ?, ?, ?, ?, ?, ?)',
            'sssssss',
            $topic, $question, $optA, $optB, $optC, $optD, $correct
        );

        if ($result === false) {
            $errors[] = "Line $lineNum: database insert failed.";
        } else {
            $inserted++;
        }
    }

    fclose($handle);
    return ['inserted' => $inserted, 'errors' => $errors];
}
