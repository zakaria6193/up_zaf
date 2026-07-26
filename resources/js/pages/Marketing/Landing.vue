<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';
import { ArrowRight, Check, QrCode, Smartphone, Sparkles, UtensilsCrossed } from 'lucide-vue-next';

defineOptions({ layout: false });

defineProps<{
    googleEnabled: boolean;
    trialMinutes: number;
}>();

const scrolled = ref(false);
let observer: IntersectionObserver | null = null;

const onScroll = () => {
    scrolled.value = window.scrollY > 20;
};

onMounted(() => {
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                }
            });
        },
        { threshold: 0.14 },
    );

    document.querySelectorAll('.reveal').forEach((el) => observer?.observe(el));
});

onUnmounted(() => {
    window.removeEventListener('scroll', onScroll);
    observer?.disconnect();
});

const features = [
    {
        title: 'Beautiful digital menus',
        text: 'Mobile-first menus guests open in one tap — no app download required.',
        image: '/images/landing/dishes.jpg',
        icon: UtensilsCrossed,
    },
    {
        title: 'Branded QR codes',
        text: 'Print-ready QR cards for tables, windows, and takeaway bags.',
        image: '/images/landing/qr-table.jpg',
        icon: QrCode,
    },
    {
        title: 'Update in real time',
        text: 'Change prices, hide sold-out dishes, and publish instantly.',
        image: '/images/landing/update.jpg',
        icon: Sparkles,
    },
    {
        title: 'Share everywhere',
        text: 'One public link for WhatsApp, Instagram, Google Maps, and more.',
        image: '/images/landing/phone-menu.jpg',
        icon: Smartphone,
    },
];

const steps = [
    { n: '01', title: 'Create your account', text: 'Sign up free and start your trial in seconds.' },
    { n: '02', title: 'Build your menu', text: 'Add categories, dishes, photos, and prices.' },
    { n: '03', title: 'Share with guests', text: 'Print your QR or send your public link.' },
];

const audiences = [
    'Fine dining',
    'Casual restaurants',
    'Cafés',
    'Food trucks',
    'Bakeries',
    'Bars & lounges',
    'Ghost kitchens',
    'Catering',
    'Juice bars',
    'Dessert shops',
];

const painPoints = [
    'No reprinting costs',
    'No outdated prices',
    'No designer wait times',
    'No menu errors',
    'No slow updates',
    'No paper waste',
];
</script>

