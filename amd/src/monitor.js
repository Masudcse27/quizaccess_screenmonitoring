define(['jquery'], function ($) {
    return {
        init: function (params) {
            const interval = params.interval || 5000;
            
            let startButtonClicked = false;

            function sendStartCapture() {
                

                console.log('📤 Sending startCapture request to extension...');
                window.postMessage({
                    type: 'FROM_MOODLE',
                    action: 'startCapture',
                    interval: interval
                }, '*');
            }

            function sendStopCapture() {
                console.log('📤 Sending stopCapture request to extension...');
                window.postMessage({
                    type: 'FROM_MOODLE',
                    action: 'stopCapture'
                }, '*');
            }

            $(document).on('click', 'form[action*="startattempt.php"] button[type=submit]', function (e) {
                e.preventDefault();
                if (!startButtonClicked) {
                    sendStartCapture();
                    startButtonClicked = true;
                }
            });

            window.addEventListener('message', function (event) {
                if (event.source !== window || !event.data || event.data.type !== 'FROM_EXTENSION') return;
                const msg = event.data;

                if (msg.status === 'captureStarted') {
                    const form = $('form[action*="startattempt.php"]')[0];
                    if (form) {
                        form.submit();
                    }
                } else if (msg.status === 'captureFailed') {
                    alert('Screen sharing was cancelled or failed. Please try again.');
                    startButtonClicked = false;
                }
            });

            $(document).on('click', 'button[type=submit]', function () {
                const text = this.textContent.trim().toLowerCase();
                if (text.includes('submit all and finish') || text.includes('finish attempt') || text.includes('submit quiz')) {
                    sendStopCapture();
                }
            });
        }
    };
});
