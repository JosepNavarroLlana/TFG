<template>
    <router-link
        :to="`/pelicula/${pelicula.id}`"
        class="pelicula-card"
        :class="{ 'pelicula-card--destacada': destacada }"
    >
        <div class="pelicula-card__poster">
            <img
                :src="pelicula.imagen"
                :alt="`Póster de ${pelicula.titulo}`"
                loading="lazy"
            />
            <span class="pelicula-card__clasificacion">{{ pelicula.clasificacion }}</span>
            <div class="pelicula-card__overlay">
                <span class="pelicula-card__cta">Ver horarios</span>
            </div>
        </div>
        <div class="pelicula-card__info">
            <h2 class="pelicula-card__titulo">{{ pelicula.titulo }}</h2>
            <div class="pelicula-card__meta">
                <span class="pelicula-card__genero">{{ pelicula.genero }}</span>
                <span class="pelicula-card__duracion">{{ pelicula.duracion }} min</span>
            </div>
        </div>
    </router-link>
</template>

<script>
export default {
    name: 'PeliculaCard',
    props: {
        pelicula: {
            type: Object,
            required: true
        },
        destacada: {
            type: Boolean,
            default: false
        }
    }
}
</script>

<style scoped>
.pelicula-card {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
    text-decoration: none;
    color: inherit;
}

.pelicula-card--destacada .pelicula-card__poster {
    box-shadow:
        0 4px 20px rgba(0, 0, 0, 0.5),
        0 0 0 2px rgba(212, 168, 67, 0.45);
}

.pelicula-card__poster {
    position: relative;
    aspect-ratio: 2 / 3;
    border-radius: 6px;
    overflow: hidden;
    background: var(--cine-surface);
    box-shadow:
        0 4px 20px rgba(0, 0, 0, 0.5),
        0 0 0 1px rgba(212, 168, 67, 0.08);
    transition: transform 0.35s cubic-bezier(0.22, 1, 0.36, 1),
                box-shadow 0.35s ease;
}

.pelicula-card:hover .pelicula-card__poster {
    transform: translateY(-6px) scale(1.02);
    box-shadow:
        0 12px 40px rgba(0, 0, 0, 0.6),
        0 0 0 1px rgba(212, 168, 67, 0.25),
        0 0 30px rgba(212, 168, 67, 0.12);
}

.pelicula-card__poster img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.5s ease;
}

.pelicula-card:hover .pelicula-card__poster img {
    transform: scale(1.05);
}

.pelicula-card__clasificacion {
    position: absolute;
    top: 0.6rem;
    right: 0.6rem;
    padding: 0.2rem 0.5rem;
    font-family: var(--cine-font-display);
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    color: var(--cine-bg);
    background: var(--cine-gold);
    border-radius: 3px;
    line-height: 1.2;
    z-index: 2;
}

.pelicula-card__overlay {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: flex-end;
    justify-content: center;
    padding-bottom: 1.25rem;
    background: linear-gradient(
        to top,
        rgba(10, 8, 6, 0.85) 0%,
        transparent 55%
    );
    opacity: 0;
    transition: opacity 0.3s ease;
    z-index: 1;
}

.pelicula-card:hover .pelicula-card__overlay {
    opacity: 1;
}

.pelicula-card__cta {
    font-family: var(--cine-font-display);
    font-size: 0.85rem;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--cine-gold);
    border: 1px solid rgba(212, 168, 67, 0.5);
    padding: 0.45rem 1rem;
    border-radius: 2px;
    background: rgba(10, 8, 6, 0.6);
    backdrop-filter: blur(4px);
}

.pelicula-card__titulo {
    font-family: var(--cine-font-display);
    font-size: clamp(0.9rem, 2vw, 1.05rem);
    font-weight: 400;
    letter-spacing: 0.03em;
    line-height: 1.25;
    color: var(--cine-text);
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.pelicula-card__meta {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.35rem;
    font-size: 0.78rem;
    color: var(--cine-text-muted);
}

.pelicula-card__genero {
    padding: 0.15rem 0.5rem;
    border: 1px solid rgba(212, 168, 67, 0.2);
    border-radius: 2px;
    color: var(--cine-gold-dim);
    font-size: 0.72rem;
    letter-spacing: 0.06em;
    text-transform: uppercase;
}

.pelicula-card__duracion::before {
    content: '·';
    margin-right: 0.5rem;
    opacity: 0.5;
}
</style>