<template>
    <Head title="Digital menus for restaurants">
        <link rel="preconnect" href="https://fonts.bunny.net" />
        <link
            href="https://fonts.bunny.net/css?family=outfit:500,600,700,800|sora:400,500,600,700"
            rel="stylesheet"
        />
    </Head>

    <div class="landing">
        <header class="landing-nav" :class="{ scrolled }">
            <a href="#top" class="brand" aria-label="UP1 home">
                <img src="/logoup1.png" alt="UP1" class="brand-logo" />
                <span class="brand-word">UP1</span>
            </a>
            <nav class="nav-links">
                <a href="#features">Features</a>
                <a href="#how">How it works</a>
                <a href="#why">Why UP1</a>
            </nav>
            <div class="nav-cta">
                <Link href="/login" class="btn-ghost">Log in</Link>
                <Link href="/register" class="btn-solid">Get started</Link>
            </div>
        </header>

        <main id="top">
            <section class="hero">
                <div class="hero-bg" aria-hidden="true">
                    <img src="/images/landing/hero.jpg" alt="" />
                    <div class="hero-veil" />
                    <div class="hero-grain" />
                </div>

                <div class="hero-content">
                    <p class="brand-hero">UP1</p>
                    <h1>Create your digital menu in minutes.</h1>
                    <p class="lede">
                        A clean, mobile-friendly menu for your restaurant — update prices anytime and share with a QR
                        code.
                    </p>
                    <div class="hero-actions">
                        <Link href="/register" class="btn-solid lg">
                            Get started for free
                            <ArrowRight class="icon" />
                        </Link>
                        <Link href="/login" class="btn-outline lg">Log in</Link>
                    </div>
                    <p class="trial-note">{{ trialMinutes }}-minute free trial · No credit card</p>
                </div>

                <div class="hero-device" aria-hidden="true">
                    <div class="device-glow" />
                    <img src="/images/landing/phone-menu.jpg" alt="" class="device-shot" />
                </div>
            </section>

            <section class="marquee-band" aria-hidden="true">
                <div class="marquee-track">
                    <span v-for="(item, i) in [...painPoints, ...painPoints]" :key="i" class="marquee-item">
                        <Check class="check" />
                        {{ item }}
                    </span>
                </div>
            </section>

            <section id="features" class="section features">
                <div class="section-head reveal">
                    <p class="eyebrow">UP1 features</p>
                    <h2>Everything you need to manage & share your menu</h2>
                </div>

                <div class="feature-stack">
                    <article
                        v-for="(feature, index) in features"
                        :key="feature.title"
                        class="feature-row reveal"
                        :class="{ reverse: index % 2 === 1 }"
                        :style="{ transitionDelay: `${index * 60}ms` }"
                    >
                        <div class="feature-media">
                            <img :src="feature.image" :alt="feature.title" loading="lazy" />
                        </div>
                        <div class="feature-body">
                            <component :is="feature.icon" class="feature-icon" />
                            <h3>{{ feature.title }}</h3>
                            <p>{{ feature.text }}</p>
                        </div>
                    </article>
                </div>
            </section>

            <section id="how" class="section how">
                <div class="how-copy reveal">
                    <p class="eyebrow">How it works</p>
                    <h2>From kitchen to customer in three steps</h2>
                    <ol>
                        <li v-for="step in steps" :key="step.n">
                            <span class="step-n">{{ step.n }}</span>
                            <div>
                                <h3>{{ step.title }}</h3>
                                <p>{{ step.text }}</p>
                            </div>
                        </li>
                    </ol>
                    <Link href="/register" class="btn-solid">
                        Start free
                        <ArrowRight class="icon" />
                    </Link>
                </div>
                <div class="how-visual reveal">
                    <img
                        src="/images/landing/qr-table.jpg"
                        alt="QR code on a restaurant table"
                        loading="lazy"
                    />
                </div>
            </section>

            <section class="section audience">
                <div class="section-head reveal">
                    <h2>Built for food businesses of all sizes</h2>
                </div>
                <div class="audience-cloud reveal">
                    <span v-for="item in audiences" :key="item">{{ item }}</span>
                </div>
            </section>

            <section id="why" class="section why">
                <div class="section-head reveal">
                    <p class="eyebrow">Why choose UP1?</p>
                    <h2>A better way to put your menu online</h2>
                </div>
                <div class="why-grid">
                    <article class="reveal">
                        <h3>Quick to set up</h3>
                        <p>Publish a professional menu in minutes. No designers. No technical setup.</p>
                    </article>
                    <article class="reveal" style="transition-delay: 80ms">
                        <h3>Easy to update</h3>
                        <p>Change prices or availability anytime — guests always see the latest version.</p>
                    </article>
                    <article class="reveal" style="transition-delay: 160ms">
                        <h3>Made to share</h3>
                        <p>QR codes and public links that look sharp on tables, stories, and maps.</p>
                    </article>
                </div>
            </section>

            <section class="final-cta reveal">
                <div class="final-cta-bg" aria-hidden="true">
                    <img src="/images/landing/update.jpg" alt="" />
                </div>
                <div class="final-cta-content">
                    <h2>Ready to go digital?</h2>
                    <p>
                        Create your UP1 account and try the full experience free for {{ trialMinutes }} minutes.
                    </p>
                    <div class="hero-actions">
                        <Link href="/register" class="btn-solid lg">Get started for free</Link>
                        <Link href="/login" class="btn-outline lg">Log in</Link>
                    </div>
                </div>
            </section>
        </main>

        <footer class="landing-footer">
            <div class="footer-brand">
                <img src="/logoup1.png" alt="" class="brand-logo" />
                <span>UP1</span>
            </div>
            <p>Digital menus for restaurants & food vendors.</p>
            <div class="footer-links">
                <Link href="/login">Log in</Link>
                <Link href="/register">Register</Link>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.landing {
    --ink: #070b10;
    --ink-soft: #3d4754;
    --mist: #f3f5f8;
    --panel: #ffffff;
    --accent: #2ee6a6;
    --accent-deep: #12c98a;
    --line: rgba(7, 11, 16, 0.1);
    --font-display: 'Outfit', ui-sans-serif, system-ui, sans-serif;
    --font-body: 'Sora', ui-sans-serif, system-ui, sans-serif;

    min-height: 100vh;
    background: var(--mist);
    color: var(--ink);
    font-family: var(--font-body);
    overflow-x: hidden;
}

