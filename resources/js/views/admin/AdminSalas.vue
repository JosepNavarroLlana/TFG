<template>
    <div class="asl">
        <header class="asl__cabecera">
            <h1 class="asl__titulo">Salas — Ocupación hoy</h1>
            <p class="asl__fecha">{{ fechaHoy }}</p>
        </header>

        <div v-if="cargando" class="asl__estado">Cargando…</div>
        <div v-else-if="error" class="asl__estado asl__estado--error">{{ error }}</div>

        <div v-else class="asl__grid">
            <article v-for="sala in salas" :key="sala.id" class="asl__sala">
                <h2 class="asl__sala-nombre">{{ sala.nombre }}</h2>
                <p class="asl__sala-cap">Capacidad: {{ sala.capacidad }} butacas</p>

                <div v-if="sala.sesiones.length === 0" class="asl__vacio">
                    Sin sesiones hoy
                </div>

                <div v-else class="asl__sesiones">
                    <div
                        v-for="s in sala.sesiones"
                        :key="s.id"
                        class="asl__sesion"
                    >
                        <div class="asl__sesion-cabecera">
                            <span class="asl__sesion-hora">{{ hora(s.fecha_hora) }}</span>
                            <span class="asl__sesion-peli">{{ s.pelicula }}</span>
                            <span class="asl__sesion-nums">{{ s.ocupados }} / {{ s.total }}</span>
                        </div>
                        <div class="asl__barra-fondo">
                            <div
                                class="asl__barra-relleno"
                                :class="claseOcupacion(s.porcentaje)"
                                :style="{ width: `${s.porcentaje}%` }"
                            />
                        </div>
                        <p class="asl__sesion-pct">{{ s.porcentaje }}% ocupado</p>
                    </div>
                </div>
            </article>
        </div>
    </div>
</template>

<script>
import axios from 'axios'

export default {
    name: 'AdminSalas',
    data() {
        return {
            salas: [],
            cargando: true,
            error: null,
        }
    },
    computed: {
        fechaHoy() {
            return new Date().toLocaleDateString('es-ES', { weekday: 'long', day: 'numeric', month: 'long' })
        },
    },
    mounted() {
        this.cargar()
    },
    methods: {
        async cargar() {
            this.cargando = true
            try {
                const res = await axios.get('/api/admin/ocupacion')
                this.salas = res.data
            } catch {
                this.error = 'No se pudo cargar la ocupación.'
            } finally {
                this.cargando = false
            }
        },
        hora(fh) {
            if (!fh) return ''
            return new Date(fh.replace(' ', 'T')).toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' })
        },
        claseOcupacion(pct) {
            if (pct >= 80) return 'asl__barra-relleno--alta'
            if (pct >= 50) return 'asl__barra-relleno--media'
            return 'asl__barra-relleno--baja'
        },
    },
}
</script>

<style scoped>
.asl__cabecera {
    display: flex;
    align-items: baseline;
    gap: 1rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.asl__titulo {
    font-family: var(--cine-font-display);
    font-size: 2rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    margin: 0;
    color: var(--cine-text);
}

.asl__fecha {
    font-size: 0.82rem;
    color: var(--cine-text-muted);
    text-transform: capitalize;
    margin: 0;
}

.asl__estado { color: var(--cine-text-muted); padding: 2rem 0; }
.asl__estado--error { color: #e87070; }

.asl__grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
}

.asl__sala {
    background: var(--cine-surface);
    border: 1px solid rgba(212, 168, 67, 0.12);
    border-radius: 8px;
    padding: 1.35rem;
}

.asl__sala-nombre {
    font-family: var(--cine-font-display);
    font-size: 1.2rem;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--cine-gold);
    margin: 0 0 0.2rem;
}

.asl__sala-cap {
    font-size: 0.75rem;
    color: var(--cine-text-muted);
    margin: 0 0 1rem;
}

.asl__vacio {
    font-size: 0.82rem;
    color: var(--cine-text-muted);
    font-style: italic;
    padding: 0.5rem 0;
}

.asl__sesiones {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.asl__sesion {
    border-top: 1px solid rgba(255,255,255,0.05);
    padding-top: 0.85rem;
}

.asl__sesion-cabecera {
    display: flex;
    align-items: baseline;
    gap: 0.5rem;
    margin-bottom: 0.45rem;
    flex-wrap: wrap;
}

.asl__sesion-hora {
    font-family: var(--cine-font-display);
    font-size: 1.15rem;
    color: var(--cine-text);
    flex-shrink: 0;
}

.asl__sesion-peli {
    font-size: 0.78rem;
    color: var(--cine-text-muted);
    flex: 1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 140px;
}

.asl__sesion-nums {
    font-size: 0.78rem;
    color: var(--cine-text-muted);
    margin-left: auto;
    white-space: nowrap;
}

.asl__barra-fondo {
    height: 6px;
    background: rgba(255,255,255,0.08);
    border-radius: 999px;
    overflow: hidden;
}

.asl__barra-relleno {
    height: 100%;
    border-radius: 999px;
    transition: width 0.6s ease;
}

.asl__barra-relleno--baja { background: #6fce8a; }
.asl__barra-relleno--media { background: var(--cine-gold); }
.asl__barra-relleno--alta { background: #e87070; }

.asl__sesion-pct {
    font-size: 0.7rem;
    color: var(--cine-text-muted);
    margin: 0.3rem 0 0;
}
</style>
