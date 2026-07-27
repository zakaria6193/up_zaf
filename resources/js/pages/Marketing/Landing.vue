<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';
import { ArrowRight } from 'lucide-vue-next';

defineOptions({ layout: false });

defineProps<{
    googleEnabled: boolean;
    trialMinutes: number;
}>();

const scrolled = ref(false);

const onScroll = () => {
    scrolled.value = window.scrollY > 8;
};

onMounted(() => {
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
});

onUnmounted(() => {
    window.removeEventListener('scroll', onScroll);
});

const capabilities = [
    {
        title: 'Menu mobile',
        text: 'Vos clients ouvrent la carte sur leur téléphone — pas d’application à installer.',
    },
    {
        title: 'QR & lien public',
        text: 'Un QR pour les tables, un lien pour WhatsApp, Instagram ou Google Maps.',
    },
    {
        title: 'Mises à jour immédiates',
        text: 'Prix, plats du jour, ruptures de stock : vous modifiez, ils voient la version actuelle.',
    },
];

const steps = [
    { label: 'Compte', detail: 'Inscription en moins d’une minute.' },
    { label: 'Carte', detail: 'Catégories, plats, photos, prix.' },
    { label: 'Partage', detail: 'QR imprimable ou lien à envoyer.' },
];
</script>

<template>
    <Head title="UP1 — Menus digitaux pour restaurants" />

    <div class="page">
        <header class="topbar" :class="{ scrolled }">
            <a href="#top" class="logo-link">
                <img src="/logoup1.png" alt="UP1" class="logo" />
            </a>
            <nav class="topnav" aria-label="Sections">
                <a href="#offre">Offre</a>
                <a href="#demarrage">Démarrage</a>
            </nav>
            <div class="top-actions">
                <Link href="/login" class="link-quiet">Connexion</Link>
                <Link href="/register" class="btn-primary">Créer un compte</Link>
            </div>
        </header>

        <main id="top">
            <section class="hero">
                <div class="hero-text">
                    <p class="kicker">Menus digitaux pour restaurants</p>
                    <h1>
                        Votre carte en ligne,
                        <em>sans repasser par l’imprimeur.</em>
                    </h1>
                    <p class="intro">
                        UP1 aide les restaurateurs à publier un menu clair sur mobile, le partager avec un QR
                        code, et le mettre à jour quand les prix changent.
                    </p>
                    <div class="hero-cta">
                        <Link href="/register" class="btn-primary">
                            Commencer
                            <ArrowRight class="icon" />
                        </Link>
                        <Link href="/login" class="btn-secondary">J’ai déjà un compte</Link>
                    </div>
                    <p class="fine-print">Essai gratuit {{ trialMinutes }} minutes · sans carte bancaire</p>
                </div>

                <div class="hero-visual" aria-hidden="true">
                    <figure class="shot shot-menu">
                        <img src="/images/landing/phone-menu.jpg" alt="" />
                        <figcaption>Vue client</figcaption>
                    </figure>
                    <figure class="shot shot-qr">
                        <img src="/images/landing/qr-table.jpg" alt="" />
                        <figcaption>QR sur table</figcaption>
                    </figure>
                </div>
            </section>

            <section id="offre" class="offer">
                <div class="offer-head">
                    <h2>Ce que vous obtenez</h2>
                    <p>Pas de modules compliqués. Trois choses utiles, bien faites.</p>
                </div>

                <ul class="offer-list">
                    <li v-for="(item, index) in capabilities" :key="item.title">
                        <span class="offer-index">{{ String(index + 1).padStart(2, '0') }}</span>
                        <div>
                            <h3>{{ item.title }}</h3>
                            <p>{{ item.text }}</p>
                        </div>
                    </li>
                </ul>

                <div class="offer-aside">
                    <img src="/images/landing/dishes.jpg" alt="Plats en salle" loading="lazy" />
                    <p class="aside-note">
                        Pensé pour les cafés, restaurants, food trucks et traiteurs qui veulent une carte propre
                        sans refaire le design à chaque changement de prix.
                    </p>
                </div>
            </section>

            <section id="demarrage" class="start">
                <div class="start-inner">
                    <h2>Démarrer</h2>
                    <ol class="start-steps">
                        <li v-for="step in steps" :key="step.label">
                            <span class="step-label">{{ step.label }}</span>
                            <span class="step-detail">{{ step.detail }}</span>
                        </li>
                    </ol>
                    <Link href="/register" class="btn-primary on-dark">
                        Créer mon entreprise
                        <ArrowRight class="icon" />
                    </Link>
                </div>
                <div class="start-image">
                    <img src="/images/landing/update.jpg" alt="Mise à jour du menu" loading="lazy" />
                </div>
            </section>
        </main>

        <footer class="footer">
            <img src="/logoup1.png" alt="" class="footer-logo" />
            <p>UP1 — menus digitaux pour restaurants et métiers de bouche.</p>
            <div class="footer-links">
                <Link href="/login">Connexion</Link>
                <Link href="/register">Inscription</Link>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.page {
    --paper: #faf8f5;
    --paper-deep: #f0ebe4;
    --ink: #1a1714;
    --ink-muted: #5c564f;
    --rule: #ddd5cb;
    --accent: #e85d2b;
    --accent-dark: #c44a1c;
    --dark: #1a1714;
    --font-serif: 'Newsreader', ui-serif, Georgia, serif;
    --font-sans: 'IBM Plex Sans', ui-sans-serif, system-ui, sans-serif;

    min-height: 100vh;
    background: var(--paper);
    color: var(--ink);
    font-family: var(--font-sans);
    font-size: 1rem;
    line-height: 1.55;
}