.landing-nav {
    position: fixed;
    inset: 0 0 auto;
    z-index: 50;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 1rem 1.25rem;
    transition:
        background 0.35s ease,
        backdrop-filter 0.35s ease,
        box-shadow 0.35s ease;
}

.landing-nav.scrolled {
    background: rgba(243, 245, 248, 0.9);
    backdrop-filter: blur(14px);
    box-shadow: 0 1px 0 var(--line);
}

.brand {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    text-decoration: none;
    color: inherit;
}

.brand-logo {
    height: 2rem;
    width: auto;
}

.brand-word {
    font-family: var(--font-display);
    font-weight: 800;
    letter-spacing: -0.04em;
    font-size: 1.2rem;
}

.nav-links {
    display: none;
    gap: 1.6rem;
}

.nav-links a {
    color: var(--ink-soft);
    text-decoration: none;
    font-size: 0.92rem;
    font-weight: 500;
}

.nav-links a:hover {
    color: var(--ink);
}

.nav-cta {
    display: flex;
    align-items: center;
    gap: 0.45rem;
}

.btn-ghost,
.btn-solid,
.btn-outline {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    border-radius: 0.65rem;
    font-weight: 600;
    text-decoration: none;
    transition:
        transform 0.2s ease,
        background 0.2s ease,
        color 0.2s ease,
        border-color 0.2s ease,
        box-shadow 0.2s ease;
}

.btn-ghost {
    padding: 0.55rem 0.9rem;
    color: var(--ink);
}

.btn-solid {
    padding: 0.65rem 1.15rem;
    background: var(--accent);
    color: var(--ink);
    box-shadow: 0 8px 24px rgba(46, 230, 166, 0.28);
}

.btn-solid:hover {
    background: var(--accent-deep);
    transform: translateY(-1px);
}

.btn-outline {
    padding: 0.65rem 1.15rem;
    border: 1.5px solid rgba(255, 255, 255, 0.45);
    color: white;
}

.btn-outline:hover {
    background: rgba(255, 255, 255, 0.1);
}

.btn-solid.lg,
.btn-outline.lg {
    padding: 0.95rem 1.45rem;
    font-size: 1rem;
}

.icon {
    width: 1rem;
    height: 1rem;
}

.hero {
    position: relative;
    min-height: 100svh;
    display: grid;
    align-items: end;
    gap: 2rem;
    padding: 6.5rem 1.25rem 3.5rem;
    color: white;
    overflow: hidden;
}

.hero-bg {
    position: absolute;
    inset: 0;
    overflow: hidden;
}

.hero-bg img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transform: scale(1.05);
    animation: hero-drift 22s ease-in-out infinite alternate;
}

.hero-veil {
    position: absolute;
    inset: 0;
    background:
        linear-gradient(115deg, rgba(7, 11, 16, 0.88) 0%, rgba(7, 11, 16, 0.55) 48%, rgba(7, 11, 16, 0.35) 100%),
        linear-gradient(180deg, rgba(7, 11, 16, 0.25) 0%, rgba(7, 11, 16, 0.75) 100%);
}

.hero-grain {
    position: absolute;
    inset: 0;
    opacity: 0.18;
    background-image: radial-gradient(rgba(255, 255, 255, 0.15) 0.5px, transparent 0.5px);
    background-size: 3px 3px;
    mix-blend-mode: soft-light;
    pointer-events: none;
}

.hero-content {
    position: relative;
    z-index: 1;
    max-width: 38rem;
    animation: rise 0.95s cubic-bezier(0.22, 1, 0.36, 1) both;
}

