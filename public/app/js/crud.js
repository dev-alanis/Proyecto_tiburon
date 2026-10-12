/**
 * Crud — Renderiza una pantalla CRUD completa a partir de un config.
 *
 * Config:
 *   endpoint:    '/empleados'
 *   columns:     [{ key, label, render?(row) }]
 *   fields:      [{ name, label, type, required?, options?, optionsFrom?, optionValue?, optionLabel?, full? }]
 *   canCreate:   roles o función
 *   canEdit:     roles o función
 *   canDelete:   roles o función (opcional)
 *   filters:     [{ name, label, type, optionsFrom?, optionValue?, optionLabel? }]
 *   transformIn?: (row) => body   -- ajusta antes de enviar
 *   transformOut?:(row) => row    -- ajusta al recibir para edición
 */
const Crud = {
    async render(config) {
        const state = { page: 1, limit: 20, items: [], total: 0, filterValues: {} };
        const app = document.getElementById('app');

        const canCreate = Crud._check(config.canCreate);
        const canEdit   = Crud._check(config.canEdit);
        const canDelete = Crud._check(config.canDelete);

        const btnNew = document.getElementById('btn-new');
        if (btnNew) {
            btnNew.style.display = canCreate ? '' : 'none';
            btnNew.onclick = () => Crud._openForm(config, null, state);
        }

        // Filtros
        if (config.filters && config.filters.length) {
            const filterHost = document.getElementById('filters');
            const fields = [];
            for (const f of config.filters) {
                fields.push(await Crud._fieldInput(f));
            }
            const btn = UI.el('button', {
                class: 'btn primary', text: 'Buscar',
                onclick: () => {
                    state.filterValues = {};
                    fields.forEach((inp) => {
                        if (inp.value) state.filterValues[inp.name] = inp.value;
                    });
                    state.page = 1;
                    Crud._load(config, state, app, canEdit, canDelete);
                },
            });
            const clear = UI.el('button', {
                class: 'btn secondary', text: 'Limpiar',
                onclick: () => {
                    fields.forEach((inp) => { inp.value = ''; });
                    state.filterValues = {};
                    state.page = 1;
                    Crud._load(config, state, app, canEdit, canDelete);
                },
            });
            filterHost.innerHTML = '';
            fields.forEach((f) => filterHost.appendChild(f.closest('.field')));
            filterHost.appendChild(UI.el('div', { class: 'field' }, [btn, clear]));
        }

        await Crud._load(config, state, app, canEdit, canDelete);
    },

    _check(rule) {
        if (rule === undefined) return true;
        if (typeof rule === 'function') return rule();
        if (Array.isArray(rule)) return Auth.hasRole(...rule);
        return !!rule;
    },

    async _fieldInput(f) {
        const wrap = UI.el('div', { class: 'field' });
        wrap.appendChild(UI.el('label', { text: f.label || f.name }));

        let input;
        if (f.optionsFrom) {
            input = UI.el('select', { name: f.name });
            input.appendChild(UI.el('option', { value: '', text: '— Todos —' }));
            try {
                const res = await API.get(f.optionsFrom);
                const list = res.data || res;
                list.forEach((o) => {
                    input.appendChild(UI.el('option', {
                        value: o[f.optionValue], text: o[f.optionLabel],
                    }));
                });
            } catch { /* silencio */ }
        } else if (f.options) {
            input = UI.el('select', { name: f.name });
            f.options.forEach((o) => {
                input.appendChild(UI.el('option', {
                    value: o.value, text: o.label,
                }));
            });
        } else {
            input = UI.el('input', {
                name: f.name,
                type: f.type || 'text',
                placeholder: f.placeholder || '',
            });
        }
        wrap.appendChild(input);
        return input;
    },

    async _load(config, state, app, canEdit, canDelete) {
        app.innerHTML = '<div class="empty">Cargando…</div>';

        const params = new URLSearchParams({
            page: state.page,
            limit: state.limit,
            ...state.filterValues,
        });

        let res;
        try {
            res = await API.get(`${config.endpoint}?${params}`);
        } catch (e) {
            app.innerHTML = `<div class="empty">Error: ${UI.escape(e.message)}</div>`;
            return;
        }

        const items = res.data || res;
        const meta  = res.meta || { page: 1, limit: state.limit, total: items.length, totalPages: 1 };

        state.items = items;
        state.total = meta.total;

        const wrap = UI.el('div', { class: 'table-wrap' });

        if (!items.length) {
            wrap.appendChild(UI.el('div', { class: 'empty', text: 'Sin registros.' }));
        } else {
            const table = UI.el('table');
            const thead = UI.el('thead');
            const headRow = UI.el('tr');
            config.columns.forEach((c) => headRow.appendChild(UI.el('th', { text: c.label })));
            if (canEdit || canDelete) headRow.appendChild(UI.el('th', { text: 'Acciones' }));
            thead.appendChild(headRow);
            table.appendChild(thead);

            const tbody = UI.el('tbody');
            items.forEach((row) => {
                const tr = UI.el('tr');
                config.columns.forEach((c) => {
                    const td = UI.el('td');
                    if (c.render) td.innerHTML = c.render(row);
                    else td.textContent = row[c.key] ?? '—';
                    tr.appendChild(td);
                });

                if (canEdit || canDelete) {
                    const actions = UI.el('td', { class: 'actions' });
                    if (canEdit) {
                        actions.appendChild(UI.el('button', {
                            class: 'btn ghost sm', text: 'Editar',
                            onclick: () => Crud._openForm(config, row, state),
                        }));
                    }
                    if (canDelete) {
                        actions.appendChild(UI.el('button', {
                            class: 'btn ghost sm', style: 'color:#d64545', text: 'Eliminar',
                            onclick: () => Crud._delete(config, row, state, app, canEdit, canDelete),
                        }));
                    }
                    tr.appendChild(actions);
                }
                tbody.appendChild(tr);
            });
            table.appendChild(tbody);
            wrap.appendChild(table);

            // Paginación
            const totalPages = meta.totalPages || 1;
            if (totalPages > 1) {
                const pag = UI.el('div', { class: 'pagination' }, [
                    UI.el('span', {
                        class: 'info',
                        text: `Página ${meta.page} de ${totalPages} · ${meta.total} registros`,
                    }),
                    UI.el('button', {
                        class: 'btn secondary sm', text: '← Anterior',
                        disabled: meta.page <= 1 ? '' : null,
                        onclick: () => {
                            if (meta.page <= 1) return;
                            state.page = meta.page - 1;
                            Crud._load(config, state, app, canEdit, canDelete);
                        },
                    }),
                    UI.el('button', {
                        class: 'btn secondary sm', text: 'Siguiente →',
                        disabled: meta.page >= totalPages ? '' : null,
                        onclick: () => {
                            if (meta.page >= totalPages) return;
                            state.page = meta.page + 1;
                            Crud._load(config, state, app, canEdit, canDelete);
                        },
                    }),
                ]);
                wrap.appendChild(pag);
            }
        }

        app.innerHTML = '';
        app.appendChild(wrap);
    },

    async _openForm(config, row, state) {
        const isEdit = !!row;

        const body = UI.el('div', { class: 'form-grid' });
        for (const f of config.fields) {
            const wrap = UI.el('div', { class: 'field' + (f.full ? ' full' : '') });
            wrap.appendChild(UI.el('label', { text: f.label + (f.required ? ' *' : '') }));

            let input;
            const value = row ? (config.transformOut ? config.transformOut(row)[f.name] : row[f.name]) : '';

            if (f.optionsFrom) {
                input = UI.el('select', { name: f.name });
                input.appendChild(UI.el('option', { value: '', text: '— Selecciona —' }));
                try {
                    const res = await API.get(f.optionsFrom);
                    const list = res.data || res;
                    list.forEach((o) => {
                        const opt = UI.el('option', {
                            value: o[f.optionValue], text: o[f.optionLabel],
                        });
                        if (String(o[f.optionValue]) === String(value)) opt.selected = true;
                        input.appendChild(opt);
                    });
                } catch { /* silencio */ }
            } else if (f.options) {
                input = UI.el('select', { name: f.name });
                f.options.forEach((o) => {
                    const opt = UI.el('option', { value: o.value, text: o.label });
                    if (String(o.value) === String(value)) opt.selected = true;
                    input.appendChild(opt);
                });
            } else if (f.type === 'textarea') {
                input = UI.el('textarea', { name: f.name });
                input.value = value ?? '';
            } else {
                input = UI.el('input', {
                    name: f.name,
                    type: f.type || 'text',
                    step: f.step || null,
                    min: f.min || null,
                    max: f.max || null,
                });
                if (f.type === 'checkbox') {
                    input.checked = !!value;
                } else {
                    input.value = value ?? '';
                }
            }
            input.required = !!f.required;
            wrap.appendChild(input);
            body.appendChild(wrap);
        }

        UI.openModal({
            title: isEdit ? 'Editar' : 'Nuevo',
            bodyHTML: '',
            confirmText: isEdit ? 'Actualizar' : 'Crear',
            onConfirm: async () => {
                const form = document.querySelector('.modal-backdrop .modal');
                const data = {};
                config.fields.forEach((f) => {
                    const el = form.querySelector(`[name="${f.name}"]`);
                    if (!el) return;
                    if (f.type === 'checkbox') {
                        data[f.name] = el.checked;
                    } else if (f.type === 'number') {
                        data[f.name] = el.value === '' ? null : Number(el.value);
                    } else {
                        data[f.name] = el.value === '' ? null : el.value;
                    }
                });

                // Quitar nulls/strings vacíos en create para no pisar defaults
                const body = config.transformIn ? config.transformIn(data, isEdit) : data;

                try {
                    if (isEdit) {
                        const id = Crud._idOf(config, row);
                        await API.put(`${config.endpoint}/${id}`, body);
                        UI.success('Actualizado');
                    } else {
                        await API.post(config.endpoint, body);
                        UI.success('Creado');
                    }
                    UI.closeModal();
                    Crud._load(config, state, document.getElementById('app'), true, true);
                } catch (e) {
                    UI.error(e.message);
                }
            },
        });

        // Reemplaza el body con el form real
        const modalBody = document.querySelector('.modal-backdrop .modal-body');
        modalBody.innerHTML = '';
        modalBody.appendChild(body);
    },

    async _delete(config, row, state, app, canEdit, canDelete) {
        const ok = await UI.confirm('¿Eliminar este registro?');
        if (!ok) return;
        try {
            const id = Crud._idOf(config, row);
            await API.delete(`${config.endpoint}/${id}`);
            UI.success('Eliminado');
            Crud._load(config, state, app, canEdit, canDelete);
        } catch (e) {
            UI.error(e.message);
        }
    },

    _idOf(config, row) {
        // Toma la primera clave que parece id en el objeto
        if (row.id) return row.id;
        const keys = Object.keys(row).filter((k) => k.startsWith('id_'));
        return row[keys[0]];
    },
};