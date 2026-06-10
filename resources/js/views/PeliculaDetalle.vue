<template>
    <section class="detalle">
        <div v-if="loading" class="detalle__estado">
            Cargando película...
        </div>

        <div v-else-if="error" class="detalle__estado detalle__estado--error">
            <p>{{ error }}</p>
            <router-link to="/" class="detalle__volver">← Volver a cartelera</router-link>
        </div>

        <template v-else-if="pelicula">
            <!-- Banner con tráiler en autoplay con audio -->
            <div class="detalle__banner">
                <router-link to="/" class="detalle__volver-banner">
                    ← Cartelera
                </router-link>

                <TrailerPlayer
                    ref="bannerTrailer"
                    v-if="pelicula.trailer_url && !trailerOculto"
                    :video-id="pelicula.trailer_url"
                    variant="cover"
                >
                    <template #overlay>
                        <div class="detalle__banner-acciones">
                            <button
                                class="detalle__expandir-trailer"
                                aria-label="Expandir tráiler"
                                @click="abrirExpandido"
                            >
                                ⛶ Expandir
                            </button>
                            <button
                                class="detalle__cerrar-trailer"
                                @click="trailerOculto = true"
                            >
                                ✕ Cerrar tráiler
                            </button>
                        </div>
                    </template>
                </TrailerPlayer>
                <template v-else>
                    <img
                        class="detalle__banner-img"
                        :src="pelicula.imagen"
                        :alt="pelicula.titulo"
                    />
                    <div class="detalle__banner-overlay" />
                    <div
                        v-if="pelicula.trailer_url"
                        class="detalle__banner-capa"
                    >
                        <div class="detalle__banner-acciones">
                            <button
                                class="detalle__expandir-trailer"
                                aria-label="Expandir tráiler"
                                @click="trailerExpandido = true"
                            >
                                ⛶ Expandir
                            </button>
                            <button
                                class="detalle__btn-mostrar-trailer"
                                @click="trailerOculto = false"
                            >
                                ▶ Ver tráiler
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Modal tráiler expandido -->
            <Teleport to="body">
                <div
                    v-if="trailerExpandido && pelicula.trailer_url"
                    class="detalle__modal"
                    @click.self="cerrarExpandido"
                >
                    <div class="detalle__modal-contenido">
                        <button
                            class="detalle__modal-cerrar"
                            aria-label="Cerrar"
                            @click="cerrarExpandido"
                        >
                            ✕
                        </button>
                        <TrailerPlayer
                            ref="modalTrailer"
                            :video-id="pelicula.trailer_url"
                            variant="modal"
                        />
                    </div>
                </div>
            </Teleport>

            <div class="detalle__cuerpo">
                <div class="detalle__layout">
                    <!-- Columna izquierda: póster + descripción -->
                    <div class="detalle__col-izq">
                        <div class="detalle__poster">
                            <img
                                :src="pelicula.imagen"
                                :alt="`Póster de ${pelicula.titulo}`"
                            />
                        </div>

                        <div class="detalle__info">
                            <h2 class="detalle__seccion-titulo">Descripción</h2>
                            <p class="detalle__descripcion">{{ pelicula.descripcion }}</p>

                            <div v-if="pelicula.direccion || pelicula.reparto" class="detalle__meta">
                                <div v-if="pelicula.direccion" class="detalle__meta-item">
                                    <span class="detalle__meta-label">Dirección</span>
                                    <span class="detalle__meta-valor">{{ pelicula.direccion }}</span>
                                </div>
                                <div v-if="pelicula.reparto" class="detalle__meta-item">
                                    <span class="detalle__meta-label">Reparto</span>
                                    <span class="detalle__meta-valor">{{ pelicula.reparto }}</span>
                                </div>
                            </div>

                            <p class="detalle__duracion">{{ pelicula.duracion }} min</p>
                        </div>
                    </div>

                    <!-- Columna central: título + reserva -->
                    <div class="detalle__col-centro">
                        <div class="detalle__cabecera">
                            <h1 class="detalle__titulo">{{ pelicula.titulo }}</h1>
                            <div class="detalle__tags">
                                <span class="detalle__tag">{{ pelicula.clasificacion }}</span>
                                <span class="detalle__tag">{{ pelicula.genero }}</span>
                                <span v-if="pelicula.estado === 'proximamente'" class="detalle__tag detalle__tag--proximamente">
                                    Próximamente
                                </span>
                            </div>
                        </div>

                        <div v-if="pelicula.estado === 'proximamente' && pelicula.fecha_estreno" class="detalle__estreno-badge">
                            Estreno: <strong>{{ formatearFechaEstreno(pelicula.fecha_estreno) }}</strong>
                        </div>

                        <ReservaPanel :sesiones="pelicula.sesiones || []" :estado="pelicula.estado" />
                    </div>
                </div>
            </div>
        </template>
    </section>
