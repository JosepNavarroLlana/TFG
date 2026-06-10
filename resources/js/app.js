import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import { auth } from './store/auth'

auth.inicializar()

const app = createApp(App)
app.use(router)
app.mount('#app')