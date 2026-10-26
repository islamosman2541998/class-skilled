// Public entry point. Livewire (with its bundled Alpine) comes from the ESM build so the
// site's Alpine components register before Alpine starts. Swiper is loaded on demand.
import { Livewire, Alpine } from '../../../vendor/livewire/livewire/dist/livewire.esm';
import { registerToasts } from './toast';
import { registerConfirm } from './confirm';
import { initHeader, registerDropdown } from './header';
import { initMobileNav } from './mobile-nav';
import { initReveal } from './reveal';
import { registerLightbox } from './lightbox';
import { registerVideoPlayer } from './video-player';
import { initTracking } from './track';

registerToasts(Livewire);
registerConfirm(Alpine);
registerDropdown(Alpine);
registerLightbox(Alpine);
registerVideoPlayer(Alpine);

// Slider components: the Swiper chunk downloads only when a slider is on the page.
const slider = (kind) => (options = {}) => ({
    swiper: null,
    active: 0,
    playing: true,
    progress: 0,
    filterKey: null,
    go() {},
    toggle() {},
    filter() {},
    async init() {
        const sliders = await import('./sliders');
        sliders[kind](this, options);
    },
    destroy() {
        this.observer?.disconnect();
        this.thumbsSwiper?.destroy(true, true);
        this.swiper?.destroy(true, true);
        this.swiper = null;
    },
});
Alpine.data('heroSlider', slider('initHero'));
Alpine.data('cardSlider', slider('initCards'));
Alpine.data('gallerySlider', slider('initGallery'));
Alpine.data('mediaGallery', slider('initMedia'));

const boot = () => {
    initHeader();
    initMobileNav();
    initReveal();
    initTracking();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
} else {
    boot();
}

Livewire.start();