.brand-hero {
    font-family: var(--font-display);
    font-size: clamp(4rem, 14vw, 7.5rem);
    font-weight: 800;
    letter-spacing: -0.07em;
    line-height: 0.85;
    margin: 0 0 1rem;
    background: linear-gradient(120deg, #fff 30%, var(--accent) 120%);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}

.hero h1 {
    font-family: var(--font-display);
    font-size: clamp(1.7rem, 4.2vw, 2.65rem);
    font-weight: 700;
    letter-spacing: -0.03em;
    line-height: 1.12;
    margin: 0 0 0.9rem;
    max-width: 16ch;
    color: #fff;
}

.lede {
    font-size: 1.02rem;
    line-height: 1.55;
    color: rgba(255, 255, 255, 0.82);
    margin: 0 0 1.5rem;
    max-width: 34rem;
}

.hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.trial-note {
    margin: 1rem 0 0;
    font-size: 0.84rem;
    color: rgba(255, 255, 255, 0.68);
}

.hero-device {
    position: relative;
    z-index: 1;
    justify-self: center;
    width: min(100%, 280px);
    animation: float-y 6.5s ease-in-out infinite;
}

.device-glow {
    position: absolute;
    inset: 12% -8%;
    background: radial-gradient(circle, rgba(46, 230, 166, 0.45), transparent 70%);
    filter: blur(28px);
    z-index: 0;
}

.device-shot {
    position: relative;
    z-index: 1;
    width: 100%;
    border-radius: 1.4rem;
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 30px 60px rgba(0, 0, 0, 0.45);
}

.marquee-band {
    background: var(--ink);
    color: var(--mist);
    overflow: hidden;
    padding: 0.9rem 0;
}

.marquee-track {
    display: flex;
    width: max-content;
    gap: 2rem;
    animation: marquee 30s linear infinite;
}

.marquee-item {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    white-space: nowrap;
    font-size: 0.92rem;
    font-weight: 500;
}

.check {
    width: 1rem;
    height: 1rem;
    color: var(--accent);
}

.section {
    padding: 4.5rem 1.25rem;
    max-width: 72rem;
    margin-inline: auto;
}

.section-head {
    max-width: 40rem;
    margin-bottom: 2.5rem;
}

.eyebrow {
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--accent-deep);
    margin: 0 0 0.6rem;
}

.section-head h2,
.how-copy h2,
.final-cta h2 {
    font-family: var(--font-display);
    font-size: clamp(1.85rem, 4vw, 2.7rem);
    letter-spacing: -0.035em;
    line-height: 1.12;
    margin: 0;
}

.feature-stack {
    display: grid;
    gap: 2.5rem;
}

.feature-row {
    display: grid;
    gap: 1.25rem;
    align-items: center;
}

.feature-media {
    overflow: hidden;
    border-radius: 1.25rem;
    aspect-ratio: 16 / 11;
    background: #dbe2ea;
}

.feature-media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.8s ease;
}

.feature-row:hover .feature-media img {
    transform: scale(1.04);
}

.feature-body {
    padding: 0.25rem 0.15rem;
}

.feature-icon {
    width: 1.35rem;
    height: 1.35rem;
    color: var(--accent-deep);
    margin-bottom: 0.7rem;
}

.feature-body h3 {
    font-family: var(--font-display);
    font-size: 1.45rem;
    margin: 0 0 0.45rem;
    letter-spacing: -0.02em;
}

.feature-body p {
    margin: 0;
    color: var(--ink-soft);
    line-height: 1.55;
    max-width: 34rem;
}

.how {
    display: grid;
    gap: 2.5rem;
    align-items: center;
}

.how-copy ol {
    list-style: none;
    padding: 0;
    margin: 1.75rem 0 1.75rem;
    display: grid;
    gap: 1.25rem;
}

.how-copy li {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 1rem;
}

.step-n {
    font-family: var(--font-display);
    font-weight: 800;
    font-size: 1.45rem;
    color: var(--accent-deep);
}

.how-copy h3 {
    margin: 0 0 0.25rem;
    font-size: 1.08rem;
}

.how-copy p {
    margin: 0;
    color: var(--ink-soft);
}

.how-visual {
    overflow: hidden;
    border-radius: 1.4rem;
    aspect-ratio: 4 / 5;
}

