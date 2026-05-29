import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

const PST_SIDEBAR_STORAGE_KEY = 'pst-sidebar-open';

function pstSidebarInitialOpen() {
    try {
        const stored = window.localStorage.getItem(PST_SIDEBAR_STORAGE_KEY);
        if (stored === 'false') {
            return false;
        }
        if (stored === 'true') {
            return true;
        }
        // No preference yet: expanded rail on md+, drawer closed on small screens.
        return window.matchMedia('(min-width: 768px)').matches;
    } catch (e) {
        return window.matchMedia('(min-width: 768px)').matches;
    }
}

Alpine.data('pstSidebar', () => {
    const initialOpen = pstSidebarInitialOpen();

    return {
        open: initialOpen,
        salesGroupOpen: false,
        purchasesGroupOpen: false,

        init() {
            try {
                const path = window.location.pathname || '';
                // Only expand the group that matches the current page (collapse when navigating away).
                this.salesGroupOpen =
                    path.startsWith('/sales-quotations') ||
                    path === '/sales' ||
                    path.startsWith('/sales/');
                this.purchasesGroupOpen = path.startsWith('/purchases');
            } catch (e) {
                /* ignore */
            }
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
