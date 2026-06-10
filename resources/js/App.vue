<template>
    <div class="app">
        <header class="app__header">
            <div class="app__header-inner">
                <router-link to="/" class="app__logo">
                    <span class="app__logo-icon">▶</span>
                    <span class="app__logo-text">Cine</span>
                </router-link>
                <nav class="app__nav">
                    <router-link to="/" class="app__nav-link">Cartelera</router-link>
                </nav>
            </div>
        </header>
        <main class="app__main">
            <router-view />
        </main>
        <footer class="app__footer">
            <div class="app__footer-inner">
                <div class="app__footer-col app__footer-col--marca">
                    <p class="app__footer-logo">
                        <span class="app__logo-icon">▶</span>
                        {{ cine.nombre || 'Cine' }}
                    </p>
                    <p class="app__footer-texto">
                        Tu cine de siempre, con los estrenos de ahora.
                    </p>
                </div>
                <div class="app__footer-col">
                    <h3 class="app__footer-titulo">Visítanos</h3>
                    <p>{{ cine.direccion }}</p>
                    <p>{{ cine.ciudad }}</p>
                </div>
                <div class="app__footer-col">
                    <h3 class="app__footer-titulo">Contacto</h3>
                    <p>{{ cine.telefono }}</p>
                    <p>{{ cine.email }}</p>
                </div>
                <div class="app__footer-col">
                    <h3 class="app__footer-titulo">Horario</h3>
                    <p>{{ cine.horario }}</p>
                </div>
            </div>
            <div class="app__footer-legal">
                © {{ anio }} {{ cine.razon_social }} · CIF {{ cine.cif }}
            </div>
        </footer>
    </div>
</template>

<script>
import axios from 'axios'

export default {
    name: 'App',
    data() {
        return {
            cine: {}
        }
    },
    computed: {
        anio() {
            return new Date().getFullYear()
        }
    },
    mounted() {
        axios.get('/api/cine')
            .then(res => { this.cine = res.data })
            .catch(() => {})
    }
}
</script>

<style>
@import url('https://fonts.bunny.net/css?family=bebas-neue:400|dm-sans:400,500');

:root {
    --cine-bg: #0a0806;
    --cine-surface: #14100c;
    --cine-text: #f0e8dc;
    --cine-text-muted: #8a7d6e;
    --cine-gold: #d4a843;
    --cine-gold-dim: #b8923a;
    --cine-font-display: 'Bebas Neue', sans-serif;
    --cine-font-body: 'DM Sans', sans-serif;
}

*,
*::before,
*::after {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: var(--cine-font-body);
    background-color: var(--cine-bg);
    color: var(--cine-text);
    -webkit-font-smoothing: antialiased;
    overflow-x: hidden;
}

body::before {
    content: '';
    position: fixed;
    inset: 0;
    pointer-events: none;
    z-index: 9999;
    opacity: 0.035;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
    background-repeat: repeat;
    background-size: 180px;
}
</style>

<style scoped>
.app {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

.app__header {
    position: sticky;
    top: 0;
    z-index: 100;
    background: rgba(10, 8, 6, 0.92);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid rgba(212, 168, 67, 0.12);
}

.app__header-inner {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1.5rem;
    height: 4rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.app__logo {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
    color: var(--cine-text);
}

.app__logo-icon {
    color: var(--cine-gold);
    font-size: 0.9rem;
}

.app__logo-text {
    font-family: var(--cine-font-display);
    font-size: 1.6rem;
    letter-spacing: 0.12em;
    text-transform: uppercase;
}

.app__nav-link {
    font-family: var(--cine-font-display);
    font-size: 0.85rem;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    text-decoration: none;
    color: var(--cine-text-muted);
    transition: color 0.2s ease;
    padding: 0.25rem 0;
    border-bottom: 1px solid transparent;
}

.app__nav-link:hover,
.app__nav-link.router-link-active {
    color: var(--cine-gold);
    border-bottom-color: var(--cine-gold);
}

.app__main {
    flex: 1;
    max-width: 1200px;
    width: 100%;
    margin: 0 auto;
    padding: 0 1.5rem;
}

.app__footer {
    margin-top: 4rem;
    background: var(--cine-surface);
    border-top: 1px solid rgba(212, 168, 67, 0.12);
}

.app__footer-inner {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2.5rem 1.5rem;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 2rem;
}

.app__footer-col p {
    margin: 0 0 0.35rem;
    font-size: 0.85rem;
    color: var(--cine-text-muted);
}

.app__footer-col p.app__footer-logo {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-family: var(--cine-font-display);
    font-size: 1.4rem;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--cine-text);
    margin: 0 0 0.5rem;
}

.app__footer-texto {
    max-width: 24ch;
}

.app__footer-titulo {
    font-family: var(--cine-font-display);
    font-size: 0.8rem;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: var(--cine-gold);
    margin: 0 0 0.75rem;
}

.app__footer-legal {
    max-width: 1200px;
    margin: 0 auto;
    padding: 1rem 1.5rem;
    border-top: 1px solid rgba(212, 168, 67, 0.08);
    font-size: 0.75rem;
    color: var(--cine-text-muted);
}

@media (min-width: 900px) {
    .app__header-inner,
    .app__main,
    .app__footer-inner,
    .app__footer-legal {
        padding-left: 2rem;
        padding-right: 2rem;
    }
}
</style>
