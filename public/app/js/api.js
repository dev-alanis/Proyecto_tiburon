/**
 * API client — Acuario Tiburón Feliz
 * Detecta automáticamente la base del API según la URL actual.
 */
const API = (() => {
    const base = (() => {
        const m = window.location.pathname.match(/^(.*\/public\/)/);
        return (m ? m[1] : '/') + 'api/v1';
    })();

    const TOKEN_KEY   = 'atf_token';
    const REFRESH_KEY = 'atf_refresh';
    const USER_KEY    = 'atf_user';

    function getToken()        { return localStorage.getItem(TOKEN_KEY); }
    function getRefreshToken() { return localStorage.getItem(REFRESH_KEY); }
    function getUser() {
        try { return JSON.parse(localStorage.getItem(USER_KEY) || 'null'); }
        catch { return null; }
    }

    function setSession(token, refreshToken, user) {
        if (token)        localStorage.setItem(TOKEN_KEY, token);
        if (refreshToken) localStorage.setItem(REFRESH_KEY, refreshToken);
        if (user)         localStorage.setItem(USER_KEY, JSON.stringify(user));
    }

    function clearSession() {
        localStorage.removeItem(TOKEN_KEY);
        localStorage.removeItem(REFRESH_KEY);
        localStorage.removeItem(USER_KEY);
    }

    async function refresh() {
        const rt = getRefreshToken();
        if (!rt) return false;
        try {
            const res = await fetch(base + '/auth/refresh', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ refreshToken: rt }),
            });
            if (!res.ok) return false;
            const data = await res.json();
            setSession(data.token, data.refreshToken, getUser());
            return true;
        } catch { return false; }
    }

    async function request(path, options = {}, retry = true) {
        const headers = {
            'Content-Type': 'application/json',
            ...(options.headers || {}),
        };
        const token = getToken();
        if (token) headers['Authorization'] = `Bearer ${token}`;

        const res = await fetch(base + path, { ...options, headers });

        if (res.status === 401 && retry) {
            if (await refresh()) return request(path, options, false);
            clearSession();
            window.location.href = 'login.html';
            throw new Error('Sesión expirada');
        }

        if (res.status === 204) return null;

        const text = await res.text();
        const data = text ? JSON.parse(text) : null;

        if (!res.ok) {
            const err = new Error((data && data.error && data.error.message) || 'Error');
            err.status = res.status;
            err.body   = data;
            throw err;
        }
        return data;
    }

    return {
        base,
        get:    (p)       => request(p, { method: 'GET' }),
        post:   (p, body) => request(p, { method: 'POST',   body: JSON.stringify(body) }),
        put:    (p, body) => request(p, { method: 'PUT',    body: JSON.stringify(body) }),
        patch:  (p, body) => request(p, { method: 'PATCH',  body: JSON.stringify(body) }),
        delete: (p)       => request(p, { method: 'DELETE' }),

        // Auth
        login: (creds) => request('/auth/login', {
            method: 'POST',
            body: JSON.stringify(creds),
        }, false),
        logout: () => request('/auth/logout', { method: 'POST' }).catch(() => {}),
        me:     () => request('/auth/me'),

        // Session helpers
        getToken, getRefreshToken, getUser,
        setSession, clearSession,
    };
})();