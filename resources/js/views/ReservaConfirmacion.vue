<template>
    <section class="confirmacion">
        <div v-if="loading" class="confirmacion__estado">
            Cargando reserva...
        </div>

        <div v-else-if="error" class="confirmacion__estado confirmacion__estado--error">
            <p>{{ error }}</p>
            <router-link to="/" class="confirmacion__volver">← Volver a cartelera</router-link>
        </div>

        <template v-else-if="reserva">
            <div class="confirmacion__icono">✓</div>
            <h1 class="confirmacion__titulo">Reserva confirmada</h1>
            <p class="confirmacion__codigo">Nº {{ reserva.id }}</p>

            <div class="confirmacion__tarjeta">
                <div class="confirmacion__pelicula">
                    <img
                        v-if="reserva.sesion?.pelicula?.imagen"
                        :src="reserva.sesion.pelicula.imagen"
                        :alt="reserva.sesion.pelicula.titulo"
                        class="confirmacion__poster"
                    />
                    <div>
                        <h2 class="confirmacion__pelicula-titulo">
                            {{ reserva.sesion?.pelicula?.titulo }}
                        </h2>
                        <p class="confirmacion__detalle">
                            {{ formatearFecha(reserva.sesion?.fecha_hora) }}
                            · {{ formatearHora(reserva.sesion?.fecha_hora) }}
                        </p>
                        <p class="confirmacion__detalle">
                            {{ reserva.sesion?.sala?.nombre }}
                        </p>
                    </div>
                </div>

                <div class="confirmacion__bloque">
                    <span class="confirmacion__label">Butacas</span>
                    <span class="confirmacion__valor">{{ etiquetaButacas }}</span>
                </div>

                <div class="confirmacion__bloque">
                    <span class="confirmacion__label">Titular</span>
                    <span class="confirmacion__valor">{{ reserva.user?.name }}</span>
                </div>

                <div class="confirmacion__bloque">
                    <span class="confirmacion__label">Email</span>
                    <span class="confirmacion__valor">{{ reserva.user?.email }}</span>
                </div>

                <div class="confirmacion__total">
                    <span>Total pagado</span>
                    <strong>{{ formatearPrecio(reserva.total) }}</strong>
                </div>
            </div>

            <p v-if="correoEnviado" class="confirmacion__aviso confirmacion__aviso--correo">
                Hemos enviado los detalles de tu reserva a
                <strong>{{ reserva.user?.email }}</strong>.
            </p>
            <p class="confirmacion__aviso">
                Presenta este número de reserva en taquilla.
            </p>

            <div class="confirmacion__acciones">
                <router-link
                    v-if="reserva.sesion?.pelicula?.id"
                    :to="`/pelicula/${reserva.sesion.pelicula.id}`"
                    class="confirmacion__btn confirmacion__btn--secundario"
                >
                    Volver a la película
                </router-link>
                <router-link to="/" class="confirmacion__btn">
                    Ir a cartelera
                </router-link>
            </div>
        </template>
    </section>
</template>

<script>
import axios from 'axios'

export default {
    name: 'ReservaConfirmacion',
    data() {
        return {
            reserva: null,
            loading: true,
            error: null
        }
    },
    computed: {
        correoEnviado() {
            return this.$route.query.correo === '1'
        },
        etiquetaButacas() {
            if (!this.reserva?.asientos?.length) return '—'
            return this.reserva.asientos
                .map(a => `${a.fila}${a.numero}`)
                .sort()
                .join(', ')
        }
    },
    watch: {
        '$route.params.id': {
            immediate: true,
            handler() {
                this.cargarReserva()
            }
        }
    },
    methods: {
        async cargarReserva() {
            this.loading = true
            this.error = null
            try {
                const res = await axios.get(`/api/reservas/${this.$route.params.id}`)
                this.reserva = res.data
            } catch {
                this.error = 'No se encontró la reserva.'
                this.reserva = null
            } finally {
                this.loading = false
            }
        },
        formatearHora(fechaHora) {
            if (!fechaHora) return ''
            const date = new Date(fechaHora.replace(' ', 'T'))
            return date.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' })
        },
        formatearFecha(fechaHora) {
            if (!fechaHora) return ''
            const date = new Date(fechaHora.replace(' ', 'T'))
            return date.toLocaleDateString('es-ES', {
                weekday: 'long',
                day: 'numeric',
                month: 'long'
            })
        },
        formatearPrecio(precio) {
            return Number(precio).toLocaleString('es-ES', { style: 'currency', currency: 'EUR' })
        }
    }
}
</script>

