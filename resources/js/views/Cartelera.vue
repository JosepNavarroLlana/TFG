<template>

    <section class="cartelera">

        <!-- Hero rotativo -->

        <article

            v-if="!loading && peliculaHero"

            class="cartelera__hero"

            :style="{ '--hero-intervalo': `${heroIntervalo}ms` }"

            @mouseenter="pausarHero"

            @mouseleave="reanudarHero"

        >

            <div class="cartelera__hero-fondos" aria-hidden="true">

                <img

                    v-for="(p, i) in heroPeliculas"

                    :key="p.id"

                    class="cartelera__hero-bg"

                    :class="{ 'cartelera__hero-bg--activa': i === heroIndice }"

                    :src="p.imagen"

                    :alt="p.titulo"

                />

            </div>

            <div class="cartelera__hero-overlay" />

            <transition name="hero-texto" mode="out-in">

                <div :key="peliculaHero.id" class="cartelera__hero-inner">

                    <p class="cartelera__hero-eyebrow">

                        Estreno destacado · {{ heroIndice + 1 }} / {{ heroPeliculas.length }}

                    </p>

                    <h1 class="cartelera__hero-titulo">{{ peliculaHero.titulo }}</h1>

                    <p class="cartelera__hero-desc">

                        {{ descripcionCorta(peliculaHero.descripcion) }}

                    </p>

                    <div class="cartelera__hero-meta">

                        <span class="cartelera__hero-tag">{{ peliculaHero.clasificacion }}</span>

                        <span class="cartelera__hero-tag">{{ peliculaHero.genero }}</span>

                        <span class="cartelera__hero-tag">{{ peliculaHero.duracion }} min</span>

                    </div>

                    <router-link

                        :to="`/pelicula/${peliculaHero.id}`"

                        class="cartelera__hero-cta"

                    >

                        Reservar entradas

                    </router-link>

                </div>

            </transition>

            <div

                v-if="heroPeliculas.length > 1"

                class="cartelera__hero-controles"

            >

                <button

                    type="button"

                    class="cartelera__hero-flecha"

                    aria-label="Película anterior"

                    @click="anteriorHero"

                >

                    ‹

                </button>

                <div class="cartelera__hero-dots" role="tablist" aria-label="Películas destacadas">

                    <button

                        v-for="(p, i) in heroPeliculas"

                        :key="p.id"

                        type="button"

                        role="tab"

                        class="cartelera__hero-dot"

                        :class="{ 'cartelera__hero-dot--activo': i === heroIndice }"

                        :aria-selected="i === heroIndice"

                        :aria-label="p.titulo"

                        @click="irHero(i)"

                    >

                        <span class="cartelera__hero-dot-track">

                            <span

                                v-if="i === heroIndice && heroAnimar"

                                :key="`prog-${heroIndice}-${heroProgresoKey}`"

                                class="cartelera__hero-dot-fill"

                                :class="{ 'cartelera__hero-dot-fill--pausado': heroPausado }"

                            />

                        </span>

                    </button>

                </div>

                <button

                    type="button"

                    class="cartelera__hero-flecha"

                    aria-label="Película siguiente"

                    @click="siguienteHero"

                >

                    ›

                </button>

            </div>

        </article>



        <!-- Sesiones de hoy -->

        <section v-if="!loading && sesionesHoyProximas.length > 0" class="cartelera__hoy">

            <header class="cartelera__hoy-cabecera">

                <div class="cartelera__hoy-grupo">

                    <h2 class="cartelera__titulo">Sesiones de hoy</h2>

                    <span v-if="proximaSesion" class="cartelera__hoy-countdown">

                        Próxima sesión en {{ cuentaAtras }}

                    </span>

                </div>

                <p class="cartelera__subtitulo">{{ fechaHoy }}</p>

            </header>

            <div class="cartelera__hoy-lista">

                <component

                    :is="esPasada(sesion) ? 'div' : 'router-link'"

                    v-for="sesion in sesionesHoyProximas"

                    :key="sesion.id"

                    :to="esPasada(sesion) ? undefined : `/pelicula/${sesion.pelicula.id}`"

                    class="cartelera__hoy-card"

                    :class="{

                        'cartelera__hoy-card--pasada': esPasada(sesion),

                        'cartelera__hoy-card--proxima': esProxima(sesion)

                    }"

                >

                    <span class="cartelera__hoy-hora">{{ hora(sesion.fecha_hora) }}</span>

                    <span class="cartelera__hoy-pelicula">{{ sesion.pelicula.titulo }}</span>

                    <span class="cartelera__hoy-detalle">

                        {{ sesion.sala.nombre }} · {{ formatoPrecio(sesion.precio) }}

                    </span>

                    <span class="cartelera__hoy-estado">

                        {{ estadoSesion(sesion) }}

                    </span>

                </component>

            </div>

        </section>



        <!-- Cabecera listado -->

        <header class="cartelera__header">

            <div class="cartelera__header-texto">

                <h2 class="cartelera__titulo">En cartelera</h2>

                <p v-if="!loading" class="cartelera__subtitulo">

                    {{ peliculasFiltradas.length }}

                    {{ peliculasFiltradas.length === 1 ? 'película' : 'películas' }}

                </p>

            </div>



            <div v-if="!loading && peliculas.length > 0" class="cartelera__busqueda">

                <input

                    v-model.trim="busqueda"

                    type="search"

                    class="cartelera__busqueda-input"

                    placeholder="Buscar por título..."

                    autocomplete="off"

                />

            </div>

        </header>



        <div v-if="!loading && generos.length > 1" class="cartelera__filtros">

            <button

                v-for="genero in generos"

                :key="genero"

                class="cartelera__filtro"

                :class="{ 'cartelera__filtro--activo': generoActivo === genero }"

                @click="generoActivo = genero"

            >

                {{ genero === 'todos' ? 'Todas' : genero }}

            </button>

        </div>



        <div v-if="loading" class="cartelera__grid cartelera__grid--loading">

            <div v-for="n in 6" :key="n" class="cartelera__skeleton">

                <div class="cartelera__skeleton-poster" />

                <div class="cartelera__skeleton-line cartelera__skeleton-line--wide" />

                <div class="cartelera__skeleton-line" />

            </div>

        </div>



        <div v-else-if="error" class="cartelera__estado cartelera__estado--error">

            <p>{{ error }}</p>

            <button class="cartelera__reintentar" @click="cargarPeliculas">

                Reintentar

            </button>

        </div>



        <div v-else-if="peliculasFiltradas.length === 0" class="cartelera__estado">

            <p v-if="busqueda">No hay resultados para «{{ busqueda }}».</p>

            <p v-else>No hay películas en este género.</p>

        </div>



        <div v-else class="cartelera__grid">

            <PeliculaCard

                v-for="(pelicula, index) in peliculasFiltradas"

                :key="pelicula.id"

                :pelicula="pelicula"

                :destacada="pelicula.id === peliculaHero?.id"

                class="cartelera__item"

                :style="{ animationDelay: `${index * 50}ms` }"

            />

        </div>



        <!-- Próximamente -->

        <section v-if="!loading && proximamente.length > 0" class="cartelera__prox">

            <header class="cartelera__prox-cabecera">

                <h2 class="cartelera__titulo">Próximamente</h2>

                <p class="cartelera__subtitulo">Muy pronto en tu cine</p>

            </header>

            <div class="cartelera__prox-lista">

                <router-link

                    v-for="pelicula in proximamente"

                    :key="pelicula.id"

                    :to="`/pelicula/${pelicula.id}`"

                    class="cartelera__prox-card"

                >

                    <div class="cartelera__prox-poster">

                        <img

                            v-if="pelicula.imagen"

                            :src="pelicula.imagen"

                            :alt="pelicula.titulo"

                            class="cartelera__prox-img"

                        />

                        <span v-else class="cartelera__prox-inicial">

                            {{ pelicula.titulo.charAt(0) }}

                        </span>

                        <span class="cartelera__prox-badge">

                            Estreno {{ fechaEstreno(pelicula.fecha_estreno) }}

                        </span>

                    </div>

                    <h3 class="cartelera__prox-titulo">{{ pelicula.titulo }}</h3>

                    <p class="cartelera__prox-meta">

                        {{ pelicula.genero }} · {{ pelicula.duracion }} min · {{ pelicula.clasificacion }}

                    </p>

                </router-link>

            </div>

        </section>

    </section>