/* —— Top bar —— */
.topbar {
    position: sticky;
    top: 0;
    z-index: 40;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 0.85rem 1.25rem;
    border-bottom: 1px solid transparent;
    background: rgba(250, 248, 245, 0.92);
    backdrop-filter: blur(6px);
    transition: border-color 0.2s ease;
}

.topbar.scrolled {
    border-bottom-color: var(--rule);
}

.logo-link {
    flex-shrink: 0;
}

.logo {
    height: 1.75rem;
    width: auto;
    display: block;
}

.topnav {
    display: none;
    gap: 1.75rem;
}

.topnav a {
    color: var(--ink-muted);
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
}

.topnav a:hover {
    color: var(--ink);
}

.top-actions {
    display: flex;
    align-items: center;
    gap: 0.65rem;
}

.link-quiet {
    display: none;
    color: var(--ink-muted);
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
}

.link-quiet:hover {
    color: var(--ink);
}

.btn-primary,
.btn-secondary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9rem;
    transition: background 0.15s ease, color 0.15s ease;
}

.btn-primary {
    padding: 0.6rem 1rem;
    background: var(--accent);
    color: white;
}

.btn-primary:hover {
    background: var(--accent-dark);
}

.btn-primary.on-dark {
    margin-top: 1.75rem;
}

.btn-secondary {
    padding: 0.6rem 0.85rem;
    color: var(--ink);
    border-bottom: 1px solid var(--ink);
}

.btn-secondary:hover {
    color: var(--accent-dark);
    border-color: var(--accent-dark);
}

.icon {
    width: 0.95rem;
    height: 0.95rem;
}

/* —— Hero —— */
.hero {
    display: grid;
    gap: 2.5rem;
    max-width: 68rem;
    margin: 0 auto;
    padding: 2.5rem 1.25rem 4rem;
    align-items: center;
}

.kicker {
    margin: 0 0 1rem;
    font-size: 0.8rem;
    font-weight: 600;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--accent-dark);
}

.hero h1 {
    margin: 0 0 1.1rem;
    font-family: var(--font-serif);
    font-size: clamp(2.35rem, 6vw, 3.75rem);
    font-weight: 600;
    line-height: 1.08;
    letter-spacing: -0.02em;
    max-width: 14ch;
}

.hero h1 em {
    font-style: italic;
    font-weight: 500;
    color: var(--ink-muted);
}

.intro {
    margin: 0 0 1.5rem;
    max-width: 38ch;
    color: var(--ink-muted);
    font-size: 1.02rem;
}

.hero-cta {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.75rem 1rem;
}

.fine-print {
    margin: 1rem 0 0;
    font-size: 0.82rem;
    color: var(--ink-muted);
}

.hero-visual {
    position: relative;
    min-height: 18rem;
}

.shot {
    margin: 0;
    overflow: hidden;
    border: 1px solid var(--rule);
    background: var(--paper-deep);
}

.shot img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.shot figcaption {
    padding: 0.45rem 0.65rem;
    font-size: 0.72rem;
    font-weight: 500;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--ink-muted);
    border-top: 1px solid var(--rule);
    background: white;
}

.shot-menu {
    width: min(72%, 260px);
    position: relative;
    z-index: 2;
}

.shot-qr {
    position: absolute;
    right: 0;
    bottom: -1.25rem;
    width: min(58%, 200px);
    transform: rotate(2deg);
    box-shadow: 8px 12px 0 rgba(26, 23, 20, 0.08);
}

