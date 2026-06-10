<template>
    <div class="adm">
        <aside class="adm__sidebar">
            <router-link to="/" class="adm__logo">
                <span class="adm__logo-icon">▶</span>
                <span class="adm__logo-text">Cine</span>
            </router-link>
            <p class="adm__sidebar-label">Administración</p>
            <nav class="adm__nav">
                <router-link to="/admin/peliculas" class="adm__nav-link">
                    <span class="adm__nav-icon">🎬</span> Películas
                </router-link>
                <router-link to="/admin/sesiones" class="adm__nav-link">
                    <span class="adm__nav-icon">🕐</span> Sesiones
                </router-link>
                <router-link to="/admin/reservas" class="adm__nav-link">
                    <span class="adm__nav-icon">🎟</span> Reservas
                </router-link>
                <router-link to="/admin/salas" class="adm__nav-link">
                    <span class="adm__nav-icon">📊</span> Salas
                </router-link>
            </nav>
            <button class="adm__logout" @click="cerrarSesion">
                Cerrar sesión
            </button>
        </aside>

        <main class="adm__main">
            <router-view />
        </main>
    </div>
</template>

<script>
import { auth } from '../../store/auth'

export default {
    name: 'AdminLayout',
    methods: {
        async cerrarSesion() {
            await auth.logout()
            this.$router.push('/admin/login')
        },
    },
}
</script>

<style scoped>
.adm {
    display: flex;
    min-height: 100vh;
    background: var(--cine-bg);
}

.adm__sidebar {
    width: 220px;
    flex-shrink: 0;
    background: var(--cine-surface);
    border-right: 1px solid rgba(212, 168, 67, 0.12);
    display: flex;
    flex-direction: column;
    padding: 1.75rem 1.25rem;
    gap: 0.25rem;
    position: sticky;
    top: 0;
    height: 100vh;
    overflow-y: auto;
}

.adm__logo {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
    color: var(--cine-text);
    margin-bottom: 1rem;
}

.adm__logo-icon {
    color: var(--cine-gold);
    font-size: 0.9rem;
}

.adm__logo-text {
    font-family: var(--cine-font-display);
    font-size: 1.5rem;
    letter-spacing: 0.12em;
    text-transform: uppercase;
}

.adm__sidebar-label {
    font-size: 0.65rem;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: var(--cine-text-muted);
    margin: 0.5rem 0 0.75rem;
    padding-left: 0.25rem;
}

.adm__nav {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
    flex: 1;
}

.adm__nav-link {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.6rem 0.75rem;
    border-radius: 6px;
    text-decoration: none;
    font-size: 0.9rem;
    color: var(--cine-text-muted);
    transition: background 0.15s ease, color 0.15s ease;
}

.adm__nav-link:hover {
    background: rgba(212, 168, 67, 0.07);
    color: var(--cine-text);
}

.adm__nav-link.router-link-active {
    background: rgba(212, 168, 67, 0.12);
    color: var(--cine-gold);
}

.adm__nav-icon {
    font-size: 1rem;
    width: 1.25rem;
    text-align: center;
}

.adm__logout {
    margin-top: 1rem;
    padding: 0.55rem 0.75rem;
    background: transparent;
    border: 1px solid rgba(212, 168, 67, 0.2);
    border-radius: 6px;
    color: var(--cine-text-muted);
    font-family: var(--cine-font-body);
    font-size: 0.82rem;
    cursor: pointer;
    transition: border-color 0.2s ease, color 0.2s ease;
    text-align: left;
}

.adm__logout:hover {
    border-color: #e87070;
    color: #e87070;
}

.adm__main {
    flex: 1;
    min-width: 0;
    padding: 2rem 2.5rem;
    overflow-y: auto;
}

@media (max-width: 700px) {
    .adm {
        flex-direction: column;
    }

    .adm__sidebar {
        width: 100%;
        height: auto;
        position: static;
        flex-direction: row;
        flex-wrap: wrap;
        padding: 1rem;
        gap: 0.5rem;
    }

    .adm__logo, .adm__sidebar-label { display: none; }

    .adm__nav {
        flex-direction: row;
        flex-wrap: wrap;
        flex: none;
    }

    .adm__logout { margin-top: 0; }

    .adm__main { padding: 1.25rem 1rem; }
}
</style>