</template>



<script>

import axios from 'axios'

import PeliculaCard from '../components/PeliculaCard.vue'



export default {

    name: 'Cartelera',

    components: { PeliculaCard },

    data() {

        return {

            peliculas: [],

            sesionesHoy: [],

            proximamente: [],

            ahora: Date.now(),

            relojTimer: null,

            loading: true,

            error: null,

            generoActivo: 'todos',

            busqueda: '',

            heroIndice: 0,

            heroTimer: null,

            heroPausado: false,

            heroProgresoKey: 0,

            heroIntervalo: 6000

        }

    },

    computed: {

        heroAnimar() {

            return !window.matchMedia('(prefers-reduced-motion: reduce)').matches

        },

        heroPeliculas() {

            const max = 5

            const esHeMan = p => /he-man/i.test(p.titulo)

            const sinHeMan = this.peliculas.filter(p => !esHeMan(p))

            const scary = this.peliculas.find(p => /scary movie/i.test(p.titulo))

            let hero = sinHeMan.slice(0, max)

            if (scary && !hero.some(p => p.id === scary.id)) {

                hero = [...sinHeMan.slice(0, max - 1), scary]

            }

            return hero

        },

        peliculaHero() {

            return this.heroPeliculas[this.heroIndice] ?? null

        },

        fechaHoy() {

            return new Date().toLocaleDateString('es-ES', {

                weekday: 'long',

                day: 'numeric',

                month: 'long'

            })

        },

        proximaSesion() {

            return this.sesionesHoyProximas[0] ?? null

        },

        sesionesHoyProximas() {

            return this.sesionesHoy.filter(

                s => new Date(s.fecha_hora.replace(' ', 'T')).getTime() > this.ahora

            )

        },

        cuentaAtras() {

            if (!this.proximaSesion) return ''

            const inicio = new Date(this.proximaSesion.fecha_hora.replace(' ', 'T')).getTime()

            const minutos = Math.ceil((inicio - this.ahora) / 60000)

            if (minutos >= 60) {

                const horas = Math.floor(minutos / 60)

                const resto = minutos % 60

                return resto > 0 ? `${horas} h ${resto} min` : `${horas} h`

            }

            return `${minutos} min`

        },

        generos() {

            const unicos = [...new Set(this.peliculas.map(p => p.genero))]

            return ['todos', ...unicos.sort()]

        },

        peliculasFiltradas() {

            let lista = this.peliculas



            if (this.generoActivo !== 'todos') {

                lista = lista.filter(p => p.genero === this.generoActivo)

            }



            if (this.busqueda) {

                const q = this.busqueda.toLowerCase()

                lista = lista.filter(p => p.titulo.toLowerCase().includes(q))

            }



            return lista

        }

    },

    mounted() {

        this.cargarPeliculas()

        this.cargarSesionesHoy()

        this.cargarProximamente()

        this.relojTimer = setInterval(() => {

            this.ahora = Date.now()

        }, 30000)

    },

    unmounted() {

        this.detenerHero()

        clearInterval(this.relojTimer)

    },

    watch: {

        heroIndice() {

            this.heroProgresoKey++

        }

    },

    methods: {

        async cargarPeliculas() {

            this.loading = true

            this.error = null

            try {

                const res = await axios.get('/api/peliculas')

                this.peliculas = res.data

                this.heroIndice = 0

                this.$nextTick(() => this.iniciarHero())

            } catch {

                this.error = 'No se pudieron cargar las películas. Inténtalo de nuevo.'

            } finally {

                this.loading = false

            }

        },

        async cargarSesionesHoy() {

            try {

                const res = await axios.get('/api/sesiones-hoy')

                this.sesionesHoy = res.data

            } catch {

                this.sesionesHoy = []

            }

        },

        async cargarProximamente() {

            try {

                const res = await axios.get('/api/proximamente')

                this.proximamente = res.data

            } catch {

                this.proximamente = []

            }

        },

        hora(fechaHora) {

            return new Date(fechaHora.replace(' ', 'T')).toLocaleTimeString('es-ES', {

                hour: '2-digit',

                minute: '2-digit'

            })

        },

        esPasada(sesion) {

            return new Date(sesion.fecha_hora.replace(' ', 'T')).getTime() < this.ahora

        },

        esProxima(sesion) {

            return this.proximaSesion?.id === sesion.id

        },

        estadoSesion(sesion) {

            if (this.esPasada(sesion)) return 'Finalizada'

            if (this.esProxima(sesion)) return `Empieza en ${this.cuentaAtras}`

            return 'Reservar →'

        },

        fechaEstreno(fecha) {

            if (!fecha) return 'próximamente'

            return new Date(`${fecha}T00:00:00`).toLocaleDateString('es-ES', {

                day: 'numeric',

                month: 'short'

            })

        },

        formatoPrecio(precio) {

            return `${Number(precio).toFixed(2).replace('.', ',')} €`

        },

        descripcionCorta(texto) {

            if (!texto) return ''

            return texto.length > 160 ? `${texto.slice(0, 160)}…` : texto

        },

        iniciarHero() {

            this.detenerHero()

            if (this.heroPeliculas.length <= 1) return

            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return

            this.heroTimer = setInterval(() => {

                this.heroIndice = (this.heroIndice + 1) % this.heroPeliculas.length

            }, this.heroIntervalo)

        },

        detenerHero() {

            if (this.heroTimer) {

                clearInterval(this.heroTimer)

                this.heroTimer = null

            }

        },

        pausarHero() {

            this.heroPausado = true

            this.detenerHero()

        },

        reanudarHero() {

            this.heroPausado = false

            this.heroProgresoKey++

            this.iniciarHero()

        },

        irHero(indice) {

            this.heroIndice = indice

            this.iniciarHero()

        },

        siguienteHero() {

            this.heroIndice = (this.heroIndice + 1) % this.heroPeliculas.length

            this.iniciarHero()

        },

        anteriorHero() {

            this.heroIndice = (this.heroIndice - 1 + this.heroPeliculas.length) % this.heroPeliculas.length

            this.iniciarHero()

        }

    }

}

