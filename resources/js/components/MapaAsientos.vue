<template>
    <div class="mapa">
        <div v-if="cargando" class="mapa__cargando">Cargando butacas...</div>

        <div v-else class="mapa__sala-contenedor">
            <div class="mapa__sala">
                <div class="mapa__pantalla">PANTALLA</div>

                <div class="mapa__grid">
                    <div
                        v-for="fila in filas"
                        :key="fila.letra"
                        class="mapa__fila"
                    >
                        <span class="mapa__fila-label">{{ fila.letra }}</span>
                        <div class="mapa__fila-asientos">
                            <button
                                v-for="asiento in fila.asientos"
                                :key="asiento.id"
                                class="mapa__asiento"
                                :class="{
                                    'mapa__asiento--ocupado': ocupados.includes(asiento.id),
                                    'mapa__asiento--seleccionado': seleccionados.includes(asiento.id)
                                }"
                                :disabled="ocupados.includes(asiento.id)"
                                :aria-label="`Fila ${asiento.fila}, asiento ${asiento.numero}`"
                                @click="toggleAsiento(asiento.id)"
                            >
                                <span class="mapa__asiento-numero">{{ asiento.numero }}</span>
                            </button>
                        </div>
                        <span class="mapa__fila-label mapa__fila-label--der">{{ fila.letra }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="mapa__leyenda">
            <span class="mapa__leyenda-item">
                <span class="mapa__leyenda-asiento" /> Libre
            </span>
            <span class="mapa__leyenda-item">
                <span class="mapa__leyenda-asiento mapa__leyenda-asiento--seleccionado" /> Seleccionado
            </span>
            <span class="mapa__leyenda-item">
                <span class="mapa__leyenda-asiento mapa__leyenda-asiento--ocupado" /> Ocupado
            </span>
        </div>
    </div>
</template>

<script>
export default {
    name: 'MapaAsientos',
    props: {
        asientos: { type: Array, default: () => [] },
        ocupados: { type: Array, default: () => [] },
        seleccionados: { type: Array, default: () => [] },
        cargando: { type: Boolean, default: false }
    },
    emits: ['toggle'],
    computed: {
        filas() {
            const grupos = {}
            for (const asiento of this.asientos) {
                if (!grupos[asiento.fila]) grupos[asiento.fila] = []
                grupos[asiento.fila].push(asiento)
            }
            return Object.entries(grupos)
                .sort(([a], [b]) => a.localeCompare(b))
                .map(([letra, asientos]) => ({
                    letra,
                    asientos: asientos.sort((a, b) => a.numero - b.numero)
                }))
        }
    },
    methods: {
        toggleAsiento(id) {
            this.$emit('toggle', id)
        }
    }
}
</script>

<style scoped>
.mapa {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
    max-width: 100%;
}

.mapa__sala-contenedor {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    padding-bottom: 0.75rem;
}

/* Custom scrollbar to match cinema theme */
.mapa__sala-contenedor::-webkit-scrollbar {
    height: 6px;
}
.mapa__sala-contenedor::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.03);
    border-radius: 3px;
}
.mapa__sala-contenedor::-webkit-scrollbar-thumb {
    background: rgba(212, 168, 67, 0.35);
    border-radius: 3px;
}
.mapa__sala-contenedor::-webkit-scrollbar-thumb:hover {
    background: rgba(212, 168, 67, 0.6);
}

.mapa__sala {
    display: flex;
    flex-direction: column;
    align-items: center;
    min-width: max-content;
    width: 100%;
    box-sizing: border-box;
    padding: 0 1rem;
}

.mapa__pantalla {
    width: 100%;
    text-align: center;
    font-family: var(--cine-font-display);
    font-size: 0.7rem;
    letter-spacing: 0.3em;
    color: var(--cine-text-muted);
    padding: 0.45rem 1.5rem;
    margin-bottom: 1.5rem;
    border-top: 2px solid rgba(212, 168, 67, 0.35);
    border-bottom: 2px solid rgba(212, 168, 67, 0.35);
    background: linear-gradient(
        to bottom,
        rgba(212, 168, 67, 0.08) 0%,
        rgba(212, 168, 67, 0.02) 100%
    );
    border-radius: 2px;
    box-shadow: 0 4px 20px rgba(212, 168, 67, 0.06);
}

