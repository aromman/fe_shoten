/*!
    * Start Bootstrap - SB Admin v7.0.5 (https://startbootstrap.com/template/sb-admin)
    * Copyright 2013-2022 Start Bootstrap
    * Licensed under MIT (https://github.com/StartBootstrap/startbootstrap-sb-admin/blob/master/LICENSE)
    */
    // 
// Scripts
// 

const initializeSaikoNavigation = () => {
    // Algunas vistas heredadas vuelven a cargar este archivo al final del HTML.
    // Evita inicializar y reestructurar la navegación más de una vez.
    if (document.documentElement.dataset.saikoNavigationInitialized === 'true') {
        return;
    }
    document.documentElement.dataset.saikoNavigationInitialized = 'true';

    // Toggle the side navigation
    const sidebarToggle = document.body.querySelector('#sidebarToggle');
    if (sidebarToggle) {
        const desktopSidebarIsCollapsed = localStorage.getItem('sb|sidebar-toggle') === 'true';

        if (window.matchMedia('(min-width: 992px)').matches && desktopSidebarIsCollapsed) {
            document.body.classList.add('sb-sidenav-toggled');
        }

        sidebarToggle.setAttribute('type', 'button');
        sidebarToggle.setAttribute('aria-label', 'Mostrar u ocultar el menú principal');
        sidebarToggle.setAttribute('aria-controls', 'layoutSidenav_nav');
        sidebarToggle.setAttribute('aria-expanded', String(!document.body.classList.contains('sb-sidenav-toggled')));

        sidebarToggle.addEventListener('click', event => {
            event.preventDefault();
            document.body.classList.toggle('sb-sidenav-toggled');
            sidebarToggle.setAttribute('aria-expanded', String(!document.body.classList.contains('sb-sidenav-toggled')));
            localStorage.setItem('sb|sidebar-toggle', document.body.classList.contains('sb-sidenav-toggled'));
        });
    }

    // Turn every sidebar heading and its links into an expandable menu section.
    const sidebarNav = document.querySelector('#sidenavAccordion .sb-sidenav-menu > .nav');
    if (sidebarNav) {
        const headings = Array.from(sidebarNav.querySelectorAll(':scope > .sb-sidenav-menu-heading'));
        const currentPath = window.location.pathname.replace(/\/$/, '').toLowerCase();
        const menuSections = [];

        headings.forEach((heading, index) => {
            const sectionId = `sidebar-section-${index}`;
            const section = document.createElement('div');
            const button = document.createElement('button');
            let sibling = heading.nextElementSibling;

            section.className = 'sb-sidenav-menu-section';
            section.id = sectionId;

            while (sibling && !sibling.classList.contains('sb-sidenav-menu-heading')) {
                const nextSibling = sibling.nextElementSibling;
                section.appendChild(sibling);
                sibling = nextSibling;
            }

            heading.parentNode.insertBefore(section, sibling);

            button.className = 'sb-sidenav-menu-toggle';
            button.type = 'button';
            button.innerHTML = `<span>${heading.textContent.trim()}</span><span class="sb-sidenav-menu-chevron" aria-hidden="true"></span>`;
            button.setAttribute('aria-controls', sectionId);
            heading.replaceChildren(button);

            const containsCurrentPage = Array.from(section.querySelectorAll('a[href]')).some(link => {
                const linkPath = new URL(link.href, window.location.href).pathname.replace(/\/$/, '').toLowerCase();
                const isCurrent = linkPath === currentPath;
                if (isCurrent) {
                    link.classList.add('active');
                    link.setAttribute('aria-current', 'page');
                }
                return isCurrent;
            });
            const storageKey = `sb|menu-section|${heading.textContent.trim()}|${index}`;
            const storedState = localStorage.getItem(storageKey);
            const expanded = containsCurrentPage || storedState === 'open';
            const menuArea = heading.dataset.menuArea;

            heading.dataset.menuArea = menuArea;
            section.dataset.menuArea = menuArea;
            section.dataset.expanded = String(expanded);
            section.hidden = !expanded;
            heading.classList.toggle('expanded', expanded);
            button.setAttribute('aria-expanded', String(expanded));

            button.addEventListener('click', () => {
                const shouldExpand = section.dataset.expanded !== 'true';
                section.dataset.expanded = String(shouldExpand);
                section.hidden = !shouldExpand;
                heading.classList.toggle('expanded', shouldExpand);
                button.setAttribute('aria-expanded', String(shouldExpand));
                localStorage.setItem(storageKey, shouldExpand ? 'open' : 'closed');
            });

            section.querySelectorAll('a[href]').forEach(link => {
                link.addEventListener('click', () => localStorage.setItem('sb|menu-area', menuArea));
            });

            menuSections.push({ heading, section, menuArea, containsCurrentPage });
        });

        const areaButtons = Array.from(document.querySelectorAll('.topbar-section[data-menu-area]'));
        const availableAreas = new Set(menuSections.map(item => item.menuArea));
        const currentArea = menuSections.find(item => item.containsCurrentPage)?.menuArea;
        const storedArea = localStorage.getItem('sb|menu-area');
        // La página actual define el área activa. Esto evita que una selección
        // guardada de una sesión anterior prevalezca al ingresar con otro rol.
        let selectedArea = currentArea || (availableAreas.has(storedArea) ? storedArea : undefined);

        if (!selectedArea) {
            selectedArea = areaButtons.find(button => availableAreas.has(button.dataset.menuArea))?.dataset.menuArea;
        }

        const selectMenuArea = menuArea => {
            localStorage.setItem('sb|menu-area', menuArea);
            sidebarNav.dataset.activeMenuArea = menuArea;
            const activeAreaButton = areaButtons.find(button => button.dataset.menuArea === menuArea);
            const sidebarAreaTitle = document.querySelector('#sidebarAreaTitle');
            const sidebarAreaIcon = document.querySelector('#sidebarAreaIcon');

            if (activeAreaButton && sidebarAreaTitle) {
                sidebarAreaTitle.textContent = activeAreaButton.querySelector('span')?.textContent || activeAreaButton.getAttribute('aria-label');
            }
            if (activeAreaButton && sidebarAreaIcon) {
                const activeAreaIcon = activeAreaButton.querySelector('svg, i');
                if (activeAreaIcon) {
                    sidebarAreaIcon.replaceChildren(activeAreaIcon.cloneNode(true));
                }
            }

            menuSections.forEach(item => {
                const areaIsVisible = item.menuArea === menuArea;
                const sectionIsExpanded = item.section.dataset.expanded === 'true';
                item.heading.hidden = !areaIsVisible;
                item.heading.setAttribute('aria-hidden', String(!areaIsVisible));
                item.heading.classList.toggle('menu-area-hidden', !areaIsVisible);
                item.section.setAttribute('aria-hidden', String(!areaIsVisible));
                item.section.classList.toggle('menu-area-hidden', !areaIsVisible);
                item.section.hidden = !areaIsVisible || !sectionIsExpanded;
            });

            areaButtons.forEach(button => {
                const isAvailable = availableAreas.has(button.dataset.menuArea);
                const isActive = button.dataset.menuArea === menuArea;
                button.hidden = !isAvailable;
                button.classList.toggle('active', isActive);
                if (isActive) {
                    button.setAttribute('aria-current', 'true');
                } else {
                    button.removeAttribute('aria-current');
                }
            });
        };

        areaButtons.forEach(button => {
            button.addEventListener('click', () => {
                selectMenuArea(button.dataset.menuArea);

                const desktopView = window.matchMedia('(min-width: 992px)').matches;
                document.body.classList.toggle('sb-sidenav-toggled', !desktopView);
                sidebarToggle?.setAttribute('aria-expanded', 'true');
                if (desktopView) {
                    localStorage.setItem('sb|sidebar-toggle', 'false');
                }
            });
        });

        if (selectedArea) {
            selectMenuArea(selectedArea);
        }
    }

};

// Cuando el script se carga desde sidebar.php, la navegación ya existe y se
// puede activar sin esperar a que termine de renderizar el contenido principal.
if (document.querySelector('#sidenavAccordion')) {
    initializeSaikoNavigation();
} else {
    window.addEventListener('DOMContentLoaded', initializeSaikoNavigation);
}