</script>



<style scoped>

.cartelera {

    padding-bottom: 4rem;

}



/* ── Hero ── */

.cartelera__hero {

    position: relative;

    width: 100vw;

    margin-left: calc(50% - 50vw);

    margin-bottom: 2.5rem;

    min-height: clamp(320px, 55vh, 520px);

    display: flex;

    align-items: flex-end;

    overflow: hidden;

    --hero-pad-x: 1.5rem;

    --hero-pad-y: 1.5rem;

}



.cartelera__hero-fondos {

    position: absolute;

    inset: 0;

}

.cartelera__hero-bg {

    position: absolute;

    inset: 0;

    width: 100%;

    height: 100%;

    object-fit: cover;

    object-position: center 20%;

    opacity: 0;

    transition: opacity 1.2s ease;

}

.cartelera__hero-bg--activa {

    opacity: 1;

}



.cartelera__hero-overlay {

    position: absolute;

    inset: 0;

    background:

        linear-gradient(to top, var(--cine-bg) 0%, rgba(10, 8, 6, 0.75) 35%, rgba(10, 8, 6, 0.35) 100%),

        linear-gradient(to right, rgba(10, 8, 6, 0.85) 0%, transparent 60%);

}



.cartelera__hero-inner {

    position: relative;

    z-index: 1;

    max-width: 1200px;

    width: 100%;

    margin: 0 auto;

    padding: 2.5rem var(--hero-pad-x) calc(3rem + var(--hero-pad-y));

    box-sizing: border-box;

}



