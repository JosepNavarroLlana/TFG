<template>
    <div class="ap">
        <header class="ap__cabecera">
            <h1 class="ap__titulo">Películas</h1>
            <button class="ap__btn ap__btn--primario" @click="abrirNueva">+ Nueva película</button>
        </header>

        <div v-if="cargando" class="ap__estado">Cargando…</div>
        <div v-else-if="error" class="ap__estado ap__estado--error">{{ error }}</div>
        <div v-else class="ap__tabla-wrapper">
            <table class="ap__tabla">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Género</th>
                        <th>Dur.</th>
                        <th>Clasif.</th>
                        <th>Estado</th>
                        <th>Estreno</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="p in peliculas" :key="p.id">
                        <td class="ap__td-titulo">{{ p.titulo }}</td>
                        <td>{{ p.genero }}</td>
                        <td>{{ p.duracion }} min</td>
                        <td>{{ p.clasificacion }}</td>
                        <td>
                            <select
                                class="ap__estado-select"
                                :value="p.estado"
                                @change="cambiarEstado(p, $event.target.value)"
                            >
                                <option value="activa">Activa</option>
                                <option value="proximamente">Próximamente</option>
                                <option value="inactiva">Inactiva</option>
                            </select>
                        </td>
                        <td>{{ p.fecha_estreno || '—' }}</td>
                        <td class="ap__acciones">
                            <button class="ap__btn ap__btn--sm" @click="abrirEdicion(p)">Editar</button>
                            <button class="ap__btn ap__btn--sm ap__btn--peligro" @click="confirmarBorrar(p)">Borrar</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Modal edición / creación -->
        <div v-if="modal" class="ap__modal-fondo" @click.self="cerrarModal">
            <div class="ap__modal">
                <h2 class="ap__modal-titulo">{{ editando ? 'Editar película' : 'Nueva película' }}</h2>
                <form class="ap__form" @submit.prevent="guardar">
                    <div class="ap__fila">
                        <div class="ap__campo ap__campo--ancho">
                            <label class="ap__label">Título *</label>
                            <input v-model="form.titulo" class="ap__input" required />
                        </div>
                    </div>
                    <div class="ap__campo">
                        <label class="ap__label">Descripción *</label>
                        <textarea v-model="form.descripcion" class="ap__input ap__input--area" rows="3" required />
                    </div>
                    <div class="ap__fila">
                        <div class="ap__campo">
                            <label class="ap__label">Género *</label>
                            <input v-model="form.genero" class="ap__input" required />
                        </div>
                        <div class="ap__campo">
                            <label class="ap__label">Duración (min) *</label>
                            <input v-model.number="form.duracion" type="number" min="1" class="ap__input" required />
                        </div>
                        <div class="ap__campo">
                            <label class="ap__label">Clasificación *</label>
                            <input v-model="form.clasificacion" class="ap__input" required />
                        </div>
                    </div>
                    <div class="ap__fila">
                        <div class="ap__campo ap__campo--ancho">
                            <label class="ap__label">Imagen del póster</label>
                            <div class="ap__imagen-zona">
                                <div v-if="form.imagen" class="ap__imagen-preview">
                                    <img :src="form.imagen" alt="Póster actual" class="ap__imagen-thumb" />
                                    <button type="button" class="ap__imagen-quitar" @click="form.imagen = ''">✕</button>
                                </div>
                                <label class="ap__imagen-label" :class="{ 'ap__imagen-label--subiendo': subiendoImagen }">
                                    <input
                                        type="file"
                                        accept="image/*"
                                        class="ap__imagen-input"
                                        @change="subirImagen"
                                        :disabled="subiendoImagen"
                                    />
                                    <span v-if="subiendoImagen">Subiendo…</span>
                                    <span v-else-if="form.imagen">Cambiar imagen</span>
                                    <span v-else>+ Seleccionar imagen</span>
                                </label>
                            </div>
                        </div>
                        <div class="ap__campo ap__campo--ancho">
                            <label class="ap__label">URL tráiler (YouTube)</label>
                            <input v-model="form.trailer_url" class="ap__input" placeholder="https://youtube.com/watch?v=..." />
                        </div>
                    </div>
                    <div class="ap__fila">
                        <div class="ap__campo">
                            <label class="ap__label">Estado</label>
                            <select v-model="form.estado" class="ap__input ap__select">
                                <option value="activa">Activa</option>
                                <option value="proximamente">Próximamente</option>
                                <option value="inactiva">Inactiva</option>
                            </select>
                        </div>
                        <div class="ap__campo">
                            <label class="ap__label">Fecha de estreno</label>
                            <input v-model="form.fecha_estreno" type="date" class="ap__input" />
                        </div>
                    </div>

                    <p v-if="errorModal" class="ap__error">{{ errorModal }}</p>

                    <div class="ap__modal-acciones">
                        <button type="button" class="ap__btn" @click="cerrarModal">Cancelar</button>
                        <button type="submit" class="ap__btn ap__btn--primario" :disabled="guardando">
                            {{ guardando ? 'Guardando…' : 'Guardar' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal confirmación borrar -->
        <div v-if="confirmar" class="ap__modal-fondo" @click.self="confirmar = null">
            <div class="ap__modal ap__modal--sm">
                <h2 class="ap__modal-titulo">¿Borrar película?</h2>
                <p class="ap__modal-texto">
                    Se eliminará <strong>{{ confirmar.titulo }}</strong> y todas sus sesiones asociadas. Esta acción no se puede deshacer.
                </p>
                <div class="ap__modal-acciones">
                    <button class="ap__btn" @click="confirmar = null">Cancelar</button>
                    <button class="ap__btn ap__btn--peligro" :disabled="guardando" @click="borrar">
                        {{ guardando ? 'Borrando…' : 'Sí, borrar' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios'

const formVacio = () => ({
    titulo: '',
    descripcion: '',
    genero: '',
    duracion: '',
    clasificacion: '',
    imagen: '',
    trailer_url: '',
    estado: 'activa',
    fecha_estreno: '',
})

export default {
    name: 'AdminPeliculas',
    data() {
        return {
            peliculas: [],
            cargando: true,
            error: null,
            modal: false,
            editando: null,
            form: formVacio(),
            guardando: false,
            subiendoImagen: false,
            errorModal: null,
            confirmar: null,
        }
    },
    mounted() {
        this.cargar()
    },
    methods: {
        async cargar() {
            this.cargando = true
            try {
                const res = await axios.get('/api/peliculas?all=1')
                // El endpoint público solo devuelve activas; llamamos al de admin
                const resAll = await axios.get('/api/admin/peliculas')
                this.peliculas = resAll.data
            } catch {
                // fallback al endpoint público
                try {
                    const res = await axios.get('/api/peliculas')
                    this.peliculas = res.data
                } catch {
                    this.error = 'No se pudieron cargar las películas.'
                }
            } finally {
                this.cargando = false
            }
        },
        abrirNueva() {
            this.editando = null
            this.form = formVacio()
            this.errorModal = null
            this.modal = true
        },
        abrirEdicion(p) {
            this.editando = p
            this.form = {
                titulo: p.titulo,
                descripcion: p.descripcion,
                genero: p.genero,
                duracion: p.duracion,
                clasificacion: p.clasificacion,
                imagen: p.imagen ?? '',
                trailer_url: p.trailer_url ?? '',
                estado: p.estado,
                fecha_estreno: p.fecha_estreno ?? '',
            }
            this.errorModal = null
            this.modal = true
        },
        cerrarModal() {
            this.modal = false
        },
        async guardar() {
            this.guardando = true
            this.errorModal = null
            try {
                const datos = { ...this.form }
                if (!datos.imagen) delete datos.imagen
                if (!datos.trailer_url) delete datos.trailer_url
                if (!datos.fecha_estreno) delete datos.fecha_estreno

                if (this.editando) {
                    const res = await axios.put(`/api/peliculas/${this.editando.id}`, datos)
                    const idx = this.peliculas.findIndex(p => p.id === this.editando.id)
                    if (idx !== -1) this.peliculas[idx] = res.data
                } else {
                    const res = await axios.post('/api/peliculas', datos)
                    this.peliculas.push(res.data)
                }
                this.modal = false
            } catch (e) {
                this.errorModal = e.response?.data?.message ?? 'Error al guardar.'
            } finally {
                this.guardando = false
            }
        },
        async subirImagen(event) {
            const archivo = event.target.files[0]
            if (!archivo) return
            this.subiendoImagen = true
            this.errorModal = null
            try {
                const formData = new FormData()
                formData.append('imagen', archivo)
                const res = await axios.post('/api/admin/subir-imagen', formData, {
                    headers: { 'Content-Type': 'multipart/form-data' },
                })
                this.form.imagen = res.data.ruta
            } catch (e) {
                this.errorModal = e.response?.data?.message ?? 'Error al subir la imagen.'
            } finally {
                this.subiendoImagen = false
                event.target.value = ''
            }
        },

        async cambiarEstado(pelicula, nuevoEstado) {
            try {
                const res = await axios.put(`/api/peliculas/${pelicula.id}`, { estado: nuevoEstado })
                pelicula.estado = res.data.estado
            } catch {
                // revertir visualmente si falla
                pelicula.estado = pelicula.estado
            }
        },
        confirmarBorrar(p) {
            this.confirmar = p
        },
        async borrar() {
            this.guardando = true
            try {
                await axios.delete(`/api/peliculas/${this.confirmar.id}`)
                this.peliculas = this.peliculas.filter(p => p.id !== this.confirmar.id)
                this.confirmar = null
            } catch {
                alert('No se pudo borrar la película.')
            } finally {
                this.guardando = false
            }
        },
    },
}
</script>

<style scoped>
.ap__cabecera {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.75rem;
}

.ap__titulo {
    font-family: var(--cine-font-display);
    font-size: 2rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    margin: 0;
    color: var(--cine-text);
}

.ap__estado {
    color: var(--cine-text-muted);
    padding: 2rem 0;
}

.ap__estado--error { color: #e87070; }

.ap__tabla-wrapper {
    overflow-x: auto;
}

.ap__tabla {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.88rem;
}

.ap__tabla th {
    text-align: left;
    padding: 0.6rem 0.85rem;
    font-family: var(--cine-font-display);
    font-size: 0.72rem;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--cine-gold);
    border-bottom: 1px solid rgba(212, 168, 67, 0.18);
}

.ap__tabla td {
    padding: 0.65rem 0.85rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    color: var(--cine-text);
    vertical-align: middle;
}

.ap__tabla tr:hover td {
    background: rgba(255, 255, 255, 0.02);
}

.ap__td-titulo {
    font-family: var(--cine-font-display);
    font-size: 0.92rem;
    letter-spacing: 0.04em;
    max-width: 260px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.ap__estado-select {
    background: var(--cine-surface);
    border: 1px solid rgba(212, 168, 67, 0.2);
    border-radius: 4px;
    color: var(--cine-text);
    padding: 0.25rem 0.5rem;
    font-size: 0.8rem;
    cursor: pointer;
}

.ap__acciones {
    display: flex;
    gap: 0.4rem;
    white-space: nowrap;
}

/* Botones */
.ap__btn {
    padding: 0.45rem 0.9rem;
    border: 1px solid rgba(212, 168, 67, 0.25);
    border-radius: 5px;
    background: transparent;
    color: var(--cine-text-muted);
    font-size: 0.8rem;
    cursor: pointer;
    transition: border-color 0.15s, color 0.15s, background 0.15s;
}

.ap__btn:hover:not(.ap__btn--primario) { border-color: var(--cine-gold); color: var(--cine-gold); }

.ap__btn--primario {
    background: var(--cine-gold);
    border-color: var(--cine-gold);
    color: var(--cine-bg);
    font-family: var(--cine-font-display);
    letter-spacing: 0.1em;
    text-transform: uppercase;
}

.ap__btn--primario:hover { opacity: 0.88; color: var(--cine-bg); }

.ap__btn--sm { padding: 0.3rem 0.65rem; }

.ap__btn--peligro { border-color: rgba(232, 112, 112, 0.3); color: #e87070; }
.ap__btn--peligro:hover { border-color: #e87070; background: rgba(232, 112, 112, 0.1); }

.ap__btn:disabled { opacity: 0.5; cursor: not-allowed; }

/* Modal */
.ap__modal-fondo {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.65);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 500;
    padding: 1rem;
}

.ap__modal {
    background: var(--cine-surface);
    border: 1px solid rgba(212, 168, 67, 0.2);
    border-radius: 10px;
    padding: 2rem;
    width: 100%;
    max-width: 700px;
    max-height: 90vh;
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: rgba(212, 168, 67, 0.55) rgba(255, 255, 255, 0.05);
}

.ap__modal::-webkit-scrollbar {
    width: 10px;
}

.ap__modal::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 999px;
}

.ap__modal::-webkit-scrollbar-thumb {
    background: rgba(212, 168, 67, 0.55);
    border-radius: 999px;
    border: 2px solid rgba(255, 255, 255, 0.05);
}

.ap__modal::-webkit-scrollbar-thumb:hover {
    background: rgba(212, 168, 67, 0.75);
}

.ap__modal--sm { max-width: 420px; }

.ap__modal-titulo {
    font-family: var(--cine-font-display);
    font-size: 1.4rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    margin: 0 0 1.5rem;
    color: var(--cine-text);
}

.ap__modal-texto {
    font-size: 0.9rem;
    color: var(--cine-text-muted);
    margin: 0 0 1.5rem;
    line-height: 1.5;
}

.ap__form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.ap__fila {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.ap__campo {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
    flex: 1;
    min-width: 140px;
}

.ap__campo--ancho { flex: 2; min-width: 200px; }

.ap__label {
    font-size: 0.72rem;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--cine-text-muted);
}

.ap__input {
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(212, 168, 67, 0.18);
    border-radius: 5px;
    padding: 0.55rem 0.75rem;
    color: var(--cine-text);
    font-family: var(--cine-font-body);
    font-size: 0.9rem;
    transition: border-color 0.2s;
    width: 100%;
    box-sizing: border-box;
}

.ap__input:focus { outline: none; border-color: var(--cine-gold); }

.ap__select {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-color: var(--cine-surface);
    color: var(--cine-text);
    padding-right: 2rem;
    background-image: linear-gradient(45deg, transparent 50%, var(--cine-gold) 50%),
        linear-gradient(135deg, var(--cine-gold) 50%, transparent 50%);
    background-position: calc(100% - 0.85rem) calc(50% - 2px), calc(100% - 0.65rem) calc(50% - 2px);
    background-size: 6px 6px, 6px 6px;
    background-repeat: no-repeat;
}

.ap__select option {
    background: var(--cine-surface);
    color: var(--cine-text);
}

.ap__input--area { resize: vertical; }

.ap__error {
    font-size: 0.82rem;
    color: #e87070;
    padding: 0.5rem 0.75rem;
    background: rgba(232,112,112,0.1);
    border-left: 3px solid #e87070;
    border-radius: 4px;
    margin: 0;
}

.ap__modal-acciones {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    margin-top: 0.5rem;
}

/* Zona de subida de imagen */
.ap__imagen-zona {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
}

.ap__imagen-preview {
    position: relative;
    display: inline-flex;
    width: 80px;
}

.ap__imagen-thumb {
    width: 80px;
    height: 110px;
    object-fit: cover;
    border-radius: 4px;
    border: 1px solid rgba(212, 168, 67, 0.25);
}

.ap__imagen-quitar {
    position: absolute;
    top: -6px;
    right: -6px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: none;
    background: #e87070;
    color: #fff;
    font-size: 0.65rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
}

.ap__imagen-label {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem 0.9rem;
    border: 1px dashed rgba(212, 168, 67, 0.35);
    border-radius: 5px;
    font-size: 0.82rem;
    color: var(--cine-text-muted);
    cursor: pointer;
    transition: border-color 0.2s ease, color 0.2s ease;
}

.ap__imagen-label:hover {
    border-color: var(--cine-gold);
    color: var(--cine-gold);
}

.ap__imagen-label--subiendo {
    opacity: 0.6;
    cursor: not-allowed;
}

.ap__imagen-input {
    display: none;
}
</style>
