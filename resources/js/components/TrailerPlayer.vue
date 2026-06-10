<template>
    <div class="trailer" :class="`trailer--${variant}`">
        <div ref="viewport" class="trailer__viewport">
            <iframe
                :id="playerId"
                class="trailer__iframe"
                :src="embedUrl"
                title="Tráiler"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen"
                allowfullscreen
            />
        </div>

        <div
            v-if="variant === 'cover'"
            class="trailer__capa"
        >
            <slot name="overlay" />
            <div v-if="listo" class="trailer__controles">
                <button
                    class="trailer__btn-mute"
                    :aria-label="silenciado ? 'Activar sonido' : 'Silenciar'"
                    @click="toggleSilencio"
                >
                    <span v-if="silenciado">🔇</span>
                    <span v-else>🔊</span>
                </button>
                <input
                    type="range"
                    class="trailer__volumen"
                    min="0"
                    max="100"
                    :value="volumen"
                    aria-label="Volumen"
                    @input="cambiarVolumen"
                />
                <span class="trailer__volumen-label">{{ volumen }}%</span>
            </div>
        </div>

        <div v-else-if="listo" class="trailer__controles">
            <button
                class="trailer__btn-mute"
                :aria-label="silenciado ? 'Activar sonido' : 'Silenciar'"
                @click="toggleSilencio"
            >
                <span v-if="silenciado">🔇</span>
                <span v-else>🔊</span>
            </button>
            <input
                type="range"
                class="trailer__volumen"
                min="0"
                max="100"
                :value="volumen"
                aria-label="Volumen"
                @input="cambiarVolumen"
            />
            <span class="trailer__volumen-label">{{ volumen }}%</span>
        </div>
    </div>
</template>

<script>
let ytApiCargando = null
let playerIdCounter = 0

function cargarYoutubeApi() {
    if (window.YT?.Player) return Promise.resolve()
    if (ytApiCargando) return ytApiCargando

    ytApiCargando = new Promise((resolve) => {
        window.onYouTubeIframeAPIReady = () => resolve()
        const script = document.createElement('script')
        script.src = 'https://www.youtube.com/iframe_api'
        document.head.appendChild(script)
    })

    return ytApiCargando
}