.cartelera__hero-eyebrow {

    font-family: var(--cine-font-display);

    font-size: 0.75rem;

    letter-spacing: 0.28em;

    text-transform: uppercase;

    color: var(--cine-gold);

    margin: 0 0 0.65rem;

    text-shadow: 0 1px 12px rgba(0, 0, 0, 0.6);

}



.cartelera__hero-titulo {

    font-family: var(--cine-font-display);

    font-size: clamp(2.2rem, 7vw, 4.5rem);

    font-weight: 400;

    letter-spacing: 0.04em;

    line-height: 0.95;

    text-transform: uppercase;

    color: var(--cine-text);

    margin: 0 0 1rem;

    max-width: 14ch;

    text-shadow: 0 2px 24px rgba(0, 0, 0, 0.75);

}



.cartelera__hero-desc {

    margin: 0 0 1.25rem;

    font-size: 0.92rem;

    line-height: 1.55;

    color: rgba(240, 232, 220, 0.75);

    max-width: 42ch;

    text-shadow: 0 1px 16px rgba(0, 0, 0, 0.65);

}



.cartelera__hero-meta {

    display: flex;

    flex-wrap: wrap;

    gap: 0.5rem;

    margin-bottom: 1.5rem;

}



.cartelera__hero-tag {

    font-family: var(--cine-font-display);

    font-size: 0.72rem;

    letter-spacing: 0.1em;

    text-transform: uppercase;

    padding: 0.3rem 0.65rem;

    border: 1px solid rgba(212, 168, 67, 0.35);

    border-radius: 2px;

    color: var(--cine-gold);

}



