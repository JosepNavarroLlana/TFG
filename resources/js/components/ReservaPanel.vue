<template>
    <div class="reserva">
        <h2 class="reserva__titulo">Reserva</h2>

        <!-- Mensaje para películas próximamente sin sesiones -->
        <div v-if="estado === 'proximamente' && sesiones.length === 0" class="reserva__no-disponible">
            <p>Esta película aún no está disponible para reservas.</p>
            <p class="reserva__no-disponible-subtitulo">Vuelve pronto para poder comprar tus entradas.</p>
        </div>

        <!-- Selector de día -->
        <div v-else class="reserva__fecha">
            <button class="reserva__fecha-btn" aria-label="Día anterior" @click="cambiarDia(-1)">
                ‹
            </button>
            <button class="reserva__fecha-texto" @click="abrirCalendario">
                {{ etiquetaFecha }}
            </button>
            <button class="reserva__fecha-btn" aria-label="Día siguiente" @click="cambiarDia(1)">
                ›
            </button>
            <input
                ref="calendario"
                type="date"
                class="reserva__calendario"
                :value="fechaSeleccionada"
                :min="fechaMinima"
                @change="seleccionarFecha"
            />
        </div>

        <!-- Sesiones del día -->
        <div v-if="sesionesDelDia.length === 0 && estado !== 'proximamente'" class="reserva__vacio">
            No hay sesiones para este día.
        </div>

        <div v-else-if="sesionesDelDia.length > 0" class="reserva__sesiones">
            <button
                v-for="sesion in sesionesDelDia"
                :key="sesion.id"
                class="reserva__sesion"
                :class="{
                    'reserva__sesion--activa': sesionSeleccionada?.id === sesion.id,
                    'reserva__sesion--pasada': esSesionPasada(sesion)
                }"
                :disabled="esSesionPasada(sesion)"
                @click="seleccionarSesion(sesion)"
            >
                <span v-if="esSesionPasada(sesion)" class="reserva__sesion-badge">Finalizada</span>
                <span class="reserva__sesion-hora">{{ formatearHora(sesion.fecha_hora) }}</span>
                <span class="reserva__sesion-sala">{{ sesion.sala.nombre }}</span>
                <span class="reserva__sesion-precio">{{ formatearPrecio(sesion.precio) }}</span>
            </button>
        </div>

        <!-- Mapa de butacas -->
        <div v-if="sesionSeleccionada" class="reserva__butacas">
            <h3 class="reserva__butacas-titulo">
                {{ sesionSeleccionada.sala.nombre }} — Selecciona tus butacas
            </h3>

            <MapaAsientos
                :asientos="asientos"
                :ocupados="ocupados"
                :seleccionados="asientosSeleccionados"
                :cargando="cargandoAsientos"
                @toggle="toggleAsiento"
            />

            <div v-if="asientosSeleccionados.length > 0" class="reserva__resumen">
                <p class="reserva__resumen-total">
                    {{ asientosSeleccionados.length }}
                    {{ asientosSeleccionados.length === 1 ? 'butaca' : 'butacas' }}
                    — Total: <strong>{{ formatearPrecio(total) }}</strong>
                </p>

                <div class="reserva__datos">
                    <label class="reserva__campo">
                        <span class="reserva__campo-label">Nombre</span>
                        <input
                            v-model.trim="nombre"
                            name="res_nombre"
                            type="text"
                            class="reserva__input"
                            autocomplete="off"
                            data-lpignore="true"
                            readonly
                            @focus="$event.target.removeAttribute('readonly')"
                        />
                    </label>
                    <label class="reserva__campo">
                        <span class="reserva__campo-label">Email</span>
                        <input
                            v-model.trim="email"
                            name="res_email"
                            type="text"
                            class="reserva__input"
                            autocomplete="off"
                            data-lpignore="true"
                            readonly
                            @focus="$event.target.removeAttribute('readonly')"
                        />
                    </label>
                </div>

                <button
                    class="reserva__confirmar"
                    :disabled="confirmando || !formularioValido"
                    @click="confirmarReserva"
                >
                    {{ confirmando ? 'Reservando...' : 'Confirmar reserva' }}
                </button>
                <p v-if="mensajeReserva" class="reserva__mensaje" :class="{ 'reserva__mensaje--error': errorReserva }">
                    {{ mensajeReserva }}
                </p>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios'
