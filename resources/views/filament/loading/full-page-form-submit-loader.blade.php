<div
    data-admin-form-loader
    class="pointer-events-none fixed inset-0 z-[110] hidden items-center justify-center bg-white/80 backdrop-blur-xs dark:bg-gray-950/80"
>
    <div class="flex max-w-sm flex-col items-center gap-4 rounded-2xl bg-white px-8 py-7 text-center shadow-2xl ring-1 ring-gray-200 dark:bg-gray-900 dark:ring-white/10">
        <div class="h-12 w-12 animate-spin rounded-full border-4 border-gray-200 border-t-primary-600 dark:border-gray-700 dark:border-t-primary-400"></div>

        <div class="space-y-1">
            <p class="text-base font-semibold text-gray-950 dark:text-white">
                Saving changes
            </p>

            <p class="text-sm text-gray-600 dark:text-gray-300">
                Please stay on this page while your content is being processed.
            </p>
        </div>
    </div>
</div>

<script>
    (() => {
        if (window.__forlledAdminFormLoaderBooted) {
            return;
        }

        window.__forlledAdminFormLoaderBooted = true;

        let shouldShowForNextRequest = false;
        let activeRequests = 0;

        const resolveOverlay = () => document.querySelector('[data-admin-form-loader]');

        const showOverlay = () => {
            const overlay = resolveOverlay();

            if (!overlay) {
                return;
            }

            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
        };

        const hideOverlay = () => {
            const overlay = resolveOverlay();

            if (!overlay) {
                return;
            }

            overlay.classList.add('hidden');
            overlay.classList.remove('flex');
        };

        document.addEventListener('submit', (event) => {
            const form = event.target;

            if (!(form instanceof HTMLFormElement)) {
                return;
            }

            if (!form.closest('.fi-main')) {
                return;
            }

            shouldShowForNextRequest = true;
        }, true);

        document.addEventListener('livewire:init', () => {
            if (!window.Livewire?.hook) {
                return;
            }

            window.Livewire.hook('request', ({ succeed, fail }) => {
                if (!shouldShowForNextRequest) {
                    return;
                }

                shouldShowForNextRequest = false;
                activeRequests++;
                showOverlay();

                const finish = () => {
                    activeRequests = Math.max(0, activeRequests - 1);

                    if (activeRequests === 0) {
                        hideOverlay();
                    }
                };

                const queueFinish = () => {
                    requestAnimationFrame(() => {
                        queueMicrotask(finish);
                    });
                };

                succeed(queueFinish);
                fail(queueFinish);
            });
        });

        document.addEventListener('livewire:navigated', hideOverlay);
    })();
</script>
