    <script src="{{ asset('admin-assets/vendor/js/bootstrap.bundle.min.js') }}"></script>
    <script>
        // #region debug-point admin-login-hangs-runtime
        (function () {
            if (!['127.0.0.1', 'localhost'].includes(window.location.hostname)) {
                return;
            }

            const endpoint = 'http://127.0.0.1:7778/event';

            const toMeta = function (element) {
                if (!element) {
                    return null;
                }

                return {
                    tag: element.tagName,
                    id: element.id || '',
                    className: typeof element.className === 'string' ? element.className : '',
                };
            };

            const send = function (point, detail) {
                try {
                    fetch(endpoint, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        keepalive: true,
                        body: JSON.stringify({
                            sessionId: 'admin-login-hangs',
                            point,
                            href: window.location.href,
                            tsClient: Date.now(),
                            detail,
                        }),
                    }).catch(function () {});
                } catch (_) {}
            };

            const inspect = function (phase) {
                const form = document.querySelector('form[action*="/admin/login"]');
                const email = document.querySelector('input[name="email"]');
                const password = document.querySelector('input[name="password"]');
                const button = form ? form.querySelector('button[type="submit"]') : null;

                send('page-state', {
                    phase,
                    readyState: document.readyState,
                    visibilityState: document.visibilityState,
                    topElement: toMeta(document.elementFromPoint(Math.round(window.innerWidth / 2), 24)),
                    form: form ? {
                        action: form.getAttribute('action'),
                        method: form.getAttribute('method'),
                    } : null,
                    email: email ? {
                        disabled: email.disabled,
                        readOnly: email.readOnly,
                    } : null,
                    password: password ? {
                        disabled: password.disabled,
                        readOnly: password.readOnly,
                    } : null,
                    submitButton: button ? {
                        disabled: button.disabled,
                        text: (button.textContent || '').trim().slice(0, 80),
                    } : null,
                    perf: window.performance && performance.timing ? {
                        domContentLoadedMs: performance.timing.domContentLoadedEventEnd && performance.timing.navigationStart
                            ? performance.timing.domContentLoadedEventEnd - performance.timing.navigationStart
                            : null,
                        loadMs: performance.timing.loadEventEnd && performance.timing.navigationStart
                            ? performance.timing.loadEventEnd - performance.timing.navigationStart
                            : null,
                    } : null,
                });
            };

            document.addEventListener('DOMContentLoaded', function () {
                inspect('dom-content-loaded');
            }, { once: true });

            window.addEventListener('load', function () {
                inspect('window-load');
            }, { once: true });

            document.addEventListener('submit', function (event) {
                const form = event.target.closest ? event.target.closest('form') : null;

                send('submit', {
                    target: toMeta(event.target),
                    formAction: form ? form.getAttribute('action') : null,
                    method: form ? form.getAttribute('method') : null,
                    defaultPrevented: event.defaultPrevented,
                });
            }, true);

            document.addEventListener('focusin', function (event) {
                if (!event.target.matches || !event.target.matches('input, button, a')) {
                    return;
                }

                send('focusin', {
                    target: toMeta(event.target),
                });
            }, true);

            window.addEventListener('pagehide', function () {
                inspect('page-hide');
            });
        })();
        // #endregion
    </script>
</body>

</html>