import MapaAsientos from './MapaAsientos.vue'

export default {
    name: 'ReservaPanel',
    components: { MapaAsientos },
    props: {
        sesiones: { type: Array, default: () => [] },
        estado: { type: String, default: 'activa' }
    },
    data() {
        return {
            ahora: Date.now(),
            fechaSeleccionada: this.hoy(),
            sesionSeleccionada: null,
            asientos: [],
            ocupados: [],
            asientosSeleccionados: [],
            cargandoAsientos: false,
            confirmando: false,
            mensajeReserva: null,
            errorReserva: false,
            nombre: '',
            email: '',
            relojTimer: null
        }
    },
    computed: {
        formularioValido() {
            return this.nombre.length > 0 && this.email.includes('@')
        },
        fechaMinima() {
            return this.hoy()
        },
        etiquetaFecha() {
            const hoy = this.hoy()
            if (this.fechaSeleccionada === hoy) return 'Hoy'
            const manana = this.sumarDias(hoy, 1)
            if (this.fechaSeleccionada === manana) return 'Mañana'
            const [y, m, d] = this.fechaSeleccionada.split('-').map(Number)
            const date = new Date(y, m - 1, d)
            return date.toLocaleDateString('es-ES', {
                weekday: 'long',
                day: 'numeric',
                month: 'long'
            })
        },
        sesionesDelDia() {
            return this.sesiones
                .filter(s => s.fecha_hora.slice(0, 10) === this.fechaSeleccionada)
                .sort((a, b) => a.fecha_hora.localeCompare(b.fecha_hora))
        },
        total() {
            if (!this.sesionSeleccionada) return 0
            return this.sesionSeleccionada.precio * this.asientosSeleccionados.length
        }
    },
    watch: {
        sesiones() {
            this.inicializarFecha()
        },
        fechaSeleccionada() {
            this.sesionSeleccionada = null
            this.limpiarButacas()
        }
    },
    mounted() {
        this.inicializarFecha()
        this.relojTimer = setInterval(() => {
            this.ahora = Date.now()
        }, 30000)
    },
    beforeUnmount() {
        clearInterval(this.relojTimer)
    },
    methods: {
        hoy() {
            const d = new Date()
            return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`
        },
        sumarDias(fecha, dias) {
            const [y, m, d] = fecha.split('-').map(Number)
            const date = new Date(y, m - 1, d + dias)
            return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
        },
        inicializarFecha() {
            const fechasConSesion = [...new Set(this.sesiones.map(s => s.fecha_hora.slice(0, 10)))]
            const hoy = this.hoy()
            if (fechasConSesion.includes(hoy)) {
                this.fechaSeleccionada = hoy
            } else if (fechasConSesion.length > 0) {
                this.fechaSeleccionada = fechasConSesion.sort()[0]
            }
        },
        cambiarDia(delta) {
            this.fechaSeleccionada = this.sumarDias(this.fechaSeleccionada, delta)
        },
        abrirCalendario() {
            const input = this.$refs.calendario
            if (input.showPicker) {
                input.showPicker()
            } else {
                input.click()
            }
        },
        seleccionarFecha(e) {
            this.fechaSeleccionada = e.target.value
        },
        async seleccionarSesion(sesion) {
            if (this.esSesionPasada(sesion)) return
            if (this.sesionSeleccionada?.id === sesion.id) return
            this.sesionSeleccionada = sesion
            this.limpiarButacas()
            this.cargandoAsientos = true
            try {
                const res = await axios.get(`/api/sesiones/${sesion.id}/asientos`)
                this.asientos = res.data.asientos
                this.ocupados = res.data.ocupados
            } catch {
                this.asientos = []
                this.ocupados = []
            } finally {
                this.cargandoAsientos = false
            }
        },
        toggleAsiento(id) {
            const idx = this.asientosSeleccionados.indexOf(id)
            if (idx === -1) {
                this.asientosSeleccionados.push(id)
            } else {
                this.asientosSeleccionados.splice(idx, 1)
            }
            this.mensajeReserva = null
        },
        limpiarButacas() {
            this.asientos = []
            this.ocupados = []
            this.asientosSeleccionados = []
            this.mensajeReserva = null
            this.errorReserva = false
        },
        async confirmarReserva() {
            this.confirmando = true
            this.mensajeReserva = null
            this.errorReserva = false
            try {
                const res = await axios.post('/api/reservas', {
                    sesion_id: this.sesionSeleccionada.id,
                    asientos: this.asientosSeleccionados,
                    nombre: this.nombre,
                    email: this.email
                })
                const query = res.data.email_enviado ? { correo: '1' } : {}
                this.$router.push({ path: `/reserva/${res.data.id}`, query })
            } catch (err) {
                if (err.response?.status === 409) {
                    this.mensajeReserva = err.response.data?.mensaje || 'Butacas no disponibles.'
                    const ocupados = err.response.data?.ocupados || []
                    this.ocupados = [...new Set([...this.ocupados, ...ocupados])]
                    this.asientosSeleccionados = this.asientosSeleccionados.filter(
                        id => !ocupados.includes(id)
                    )
                } else if (err.response?.status === 422) {
                    this.mensajeReserva = 'Revisa nombre y email.'
                } else {
                    this.mensajeReserva = 'No se pudo completar la reserva.'
                }
                this.errorReserva = true
            } finally {
                this.confirmando = false
            }
        },
        formatearHora(fechaHora) {
            const date = new Date(fechaHora.replace(' ', 'T'))
            return date.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' })
        },
        formatearPrecio(precio) {
            return Number(precio).toLocaleString('es-ES', { style: 'currency', currency: 'EUR' })
        },
        esSesionPasada(sesion) {
            return new Date(sesion.fecha_hora.replace(' ', 'T')).getTime() < this.ahora
        }
    }
}
</script>

<style scoped>
.reserva {
    border: 1px solid rgba(212, 168, 67, 0.25);
    border-radius: 6px;
    padding: 1.5rem;
    background: rgba(10, 8, 6, 0.4);
}

.reserva__titulo {
    font-family: var(--cine-font-display);
    font-size: 1.3rem;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--cine-text);
    margin: 0 0 1.25rem;
    padding-bottom: 0.65rem;
    border-bottom: 1px solid rgba(212, 168, 67, 0.15);
}

.reserva__fecha {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    margin-bottom: 1.25rem;
    position: relative;
}

.reserva__fecha-btn {
    width: 2rem;
    height: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    line-height: 1;
    color: var(--cine-gold);
    background: transparent;
    border: 1px solid rgba(212, 168, 67, 0.3);
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.2s ease;
    padding: 0;
}

.reserva__fecha-btn:hover {
    background: rgba(212, 168, 67, 0.1);
}

.reserva__fecha-texto {
    font-family: var(--cine-font-display);
    font-size: 1rem;
    letter-spacing: 0.06em;
    text-transform: capitalize;
    color: var(--cine-text);
    background: none;
    border: none;
    cursor: pointer;
    padding: 0.25rem 0.5rem;
    border-bottom: 1px dashed rgba(212, 168, 67, 0.4);
    transition: color 0.2s ease;
}

.reserva__fecha-texto:hover {
    color: var(--cine-gold);
}

.reserva__calendario {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
    pointer-events: none;
}

.reserva__no-disponible {
    font-size: 0.9rem;
    color: var(--cine-text);
    text-align: center;
    padding: 1.5rem;
    background: rgba(100, 150, 200, 0.08);
    border: 1px solid rgba(100, 150, 200, 0.25);
    border-radius: 4px;
    margin-bottom: 1rem;
}

.reserva__no-disponible p {
    margin: 0 0 0.5rem;
}

.reserva__no-disponible p:last-child {
    margin-bottom: 0;
}

.reserva__no-disponible-subtitulo {
    font-size: 0.8rem;
    color: var(--cine-text-muted);
}

.reserva__vacio {
    font-size: 0.85rem;
    color: var(--cine-text-muted);
    text-align: center;
    padding: 1rem 0;
}

.reserva__sesiones {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
}

.reserva__sesion {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.1rem;
    padding: 0.6rem 0.9rem;
    min-width: 110px;
    border: 1px solid rgba(212, 168, 67, 0.2);
    border-radius: 4px;
    background: var(--cine-bg);
    cursor: pointer;
    transition: all 0.2s ease;
    text-align: left;
}

.reserva__sesion:hover {
    border-color: var(--cine-gold);
}

.reserva__sesion:disabled {
    cursor: not-allowed;
    opacity: 0.45;
    background: rgba(255, 255, 255, 0.03);
    border-color: rgba(212, 168, 67, 0.1);
}

.reserva__sesion--pasada,
.reserva__sesion--pasada:hover {
    border-color: rgba(255, 255, 255, 0.08);
    background: rgba(255, 255, 255, 0.03);
}

.reserva__sesion--pasada .reserva__sesion-hora,
.reserva__sesion--pasada .reserva__sesion-sala,
.reserva__sesion--pasada .reserva__sesion-precio {
    color: rgba(190, 181, 170, 0.45);
}

.reserva__sesion-badge {
    align-self: flex-start;
    margin-bottom: 0.25rem;
    padding: 0.18rem 0.45rem;
    border: 1px solid rgba(190, 181, 170, 0.25);
    border-radius: 999px;
    font-family: var(--cine-font-display);
    font-size: 0.62rem;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: rgba(190, 181, 170, 0.7);
    background: rgba(255, 255, 255, 0.03);
}

.reserva__sesion--activa {
    border-color: var(--cine-gold);
    background: rgba(212, 168, 67, 0.12);
}

.reserva__sesion-hora {
    font-family: var(--cine-font-display);
    font-size: 1.05rem;
    letter-spacing: 0.05em;
    color: var(--cine-text);
}

.reserva__sesion-sala {
    font-size: 0.65rem;
    color: var(--cine-text-muted);
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.reserva__sesion-precio {
    font-size: 0.75rem;
    color: var(--cine-gold);
}

.reserva__butacas {
    border-top: 1px solid rgba(212, 168, 67, 0.15);
    padding-top: 1.25rem;
}

.reserva__butacas-titulo {
    font-family: var(--cine-font-display);
    font-size: 0.9rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--cine-gold-dim);
    margin: 0 0 1rem;
}

.reserva__resumen {
    margin-top: 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    align-items: flex-start;
}

.reserva__resumen-total {
    margin: 0;
    font-size: 0.9rem;
    color: var(--cine-text-muted);
}

.reserva__datos {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    width: 100%;
}

.reserva__campo {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.reserva__campo-label {
    font-size: 0.68rem;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--cine-gold-dim);
}

.reserva__input {
    font-family: var(--cine-font-body);
    font-size: 0.9rem;
    color: var(--cine-text);
    background: var(--cine-bg);
    border: 1px solid rgba(212, 168, 67, 0.25);
    border-radius: 4px;
    padding: 0.55rem 0.75rem;
    outline: none;
    transition: border-color 0.2s ease;
}

.reserva__input:focus {
    border-color: var(--cine-gold);
}

.reserva__input::placeholder {
    color: rgba(138, 125, 110, 0.6);
}

.reserva__resumen strong {
    color: var(--cine-gold);
}

.reserva__confirmar {
    font-family: var(--cine-font-display);
    font-size: 0.85rem;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    padding: 0.6rem 1.5rem;
    background: var(--cine-gold);
    color: var(--cine-bg);
    border: none;
    border-radius: 3px;
    cursor: pointer;
    transition: opacity 0.2s ease;
}

.reserva__confirmar:hover:not(:disabled) {
    opacity: 0.9;
}

.reserva__confirmar:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.reserva__mensaje {
    font-size: 0.85rem;
    color: var(--cine-gold);
    margin: 0;
}

.reserva__mensaje--error {
    color: #e8a0a0;
}

@media (max-width: 768px) {
    .reserva {
        padding: 1.25rem;
    }
}

@media (max-width: 480px) {
    .reserva {
        padding: 1rem;
    }
    .reserva__titulo {
        font-size: 1.15rem;
        margin-bottom: 1rem;
    }
    .reserva__sesiones {
        gap: 0.35rem;
    }
    .reserva__sesion {
        padding: 0.5rem 0.75rem;
        min-width: 95px;
    }
    .reserva__sesion-hora {
        font-size: 0.95rem;
    }
    .reserva__butacas-titulo {
        font-size: 0.85rem;
    }
    .reserva__resumen {
        margin-top: 1rem;
        gap: 0.5rem;
    }
}
</style>