.cartelera__hero-cta {

    display: inline-block;

    font-family: var(--cine-font-display);

    font-size: 0.9rem;

    letter-spacing: 0.14em;

    text-transform: uppercase;

    text-decoration: none;

    padding: 0.7rem 1.6rem;

    background: var(--cine-gold);

    color: var(--cine-bg);

    border-radius: 3px;

    transition: opacity 0.2s ease, transform 0.2s ease;

}



.cartelera__hero-cta:hover {

    opacity: 0.92;

    transform: translateY(-1px);

}



.hero-texto-enter-active,

.hero-texto-leave-active {

    transition: opacity 0.45s ease, transform 0.45s ease;

}



.hero-texto-enter-from,

.hero-texto-leave-to {

    opacity: 0;

    transform: translateY(12px);

}



.cartelera__hero-controles {

    position: absolute;

    bottom: var(--hero-pad-y);

    right: var(--hero-pad-x);

    z-index: 2;

    display: flex;

    align-items: center;

    gap: 0.75rem;

}



.cartelera__hero-flecha {

    width: 2.25rem;

    height: 2.25rem;

    border: 1px solid rgba(212, 168, 67, 0.35);

    border-radius: 50%;

    background: rgba(10, 8, 6, 0.55);

    color: var(--cine-gold);

    font-size: 1.4rem;

    line-height: 1;

    cursor: pointer;

    transition: background 0.2s ease, border-color 0.2s ease;

}



.cartelera__hero-flecha:hover {

    background: rgba(10, 8, 6, 0.8);

    border-color: var(--cine-gold);

}



.cartelera__hero-dots {

    display: flex;

    gap: 0.45rem;

}



.cartelera__hero-dot {

    width: 2.5rem;

    height: 3px;

    padding: 0;

    border: none;

    border-radius: 2px;

    background: transparent;

    cursor: pointer;

}



.cartelera__hero-dot-track {

    display: block;

    width: 100%;

    height: 100%;

    border-radius: 2px;

    background: rgba(255, 255, 255, 0.25);

    overflow: hidden;

}



.cartelera__hero-dot-fill {

    display: block;

    height: 100%;

    width: 0;

    background: var(--cine-gold);

    border-radius: 2px;

    animation: hero-progreso var(--hero-intervalo, 6s) linear forwards;

}



