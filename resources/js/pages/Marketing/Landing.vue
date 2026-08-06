<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';
import { ArrowRight, QrCode, RefreshCw, ScanLine, Smartphone } from 'lucide-vue-next';

defineOptions({ layout: false });

defineProps<{
    googleEnabled: boolean;
    trialMinutes: number;
}>();

const scrolled = ref(false);

const onScroll = () => {
    scrolled.value = window.scrollY > 10;
};

onMounted(() => {
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
});

onUnmounted(() => {
    window.removeEventListener('scroll', onScroll);
});

/** Same primary action everywhere - one job for the page. */
const ctaLabel = (minutes: number) => `Try ${minutes} free minutes`;

/** Bust CDN/browser cache after swapping stock photos. */
const img = (path: string) => `${path}?v=20`;

const featureTiles = [
    {
        icon: Smartphone,
        title: 'A menu guests open instantly',
        text: 'On their phone, no app. Categories, photos, and prices - clear in the dining room or for delivery.',
    },
    {
        icon: QrCode,
        title: 'One QR and one public link',
        text: 'Tables, WhatsApp, Instagram, Google Maps: one live menu everywhere customers find you.',
    },
    {
        icon: RefreshCw,
        title: 'Prices that stay current',
        text: 'No reprinting. Change an item and the public menu updates - including sold-outs and specials.',
    },
];

const steps = [
    { title: 'Create', text: 'Open your account and add your restaurant.' },
    { title: 'Import with AI', text: 'Photo your paper menu - AI extracts items and prices for you to review.' },
    { title: 'Share', text: 'Print your QR or send the link in one tap.' },
];
</script>