<style scoped>
.confirmacion {
    max-width: 520px;
    margin: 2rem auto 4rem;
    text-align: center;
}

.confirmacion__estado {
    padding: 4rem 0;
    color: var(--cine-text-muted);
}

.confirmacion__estado--error {
    color: #e8a0a0;
}

.confirmacion__volver {
    color: var(--cine-gold);
    text-decoration: none;
}

.confirmacion__icono {
    width: 4rem;
    height: 4rem;
    margin: 0 auto 1.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    color: var(--cine-bg);
    background: var(--cine-gold);
    border-radius: 50%;
}

.confirmacion__titulo {
    font-family: var(--cine-font-display);
    font-size: 2rem;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--cine-text);
    margin: 0 0 0.35rem;
}

.confirmacion__codigo {
    font-size: 0.85rem;
    color: var(--cine-text-muted);
    margin: 0 0 2rem;
    letter-spacing: 0.08em;
}

.confirmacion__tarjeta {
    text-align: left;
    border: 1px solid rgba(212, 168, 67, 0.25);
    border-radius: 6px;
    padding: 1.5rem;
    background: rgba(10, 8, 6, 0.5);
    margin-bottom: 1.25rem;
}

.confirmacion__pelicula {
    display: flex;
    gap: 1rem;
    padding-bottom: 1.25rem;
    margin-bottom: 1.25rem;
    border-bottom: 1px solid rgba(212, 168, 67, 0.15);
}

.confirmacion__poster {
    width: 4.5rem;
    aspect-ratio: 2 / 3;
    object-fit: cover;
    border-radius: 4px;
    flex-shrink: 0;
}

.confirmacion__pelicula-titulo {
    font-family: var(--cine-font-display);
    font-size: 1.35rem;
    letter-spacing: 0.06em;
    color: var(--cine-text);
    margin: 0 0 0.5rem;
}

.confirmacion__detalle {
    margin: 0 0 0.25rem;
    font-size: 0.85rem;
    color: var(--cine-text-muted);
    text-transform: capitalize;
}

.confirmacion__bloque {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: 1rem;
    padding: 0.5rem 0;
    border-bottom: 1px solid rgba(212, 168, 67, 0.08);
}

.confirmacion__label {
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--cine-gold-dim);
}

.confirmacion__valor {
    font-size: 0.9rem;
    color: var(--cine-text);
    text-align: right;
}

.confirmacion__aviso--correo {
    color: var(--cine-gold);
}

.confirmacion__aviso--correo strong {
    color: var(--cine-text);
}

.confirmacion__total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 1.25rem;
    padding-top: 1rem;
    font-size: 0.9rem;
    color: var(--cine-text-muted);
}

.confirmacion__total strong {
    font-family: var(--cine-font-display);
    font-size: 1.4rem;
    letter-spacing: 0.06em;
    color: var(--cine-gold);
}

.confirmacion__aviso {
    font-size: 0.8rem;
    color: var(--cine-text-muted);
    margin: 0 0 1.5rem;
}

.confirmacion__acciones {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    justify-content: center;
}

.confirmacion__btn {
    font-family: var(--cine-font-display);
    font-size: 0.85rem;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    padding: 0.6rem 1.25rem;
    background: var(--cine-gold);
    color: var(--cine-bg);
    border: none;
    border-radius: 3px;
    text-decoration: none;
    transition: opacity 0.2s ease;
}

.confirmacion__btn:hover {
    opacity: 0.9;
}

.confirmacion__btn--secundario {
    background: transparent;
    color: var(--cine-gold);
    border: 1px solid rgba(212, 168, 67, 0.4);
}
</style>
