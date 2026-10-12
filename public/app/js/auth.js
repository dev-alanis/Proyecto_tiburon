/**
 * Auth — guard de sesión y helpers.
 */
const Auth = {
    require() {
        const token = API.getToken();
        if (!token) {
            window.location.href = 'login.html';
            return false;
        }
        return true;
    },

    redirectIfLogged() {
        if (API.getToken()) {
            window.location.href = 'dashboard.html';
        }
    },

    async login(nombreUsuario, password) {
        const data = await API.login({
            nombre_usuario: nombreUsuario,
            password: password,
        });
        API.setSession(data.token, data.refreshToken, data.usuario);
        return data;
    },

    async logout() {
        await API.logout();
        API.clearSession();
        window.location.href = 'login.html';
    },

    user() { return API.getUser() || {}; },
    isAdmin() { return (API.getUser() || {}).rol === 'ADMIN'; },
    isCuidador() { return (API.getUser() || {}).rol === 'CUIDADOR'; },
    hasRole(...roles) { return roles.includes((API.getUser() || {}).rol); },
};