<template>
    <Head title="UP1 - Digital menus for restaurants, powered by AI" />

    <div class="page">
        <header class="top" :class="{ scrolled }">
            <a href="#top" class="brand" aria-label="UP1 home">
                <img src="/logoup1.png" alt="UP1" />
            </a>
            <div class="top-actions">
                <Link href="/login" class="login">Log in</Link>
                <Link href="/register" class="btn btn-primary btn-sm">
                    {{ ctaLabel(trialMinutes) }}
                </Link>
            </div>
        </header>

        <main id="top">
            <!-- Hero -->
            <section class="hero">
                <div class="hero-bg" aria-hidden="true">
                    <img :src="img('/images/landing/hero.jpg')" alt="" />
                    <div class="hero-scrim" />
                </div>

                <div class="hero-grid">
                    <div class="hero-copy">
                        <p class="brand-word">UP1</p>
                        <h1>Your menu, live on phones - built with AI.</h1>
                        <p class="lead">
                            Photograph your paper menu and let AI turn it into a digital menu. Share it with a QR
                            code or link, then update prices whenever service needs it.
                        </p>

                        <div class="cta-block">
                            <Link href="/register" class="btn btn-primary">
                                {{ ctaLabel(trialMinutes) }}
                                <ArrowRight class="ico" />
                            </Link>
                            <p class="reassure">No credit card. Ready in a few minutes</p>
                        </div>
                    </div>

                    <div class="hero-product">
                        <div class="device">
                            <div class="device-notch" aria-hidden="true" />
                            <img
                                :src="img('/images/landing/product-menu.png')"
                                alt="Example of an UP1 digital menu on a phone"
                                class="device-screen"
                            />
                        </div>
                    </div>
                </div>
            </section>

            <section class="proof" aria-label="Key points">
                <ul>
                    <li>AI menu from a photo</li>
                    <li>QR + public link</li>
                    <li>Instant updates</li>
                    <li>{{ trialMinutes }}-minute free trial</li>
                </ul>
            </section>

            <section class="story">
                <div class="story-text">
                    <h2>Paper menus fall behind the kitchen.</h2>
                    <p>
                        A price change, a sold-out dish, a daily special - and the printed card is already wrong.
                        Guests deserve tonight's menu, not yesterday's.
                    </p>
                </div>
                <figure class="story-shot">
                    <img
                        :src="img('/images/landing/dishes.jpg')"
                        alt="Cheeseburger with fries ready to serve"
                        loading="lazy"
                    />
                    <figcaption>Your menu should move as fast as the kitchen - not the printer.</figcaption>
                </figure>
            </section>

            <!-- Features: text-first, no photos -->
            <section class="features" id="features">
                <div class="features-inner">
                    <div class="features-head">
                        <p class="eyebrow">How UP1 helps</p>
                        <h2>From paper photo to live menu - without the busywork.</h2>
                    </div>

                    <article class="ai-band">
                        <div class="ai-band-mark" aria-hidden="true">
                            <ScanLine class="ai-band-ico" />
                        </div>
                        <div class="ai-band-copy">
                            <span class="ai-badge">AI menu import</span>
                            <h3>Snap a photo. AI builds the draft.</h3>
                            <p>
                                Point your camera at the paper menu you already use. UP1's AI reads the dishes and
                                prices, then lets you review before publishing - so your team spends minutes, not an
                                afternoon typing.
                            </p>
                        </div>
                        <Link href="/register" class="btn btn-primary ai-band-cta">
                            {{ ctaLabel(trialMinutes) }}
                            <ArrowRight class="ico" />
                        </Link>
                    </article>

                    <div class="feature-list">
                        <article v-for="tile in featureTiles" :key="tile.title" class="feature-item">
                            <span class="feature-ico-wrap" aria-hidden="true">
                                <component :is="tile.icon" class="feature-ico" />
                            </span>
                            <h3>{{ tile.title }}</h3>
                            <p>{{ tile.text }}</p>
                        </article>
                    </div>
                </div>
            </section>

            <section class="how" id="how">
                <div class="how-inner">
                    <h2>Three steps</h2>
                    <ol>
                        <li v-for="(step, i) in steps" :key="step.title">
                            <span>{{ i + 1 }}</span>
                            <strong>{{ step.title }}</strong>
                            <p>{{ step.text }}</p>
                        </li>
                    </ol>
                </div>
                <div class="how-visual">
                    <img
                        :src="img('/images/landing/qr-table.jpg')"
                        alt="Smash burger ready to serve"
                        loading="lazy"
                    />
                    <img
                        :src="img('/images/landing/update.jpg')"
                        alt="QR code tent card on a restaurant table"
                        loading="lazy"
                        class="how-overlay"
                    />
                </div>
            </section>

            <section class="close">
                <blockquote>
                    <p>
                        You don't need another complicated tool. You need the menu your guest reads to be the
                        right one - tonight, at that table. AI gets you there faster.
                    </p>
                </blockquote>
                <div class="close-cta">
                    <Link href="/register" class="btn btn-primary btn-lg">
                        {{ ctaLabel(trialMinutes) }}
                        <ArrowRight class="ico" />
                    </Link>
                    <Link href="/login" class="text-link">I already have an account</Link>
                </div>
            </section>
        </main>

        <footer class="foot">
            <img src="/logoup1.png" alt="" class="foot-logo" />
            <p>UP1 - digital menus for restaurants, with AI import from photos.</p>
            <div class="foot-links">
                <Link href="/login">Log in</Link>
                <Link href="/register">Sign up</Link>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.page {
    --bg: #140c08;
    --panel: #1f130c;
    --paper: #fff8f3;
    --ink: #2a170f;
    --muted: #6e5346;
    --line: rgba(42, 23, 15, 0.12);
    --ember: #e84e1b;
    --ember-deep: #c53c10;
    --sand: #f3e4d8;
    --font: 'Plus Jakarta Sans', 'IBM Plex Sans', ui-sans-serif, system-ui, sans-serif;

    min-height: 100vh;
    background: var(--paper);
    color: var(--ink);
    font-family: var(--font);
    line-height: 1.55;
}

.ico {
    width: 1.05rem;
    height: 1.05rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    padding: 0.85rem 1.25rem;
    border-radius: 0.85rem;
    font-weight: 700;
    font-size: 0.95rem;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition:
        background 0.15s ease,
        transform 0.15s ease;
}

.btn:hover {
    transform: translateY(-1px);
}

.btn-sm {
    padding: 0.55rem 0.95rem;
    font-size: 0.84rem;
    border-radius: 0.7rem;
}

.btn-lg {
    padding: 1rem 1.45rem;
    font-size: 1.02rem;
}

.btn-primary {
    background: var(--ember);
    color: #fff;
}

.btn-primary:hover {
    background: var(--ember-deep);
}

/* -- Top bar: logo + one CTA (less nav leakage) -- */
.top {
    position: sticky;
    top: 0;
    z-index: 40;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 0.85rem 1.15rem;
    background: rgba(20, 12, 8, 0.72);
    backdrop-filter: blur(10px);
    transition: background 0.2s ease;
}

