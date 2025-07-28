define(['jquery'], function ($) {
    return {
        init: function (params) {
            const interval = params.interval || 5000;
            const quizid = params.quizid;
            const cmid = params.cmid;
            const userid = params.userid;
            const attemptid = params.attemptid;
            const token = params.token;
            const uploadurl = params.uploadurl;

            let startButtonClicked = false;
            let extensionResponded = false;

            function sendStartCapture() {
                const metadata = {
                    interval: interval,
                    quizid: quizid,
                    cmid: cmid,
                    userid: userid,
                    attemptid: attemptid,
                    uploadurl: uploadurl
                };

                // Listen for extension reply
                function waitForExtensionResponse() {
                    setTimeout(() => {
                        if (!extensionResponded) {
                            alert('⚠️ Moodle Screen Capture Extension is not installed or not running.\n\nPlease install and enable the extension before starting the quiz.');
                            startButtonClicked = false;
                        }
                    }, 1000); // 1 second timeout
                }

                extensionResponded = false; // reset
                window.postMessage({
                    type: 'FROM_MOODLE',
                    action: 'startCapture',
                    interval: interval,
                    meta: metadata
                }, '*');
                waitForExtensionResponse();
            }

            function sendStopCapture() {
                window.postMessage({
                    type: 'FROM_MOODLE',
                    action: 'stopCapture'
                }, '*');
            }

            // Trigger on start button click
            $(document).on('click', 'form[action*="startattempt.php"] button[type=submit]', function (e) {
                e.preventDefault();
                if (!startButtonClicked) {
                    sendStartCapture();
                    startButtonClicked = true;
                }
            });

            // Listen for extension response
            window.addEventListener('message', function (event) {
                if (event.source !== window || !event.data || event.data.type !== 'FROM_EXTENSION') return;

                const msg = event.data;

                if (msg.status === 'extensionPresent') {
                    extensionResponded = true; // ✅ Extension responded early
                    console.log('🧩 Extension confirmed installed');
                }

                if (msg.status === 'captureStarted' || msg.status === 'already_open') {
                    extensionResponded = true;
                    const form = $('form[action*="startattempt.php"]')[0];
                    if (form) form.submit();
                }

                if (msg.status === 'captureFailed') {
                    extensionResponded = true;
                    alert('❌ Screen sharing was cancelled or failed. You are being redirected to the quiz page.');

                    const cmid = params.cmid;
                    if (cmid) {
                        window.location.href = M.cfg.wwwroot + '/mod/quiz/view.php?id=' + cmid;
                    } else {
                        window.location.href = M.cfg.wwwroot + '/mod/quiz/';
                    }
                }
            });

            $(document).on('click', 'button[type=submit]', function () {
                const text = this.textContent.trim().toLowerCase();
                if (text.includes('submit all and finish') || text.includes('finish attempt') || text.includes('submit quiz')) {
                    sendStopCapture();
                }
            });
            if (window.location.pathname.includes('attempt.php')) {
                console.log('📌 This is attempt page');

                // Retry-based ping popup status checker
                function checkPopupOpenStatus(maxRetries = 3, delay = 500) {
                    return new Promise((resolve) => {
                        let attempt = 0;

                        function tryPing() {
                            let responded = false;

                            function onMessage(event) {
                                if (event.source !== window || !event.data || event.data.type !== 'FROM_EXTENSION') return;

                                if (event.data.status === 'popupStatus') {
                                    window.removeEventListener('message', onMessage);
                                    responded = true;
                                    resolve(event.data.popupOpen);
                                }
                            }

                            window.addEventListener('message', onMessage);

                            // Send pingPopup message to extension
                            window.postMessage({
                                type: 'FROM_MOODLE',
                                action: 'pingPopup'
                            }, '*');

                            // Timeout fallback if no response
                            setTimeout(() => {
                                window.removeEventListener('message', onMessage);
                                if (!responded) {
                                    attempt++;
                                    if (attempt < maxRetries) {
                                        setTimeout(tryPing, delay); // Retry after delay
                                    } else {
                                        resolve(false); // All attempts failed
                                    }
                                }
                            }, 1000); // Wait 1s for response
                        }

                        tryPing(); // Start first attempt
                    });
                }

                // Run popup status check
                checkPopupOpenStatus().then(isOpen => {
                    console.log('📡 Extension popup open?', isOpen);
                    if (!isOpen) {
                        console.warn('⚠️ Extension popup is NOT open.');
                        alert('⚠️ You must enable screen sharing to attempt this quiz.\n\nRedirecting to quiz page.');

                        const cmid = params.cmid;
                        if (cmid) {
                            window.location.href = M.cfg.wwwroot + '/mod/quiz/view.php?id=' + cmid;
                        } else {
                            window.location.href = M.cfg.wwwroot + '/mod/quiz/';
                        }
                    }
                });
            }
        }
    };
});
