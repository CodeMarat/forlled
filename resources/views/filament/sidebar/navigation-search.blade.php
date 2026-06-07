<div
    x-cloak
    x-show="$store.sidebar.isOpen"
    class="px-3 pb-4"
    data-sidebar-navigation-search
>
    <label for="sidebar-navigation-search" class="fi-sr-only">
        Search pages
    </label>

    <x-filament::input.wrapper
        :prefix-icon="\Filament\Support\Icons\Heroicon::MagnifyingGlass"
        :prefix-icon-alias="\Filament\View\PanelsIconAlias::GLOBAL_SEARCH_FIELD"
        inline-prefix
    >
        <input
            id="sidebar-navigation-search"
            type="search"
            autocomplete="off"
            placeholder="Search pages"
            class="fi-input fi-input-has-inline-prefix"
            data-sidebar-navigation-search-input
        />
    </x-filament::input.wrapper>
</div>

<script>
    (() => {
        const boot = () => {
            const normalize = (value) => value.trim().toLowerCase()
            const sidebarStore = () => window.Alpine?.store?.('sidebar') ?? null

            const getTopLevelItems = (group) =>
                Array.from(group.querySelectorAll(':scope > .fi-sidebar-group-items > .fi-sidebar-item'))

            const setItemVisibility = (item, isVisible) => {
                item.style.display = isVisible ? '' : 'none'
            }

            const setGroupVisibility = (group, isVisible) => {
                group.style.display = isVisible ? '' : 'none'
            }

            const expandGroupsForSearch = (state) => {
                const store = sidebarStore()

                if (! store || state.collapsedGroupsSnapshot !== null) {
                    return
                }

                state.collapsedGroupsSnapshot = [...(store.collapsedGroups ?? [])]
                store.collapsedGroups = []
            }

            const restoreGroupsAfterSearch = (state) => {
                const store = sidebarStore()

                if (! store || state.collapsedGroupsSnapshot === null) {
                    return
                }

                store.collapsedGroups = state.collapsedGroupsSnapshot
                state.collapsedGroupsSnapshot = null
            }

            const applyFilter = (input) => {
                const state = input.__sidebarNavigationSearchState
                const term = normalize(input.value)
                const groups = Array.from(document.querySelectorAll('.fi-main-sidebar .fi-sidebar-group'))

                if (term.length) {
                    expandGroupsForSearch(state)
                } else {
                    restoreGroupsAfterSearch(state)
                }

                groups.forEach((group) => {
                    const groupLabel = normalize(group.querySelector('.fi-sidebar-group-label')?.textContent ?? '')
                    const items = getTopLevelItems(group)
                    const matchesGroup = term.length && groupLabel.includes(term)

                    let hasVisibleItem = false

                    items.forEach((item) => {
                        const itemLabel = normalize(item.querySelector('.fi-sidebar-item-label')?.textContent ?? '')
                        const isVisible = ! term.length || matchesGroup || itemLabel.includes(term)

                        setItemVisibility(item, isVisible)

                        if (isVisible) {
                            hasVisibleItem = true
                        }
                    })

                    setGroupVisibility(group, ! term.length || matchesGroup || hasVisibleItem)
                })
            }

            const initializeInput = (input) => {
                if (input.dataset.sidebarNavigationSearchReady === 'true') {
                    return
                }

                input.dataset.sidebarNavigationSearchReady = 'true'
                input.__sidebarNavigationSearchState = {
                    collapsedGroupsSnapshot: null,
                }

                input.addEventListener('input', () => applyFilter(input))
                applyFilter(input)
            }

            const initializeSidebarSearch = () => {
                document
                    .querySelectorAll('[data-sidebar-navigation-search-input]')
                    .forEach((input) => initializeInput(input))
            }

            if (! window.__forlledSidebarNavigationSearchBooted) {
                window.__forlledSidebarNavigationSearchBooted = true

                document.addEventListener('livewire:navigated', () => {
                    requestAnimationFrame(initializeSidebarSearch)
                })
            }

            requestAnimationFrame(initializeSidebarSearch)
        }

        boot()
    })()
</script>