.how-visual img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    animation: float-y 8s ease-in-out infinite;
}

.audience {
    text-align: center;
}

.audience .section-head {
    margin-inline: auto;
}

.audience-cloud {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 0.65rem;
}

.audience-cloud span {
    padding: 0.55rem 0.95rem;
    background: var(--panel);
    border: 1px solid var(--line);
    border-radius: 0.55rem;
    font-weight: 500;
    font-size: 0.9rem;
    transition:
        transform 0.2s ease,
        border-color 0.2s ease;
}

.audience-cloud span:hover {
    transform: translateY(-2px);
    border-color: var(--accent-deep);
}

.why-grid {
    display: grid;
    gap: 1rem;
}

.why-grid article {
    padding: 1.5rem 1.25rem;
    background: var(--panel);
    border: 1px solid var(--line);
    border-top: 3px solid var(--accent);
}

.why-grid h3 {
    font-family: var(--font-display);
    margin: 0 0 0.45rem;
}

.why-grid p {
    margin: 0;
    color: var(--ink-soft);
    line-height: 1.5;
}

.final-cta {
    position: relative;
    margin: 1rem 1.25rem 3rem;
    padding: 3.5rem 1.5rem;
    color: white;
    text-align: center;
    border-radius: 1.5rem;
    overflow: hidden;
}

.final-cta-bg {
    position: absolute;
    inset: 0;
}

.final-cta-bg img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: brightness(0.35) saturate(0.85);
}

.final-cta-content {
    position: relative;
    z-index: 1;
}

.final-cta p {
    color: rgba(255, 255, 255, 0.8);
    max-width: 32rem;
    margin: 0.85rem auto 1.5rem;
}

.final-cta .hero-actions {
    justify-content: center;
}

.landing-footer {
    padding: 2rem 1.25rem 3rem;
    border-top: 1px solid var(--line);
    display: grid;
    gap: 0.75rem;
    text-align: center;
    color: var(--ink-soft);
}

.footer-brand {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    font-family: var(--font-display);
    font-weight: 800;
    color: var(--ink);
}

.footer-links {
    display: flex;
    justify-content: center;
    gap: 1.25rem;
}

.footer-links a {
    color: var(--ink);
    font-weight: 600;
    text-decoration: none;
}

.reveal {
    opacity: 0;
    transform: translateY(24px);
    transition:
        opacity 0.75s ease,
        transform 0.75s ease;
}

.reveal.is-visible {
    opacity: 1;
    transform: none;
}

@keyframes rise {
    from {
        opacity: 0;
        transform: translateY(32px);
    }
    to {
        opacity: 1;
        transform: none;
    }
}

@keyframes hero-drift {
    from {
        transform: scale(1.05) translateY(0);
    }
    to {
        transform: scale(1.12) translateY(-2.5%);
    }
}

@keyframes marquee {
    from {
        transform: translateX(0);
    }
    to {
        transform: translateX(-50%);
    }
}

@keyframes float-y {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-10px);
    }
}

@media (min-width: 768px) {
    .landing-nav {
        padding: 1.1rem 2rem;
    }

    .nav-links {
        display: flex;
    }

    .hero {
        grid-template-columns: 1.15fr 0.85fr;
        align-items: center;
        padding: 8rem 2rem 5rem;
        gap: 2.5rem;
    }

    .hero-device {
        width: min(100%, 340px);
        justify-self: end;
        animation-delay: 0.4s;
    }

    .section {
        padding: 5.5rem 2rem;
    }

    .feature-row {
        grid-template-columns: 1.1fr 0.9fr;
        gap: 2.5rem;
    }

    .feature-row.reverse {
        grid-template-columns: 0.9fr 1.1fr;
    }

    .feature-row.reverse .feature-media {
        order: 2;
    }

    .how {
        grid-template-columns: 1.05fr 0.95fr;
    }

    .why-grid {
        grid-template-columns: repeat(3, 1fr);
    }

    .final-cta {
        margin-inline: auto;
        max-width: 72rem;
        padding: 5rem 2rem;
    }
}

@media (max-width: 767px) {
    .hero-device {
        margin-top: 0.5rem;
    }
}
</style>