.top.scrolled {
    background: rgba(20, 12, 8, 0.92);
}

.brand img {
    height: 1.55rem;
    width: auto;
    display: block;
}

.top-actions {
    display: flex;
    align-items: center;
    gap: 0.65rem;
}

.login {
    color: rgba(255, 248, 243, 0.78);
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 600;
}

.login:hover {
    color: #fff;
}

/* -- Hero -- */
.hero {
    position: relative;
    min-height: min(92vh, 52rem);
    display: flex;
    align-items: flex-end;
    color: #fff;
    overflow: hidden;
}

.hero-bg {
    position: absolute;
    inset: 0;
}

.hero-bg img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transform: scale(1.04);
    animation: drift 18s ease-in-out infinite alternate;
}

.hero-scrim {
    position: absolute;
    inset: 0;
    background:
        linear-gradient(105deg, rgba(14, 8, 5, 0.92) 0%, rgba(14, 8, 5, 0.72) 48%, rgba(14, 8, 5, 0.35) 100%),
        linear-gradient(to top, rgba(14, 8, 5, 0.75), transparent 45%);
}

.hero-grid {
    position: relative;
    z-index: 1;
    width: min(72rem, 100%);
    margin: 0 auto;
    padding: 5.5rem 1.15rem 3.25rem;
    display: grid;
    gap: 2.25rem;
    align-items: end;
}

.brand-word {
    margin: 0 0 0.75rem;
    font-size: clamp(2.4rem, 7vw, 3.5rem);
    font-weight: 800;
    letter-spacing: -0.05em;
    line-height: 0.95;
    color: var(--ember);
}

.hero h1 {
    margin: 0 0 1rem;
    max-width: 13ch;
    font-size: clamp(2rem, 5.2vw, 3.35rem);
    font-weight: 800;
    letter-spacing: -0.035em;
    line-height: 1.08;
}

.lead {
    margin: 0 0 1.5rem;
    max-width: 36ch;
    color: rgba(255, 248, 243, 0.82);
    font-size: 1.05rem;
}

.cta-block {
    display: grid;
    gap: 0.55rem;
    justify-items: start;
}

.reassure {
    margin: 0;
    font-size: 0.82rem;
    color: rgba(255, 248, 243, 0.62);
}

.hero-product {
    display: flex;
    justify-content: center;
}

.device {
    width: min(100%, 280px);
    border-radius: 2rem;
    padding: 0.55rem 0.5rem 0.5rem;
    background: #0b0705;
    border: 1px solid rgba(255, 255, 255, 0.18);
    box-shadow: 0 28px 60px rgba(0, 0, 0, 0.45);
    animation: lift 5.5s ease-in-out infinite;
}

.device-notch {
    width: 34%;
    height: 0.4rem;
    margin: 0.15rem auto 0.4rem;
    border-radius: 999px;
    background: #1a1210;
}

.device-screen {
    display: block;
    width: 100%;
    aspect-ratio: 9 / 19.5;
    height: auto;
    min-height: 28rem;
    max-height: min(70vh, 36rem);
    object-fit: cover;
    object-position: top center;
    border-radius: 1.45rem;
    background: #fff;
}

/* -- Proof -- */
.proof {
    background: var(--panel);
    color: rgba(255, 248, 243, 0.88);
    padding: 1rem 1.15rem;
}

.proof ul {
    list-style: none;
    margin: 0 auto;
    padding: 0;
    max-width: 72rem;
    display: grid;
    gap: 0.65rem;
    font-size: 0.9rem;
    font-weight: 600;
}

.proof li {
    padding-left: 0.9rem;
    border-left: 2px solid var(--ember);
}

/* -- Story -- */
.story {
    display: grid;
    gap: 2rem;
    max-width: 72rem;
    margin: 0 auto;
    padding: 3.5rem 1.15rem;
}

.story-text h2 {
    margin: 0 0 0.85rem;
    font-size: clamp(1.65rem, 3.8vw, 2.35rem);
    font-weight: 800;
    letter-spacing: -0.03em;
    line-height: 1.15;
    max-width: 18ch;
}

.story-text p {
    margin: 0;
    color: var(--muted);
    max-width: 42ch;
    font-size: 1.05rem;
}

.story-shot {
    margin: 0;
}

