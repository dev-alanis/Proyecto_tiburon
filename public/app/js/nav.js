/**
 * Nav — barra superior con marca, links y usuario.
 * Usa window.location.href para navegar.
 */
const Nav = {
    links: [
        { key: 'dashboard',     label: 'Inicio',         href: 'dashboard.html' },
        { key: 'tanques',       label: 'Tanques',        href: 'tanques.html' },
        { key: 'peces',         label: 'Peces',          href: 'peces.html' },
        { key: 'especies',      label: 'Especies',       href: 'especies.html' },
        { key: 'pez-tanque',    label: 'Pez–Tanque',     href: 'pez-tanque.html' },
        { key: 'alimentacion',  label: 'Alimentación',   href: 'alimentacion.html' },
        { key: 'mantenimiento', label: 'Mantenimiento',  href: 'mantenimiento.html' },
        { key: 'equipamiento',  label: 'Equipamiento',   href: 'equipamiento.html' },
        { key: 'articulos',     label: 'Artículos',      href: 'articulos.html' },
        { key: 'inventario',    label: 'Inventario',     href: 'inventario.html' },
    ],

    adminLinks: [
        { key: 'empleados', label: 'Empleados', href: 'empleados.html' },
        { key: 'usuarios',  label: 'Usuarios',  href: 'usuarios.html' },
    ],

    go(href) { window.location.href = href; },

    render(activeKey = '') {
        const host = document.getElementById('nav');
        if (!host) return;

        const user = Auth.user();
        const links = Auth.isAdmin()
            ? [...Nav.links, ...Nav.adminLinks]
            : Nav.links;

        const nav = UI.el('div', { class: 'topbar' });

        const brand = UI.el('div', {
            class: 'brand',
            onclick: () => Nav.go('dashboard.html'),
        }, [
            UI.el('span', { html: sharkMiniSVG() }),
            UI.el('span', { text: 'Acuario Tiburón Feliz' }),
        ]);

        const navLinks = UI.el('nav');
        links.forEach((l) => {
            const a = UI.el('a', {
                class: l.key === activeKey ? 'active' : '',
                text: l.label,
                onclick: () => Nav.go(l.href),
            });
            navLinks.appendChild(a);
        });

        const userBox = UI.el('div', { class: 'user' }, [
            UI.el('span', { class: 'name', text: user.nombre_completo || user.nombre_usuario || '—' }),
            UI.el('span', { class: 'role', text: user.rol || '' }),
            UI.el('button', {
                class: 'btn ghost sm',
                style: 'color:#fff',
                text: 'Salir',
                onclick: () => Auth.logout(),
            }),
        ]);

        nav.appendChild(brand);
        nav.appendChild(navLinks);
        nav.appendChild(userBox);
        host.appendChild(nav);
    },
};

function sharkMiniSVG() {
    return `
    <svg viewBox="0 0 240 100" xmlns="http://www.w3.org/2000/svg">
      <path d="M 220 55 Q 200 40 180 42 L 70 45 L 30 15 L 45 50 L 25 85 L 65 68
               Q 110 78 170 75 Q 205 70 220 55 Z" fill="currentColor"/>
      <path d="M 90 45 L 100 10 L 130 45 Z" fill="currentColor"/>
      <circle cx="195" cy="52" r="3" fill="#fff"/>
    </svg>`;
}