.cartelera__hero-dot-fill--pausado {

    animation-play-state: paused;

}



@keyframes hero-progreso {

    from { width: 0; }

    to { width: 100%; }

}



/* ── Sesiones de hoy ── */

.cartelera__hoy {

    margin-bottom: 2.5rem;

}



.cartelera__hoy-cabecera {

    display: flex;

    align-items: baseline;

    justify-content: space-between;

    gap: 1rem;

    margin-bottom: 1rem;

    padding-bottom: 1rem;

    border-bottom: 1px solid rgba(212, 168, 67, 0.12);

}



.cartelera__hoy-cabecera .cartelera__subtitulo {

    text-transform: capitalize;

}



.cartelera__hoy-grupo {

    display: flex;

    align-items: center;

    flex-wrap: wrap;

    gap: 0.75rem;

}



.cartelera__hoy-countdown {

    font-family: var(--cine-font-display);

    font-size: 0.75rem;

    letter-spacing: 0.12em;

    text-transform: uppercase;

    color: var(--cine-gold);

    padding: 0.3rem 0.7rem;

    border: 1px solid rgba(212, 168, 67, 0.35);

    border-radius: 999px;

    background: rgba(212, 168, 67, 0.08);

}



.cartelera__hoy-lista {

    display: flex;

    gap: 0.85rem;

    padding-top: 0.35rem;

    overflow-x: auto;

    padding-bottom: 0.5rem;

    scroll-snap-type: x proximity;

    scrollbar-width: thin;

    scrollbar-color: rgba(212, 168, 67, 0.3) transparent;

}



.cartelera__hoy-card {

    flex: 0 0 auto;

    min-width: 11.5rem;

    display: flex;

    flex-direction: column;

    gap: 0.3rem;

    padding: 1rem 1.15rem;

    background: var(--cine-surface);

    border: 1px solid rgba(212, 168, 67, 0.15);

    border-radius: 6px;

    text-decoration: none;

    color: inherit;

    scroll-snap-align: start;

    transition: border-color 0.2s ease, transform 0.2s ease;

}



a.cartelera__hoy-card:hover {

    border-color: var(--cine-gold);

    transform: translateY(-1px);

}



.cartelera__hoy-card--pasada {

    opacity: 0.4;

}



.cartelera__hoy-card--proxima {

    border-color: var(--cine-gold);

    background: linear-gradient(160deg, rgba(212, 168, 67, 0.12), var(--cine-surface) 55%);

}



.cartelera__hoy-card--proxima .cartelera__hoy-estado {

    color: var(--cine-gold);

}



.cartelera__hoy-hora {

    font-family: var(--cine-font-display);

    font-size: 1.6rem;

    line-height: 1;

    color: var(--cine-gold);

}



.cartelera__hoy-pelicula {

    font-family: var(--cine-font-display);

    font-size: 0.95rem;

    letter-spacing: 0.08em;

    text-transform: uppercase;

    color: var(--cine-text);

    max-width: 13rem;

    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;

}



.cartelera__hoy-detalle {

    font-size: 0.78rem;

    color: var(--cine-text-muted);

}



.cartelera__hoy-estado {

    margin-top: 0.35rem;

    font-family: var(--cine-font-display);

    font-size: 0.72rem;

    letter-spacing: 0.14em;

    text-transform: uppercase;

    color: var(--cine-gold-dim);

}



/* ── Cabecera listado ── */

.cartelera__header {

    display: flex;

    flex-wrap: wrap;

    align-items: flex-end;

    justify-content: space-between;

    gap: 1rem 1.5rem;

    margin-bottom: 1.5rem;

    padding-bottom: 1rem;

    border-bottom: 1px solid rgba(212, 168, 67, 0.12);

}



.cartelera__titulo {

    font-family: var(--cine-font-display);

    font-size: clamp(1.6rem, 4vw, 2.2rem);

    font-weight: 400;

    letter-spacing: 0.1em;

    text-transform: uppercase;

    color: var(--cine-text);

    margin: 0;

    line-height: 1;

}



