<template>
    <div class="login">
        <div class="login__card">
            <div class="login__logo">
                <span class="login__logo-icon">▶</span>
                <span class="login__logo-text">Cine</span>
            </div>
            <h1 class="login__titulo">Panel de administración</h1>

            <form class="login__form" @submit.prevent="iniciarSesion">
                <div class="login__campo">
                    <label class="login__label" for="email">Email</label>
                    <input
                        id="email"
                        v-model="email"
                        type="email"
                        class="login__input"
                        placeholder="admin@cine.es"
                        autocomplete="email"
                        required
                    />
                </div>
                <div class="login__campo">
                    <label class="login__label" for="password">Contraseña</label>
                    <input
                        id="password"
                        v-model="password"
                        type="password"
                        class="login__input"
                        placeholder="••••••••"
                        autocomplete="current-password"
                        required
                    />
                </div>

                <p v-if="error" class="login__error">{{ error }}</p>

                <button type="submit" class="login__boton" :disabled="cargando">
                    {{ cargando ? 'Accediendo…' : 'Entrar' }}
                </button>
            </form>

            <router-link to="/" class="login__volver">← Volver a la cartelera</router-link>
        </div>
    </div>
</template>

<script>
import { auth } from '../../store/auth'

export default {
    name: 'AdminLogin',
    data() {
        return {
            email: '',
            password: '',
            error: null,
            cargando: false,
        }
    },
    methods: {
        async iniciarSesion() {
            this.error = null
            this.cargando = true
            try {
                await auth.login(this.email, this.password)
                this.$router.push('/admin')
            } catch (e) {
                this.error = e.response?.data?.mensaje ?? 'Error al iniciar sesión.'
            } finally {
                this.cargando = false
            }
        },
    },
}
</script>

<style scoped>
.login {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--cine-bg);
    padding: 2rem 1rem;
}

.login__card {
    width: 100%;
    max-width: 400px;
    background: var(--cine-surface);
    border: 1px solid rgba(212, 168, 67, 0.15);
    border-radius: 10px;
    padding: 2.5rem 2rem;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.login__logo {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    justify-content: center;
}

.login__logo-icon {
    color: var(--cine-gold);
    font-size: 1rem;
}

.login__logo-text {
    font-family: var(--cine-font-display);
    font-size: 1.8rem;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--cine-text);
}

.login__titulo {
    font-family: var(--cine-font-display);
    font-size: 1.1rem;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--cine-text-muted);
    text-align: center;
    margin: 0;
}

.login__form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.login__campo {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.login__label {
    font-size: 0.78rem;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--cine-text-muted);
}

.login__input {
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(212, 168, 67, 0.2);
    border-radius: 5px;
    padding: 0.65rem 0.85rem;
    color: var(--cine-text);
    font-family: var(--cine-font-body);
    font-size: 0.95rem;
    transition: border-color 0.2s ease;
}

.login__input:focus {
    outline: none;
    border-color: var(--cine-gold);
}

.login__error {
    font-size: 0.82rem;
    color: #e87070;
    margin: 0;
    padding: 0.5rem 0.75rem;
    background: rgba(232, 112, 112, 0.1);
    border-radius: 4px;
    border-left: 3px solid #e87070;
}

.login__boton {
    margin-top: 0.5rem;
    padding: 0.75rem;
    background: var(--cine-gold);
    color: var(--cine-bg);
    border: none;
    border-radius: 5px;
    font-family: var(--cine-font-display);
    font-size: 1rem;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    cursor: pointer;
    transition: opacity 0.2s ease;
}

.login__boton:disabled {
    opacity: 0.55;
    cursor: not-allowed;
}

.login__boton:hover:not(:disabled) {
    opacity: 0.88;
}

.login__volver {
    font-size: 0.8rem;
    color: var(--cine-text-muted);
    text-decoration: none;
    text-align: center;
    transition: color 0.2s ease;
}

.login__volver:hover {
    color: var(--cine-gold);
}
</style>
