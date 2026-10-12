/**
 * UI — helpers de DOM, toasts y modales.
 */
const UI = {
    el(tag, props = {}, children = []) {
        const node = document.createElement(tag);
        for (const [k, v] of Object.entries(props)) {
            if (k === 'class') node.className = v;
            else if (k === 'html') node.innerHTML = v;
            else if (k === 'text') node.textContent = v;
            else if (k.startsWith('on') && typeof v === 'function') {
                node.addEventListener(k.slice(2).toLowerCase(), v);
            } else if (v !== null && v !== undefined) {
                node.setAttribute(k, v);
            }
        }
        (Array.isArray(children) ? children : [children]).forEach((c) => {
            if (c == null) return;
            node.appendChild(typeof c === 'string' ? document.createTextNode(c) : c);
        });
        return node;
    },

    escape(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    },

    fmtDate(v) {
        if (!v) return '—';
        const d = new Date(v.replace ? v.replace(' ', 'T') : v);
        if (isNaN(d)) return v;
        return d.toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' });
    },

    fmtDateTime(v) {
        if (!v) return '—';
        const d = new Date(v.replace ? v.replace(' ', 'T') : v);
        if (isNaN(d)) return v;
        return d.toLocaleString('es-MX', {
            day: '2-digit', month: 'short', year: 'numeric',
            hour: '2-digit', minute: '2-digit',
        });
    },

    badgeBool(val) {
        return val
            ? '<span class="badge activo">Activo</span>'
            : '<span class="badge inactivo">Inactivo</span>';
    },

    /* ---------- Toasts ---------- */
    _toastHost() {
        let host = document.querySelector('.toasts');
        if (!host) {
            host = document.createElement('div');
            host.className = 'toasts';
            document.body.appendChild(host);
        }
        return host;
    },

    toast(message, type = 'info', ms = 3000) {
        const node = UI.el('div', { class: `toast ${type}`, text: message });
        UI._toastHost().appendChild(node);
        setTimeout(() => node.remove(), ms);
    },

    success(m) { UI.toast(m, 'success'); },
    error(m)   { UI.toast(m, 'error', 4000); },
    warn(m)    { UI.toast(m, 'warn'); },

    /* ---------- Modal ---------- */
    openModal({ title, bodyHTML, onConfirm, confirmText = 'Guardar', confirmClass = 'primary' }) {
        document.querySelectorAll('.modal-backdrop').forEach((n) => n.remove());

        const form = UI.el('form', {
            class: 'modal',
            onsubmit: (e) => {
                e.preventDefault();
                if (onConfirm) onConfirm();
            },
        });

        const header = UI.el('div', { class: 'modal-header' }, [
            UI.el('h3', { text: title }),
            UI.el('button', {
                class: 'close', type: 'button', text: '×',
                onclick: UI.closeModal,
            }),
        ]);

        const body = UI.el('div', { class: 'modal-body', html: bodyHTML });

        const footer = UI.el('div', { class: 'modal-footer' }, [
            UI.el('button', {
                class: 'btn secondary', type: 'button', text: 'Cancelar',
                onclick: UI.closeModal,
            }),
            UI.el('button', {
                class: `btn ${confirmClass}`, type: 'submit', text: confirmText,
            }),
        ]);

        form.appendChild(header);
        form.appendChild(body);
        form.appendChild(footer);

        const backdrop = UI.el('div', {
            class: 'modal-backdrop open',
            onclick: (e) => { if (e.target === backdrop) UI.closeModal(); },
        }, [form]);

        document.body.appendChild(backdrop);
        return form;
    },

    closeModal() {
        document.querySelectorAll('.modal-backdrop').forEach((n) => n.remove());
    },

    confirm(message) {
        return new Promise((resolve) => {
            UI.openModal({
                title: 'Confirmar',
                bodyHTML: `<p>${UI.escape(message)}</p>`,
                confirmText: 'Eliminar',
                confirmClass: 'danger',
                onConfirm: () => { UI.closeModal(); resolve(true); },
            });
            // Cancelar también resuelve false
            document.querySelectorAll('.modal-backdrop .close, .modal-backdrop .btn.secondary')
                .forEach((b) => b.addEventListener('click', () => resolve(false)));
        });
    },
};