.story-shot img {
    width: 100%;
    aspect-ratio: 5 / 3;
    object-fit: cover;
    border-radius: 1.25rem;
}

.story-shot figcaption {
    margin-top: 0.7rem;
    font-size: 0.9rem;
    color: var(--muted);
    font-weight: 600;
}

/* -- Features (text-first, no photos) -- */
.features {
    background: var(--sand);
    padding: 3.75rem 1.15rem 4rem;
}

.features-inner {
    max-width: 72rem;
    margin: 0 auto;
}

.features-head {
    max-width: 32rem;
    margin-bottom: 1.75rem;
}

.eyebrow {
    margin: 0 0 0.55rem;
    font-size: 0.8rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--ember);
}

.features-head h2 {
    margin: 0;
    font-size: clamp(1.7rem, 4vw, 2.45rem);
    font-weight: 800;
    letter-spacing: -0.035em;
    line-height: 1.12;
}

.ai-band {
    display: grid;
    gap: 1.15rem;
    padding: 1.5rem 1.35rem;
    margin-bottom: 1rem;
    border-radius: 1.35rem;
    background: var(--bg);
    color: #fff8f3;
}

.ai-band-mark {
    width: 3rem;
    height: 3rem;
    border-radius: 0.9rem;
    display: grid;
    place-items: center;
    background: rgba(232, 78, 27, 0.2);
    border: 1px solid rgba(232, 78, 27, 0.35);
}

.ai-band-ico {
    width: 1.35rem;
    height: 1.35rem;
    color: var(--ember);
}

.ai-band-copy {
    display: grid;
    gap: 0.55rem;
}

.ai-badge {
    display: inline-flex;
    width: fit-content;
    padding: 0.3rem 0.7rem;
    border-radius: 999px;
    background: rgba(232, 78, 27, 0.18);
    color: #ffb89a;
    font-size: 0.75rem;
    font-weight: 800;
    letter-spacing: 0.03em;
    text-transform: uppercase;
}

.ai-band-copy h3 {
    margin: 0;
    font-size: clamp(1.35rem, 3vw, 1.85rem);
    font-weight: 800;
    letter-spacing: -0.03em;
    line-height: 1.15;
}

.ai-band-copy p {
    margin: 0;
    color: rgba(255, 248, 243, 0.72);
    font-size: 1rem;
    max-width: 48ch;
}

.ai-band-cta {
    width: fit-content;
}

.feature-list {
    display: grid;
    gap: 0.75rem;
}

.feature-item {
    padding: 1.25rem 1.2rem;
    border-radius: 1.15rem;
    background: #fff;
    border: 1px solid rgba(42, 23, 15, 0.06);
}

.feature-ico-wrap {
    display: inline-grid;
    place-items: center;
    width: 2.4rem;
    height: 2.4rem;
    margin-bottom: 0.85rem;
    border-radius: 0.7rem;
    background: rgba(232, 78, 27, 0.1);
}

.feature-ico {
    width: 1.15rem;
    height: 1.15rem;
    color: var(--ember);
}

.feature-item h3 {
    margin: 0 0 0.4rem;
    font-size: 1.08rem;
    font-weight: 800;
    letter-spacing: -0.02em;
}

.feature-item p {
    margin: 0;
    color: var(--muted);
    font-size: 0.95rem;
    max-width: 36ch;
}

/* -- How -- */
.how {
    display: grid;
    gap: 0;
    background: var(--bg);
    color: #fff8f3;
}

.how-inner {
    padding: 3.25rem 1.15rem;
    max-width: 72rem;
    margin: 0 auto;
    width: 100%;
}

.how-inner h2 {
    margin: 0 0 1.5rem;
    font-size: clamp(1.6rem, 3.5vw, 2.1rem);
    font-weight: 800;
    letter-spacing: -0.03em;
}

.how-inner ol {
    list-style: none;
    margin: 0;
    padding: 0;
    display: grid;
    gap: 1.15rem;
}

.how-inner li {
    display: grid;
    grid-template-columns: 2rem 1fr;
    grid-template-rows: auto auto;
    column-gap: 0.75rem;
    row-gap: 0.15rem;
}

.how-inner li span {
    grid-row: 1 / span 2;
    width: 2rem;
    height: 2rem;
    border-radius: 999px;
    border: 1.5px solid var(--ember);
    display: grid;
    place-items: center;
    font-size: 0.8rem;
    font-weight: 800;
    color: var(--ember);
}

