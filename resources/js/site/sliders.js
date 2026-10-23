// Swiper sliders (§6.5), initialised from Alpine components (see app.js) so they
// survive Livewire updates. Swiper reads the direction from <html dir>; labels come from window.csLang.
import Swiper from 'swiper';
import { A11y, Autoplay, EffectFade, Keyboard, Navigation } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/effect-fade';

const reduced = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function a11y() {
    const lang = window.csLang?.slider ?? {};
    return {
        enabled: true,
        prevSlideMessage: lang.previous,
        nextSlideMessage: lang.next,
        paginationBulletMessage: lang.go_to,
        slideLabelMessage: lang.slide_of,
    };
}

// Only the active hero slide's links are tabbable.
function syncHero(swiper) {
    swiper.slides.forEach((slide, index) => {
        const current = index === swiper.activeIndex;
        slide.classList.toggle('is-active', current);
        slide.querySelectorAll('a, button').forEach((el) => el.setAttribute('tabindex', current ? '0' : '-1'));
    });
}

export function initHero(component, { delay = 6000 } = {}) {
    const root = component.$root;
    const motion = !reduced();
    const bars = () => root.querySelectorAll('.progress-bars .bar');

    component.playing = motion;

    const swiper = new Swiper(component.$refs.swiper, {
        modules: [Navigation, Autoplay, EffectFade, Keyboard, A11y],
        effect: 'fade',
        fadeEffect: { crossFade: true },
        speed: motion ? 900 : 0,
        rewind: true,
        grabCursor: true,
        keyboard: { enabled: true, onlyInViewport: true },
        a11y: a11y(),
        navigation: component.$refs.prev ? { prevEl: component.$refs.prev, nextEl: component.$refs.next } : false,
        autoplay: motion ? { delay, pauseOnMouseEnter: true, disableOnInteraction: false } : false,
        on: {
            init: syncHero,
            slideChange(s) {
                component.active = s.realIndex;
                syncHero(s);
                bars().forEach((bar) => bar.style.setProperty('--progress', '0'));
            },
            autoplayTimeLeft(s, time, ratio) {
                bars()[s.realIndex]?.style.setProperty('--progress', String(1 - ratio));
            },
        },
    });

    component.swiper = swiper;
    component.go = (index) => swiper.slideTo(index);
    component.toggle = () => {
        if (!swiper.autoplay?.enabled && !component.playing) {
            return;
        }
        if (component.playing) {
            swiper.autoplay.stop();
        } else {
            swiper.autoplay.start();
        }
        component.playing = !component.playing;
    };

    // Pause while the tab is hidden.
    document.addEventListener('visibilitychange', () => {
        if (!component.playing || !swiper.autoplay) return;
        if (document.hidden) swiper.autoplay.pause();
        else swiper.autoplay.resume();
    });
}

export function initCards(component, { autoplay = false, per = [1.15, 2.2, 3.2, 3.5] } = {}) {
    const update = (s) => {
        const total = Math.max(1, s.slides.filter((slide) => !slide.hidden).length);
        const view = typeof s.params.slidesPerView === 'number' ? s.params.slidesPerView : 1;
        const shown = s.isEnd ? total : Math.min(total, view + s.activeIndex);
        component.progress = Math.round((shown / total) * 100);
    };

    const swiper = new Swiper(component.$refs.swiper, {
        modules: [Navigation, Keyboard, A11y, Autoplay],
        slidesPerView: per[0],
        spaceBetween: 16,
        speed: reduced() ? 0 : 500,
        grabCursor: true,
        watchSlidesProgress: true,
        keyboard: { enabled: true, onlyInViewport: true },
        a11y: a11y(),
        navigation: component.$refs.prev ? { prevEl: component.$refs.prev, nextEl: component.$refs.next } : false,
        autoplay: autoplay && !reduced() ? { delay: 5000, pauseOnMouseEnter: true, disableOnInteraction: true } : false,
        breakpoints: {
            640: { slidesPerView: per[1], spaceBetween: 20 },
            1024: { slidesPerView: per[2], spaceBetween: 24 },
            1280: { slidesPerView: per[3], spaceBetween: 24 },
        },
        on: {
            init: update,
            slideChange: update,
            resize: update,
        },
    });

    component.swiper = swiper;

    // Client-side category filter (projects section): hide slides, no reload.
    component.filter = (key) => {
        component.filterKey = key;
        swiper.slides.forEach((slide) => {
            slide.hidden = key !== null && slide.dataset.filterKey !== key;
        });
        swiper.update();
        swiper.slideTo(0, 0);
        update(swiper);
    };
}