.mapa__cargando {
    text-align: center;
    padding: 2rem;
    color: var(--cine-text-muted);
    font-size: 0.85rem;
}

.mapa__grid {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.4rem;
}

.mapa__fila {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.mapa__fila-asientos {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
}

.mapa__fila-label {
    width: 1rem;
    font-family: var(--cine-font-display);
    font-size: 0.7rem;
    color: var(--cine-text-muted);
    text-align: center;
    flex-shrink: 0;
}

.mapa__fila-label--der {
    opacity: 0.35;
}

/* ── Asiento con forma de butaca ── */
.mapa__asiento {
    position: relative;
    width: 1.85rem;
    height: 2.1rem;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
    flex-shrink: 0;
    transition: transform 0.15s ease;
}

.mapa__asiento:not(:disabled):hover {
    transform: scale(1.1);
}

/* Respaldo */
.mapa__asiento::before {
    content: '';
    position: absolute;
    top: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 1.45rem;
    height: 0.6rem;
    border-radius: 5px 5px 1px 1px;
    border: 1px solid rgba(212, 168, 67, 0.35);
    border-bottom: none;
    background: var(--cine-bg);
    transition: background 0.15s ease, border-color 0.15s ease;
}

/* Asiento */
.mapa__asiento::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 1.65rem;
    height: 1rem;
    border-radius: 3px 3px 8px 8px;
    border: 1px solid rgba(212, 168, 67, 0.35);
    background: var(--cine-bg);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    transition: background 0.15s ease, border-color 0.15s ease;
}

.mapa__asiento-numero {
    position: absolute;
    bottom: 0.22rem;
    left: 50%;
    transform: translateX(-50%);
    font-size: 0.48rem;
    font-weight: 600;
    color: var(--cine-text-muted);
    z-index: 1;
    line-height: 1;
    pointer-events: none;
    transition: color 0.15s ease;
}

.mapa__asiento:not(:disabled):hover::before,
.mapa__asiento:not(:disabled):hover::after {
    border-color: var(--cine-gold);
}

.mapa__asiento:not(:disabled):hover .mapa__asiento-numero {
    color: var(--cine-gold);
}

.mapa__asiento--seleccionado::before,
.mapa__asiento--seleccionado::after {
    background: var(--cine-gold);
    border-color: var(--cine-gold);
}

.mapa__asiento--seleccionado .mapa__asiento-numero {
    color: var(--cine-bg);
}

.mapa__asiento--ocupado {
    cursor: not-allowed;
    opacity: 0.45;
}

.mapa__asiento--ocupado::before,
.mapa__asiento--ocupado::after {
    background: rgba(60, 52, 44, 0.8);
    border-color: rgba(80, 70, 60, 0.5);
    box-shadow: none;
}

.mapa__asiento--ocupado .mapa__asiento-numero {
    color: rgba(138, 125, 110, 0.4);
}

/* ── Leyenda ── */
.mapa__leyenda {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 1.25rem;
    margin-top: 1.5rem;
    width: 100%;
    font-size: 0.72rem;
    color: var(--cine-text-muted);
}

.mapa__leyenda-item {
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.mapa__leyenda-asiento {
    position: relative;
    display: inline-block;
    width: 1rem;
    height: 1.15rem;
}

.mapa__leyenda-asiento::before {
    content: '';
    position: absolute;
    top: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 0.75rem;
    height: 0.3rem;
    border-radius: 2px 2px 0 0;
    border: 1px solid rgba(212, 168, 67, 0.35);
    border-bottom: none;
    background: var(--cine-bg);
}

.mapa__leyenda-asiento::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 0.85rem;
    height: 0.55rem;
    border-radius: 2px 2px 4px 4px;
    border: 1px solid rgba(212, 168, 67, 0.35);
    background: var(--cine-bg);
}

.mapa__leyenda-asiento--seleccionado::before,
.mapa__leyenda-asiento--seleccionado::after {
    background: var(--cine-gold);
    border-color: var(--cine-gold);
}

.mapa__leyenda-asiento--ocupado::before,
.mapa__leyenda-asiento--ocupado::after {
    background: rgba(60, 52, 44, 0.8);
    border-color: rgba(80, 70, 60, 0.5);
}
</style>