</template>

<script>
import axios from 'axios'
import ReservaPanel from '../components/ReservaPanel.vue'
import TrailerPlayer from '../components/TrailerPlayer.vue'

export default {
    name: 'PeliculaDetalle',
    components: { ReservaPanel, TrailerPlayer },
    data() {
        return {
            pelicula: null,
            loading: true,
            error: null,
            trailerOculto: false,
            trailerExpandido: false
        }
    },
    watch: {
        '$route.params.id': {
            immediate: true,
            handler() {
                this.trailerOculto = false
                this.cerrarExpandido()
                this.cargarPelicula()
            }
        },
        trailerExpandido(abierto) {
            document.body.style.overflow = abierto ? 'hidden' : ''
            if (abierto) {
                this.$nextTick(() => this.$refs.modalTrailer?.reanudar())
            }
        }
    },
    mounted() {
        document.addEventListener('keydown', this.onTeclaPulsada)
    },
    unmounted() {
        document.removeEventListener('keydown', this.onTeclaPulsada)
        document.body.style.overflow = ''
    },
    methods: {
        abrirExpandido() {
            this.$refs.bannerTrailer?.pausar()
            this.trailerExpandido = true
        },
        cerrarExpandido() {
            this.$refs.modalTrailer?.pausar()
            this.$refs.bannerTrailer?.reanudar()
            this.trailerExpandido = false
        },
        onTeclaPulsada(e) {
            if (e.key === 'Escape' && this.trailerExpandido) {
                this.cerrarExpandido()
            }
        },
        formatearFechaEstreno(fecha) {
            if (!fecha) return ''
            return new Date(fecha).toLocaleDateString('es-ES', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            })
        },
        async cargarPelicula() {
            this.loading = true
            this.error = null
            this.pelicula = null

            try {
                const res = await axios.get(`/api/peliculas/${this.$route.params.id}`)
                this.pelicula = res.data
            } catch {
                this.error = 'No se pudo cargar la película.'
            } finally {
                this.loading = false
            }
        }
    }
}
</script>

<style scoped>
.detalle {
    margin: 0 -1.5rem;
}

/* ── Banner ── */
.detalle__banner {
    position: relative;
    height: clamp(220px, 35vw, 380px);
    overflow: hidden;
    background: var(--cine-surface);
}

.detalle__banner-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center 20%;
    display: block;
}

.detalle__banner-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        to bottom,
        rgba(10, 8, 6, 0.3) 0%,
        rgba(10, 8, 6, 0.65) 100%
    );
    pointer-events: none;
}

.detalle__banner-capa {
    position: absolute;
    inset: 0;
    z-index: 3;
    pointer-events: auto;
}

.detalle__banner-capa .detalle__banner-acciones {
    opacity: 0;
    transition: opacity 0.25s ease;
    pointer-events: none;
}

.detalle__banner-capa:hover .detalle__banner-acciones {
    opacity: 1;
    pointer-events: auto;
}

