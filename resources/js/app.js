import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

const PST_SIDEBAR_STORAGE_KEY = 'pst-sidebar-open';

Alpine.data('pstSidebar', () => {
    let initialOpen = false;
    try {
        initialOpen = window.localStorage.getItem(PST_SIDEBAR_STORAGE_KEY) === 'true';
    } catch (e) {
        /* ignore */
    }

    return {
        open: initialOpen,

        init() {
            this.$nextTick(() => {
                window.dispatchEvent(
                    new CustomEvent('sidebar-toggled', { detail: this.open }),
                );
            });
        },

        persistSidebar() {
            try {
                window.localStorage.setItem(
                    PST_SIDEBAR_STORAGE_KEY,
                    this.open ? 'true' : 'false',
                );
            } catch (e) {
                /* ignore */
            }
            window.dispatchEvent(
                new CustomEvent('sidebar-toggled', { detail: this.open }),
            );
        },

        toggleSidebar() {
            this.open = !this.open;
            this.persistSidebar();
        },

        closeSidebarDrawer() {
            this.open = false;
            this.persistSidebar();
        },
    };
});

Alpine.start();
