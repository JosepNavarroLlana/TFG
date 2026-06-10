<template>
    <div class="ar">
        <header class="ar__cabecera">
            <h1 class="ar__titulo">Reservas</h1>
            <div class="ar__stats" v-if="!cargando && !error">
                <span class="ar__stat">
                    <span class="ar__stat-num">{{ total }}</span> total
                </span>
                <span class="ar__stat">
                    <span class="ar__stat-num ar__stat-num--ok">{{ confirmadas }}</span> confirmadas
                </span>
                <span class="ar__stat">
                    <span class="ar__stat-num ar__stat-num--off">{{ canceladas }}</span> canceladas
                </span>
            </div>
        </header>

        <div class="ar__filtros">
            <input
                v-model="busqueda"
                class="ar__input"
                placeholder="Buscar por película, usuario o email…"
            />
            <select v-model="filtroEstado" class="ar__select">
                <option value="">Todos los estados</option>
                <option value="confirmada">Confirmadas</option>
                <option value="cancelada">Canceladas</option>
            </select>
        </div>

        <div v-if="cargando" class="ar__estado">Cargando…</div>
        <div v-else-if="error" class="ar__estado ar__estado--error">{{ error }}</div>
        <div v-else class="ar__tabla-wrapper">
            <table class="ar__tabla">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Película</th>
                        <th>Sesión</th>
                        <th>Usuario</th>
                        <th>Butacas</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="r in reservasFiltradas" :key="r.id" :class="{ 'ar__tr--cancelada': r.estado === 'cancelada' }">
                        <td class="ar__td-id">{{ r.id }}</td>
                        <td class="ar__td-titulo">{{ r.sesion?.pelicula?.titulo }}</td>
                        <td>
                            <span>{{ fecha(r.sesion?.fecha_hora) }}</span>
                            <span class="ar__hora">{{ hora(r.sesion?.fecha_hora) }}</span>
                        </td>
                        <td>
                            <span class="ar__nombre">{{ r.user?.name }}</span>
                            <span class="ar__email">{{ r.user?.email }}</span>
                        </td>
                        <td>{{ r.asientos?.length ?? '—' }}</td>
                        <td>{{ formatoPrecio(r.total) }}</td>
                        <td>
                            <span class="ar__badge" :class="`ar__badge--${r.estado}`">
                                {{ r.estado }}
                            </span>
                        </td>
                        <td>
                            <button
                                v-if="r.estado !== 'cancelada'"
                                class="ar__btn ar__btn--peligro ar__btn--sm"
                                @click="confirmarCancelar(r)"
                            >
                                Cancelar
                            </button>
                        </td>
                    </tr>
                    <tr v-if="reservasFiltradas.length === 0">
                        <td colspan="8" class="ar__vacio">Sin reservas para los filtros aplicados.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Confirmación cancelar -->
        <div v-if="confirmar" class="ar__modal-fondo" @click.self="confirmar = null">
            <div class="ar__modal">
                <h2 class="ar__modal-titulo">¿Cancelar reserva?</h2>
                <p class="ar__modal-texto">
                    Reserva <strong>#{{ confirmar.id }}</strong> de
                    <strong>{{ confirmar.user?.name }}</strong> para
                    <strong>{{ confirmar.sesion?.pelicula?.titulo }}</strong>.
                    Los asientos quedarán libres para otras reservas.
                </p>
                <div class="ar__modal-acciones">
                    <button class="ar__btn" @click="confirmar = null">Volver</button>
                    <button class="ar__btn ar__btn--peligro" :disabled="guardando" @click="cancelar">
                        {{ guardando ? 'Cancelando…' : 'Sí, cancelar' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios'

export default {
    name: 'AdminReservas',
    data() {
        return {
            reservas: [],
            cargando: true,
            error: null,
            busqueda: '',
            filtroEstado: '',
            confirmar: null,
            guardando: false,
        }
    },
    computed: {
        total() { return this.reservas.length },
        confirmadas() { return this.reservas.filter(r => r.estado === 'confirmada').length },
        canceladas() { return this.reservas.filter(r => r.estado === 'cancelada').length },
        reservasFiltradas() {
            const q = this.busqueda.toLowerCase()
            return this.reservas.filter(r => {
                if (this.filtroEstado && r.estado !== this.filtroEstado) return false
                if (!q) return true
                return (
                    r.sesion?.pelicula?.titulo?.toLowerCase().includes(q) ||
                    r.user?.name?.toLowerCase().includes(q) ||
                    r.user?.email?.toLowerCase().includes(q)
                )
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
                const res = await axios.get('/api/reservas')
                this.reservas = res.data
            } catch {
                this.error = 'No se pudieron cargar las reservas.'
            } finally {
                this.cargando = false
            }
        },
        fecha(fh) {
            if (!fh) return '—'
            return new Date(fh.replace(' ', 'T')).toLocaleDateString('es-ES')
        },
        hora(fh) {
            if (!fh) return ''
            return new Date(fh.replace(' ', 'T')).toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' })
        },
        formatoPrecio(v) {
            if (v == null) return '—'
            return `${Number(v).toFixed(2).replace('.', ',')} €`
        },
        confirmarCancelar(r) {
            this.confirmar = r
        },
        async cancelar() {
            this.guardando = true
            try {
                await axios.delete(`/api/reservas/${this.confirmar.id}`)
                const r = this.reservas.find(r => r.id === this.confirmar.id)
                if (r) r.estado = 'cancelada'
                this.confirmar = null
            } catch {
                alert('No se pudo cancelar la reserva.')
            } finally {
                this.guardando = false
            }
        },
    },
}
</script>

<style scoped>
.ar__cabecera {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.25rem;
}

.ar__titulo {
    font-family: var(--cine-font-display);
    font-size: 2rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    margin: 0;
    color: var(--cine-text);
}

.ar__stats { display: flex; gap: 1.25rem; }

.ar__stat {
    font-size: 0.78rem;
    color: var(--cine-text-muted);
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.ar__stat-num {
    font-family: var(--cine-font-display);
    font-size: 1.4rem;
    color: var(--cine-text);
    margin-right: 0.3rem;
}

.ar__stat-num--ok { color: #6fce8a; }
.ar__stat-num--off { color: #e87070; }

.ar__filtros {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
    margin-bottom: 1.5rem;
}

.ar__input {
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(212, 168, 67, 0.18);
    border-radius: 5px;
    padding: 0.5rem 0.8rem;
    color: var(--cine-text);
    font-family: var(--cine-font-body);
    font-size: 0.88rem;
    flex: 1;
    min-width: 220px;
}

.ar__input:focus { outline: none; border-color: var(--cine-gold); }

.ar__select {
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(212, 168, 67, 0.18);
    border-radius: 5px;
    padding: 0.5rem 0.8rem;
    color: var(--cine-text);
    font-size: 0.88rem;
}

.ar__estado { color: var(--cine-text-muted); padding: 2rem 0; }
.ar__estado--error { color: #e87070; }

.ar__tabla-wrapper { overflow-x: auto; }

.ar__tabla {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.86rem;
}

.ar__tabla th {
    text-align: left;
    padding: 0.6rem 0.75rem;
    font-family: var(--cine-font-display);
    font-size: 0.7rem;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--cine-gold);
    border-bottom: 1px solid rgba(212, 168, 67, 0.18);
}

.ar__tabla td {
    padding: 0.6rem 0.75rem;
    border-bottom: 1px solid rgba(255,255,255,0.05);
    color: var(--cine-text);
    vertical-align: middle;
}

.ar__tabla tr:hover td { background: rgba(255,255,255,0.02); }

.ar__tr--cancelada td { opacity: 0.45; }

.ar__td-id { color: var(--cine-text-muted); font-size: 0.78rem; }

.ar__td-titulo {
    font-family: var(--cine-font-display);
    letter-spacing: 0.04em;
    max-width: 200px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.ar__hora {
    display: block;
    font-size: 0.75rem;
    color: var(--cine-gold);
}

.ar__nombre { display: block; }
.ar__email { display: block; font-size: 0.75rem; color: var(--cine-text-muted); }

.ar__badge {
    display: inline-block;
    padding: 0.2rem 0.55rem;
    border-radius: 999px;
    font-size: 0.7rem;
    letter-spacing: 0.1em;
    text-transform: uppercase;
}

.ar__badge--confirmada { background: rgba(111, 206, 138, 0.15); color: #6fce8a; }
.ar__badge--cancelada { background: rgba(232, 112, 112, 0.12); color: #e87070; }

.ar__vacio {
    text-align: center;
    color: var(--cine-text-muted);
    font-style: italic;
    padding: 2rem !important;
}

.ar__btn {
    padding: 0.4rem 0.85rem;
    border: 1px solid rgba(212, 168, 67, 0.25);
    border-radius: 5px;
    background: transparent;
    color: var(--cine-text-muted);
    font-size: 0.8rem;
    cursor: pointer;
    transition: border-color 0.15s, color 0.15s, background 0.15s;
}

.ar__btn:hover { border-color: var(--cine-gold); color: var(--cine-gold); }
.ar__btn--sm { padding: 0.25rem 0.6rem; }
.ar__btn--peligro { border-color: rgba(232,112,112,0.3); color: #e87070; }
.ar__btn--peligro:hover { border-color: #e87070; background: rgba(232,112,112,0.1); }
.ar__btn:disabled { opacity: 0.5; cursor: not-allowed; }

.ar__modal-fondo {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.65);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 500;
    padding: 1rem;
}

.ar__modal {
    background: var(--cine-surface);
    border: 1px solid rgba(212, 168, 67, 0.2);
    border-radius: 10px;
    padding: 2rem;
    width: 100%;
    max-width: 460px;
    max-height: 90vh;
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: rgba(212, 168, 67, 0.55) rgba(255, 255, 255, 0.05);
}

.ar__modal::-webkit-scrollbar {
    width: 10px;
}

.ar__modal::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 999px;
}

.ar__modal::-webkit-scrollbar-thumb {
    background: rgba(212, 168, 67, 0.55);
    border-radius: 999px;
    border: 2px solid rgba(255, 255, 255, 0.05);
}

.ar__modal::-webkit-scrollbar-thumb:hover {
    background: rgba(212, 168, 67, 0.75);
}

.ar__modal-titulo {
    font-family: var(--cine-font-display);
    font-size: 1.4rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    margin: 0 0 1rem;
    color: var(--cine-text);
}

.ar__modal-texto {
    font-size: 0.9rem;
    color: var(--cine-text-muted);
    margin: 0 0 1.5rem;
    line-height: 1.55;
}

.ar__modal-acciones {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
}
</style>
