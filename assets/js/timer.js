/**
 * timer.js
 * Aspirian.pk Online Test System
 * Countdown timer for the test interface
 */

(function () {
    'use strict';

    const timerBox = document.getElementById('countdown-timer');
    if (!timerBox) return;

    // Total seconds passed from the PHP page via data attribute
    let totalSeconds = parseInt(timerBox.dataset.seconds, 10) || 1800;

    /**
     * Format seconds into MM:SS string
     */
    function formatTime(sec) {
        const m = Math.floor(sec / 60).toString().padStart(2, '0');
        const s = (sec % 60).toString().padStart(2, '0');
        return m + ':' + s;
    }

    /**
     * Update the timer display and class
     */
    function updateDisplay() {
        timerBox.textContent = formatTime(totalSeconds);

        // Colour coding: green > 5 min, orange 2-5 min, red < 2 min
        timerBox.className = 'timer-box';
        if (totalSeconds > 300) {
            timerBox.classList.add('ok');
        } else if (totalSeconds > 120) {
            timerBox.classList.add('warning');
        }
        // default (red) is set in CSS via .timer-box base style
    }

    updateDisplay();

    const interval = setInterval(function () {
        totalSeconds--;
        updateDisplay();

        if (totalSeconds <= 0) {
            clearInterval(interval);
            timerBox.textContent = '00:00';

            // Auto-submit the test form when time runs out
            const form = document.getElementById('test-form');
            if (form) {
                // Show notification
                const notice = document.createElement('div');
                notice.setAttribute('style',
                    'position:fixed;top:0;left:0;width:100%;background:#dc2626;' +
                    'color:#fff;text-align:center;padding:14px;font-size:1rem;' +
                    'font-weight:700;z-index:9999;');
                notice.textContent = 'Time is up! Submitting your test automatically...';
                document.body.prepend(notice);

                setTimeout(function () { form.submit(); }, 1500);
            }
        }
    }, 1000);

    /**
     * Warn user before leaving the page during a test
     */
    window.addEventListener('beforeunload', function (e) {
        const form = document.getElementById('test-form');
        if (form && totalSeconds > 0) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    /**
     * Remove beforeunload warning when form is intentionally submitted
     */
    const form = document.getElementById('test-form');
    if (form) {
        form.addEventListener('submit', function () {
            window.onbeforeunload = null;
        });
    }
})();
