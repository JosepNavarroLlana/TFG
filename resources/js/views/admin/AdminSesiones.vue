<template>
    <div class="as">
        <header class="as__cabecera">
            <h1 class="as__titulo">Sesiones</h1>
            <button class="as__btn as__btn--primario" @click="abrirNueva">+ Nueva sesión</button>
        </header>

        <div class="as__filtros">
            <label class="as__label">Filtrar por película:</label>
            <select v-model="filtroPelicula" class="as__select">
                <option value="">Todas</option>
                <option v-for="p in peliculas" :key="p.id" :value="p.id">{{ p.titulo }}</option>
            </select>
            <label class="as__label">Fecha:</label>
            <input v-model="filtroFecha" type="date" class="as__input" />
            <button class="as__btn" @click="limpiarFiltros">Limpiar</button>
        </div>

        <div v-if="cargando" class="as__estado">Cargando…</div>
        <div v-else-if="error" class="as__estado as__estado--error">{{ error }}</div>
        <div v-else class="as__tabla-wrapper">
            <table class="as__tabla">
                <thead>
                    <tr>
                        <th>Película</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Sala</th>
                        <th>Precio</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="s in sesionesFiltradas" :key="s.id">
                        <td class="as__td-titulo">{{ s.pelicula?.titulo }}</td>
                        <td>{{ fecha(s.fecha_hora) }}</td>
                        <td>{{ hora(s.fecha_hora) }}</td>
                        <td>{{ s.sala?.nombre }}</td>
                        <td>{{ formatoPrecio(s.precio) }}</td>
                        <td>
                            <button class="as__btn as__btn--sm as__btn--peligro" @click="confirmarBorrar(s)">
                                Borrar
                            </button>
                        </td>
                    </tr>
                    <tr v-if="sesionesFiltradas.length === 0">
                        <td colspan="6" class="as__vacio">Sin sesiones para los filtros aplicados.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Modal nueva sesión -->
        <div v-if="modal" class="as__modal-fondo" @click.self="modal = false">
            <div class="as__modal">
                <h2 class="as__modal-titulo">Nueva sesión</h2>
                <form class="as__form" @submit.prevent="guardar">
                    <div class="as__fila">
                        <div class="as__campo as__campo--ancho">
                            <label class="as__label">Película *</label>
                            <select v-model="form.pelicula_id" class="as__input" required>
                                <option value="" disabled>Selecciona…</option>
                                <option v-for="p in peliculas" :key="p.id" :value="p.id">{{ p.titulo }}</option>
                            </select>
                        </div>
                        <div class="as__campo">
                            <label class="as__label">Sala *</label>
                            <select v-model="form.sala_id" class="as__input" required>
                                <option value="" disabled>Selecciona…</option>
                                <option v-for="s in salas" :key="s.id" :value="s.id">{{ s.nombre }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="as__fila">
                        <div class="as__campo">
                            <label class="as__label">Fecha *</label>
                            <input v-model="form.fecha" type="date" class="as__input" required />
                        </div>
                        <div class="as__campo">
                            <label class="as__label">Hora *</label>
                            <input v-model="form.hora" type="time" class="as__input" required />
                        </div>
                        <div class="as__campo">
                            <label class="as__label">Precio (€) *</label>
                            <input v-model.number="form.precio" type="number" min="0" step="0.50" class="as__input" required />
                        </div>
                    </div>

                    <p v-if="errorModal" class="as__error">{{ errorModal }}</p>

                    <div class="as__modal-acciones">
                        <button type="button" class="as__btn" @click="modal = false">Cancelar</button>
                        <button type="submit" class="as__btn as__btn--primario" :disabled="guardando">
                            {{ guardando ? 'Guardando…' : 'Crear sesión' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal confirmar borrar -->
        <div v-if="confirmar" class="as__modal-fondo" @click.self="confirmar = null">
            <div class="as__modal as__modal--sm">
                <h2 class="as__modal-titulo">¿Borrar sesión?</h2>
                <p class="as__modal-texto">
                    Sesión del <strong>{{ fecha(confirmar.fecha_hora) }}</strong> a las
                    <strong>{{ hora(confirmar.fecha_hora) }}</strong> de
                    <strong>{{ confirmar.pelicula?.titulo }}</strong>.
                    Las reservas asociadas no se borrarán.
                </p>
                <div class="as__modal-acciones">
                    <button class="as__btn" @click="confirmar = null">Cancelar</button>
                    <button class="as__btn as__btn--peligro" :disabled="guardando" @click="borrar">
                        {{ guardando ? 'Borrando…' : 'Sí, borrar' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios'

export default {
    name: 'AdminSesiones',
    data() {
        return {
            sesiones: [],
            peliculas: [],
            salas: [],
            cargando: true,
            error: null,
            filtroPelicula: '',
            filtroFecha: '',
            modal: false,
            form: { pelicula_id: '', sala_id: '', fecha: '', hora: '', precio: 7 },
            guardando: false,
            errorModal: null,
            confirmar: null,
        }
    },
    computed: {
        sesionesFiltradas() {
            return this.sesiones.filter(s => {
                if (this.filtroPelicula && s.pelicula_id !== this.filtroPelicula) return false
                if (this.filtroFecha && !s.fecha_hora.startsWith(this.filtroFecha)) return false
                return true
            })
        },
    },
    mounted() {
        this.cargar()
    },
    methods: {
        async cargar() {
            this.cargando = true
            try {
                const [sesRes, pelRes, salRes] = await Promise.all([
                    axios.get('/api/sesiones'),
                    axios.get('/api/admin/peliculas'),
                    axios.get('/api/salas'),
                ])
                this.sesiones = sesRes.data.sort((a, b) => a.fecha_hora.localeCompare(b.fecha_hora))
                this.peliculas = pelRes.data
                this.salas = salRes.data
            } catch {
                this.error = 'No se pudieron cargar los datos.'
            } finally {
                this.cargando = false
            }
        },
        fecha(fechaHora) {
            return new Date(fechaHora.replace(' ', 'T')).toLocaleDateString('es-ES')
        },
        hora(fechaHora) {
            return new Date(fechaHora.replace(' ', 'T')).toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' })
        },
        formatoPrecio(v) {
            return `${Number(v).toFixed(2).replace('.', ',')} €`
        },
        limpiarFiltros() {
            this.filtroPelicula = ''
            this.filtroFecha = ''
        },
        abrirNueva() {
            this.form = { pelicula_id: '', sala_id: '', fecha: '', hora: '', precio: 7 }
            this.errorModal = null
            this.modal = true
        },
        async guardar() {
            this.guardando = true
            this.errorModal = null
            try {
                const payload = {
                    pelicula_id: this.form.pelicula_id,
                    sala_id: this.form.sala_id,
                    fecha_hora: `${this.form.fecha} ${this.form.hora}:00`,
                    precio: this.form.precio,
                }
                const res = await axios.post('/api/sesiones', payload)
                const sesion = { ...res.data, pelicula: this.peliculas.find(p => p.id === res.data.pelicula_id), sala: this.salas.find(s => s.id === res.data.sala_id) }
                this.sesiones.push(sesion)
                this.sesiones.sort((a, b) => a.fecha_hora.localeCompare(b.fecha_hora))
                this.modal = false
            } catch (e) {
                this.errorModal = e.response?.data?.message ?? 'Error al crear la sesión.'
            } finally {
                this.guardando = false
            }
        },
        confirmarBorrar(s) {
            this.confirmar = s
        },
        async borrar() {
            this.guardando = true
            try {
                await axios.delete(`/api/sesiones/${this.confirmar.id}`)
                this.sesiones = this.sesiones.filter(s => s.id !== this.confirmar.id)
                this.confirmar = null
            } catch {
                alert('No se pudo borrar la sesión.')
            } finally {
                this.guardando = false
            }
        },
    },
}
</script>

<style scoped>
.as__cabecera {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.25rem;
}

.as__titulo {
    font-family: var(--cine-font-display);
    font-size: 2rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    margin: 0;
    color: var(--cine-text);
}

.as__filtros {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}

.as__estado { color: var(--cine-text-muted); padding: 2rem 0; }
.as__estado--error { color: #e87070; }

.as__tabla-wrapper { overflow-x: auto; }

.as__tabla {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.88rem;
}

.as__tabla th {
    text-align: left;
    padding: 0.6rem 0.85rem;
    font-family: var(--cine-font-display);
    font-size: 0.72rem;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--cine-gold);
    border-bottom: 1px solid rgba(212, 168, 67, 0.18);
}

.as__tabla td {
    padding: 0.6rem 0.85rem;
    border-bottom: 1px solid rgba(255,255,255,0.05);
    color: var(--cine-text);
    vertical-align: middle;
}

.as__tabla tr:hover td { background: rgba(255,255,255,0.02); }

.as__td-titulo {
    font-family: var(--cine-font-display);
    letter-spacing: 0.04em;
    max-width: 220px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.as__vacio {
    text-align: center;
    color: var(--cine-text-muted);
    font-style: italic;
    padding: 2rem !important;
}

.as__btn {
    padding: 0.45rem 0.9rem;
    border: 1px solid rgba(212, 168, 67, 0.25);
    border-radius: 5px;
    background: transparent;
    color: var(--cine-text-muted);
    font-size: 0.8rem;
    cursor: pointer;
    transition: border-color 0.15s, color 0.15s, background 0.15s;
}

.as__btn:hover:not(.as__btn--primario) { border-color: var(--cine-gold); color: var(--cine-gold); }

.as__btn--primario {
    background: var(--cine-gold);
    border-color: var(--cine-gold);
    color: var(--cine-bg);
    font-family: var(--cine-font-display);
    letter-spacing: 0.1em;
    text-transform: uppercase;
}

.as__btn--primario:hover { opacity: 0.88; color: var(--cine-bg); }
.as__btn--sm { padding: 0.3rem 0.65rem; }
.as__btn--peligro { border-color: rgba(232,112,112,0.3); color: #e87070; }
.as__btn--peligro:hover { border-color: #e87070; background: rgba(232,112,112,0.1); }
.as__btn:disabled { opacity: 0.5; cursor: not-allowed; }

.as__label {
    font-size: 0.72rem;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--cine-text-muted);
    white-space: nowrap;
}

.as__select, .as__input {
    background: rgba(255,255,255,0.04);
    background-color: var(--cine-surface);
    border: 1px solid rgba(212, 168, 67, 0.18);
    border-radius: 5px;
    padding: 0.45rem 0.7rem;
    color: var(--cine-text);
    font-family: var(--cine-font-body);
    font-size: 0.88rem;
    color-scheme: dark;
}

.as__select option,
.as__input option {
    background: var(--cine-surface);
    color: var(--cine-text);
}

.as__select:focus, .as__input:focus { outline: none; border-color: var(--cine-gold); }

.as__modal-fondo {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.65);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 500;
    padding: 1rem;
}

.as__modal {
    background: var(--cine-surface);
    border: 1px solid rgba(212, 168, 67, 0.2);
    border-radius: 10px;
    padding: 2rem;
    width: 100%;
    max-width: 580px;
    max-height: 90vh;
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: rgba(212, 168, 67, 0.55) rgba(255, 255, 255, 0.05);
}

.as__modal::-webkit-scrollbar {
    width: 10px;
}

.as__modal::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 999px;
}

.as__modal::-webkit-scrollbar-thumb {
    background: rgba(212, 168, 67, 0.55);
    border-radius: 999px;
    border: 2px solid rgba(255, 255, 255, 0.05);
}

.as__modal::-webkit-scrollbar-thumb:hover {
    background: rgba(212, 168, 67, 0.75);
}

.as__modal--sm { max-width: 420px; }

.as__modal-titulo {
    font-family: var(--cine-font-display);
    font-size: 1.4rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    margin: 0 0 1.5rem;
    color: var(--cine-text);
}

.as__modal-texto {
    font-size: 0.9rem;
    color: var(--cine-text-muted);
    margin: 0 0 1.5rem;
    line-height: 1.5;
}

.as__form { display: flex; flex-direction: column; gap: 1rem; }

.as__fila { display: flex; gap: 1rem; flex-wrap: wrap; }

.as__campo { display: flex; flex-direction: column; gap: 0.3rem; flex: 1; min-width: 130px; }
.as__campo--ancho { flex: 2; min-width: 200px; }

.as__campo .as__input { width: 100%; box-sizing: border-box; }

.as__error {
    font-size: 0.82rem;
    color: #e87070;
    padding: 0.5rem 0.75rem;
    background: rgba(232,112,112,0.1);
    border-left: 3px solid #e87070;
    border-radius: 4px;
    margin: 0;
}

.as__modal-acciones {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    margin-top: 0.5rem;
}
</style>
