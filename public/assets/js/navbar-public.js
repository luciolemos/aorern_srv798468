document.addEventListener('DOMContentLoaded', () => {
    const navbar = document.querySelector('.navbar-site');
    const collapseEl = document.getElementById('navbarSite');

    if (!navbar || !collapseEl || typeof bootstrap === 'undefined') {
        return;
    }

    const toggler = navbar.querySelector('.navbar-toggler');
    const collapse = bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle: false });
    const mobileBreakpoint = window.matchMedia('(max-width: 991.98px)');
    const desktopDropdownToggles = navbar.querySelectorAll('.nav-item.dropdown.d-none.d-lg-block [data-bs-toggle="dropdown"]');
    const mobileSubnavTriggers = collapseEl.querySelectorAll('.mobile-subnav-trigger[data-mobile-subnav-target]');

    const isMobile = () => mobileBreakpoint.matches;

    const syncBodyState = () => {
        document.body.classList.toggle('navbar-mobile-open', isMobile() && collapseEl.classList.contains('show'));
    };

    const syncMobileSubnavTrigger = (trigger, expanded) => {
        trigger.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        trigger.classList.toggle('collapsed', !expanded);
    };

    const keepTriggerVisibleInMobilePanel = (trigger) => {
        if (!isMobile()) {
            return;
        }

        const panelRect = collapseEl.getBoundingClientRect();
        const triggerRect = trigger.getBoundingClientRect();
        const panelTopPadding = 8;
        const panelBottomPadding = 12;

        const triggerAboveView = triggerRect.top < panelRect.top + panelTopPadding;
        const triggerBelowView = triggerRect.bottom > panelRect.bottom - panelBottomPadding;

        if (!triggerAboveView && !triggerBelowView) {
            return;
        }

        const delta = triggerAboveView
            ? triggerRect.top - (panelRect.top + panelTopPadding)
            : triggerRect.bottom - (panelRect.bottom - panelBottomPadding);

        collapseEl.scrollTop += delta;
    };

    const closeMobileSubnavs = () => {
        collapseEl.querySelectorAll('[data-mobile-subnav].show').forEach((subnav) => {
            bootstrap.Collapse.getOrCreateInstance(subnav, { toggle: false }).hide();
        });
    };

    const closeMobileMenu = () => {
        if (!isMobile() || !collapseEl.classList.contains('show')) {
            return;
        }

        closeMobileSubnavs();
        collapse.hide();
    };

    const closeDesktopDropdowns = () => {
        desktopDropdownToggles.forEach((toggle) => {
            const dropdown = bootstrap.Dropdown.getOrCreateInstance(toggle, { autoClose: true });
            dropdown.hide();
            toggle.setAttribute('aria-expanded', 'false');
        });
    };

    const handleOutsideInteraction = (event) => {
        const target = event.target;
        if (!(target instanceof Element)) {
            return;
        }

        const clickedInsideNavbar = Boolean(target.closest('.navbar-site'));

        if (isMobile() && collapseEl.classList.contains('show') && !clickedInsideNavbar) {
            closeMobileMenu();
        }

        if (!isMobile() && !clickedInsideNavbar) {
            closeDesktopDropdowns();
        }
    };

    document.addEventListener('pointerdown', handleOutsideInteraction);

    collapseEl.querySelectorAll('a.nav-link, .mobile-subnav .dropdown-item, .navbar-mobile-action').forEach((link) => {
        link.addEventListener('click', () => {
            closeMobileMenu();
        });
    });

    collapseEl.addEventListener('hide.bs.collapse', closeMobileSubnavs);
    collapseEl.addEventListener('shown.bs.collapse', syncBodyState);
    collapseEl.addEventListener('hidden.bs.collapse', syncBodyState);

    mobileSubnavTriggers.forEach((trigger) => {
        const targetSelector = trigger.getAttribute('data-mobile-subnav-target');
        if (!targetSelector) {
            return;
        }

        const currentSubnav = collapseEl.querySelector(targetSelector);
        if (!currentSubnav) {
            return;
        }

        syncMobileSubnavTrigger(trigger, currentSubnav.classList.contains('show'));

        currentSubnav.addEventListener('shown.bs.collapse', () => {
            syncMobileSubnavTrigger(trigger, true);
            // Keep the trigger visible so users can always collapse it again.
            keepTriggerVisibleInMobilePanel(trigger);
        });

        currentSubnav.addEventListener('hidden.bs.collapse', () => {
            syncMobileSubnavTrigger(trigger, false);
        });

        trigger.addEventListener('click', (event) => {
            if (!isMobile()) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();

            const currentInstance = bootstrap.Collapse.getOrCreateInstance(currentSubnav, { toggle: false });
            const isOpen = currentSubnav.classList.contains('show');

            collapseEl.querySelectorAll('[data-mobile-subnav].show').forEach((subnav) => {
                if (subnav !== currentSubnav) {
                    bootstrap.Collapse.getOrCreateInstance(subnav, { toggle: false }).hide();
                }
            });

            if (isOpen) {
                currentInstance.hide();
                return;
            }

            currentInstance.show();
        });
    });

    desktopDropdownToggles.forEach((toggle) => {
        const dropdown = bootstrap.Dropdown.getOrCreateInstance(toggle, { autoClose: true });
        const parentItem = toggle.closest('.nav-item.dropdown');

        if (!parentItem) {
            return;
        }

        let closeTimer = null;

        const openDropdown = () => {
            if (isMobile()) {
                return;
            }

            if (closeTimer) {
                clearTimeout(closeTimer);
            }

            desktopDropdownToggles.forEach((otherToggle) => {
                if (otherToggle !== toggle) {
                    bootstrap.Dropdown.getOrCreateInstance(otherToggle, { autoClose: true }).hide();
                    otherToggle.setAttribute('aria-expanded', 'false');
                }
            });

            dropdown.show();
            toggle.setAttribute('aria-expanded', 'true');
        };

        const queueCloseDropdown = () => {
            if (isMobile()) {
                return;
            }

            closeTimer = setTimeout(() => {
                dropdown.hide();
                toggle.setAttribute('aria-expanded', 'false');
            }, 120);
        };

        parentItem.addEventListener('mouseenter', openDropdown);
        parentItem.addEventListener('mouseleave', queueCloseDropdown);

        toggle.addEventListener('click', (event) => {
            if (isMobile()) {
                return;
            }

            event.preventDefault();
            const menu = parentItem.querySelector('.dropdown-menu');
            const isOpen = Boolean(menu?.classList.contains('show'));

            if (isOpen) {
                dropdown.hide();
                toggle.setAttribute('aria-expanded', 'false');
                return;
            }

            openDropdown();
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeMobileMenu();
            closeDesktopDropdowns();
        }
    });

    mobileBreakpoint.addEventListener('change', () => {
        if (!isMobile()) {
            closeMobileSubnavs();
            syncBodyState();
            closeDesktopDropdowns();
        } else {
            closeDesktopDropdowns();
        }
    });

    toggler?.addEventListener('click', () => {
        if (!isMobile()) {
            closeMobileSubnavs();
        }
    });

    syncBodyState();
});