.detalle__banner-acciones {
    position: absolute;
    top: 1rem;
    right: 1rem;
    display: flex;
    gap: 0.5rem;
}

.detalle__expandir-trailer,
.detalle__cerrar-trailer,
.detalle__btn-mostrar-trailer {
    font-family: var(--cine-font-display);
    font-size: 0.75rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--cine-text);
    background: rgba(10, 8, 6, 0.75);
    border: 1px solid rgba(212, 168, 67, 0.3);
    padding: 0.4rem 0.75rem;
    border-radius: 2px;
    cursor: pointer;
    backdrop-filter: blur(4px);
    transition: border-color 0.2s ease, color 0.2s ease;
}

.detalle__expandir-trailer:hover,
.detalle__cerrar-trailer:hover,
.detalle__btn-mostrar-trailer:hover {
    border-color: var(--cine-gold);
    color: var(--cine-gold);
}

/* ── Modal expandido ── */
.detalle__modal {
    position: fixed;
    inset: 0;
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.92);
    padding: 1.5rem;
}

.detalle__modal-contenido {
    position: relative;
    width: min(1100px, 100%);
    aspect-ratio: 16 / 9;
    padding-bottom: 3rem;
    box-shadow: 0 0 60px rgba(0, 0, 0, 0.8);
    border-radius: 4px;
    overflow: visible;
}

.detalle__modal-cerrar {
    position: absolute;
    top: -2.5rem;
    right: 0;
    font-family: var(--cine-font-display);
    font-size: 1.2rem;
    color: var(--cine-text);
    background: none;
    border: none;
    cursor: pointer;
    padding: 0.25rem 0.5rem;
    line-height: 1;
    transition: color 0.2s ease;
}

.detalle__modal-cerrar:hover {
    color: var(--cine-gold);
}

.detalle__volver-banner {
    position: absolute;
    top: 1rem;
    left: 1rem;
    z-index: 6;
    font-size: 0.82rem;
    color: var(--cine-text);
    text-decoration: none;
    background: rgba(10, 8, 6, 0.6);
    padding: 0.35rem 0.75rem;
    border-radius: 2px;
    backdrop-filter: blur(4px);
    transition: color 0.2s ease;
}

.detalle__volver-banner:hover {
    color: var(--cine-gold);
}

/* ── Grid principal ── */
.detalle__cuerpo {
    background: var(--cine-surface);
    border-top: 1px solid rgba(212, 168, 67, 0.1);
    padding: 0 1.5rem 4rem;
}

.detalle__layout {
    display: grid;
    grid-template-columns: minmax(200px, 260px) 1fr;
    gap: 2.5rem;
    max-width: 1200px;
    margin: 0 auto;
    padding-top: 1rem;
    align-items: start;
}

/* ── Columna izquierda ── */
.detalle__col-izq {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.detalle__poster {
    margin-top: -7rem;
    position: relative;
    z-index: 2;
}

.detalle__poster img {
    width: 100%;
    aspect-ratio: 2 / 3;
    object-fit: cover;
    display: block;
    border-radius: 6px;
    box-shadow:
        0 12px 40px rgba(0, 0, 0, 0.6),
        0 0 0 1px rgba(212, 168, 67, 0.15);
}

.detalle__seccion-titulo {
    font-family: var(--cine-font-display);
    font-size: 1rem;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--cine-gold);
    margin: 0 0 0.75rem;
}

.detalle__descripcion {
    font-size: 0.88rem;
    line-height: 1.7;
    color: var(--cine-text-muted);
    margin: 0 0 1.25rem;
}

.detalle__meta {
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
    margin-bottom: 1rem;
}

.detalle__meta-item {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
}

.detalle__meta-label {
    font-family: var(--cine-font-display);
    font-size: 0.72rem;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--cine-gold-dim);
}

.detalle__meta-valor {
    font-size: 0.85rem;
    color: var(--cine-text);
}

.detalle__duracion {
    font-size: 0.82rem;
    color: var(--cine-text-muted);
    margin: 0;
}

