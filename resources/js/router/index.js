import { createRouter, createWebHistory } from 'vue-router'
import { auth } from '../store/auth'
import Cartelera from '../views/Cartelera.vue'
import PeliculaDetalle from '../views/PeliculaDetalle.vue'
import ReservaConfirmacion from '../views/ReservaConfirmacion.vue'
import AdminLogin from '../views/admin/AdminLogin.vue'
import AdminLayout from '../views/admin/AdminLayout.vue'
import AdminPeliculas from '../views/admin/AdminPeliculas.vue'
import AdminSesiones from '../views/admin/AdminSesiones.vue'
import AdminReservas from '../views/admin/AdminReservas.vue'
import AdminSalas from '../views/admin/AdminSalas.vue'

const routes = [
    {
        path: '/',
        name: 'cartelera',
        component: Cartelera
    },
    {
        path: '/pelicula/:id',
        name: 'pelicula-detalle',
        component: PeliculaDetalle
    },
    {
        path: '/reserva/:id',
        name: 'reserva-confirmacion',
        component: ReservaConfirmacion
    },
    {
        path: '/admin/login',
        name: 'admin-login',
        component: AdminLogin
    },
    {
        path: '/admin',
        component: AdminLayout,
        meta: { requiereAuth: true },
        children: [
            { path: '', redirect: '/admin/peliculas' },
            { path: 'peliculas', name: 'admin-peliculas', component: AdminPeliculas },
            { path: 'sesiones', name: 'admin-sesiones', component: AdminSesiones },
            { path: 'reservas', name: 'admin-reservas', component: AdminReservas },
            { path: 'salas', name: 'admin-salas', component: AdminSalas },
        ]
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

router.beforeEach((to) => {
    if (to.meta.requiereAuth && !auth.estaAutenticado) {
        return { name: 'admin-login' }
    }
    if (to.name === 'admin-login' && auth.estaAutenticado) {
        return { name: 'admin-peliculas' }
    }
})

export default router