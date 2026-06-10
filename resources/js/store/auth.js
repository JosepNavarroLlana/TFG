import { reactive } from 'vue'
import axios from 'axios'

const TOKEN_KEY = 'cine_admin_token'

const state = reactive({
    token: localStorage.getItem(TOKEN_KEY) ?? null,
})

export const auth = {
    get estaAutenticado() {
        return !!state.token
    },

    get token() {
        return state.token
    },

    async login(email, password) {
        const res = await axios.post('/api/admin/login', { email, password })
        state.token = res.data.token
        localStorage.setItem(TOKEN_KEY, state.token)
        axios.defaults.headers.common['Authorization'] = `Bearer ${state.token}`
    },

    async logout() {
        try {
            await axios.post('/api/admin/logout')
        } catch {
            // ignorar errores de red al cerrar sesión
        }
        state.token = null
        localStorage.removeItem(TOKEN_KEY)
        delete axios.defaults.headers.common['Authorization']
    },

    inicializar() {
        if (state.token) {
            axios.defaults.headers.common['Authorization'] = `Bearer ${state.token}`
        }
    },
}