/* ── Columna central ── */
.detalle__col-centro {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    padding-top: 1rem;
}

.detalle__cabecera {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
}

.detalle__titulo {
    font-family: var(--cine-font-display);
    font-size: clamp(1.6rem, 3.5vw, 2.6rem);
    font-weight: 400;
    letter-spacing: 0.04em;
    line-height: 1.1;
    color: var(--cine-text);
    margin: 0;
    text-transform: uppercase;
    flex: 1;
    min-width: 200px;
}

.detalle__tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    flex-shrink: 0;
}

.detalle__tag {
    font-family: var(--cine-font-display);
    font-size: 0.78rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    padding: 0.3rem 0.75rem;
    background: rgba(212, 168, 67, 0.12);
    border: 1px solid rgba(212, 168, 67, 0.35);
    border-radius: 3px;
    color: var(--cine-gold);
}

.detalle__tag--proximamente {
    background: rgba(100, 150, 200, 0.15);
    border-color: rgba(100, 150, 200, 0.4);
    color: #64b3ff;
}

.detalle__estreno-badge {
    font-size: 0.9rem;
    color: var(--cine-text);
    padding: 0.75rem 1rem;
    background: rgba(100, 150, 200, 0.08);
    border: 1px solid rgba(100, 150, 200, 0.25);
    border-radius: 4px;
    margin: 0.5rem 0 1rem;
}

/* ── Estados ── */
.detalle__estado {
    text-align: center;
    padding: 4rem 1.5rem;
    color: var(--cine-text-muted);
}

.detalle__estado--error {
    color: #e8a0a0;
}

.detalle__volver {
    display: inline-block;
    margin-top: 1rem;
    color: var(--cine-gold);
    text-decoration: none;
    font-size: 0.85rem;
}

/* ── Responsive ── */
@media (max-width: 768px) {
    .detalle {
        margin: 0 -1rem;
    }

    .detalle__banner {
        height: 260px;
    }

    .detalle__volver-banner {
        top: 0.75rem;
        left: 0.75rem;
        font-size: 0.75rem;
    }

    .detalle__banner-acciones {
        top: auto;
        right: auto;
        left: 0.75rem;
        bottom: 0.75rem;
        flex-direction: column;
        align-items: flex-start;
    }

    .detalle__expandir-trailer,
    .detalle__cerrar-trailer,
    .detalle__btn-mostrar-trailer {
        font-size: 0.7rem;
        padding: 0.35rem 0.65rem;
    }

    .detalle__modal {
        padding: 0.75rem;
    }

    .detalle__modal-contenido {
        width: 100%;
        aspect-ratio: 16 / 10;
        padding-bottom: 4rem;
    }

    .detalle__modal-cerrar {
        top: -2rem;
        font-size: 1rem;
    }

    .detalle__layout {
        grid-template-columns: minmax(0, 1fr);
        gap: 1.5rem;
    }

    .detalle__poster {
        margin-top: -5rem;
        max-width: 180px;
    }

    .detalle__col-centro {
        padding-top: 0;
        min-width: 0;
    }

    .detalle__titulo {
        font-size: clamp(1.4rem, 6vw, 2rem);
    }

    .detalle__tags {
        gap: 0.4rem;
    }

    .detalle__tag {
        font-size: 0.7rem;
        padding: 0.25rem 0.6rem;
    }

    .detalle__descripcion {
        font-size: 0.85rem;
    }
}

@media (max-width: 480px) {
    .detalle__banner {
        height: 220px;
    }

    .detalle__banner-acciones {
        gap: 0.35rem;
    }

    .detalle__expandir-trailer,
    .detalle__cerrar-trailer,
    .detalle__btn-mostrar-trailer {
        width: fit-content;
    }

    .detalle__poster {
        max-width: 160px;
    }

    .detalle__cuerpo {
        padding: 0 1rem 3rem;
    }
}
</style>