.how-inner strong {
    font-size: 1.05rem;
}

.how-inner p {
    margin: 0;
    color: rgba(255, 248, 243, 0.65);
    font-size: 0.95rem;
}

.how-visual {
    position: relative;
    min-height: 16rem;
}

.how-visual > img:first-child {
    width: 100%;
    height: 100%;
    min-height: 16rem;
    object-fit: cover;
    opacity: 0.88;
}

.how-overlay {
    position: absolute;
    right: 1rem;
    bottom: 1rem;
    width: min(46%, 220px);
    aspect-ratio: 4 / 3;
    object-fit: cover;
    border-radius: 0.9rem;
    border: 3px solid #fff8f3;
    box-shadow: 0 16px 36px rgba(0, 0, 0, 0.4);
    animation: lift 6s ease-in-out infinite;
    animation-delay: 0.8s;
}

/* -- Close -- */
.close {
    padding: 4rem 1.15rem;
    max-width: 40rem;
    margin: 0 auto;
    text-align: center;
}

.close blockquote {
    margin: 0 0 1.75rem;
}

.close blockquote p {
    margin: 0;
    font-size: clamp(1.25rem, 3vw, 1.65rem);
    font-weight: 700;
    line-height: 1.35;
    letter-spacing: -0.02em;
    color: var(--ink);
}

.close-cta {
    display: grid;
    gap: 0.85rem;
    justify-items: center;
}

.text-link {
    color: var(--muted);
    font-weight: 600;
    font-size: 0.95rem;
    text-decoration: none;
}

.text-link:hover {
    color: var(--ember);
}

/* -- Footer -- */
.foot {
    border-top: 1px solid var(--line);
    padding: 2rem 1.15rem 2.75rem;
    max-width: 72rem;
    margin: 0 auto;
    display: grid;
    gap: 0.75rem;
}

.foot-logo {
    height: 1.35rem;
    width: auto;
}

.foot p {
    margin: 0;
    color: var(--muted);
    font-size: 0.9rem;
    max-width: 34ch;
}

.foot-links {
    display: flex;
    gap: 1.15rem;
}

.foot-links a {
    color: var(--ink);
    font-weight: 700;
    text-decoration: none;
    font-size: 0.92rem;
}

.foot-links a:hover {
    color: var(--ember);
}

@keyframes drift {
    from {
        transform: scale(1.04) translate3d(0, 0, 0);
    }
    to {
        transform: scale(1.08) translate3d(-1.5%, -1%, 0);
    }
}

@keyframes lift {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-10px);
    }
}

@media (min-width: 768px) {
    .top {
        padding: 0.95rem 2rem;
    }

    .hero-grid {
        grid-template-columns: 1.15fr 0.85fr;
        padding: 6.5rem 2rem 4rem;
        align-items: center;
    }

    .hero-product {
        justify-content: flex-end;
    }

    .proof ul {
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        padding: 0.25rem 0;
    }

    .story {
        grid-template-columns: 0.95fr 1.05fr;
        align-items: center;
        padding: 4.5rem 2rem;
        gap: 3rem;
    }

    .features {
        padding: 4.5rem 2rem 5rem;
    }

    .features-head {
        max-width: 36rem;
        margin-bottom: 2.25rem;
    }

    .ai-band {
        grid-template-columns: auto 1fr auto;
        align-items: center;
        gap: 1.5rem 1.75rem;
        padding: 1.75rem 1.85rem;
        margin-bottom: 1.15rem;
    }

    .ai-band-cta {
        justify-self: end;
    }

    .feature-list {
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
    }

    .how {
        grid-template-columns: 1fr 1.1fr;
    }

    .how-inner {
        padding: 4rem 2rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .how-inner ol {
        grid-template-columns: 1fr;
    }

    .how-visual {
        min-height: 100%;
    }

    .how-visual > img:first-child {
        min-height: 100%;
        position: absolute;
        inset: 0;
    }

    .close {
        padding: 5rem 2rem;
    }

    .foot {
        grid-template-columns: auto 1fr auto;
        align-items: end;
        gap: 1.5rem;
        padding: 2.5rem 2rem 3.25rem;
    }
}

@media (prefers-reduced-motion: reduce) {
    .hero-bg img,
    .device,
    .how-overlay,
    .btn {
        animation: none !important;
        transition: none !important;
    }
}
</style>