export default {
    name: 'TrailerPlayer',
    props: {
        videoId: { type: String, required: true },
        variant: {
            type: String,
            default: 'cover',
            validator: v => ['cover', 'modal'].includes(v)
        }
    },
    data() {
        return {
            playerId: `trailer-player-${++playerIdCounter}`,
            player: null,
            listo: false,
            volumen: 100,
            silenciado: true,
            resizeObserver: null
        }
    },
    computed: {
        embedUrl() {
            const esModal = this.variant === 'modal'
            const params = new URLSearchParams({
                autoplay: '1',
                mute: esModal ? '0' : '1',
                rel: '0',
                controls: esModal ? '1' : '0',
                modestbranding: '1',
                enablejsapi: '1',
                iv_load_policy: '3',
                playsinline: '1'
            })
            return `https://www.youtube.com/embed/${this.videoId}?${params}`
        }
    },
    watch: {
        videoId() {
            this.reiniciarPlayer()
        }
    },
    mounted() {
        this.iniciarPlayer()
    },
    unmounted() {
        this.resizeObserver?.disconnect()
        this.player = null
    },
    methods: {
        async iniciarPlayer() {
            await cargarYoutubeApi()
            this.ajustarTamano()

            this.player = new window.YT.Player(this.playerId, {
                events: {
                    onReady: this.onPlayerListo
                }
            })

            this.observarRedimension()
        },
        onPlayerListo(event) {
            this.listo = true
            event.target.setVolume(this.volumen)
            if (this.variant === 'cover') {
                event.target.mute()
                this.silenciado = true
            } else {
                event.target.unMute()
                this.silenciado = false
                event.target.playVideo()
            }
        },
        ajustarTamano() {
            const viewport = this.$refs.viewport
            const iframe = viewport?.querySelector('iframe')
            if (!viewport || !iframe) return

            const W = viewport.clientWidth
            const H = viewport.clientHeight

            if (this.variant === 'modal') {
                iframe.style.width = `${W}px`
                iframe.style.height = `${H}px`
                return
            }

            const ratio = 16 / 9
            let iframeW, iframeH

            if (W / H > ratio) {
                iframeW = W
                iframeH = W / ratio
            } else {
                iframeH = H
                iframeW = H * ratio
            }

            iframe.style.width = `${Math.ceil(iframeW)}px`
            iframe.style.height = `${Math.ceil(iframeH)}px`
        },
        observarRedimension() {
            const viewport = this.$refs.viewport
            if (!viewport || typeof ResizeObserver === 'undefined') return

            this.resizeObserver?.disconnect()
            this.resizeObserver = new ResizeObserver(() => this.ajustarTamano())
            this.resizeObserver.observe(viewport)
        },
        reiniciarPlayer() {
            this.resizeObserver?.disconnect()
            this.player = null
            this.listo = false
            this.$nextTick(() => this.iniciarPlayer())
        },
        toggleSilencio() {
            if (!this.player) return
            if (this.silenciado) {
                this.player.unMute()
                if (this.volumen === 0) {
                    this.volumen = 80
                    this.player.setVolume(80)
                }
                this.silenciado = false
            } else {
                this.player.mute()
                this.silenciado = true
            }
        },
        cambiarVolumen(e) {
            const valor = Number(e.target.value)
            this.volumen = valor
            if (!this.player) return

            this.player.setVolume(valor)
            if (valor === 0) {
                this.player.mute()
                this.silenciado = true
            } else {
                this.player.unMute()
                this.silenciado = false
            }
        },
        pausar() {
            this.player?.pauseVideo?.()
        },
        reanudar() {
            if (!this.player) return
            if (this.variant === 'cover') {
                this.player.mute()
                this.player.playVideo()
                this.silenciado = true
                return
            }
            this.player.unMute()
            this.player.playVideo()
            this.silenciado = false
        }
    }
}
</script>

<style scoped>
.trailer {
    position: relative;
    width: 100%;
    height: 100%;
}

.trailer--cover {
    position: absolute;
    inset: 0;
}

.trailer__viewport {
    position: absolute;
    inset: 0;
    overflow: hidden;
}

.trailer--modal .trailer__viewport {
    border-radius: 4px;
}

.trailer__iframe {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    border: 0;
    pointer-events: auto;
}

.trailer__capa {
    position: absolute;
    inset: 0;
    z-index: 3;
    pointer-events: auto;
}

.trailer__capa :slotted(*) {
    opacity: 0;
    transition: opacity 0.25s ease;
    pointer-events: none;
}

.trailer__capa:hover :slotted(*) {
    opacity: 1;
    pointer-events: auto;
}

.trailer__controles {
    position: absolute;
    bottom: 1rem;
    right: 1rem;
    z-index: 4;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.4rem 0.65rem;
    background: rgba(10, 8, 6, 0.8);
    border: 1px solid rgba(212, 168, 67, 0.3);
    border-radius: 4px;
    backdrop-filter: blur(6px);
    opacity: 0;
    transition: opacity 0.25s ease;
    pointer-events: none;
}

.trailer__capa:hover .trailer__controles {
    opacity: 1;
    pointer-events: auto;
}

.trailer--modal .trailer__controles {
    bottom: -3rem;
    left: 0;
    right: 0;
    justify-content: center;
    background: transparent;
    border: none;
    backdrop-filter: none;
    padding: 0;
}

.trailer__btn-mute {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 1.1rem;
    line-height: 1;
    padding: 0.15rem;
    transition: transform 0.15s ease;
}

.trailer__btn-mute:hover {
    transform: scale(1.15);
}

.trailer__volumen {
    width: 90px;
    height: 4px;
    accent-color: var(--cine-gold);
    cursor: pointer;
}

.trailer--modal .trailer__volumen {
    width: 140px;
}

.trailer__volumen-label {
    font-family: var(--cine-font-display);
    font-size: 0.65rem;
    letter-spacing: 0.06em;
    color: var(--cine-text-muted);
    min-width: 2.2rem;
}
</style>