.cartelera__subtitulo {

    margin: 0.35rem 0 0;

    font-size: 0.85rem;

    color: var(--cine-text-muted);

}



.cartelera__busqueda {

    flex: 1 1 220px;

    max-width: 320px;

}



.cartelera__busqueda-input {

    width: 100%;

    font-family: var(--cine-font-body);

    font-size: 0.9rem;

    color: var(--cine-text);

    background: var(--cine-surface);

    border: 1px solid rgba(212, 168, 67, 0.2);

    border-radius: 4px;

    padding: 0.55rem 0.85rem;

    outline: none;

    transition: border-color 0.2s ease;

}



.cartelera__busqueda-input::placeholder {

    color: rgba(138, 125, 110, 0.7);

}



.cartelera__busqueda-input:focus {

    border-color: var(--cine-gold);

}



/* ── Filtros ── */

.cartelera__filtros {

    display: flex;

    flex-wrap: wrap;

    gap: 0.45rem;

    margin-bottom: 2rem;

}



.cartelera__filtro {

    font-family: var(--cine-font-display);

    font-size: 0.75rem;

    letter-spacing: 0.1em;

    text-transform: uppercase;

    padding: 0.4rem 0.9rem;

    border: 1px solid rgba(212, 168, 67, 0.18);

    border-radius: 999px;

    background: transparent;

    color: var(--cine-text-muted);

    cursor: pointer;

    transition: all 0.2s ease;

}



.cartelera__filtro:hover {

    border-color: rgba(212, 168, 67, 0.4);

    color: var(--cine-gold-dim);

}



.cartelera__filtro--activo {

    background: var(--cine-gold);

    border-color: var(--cine-gold);

    color: var(--cine-bg);

}



/* ── Grid ── */

.cartelera__grid {

    display: grid;

    grid-template-columns: repeat(2, 1fr);

    gap: 1.25rem 1rem;

}



@media (min-width: 540px) {

    .cartelera__grid {

        grid-template-columns: repeat(auto-fill, minmax(155px, 1fr));

        gap: 1.75rem 1.25rem;

    }

}



@media (min-width: 900px) {

    .cartelera__hero {

        --hero-pad-x: 2.5rem;

        --hero-pad-y: 2rem;

    }



    .cartelera__hero-controles {

        right: max(var(--hero-pad-x), calc((100% - 1200px) / 2 + var(--hero-pad-x)));

    }



    .cartelera__grid {

        grid-template-columns: repeat(auto-fill, minmax(185px, 1fr));

        gap: 2rem 1.5rem;

    }

}



.cartelera__item {

    animation: cartelera-reveal 0.45s cubic-bezier(0.22, 1, 0.36, 1) both;

}



@keyframes cartelera-reveal {

    from {

        opacity: 0;

        transform: translateY(14px);

    }

    to {

        opacity: 1;

        transform: translateY(0);

    }

}



/* ── Skeleton ── */

.cartelera__skeleton {

    display: flex;

    flex-direction: column;

    gap: 0.75rem;

}



.cartelera__skeleton-poster {

    aspect-ratio: 2 / 3;

    border-radius: 6px;

    background: linear-gradient(

        90deg,

        var(--cine-surface) 25%,

        rgba(212, 168, 67, 0.06) 50%,

        var(--cine-surface) 75%

    );

    background-size: 200% 100%;

    animation: cartelera-shimmer 1.4s ease infinite;

}



.cartelera__skeleton-line {

    height: 0.75rem;

    width: 60%;

    border-radius: 2px;

    background: var(--cine-surface);

    animation: cartelera-shimmer 1.4s ease infinite;

}



.cartelera__skeleton-line--wide {

    width: 85%;

}



@keyframes cartelera-shimmer {

    0% { background-position: 200% 0; }

    100% { background-position: -200% 0; }

}



/* ── Estados ── */