/* —— Offer —— */
.offer {
    display: grid;
    gap: 2rem;
    max-width: 68rem;
    margin: 0 auto;
    padding: 3.5rem 1.25rem;
    border-top: 1px solid var(--rule);
}

.offer-head h2 {
    margin: 0 0 0.4rem;
    font-family: var(--font-serif);
    font-size: clamp(1.75rem, 4vw, 2.35rem);
    font-weight: 600;
    letter-spacing: -0.02em;
}

.offer-head p {
    margin: 0;
    color: var(--ink-muted);
}

.offer-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: grid;
    gap: 0;
    border-top: 1px solid var(--rule);
}

.offer-list li {
    display: grid;
    grid-template-columns: 3rem 1fr;
    gap: 1rem;
    padding: 1.35rem 0;
    border-bottom: 1px solid var(--rule);
}

.offer-index {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--accent-dark);
    padding-top: 0.2rem;
}

.offer-list h3 {
    margin: 0 0 0.3rem;
    font-size: 1.05rem;
    font-weight: 600;
}

.offer-list p {
    margin: 0;
    color: var(--ink-muted);
    font-size: 0.95rem;
    max-width: 42ch;
}

.offer-aside img {
    width: 100%;
    aspect-ratio: 4 / 3;
    object-fit: cover;
    border: 1px solid var(--rule);
}

.aside-note {
    margin: 0.85rem 0 0;
    padding-left: 0.85rem;
    border-left: 3px solid var(--accent);
    color: var(--ink-muted);
    font-size: 0.92rem;
    max-width: 36ch;
}

/* —— Start —— */
.start {
    display: grid;
    gap: 0;
    background: var(--dark);
    color: #f5f2ee;
}

.start-inner {
    padding: 3rem 1.25rem;
    max-width: 68rem;
    margin: 0 auto;
    width: 100%;
}

.start-inner h2 {
    margin: 0 0 1.5rem;
    font-family: var(--font-serif);
    font-size: clamp(1.75rem, 4vw, 2.25rem);
    font-weight: 600;
}

.start-steps {
    list-style: none;
    margin: 0;
    padding: 0;
    display: grid;
    gap: 1rem;
}

.start-steps li {
    display: grid;
    gap: 0.15rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.12);
}

.step-label {
    font-weight: 600;
    font-size: 1rem;
}

.step-detail {
    color: rgba(245, 242, 238, 0.65);
    font-size: 0.92rem;
}

.start-image {
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.start-image img {
    width: 100%;
    height: 100%;
    min-height: 14rem;
    object-fit: cover;
    opacity: 0.88;
}

/* —— Footer —— */
.footer {
    padding: 2.5rem 1.25rem 3rem;
    text-align: left;
    max-width: 68rem;
    margin: 0 auto;
    border-top: 1px solid var(--rule);
}

.footer-logo {
    height: 1.5rem;
    width: auto;
    margin-bottom: 0.75rem;
}

.footer p {
    margin: 0 0 1rem;
    color: var(--ink-muted);
    font-size: 0.9rem;
    max-width: 32ch;
}

.footer-links {
    display: flex;
    gap: 1.25rem;
}

.footer-links a {
    color: var(--ink);
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
}

.footer-links a:hover {
    color: var(--accent-dark);
}

@media (min-width: 768px) {
    .topbar {
        padding: 1rem 2rem;
    }

    .topnav,
    .link-quiet {
        display: flex;
    }

    .hero {
        grid-template-columns: 1.05fr 0.95fr;
        padding: 4rem 2rem 5rem;
        gap: 3rem;
    }

    .hero-visual {
        min-height: 22rem;
    }

    .offer {
        grid-template-columns: 1fr 0.9fr;
        grid-template-rows: auto 1fr;
        padding: 4.5rem 2rem;
        gap: 2.5rem 3rem;
    }

    .offer-head {
        grid-column: 1 / -1;
    }

    .offer-aside {
        align-self: start;
    }

    .start {
        grid-template-columns: 1fr 1fr;
    }

    .start-inner {
        padding: 4rem 2rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .start-image {
        border-top: none;
        border-left: 1px solid rgba(255, 255, 255, 0.1);
    }

    .footer {
        padding: 3rem 2rem 4rem;
        display: grid;
        grid-template-columns: auto 1fr auto;
        align-items: end;
        gap: 0 2rem;
    }

    .footer-logo {
        margin: 0;
    }

    .footer p {
        margin: 0;
    }
}
</style>
