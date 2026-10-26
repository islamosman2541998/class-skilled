// Swiper sliders (§6.5), initialised from Alpine components (see app.js) so they
// survive Livewire updates. Swiper reads the direction from <html dir>; labels come from window.csLang.
import Swiper from 'swiper';
import { A11y, Autoplay, EffectFade, Keyboard, Navigation, Thumbs } from 'swiper/modules';
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

// Media of slides 2+ ships in <template data-hero-defer> and is added once the page has loaded.
function revealDeferredMedia(root) {
    const inject = () => root.querySelectorAll('template[data-hero-defer]').forEach((tpl) => tpl.replaceWith(tpl.content.cloneNode(true)));
    if (document.readyState === 'complete') {
        inject();
    } else {
        window.addEventListener('load', inject, { once: true });
    }
}

export function initHero(component, { delay = 6000 } = {}) {
    const root = component.$root;
    revealDeferredMedia(root);
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

export function initCards(component, { autoplay = false, per = [1, 2, 3, 4] } = {}) {
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

// Project gallery: main image slider (arrows, swipe/drag, keyboard, autoplay) + synced thumbnails.
// Lives inside the `lightbox` component: a click on the main image opens it full screen (and pauses autoplay).
export function initGallery(component, { delay = 5000 } = {}) {
    const motion = !reduced();
    const count = component.$refs.swiper.querySelectorAll('.swiper-slide').length;

    const thumbs = component.$refs.thumbs
        ? new Swiper(component.$refs.thumbs, {
            modules: [A11y],
            slidesPerView: 'auto',
            spaceBetween: 8,
            watchSlidesProgress: true,
            slideToClickedSlide: true,
            a11y: a11y(),
        })
        : null;

    const swiper = new Swiper(component.$refs.swiper, {
        modules: [Navigation, Autoplay, Keyboard, A11y, Thumbs],
        speed: motion ? 600 : 0,
        rewind: true,
        grabCursor: count > 1,
        allowTouchMove: count > 1,
        keyboard: { enabled: true, onlyInViewport: true },
        a11y: a11y(),
        navigation: component.$refs.prev ? { prevEl: component.$refs.prev, nextEl: component.$refs.next } : false,
        thumbs: thumbs ? { swiper: thumbs } : undefined,
        autoplay: motion && count > 1 ? { delay, pauseOnMouseEnter: true, disableOnInteraction: false } : false,
        on: {
            slideChange(s) {
                component.active = s.realIndex;
            },
        },
    });

    component.swiper = swiper;
    component.thumbsSwiper = thumbs;
    component.go = (index) => swiper.slideTo(index);

    // Pause while the lightbox is open; continue from the image that was last shown there.
    if (typeof component.shown === 'boolean' && swiper.autoplay) {
        component.$watch('shown', (open) => {
            if (open) {
                swiper.autoplay.stop();
            } else {
                swiper.slideTo(component.index ?? swiper.activeIndex);
                swiper.autoplay.start();
            }
        });
    }
}

// Media gallery (service "our work"): stage slider of images, uploaded videos (custom player) and
// YouTube/Vimeo embeds + a thumbnail strip whose active thumb fills with the autoplay timer.
// Autoplay holds while a video plays, moves on when it ends, and pauses off screen or in the lightbox.
export function initMedia(component, { delay = 6000 } = {}) {
    const root = component.$root;
    const motion = !reduced();
    const count = component.$refs.swiper.querySelectorAll('.swiper-slide').length;
    const auto = motion && count > 1;
    const setProgress = (value) => root.style.setProperty('--mg-progress', String(value));
    let held = false;

    component.playing = auto;

    const thumbs = component.$refs.thumbs
        ? new Swiper(component.$refs.thumbs, {
            modules: [A11y],
            slidesPerView: 'auto',
            spaceBetween: 10,
            watchSlidesProgress: true,
            slideToClickedSlide: true,
            a11y: a11y(),
        })
        : null;

    const swiper = new Swiper(component.$refs.swiper, {
        modules: [Navigation, Autoplay, Keyboard, A11y, Thumbs],
        speed: motion ? 650 : 0,
        rewind: true,
        grabCursor: count > 1,
        allowTouchMove: count > 1,
        keyboard: { enabled: true, onlyInViewport: true },
        a11y: a11y(),
        navigation: component.$refs.prev ? { prevEl: component.$refs.prev, nextEl: component.$refs.next } : false,
        thumbs: thumbs ? { swiper: thumbs } : undefined,
        autoplay: auto ? { delay, pauseOnMouseEnter: true, disableOnInteraction: false } : false,
        on: {
            slideChange(s) {
                component.active = s.realIndex;
                setProgress(0);
                s.slides.forEach((slide, index) => {
                    if (index !== s.activeIndex) {
                        slide.querySelectorAll('[data-media]').forEach((el) => el.dispatchEvent(new CustomEvent('media-stop')));
                    }
                });
                // The visitor moved on from a video: autoplay continues (unless they paused it).
                if (held && component.playing) {
                    held = false;
                    s.autoplay.start();
                }
            },
            autoplayTimeLeft(s, time, ratio) {
                setProgress(1 - ratio);
            },
            autoplayStop() {
                setProgress(0);
            },
        },
    });

    component.swiper = swiper;
    component.thumbsSwiper = thumbs;
    component.go = (index) => swiper.slideTo(index);
    component.toggle = () => {
        if (!swiper.autoplay || count < 2) return;
        component.playing = !component.playing;
        if (component.playing && !held) {
            swiper.autoplay.start();
        } else {
            swiper.autoplay.stop();
        }
    };

    root.addEventListener('media-play', () => {
        held = true;
        swiper.autoplay?.stop();
    });
    root.addEventListener('media-ended', () => {
        held = false;
        if (component.playing && count > 1) {
            swiper.slideNext();
            swiper.autoplay.start();
        }
    });

    // Autoplay only runs while the gallery is on screen and the tab is visible.
    const running = () => component.playing && !held && swiper.autoplay?.running;
    if (auto && 'IntersectionObserver' in window) {
        component.observer = new IntersectionObserver(([entry]) => {
            if (!running()) return;
            if (entry.isIntersecting) swiper.autoplay.resume();
            else swiper.autoplay.pause();
        }, { threshold: 0.35 });
        component.observer.observe(root);
    }
    document.addEventListener('visibilitychange', () => {
        if (!running()) return;
        if (document.hidden) swiper.autoplay.pause();
        else swiper.autoplay.resume();
    });

    // Pause while the lightbox is open; continue from the image that was last shown there.
    if (typeof component.shown === 'boolean') {
        component.$watch('shown', (open) => {
            if (open) {
                swiper.autoplay?.stop();
            } else {
                swiper.slideTo(component.index ?? swiper.activeIndex);
                if (component.playing && !held) swiper.autoplay?.start();
            }
        });
    }
}