.cartelera__estado {

    text-align: center;

    padding: 4rem 1rem;

    color: var(--cine-text-muted);

    font-size: 1rem;

}



.cartelera__estado--error {

    color: #e8a0a0;

}



.cartelera__reintentar {

    margin-top: 1rem;

    font-family: var(--cine-font-display);

    font-size: 0.8rem;

    letter-spacing: 0.1em;

    text-transform: uppercase;

    padding: 0.55rem 1.25rem;

    border: 1px solid rgba(212, 168, 67, 0.4);

    border-radius: 2px;

    background: transparent;

    color: var(--cine-gold);

    cursor: pointer;

    transition: background 0.2s ease;

}



.cartelera__reintentar:hover {

    background: rgba(212, 168, 67, 0.1);

}



/* ── Próximamente ── */

.cartelera__prox {

    margin-top: 3.5rem;

}



.cartelera__prox-cabecera {

    display: flex;

    align-items: baseline;

    justify-content: space-between;

    gap: 1rem;

    margin-bottom: 1.5rem;

    padding-bottom: 1rem;

    border-bottom: 1px solid rgba(212, 168, 67, 0.12);

}



.cartelera__prox-lista {

    display: grid;

    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));

    gap: 1.75rem 1.25rem;

}



.cartelera__prox-poster {

    position: relative;

    aspect-ratio: 2 / 3;

    border-radius: 6px;

    overflow: hidden;

    background:

        linear-gradient(160deg, rgba(212, 168, 67, 0.16), rgba(212, 168, 67, 0.03) 55%),

        var(--cine-surface);

    border: 1px dashed rgba(212, 168, 67, 0.3);

    display: flex;

    align-items: center;

    justify-content: center;

    margin-bottom: 0.75rem;

    transition: transform 0.35s cubic-bezier(0.22, 1, 0.36, 1),
                box-shadow 0.35s ease;

}



.cartelera__prox-card:hover .cartelera__prox-poster {

    transform: translateY(-6px) scale(1.02);

    box-shadow:
        0 12px 40px rgba(0, 0, 0, 0.6),
        0 0 0 1px rgba(212, 168, 67, 0.25),
        0 0 30px rgba(212, 168, 67, 0.12);

}



.cartelera__prox-img {

    position: absolute;

    inset: 0;

    width: 100%;

    height: 100%;

    object-fit: cover;

}



.cartelera__prox-inicial {

    font-family: var(--cine-font-display);

    font-size: 4.5rem;

    color: rgba(212, 168, 67, 0.35);

}


.cartelera__prox-card {
    text-decoration: none;
    color: inherit;
}

.cartelera__prox-badge {

    position: absolute;

    bottom: 0;

    left: 0;

    right: 0;

    padding: 0.45rem 0.6rem;

    background: rgba(10, 8, 6, 0.85);

    border-top: 1px solid rgba(212, 168, 67, 0.3);

    font-family: var(--cine-font-display);

    font-size: 0.72rem;

    letter-spacing: 0.14em;

    text-transform: uppercase;

    text-align: center;

    color: var(--cine-gold);

}



.cartelera__prox-titulo {

    font-family: var(--cine-font-display);

    font-size: 1rem;

    font-weight: 400;

    letter-spacing: 0.06em;

    text-transform: uppercase;

    color: var(--cine-text);

    margin: 0 0 0.25rem;

}



.cartelera__prox-meta {

    font-size: 0.75rem;

    color: var(--cine-text-muted);

    margin: 0;

}



@media (prefers-reduced-motion: reduce) {

    .cartelera__item {

        animation: none;

    }



    .cartelera__skeleton-poster,

    .cartelera__skeleton-line {

        animation: none;

    }



    .cartelera__hero-cta:hover {

        transform: none;

    }



    .cartelera__hero-bg {

        transition: none;

    }



    .hero-texto-enter-active,

    .hero-texto-leave-active {

        transition: none;

    }



    .cartelera__hero-dot-fill {

        width: 100%;

        animation: none;

    }

}

</style>


