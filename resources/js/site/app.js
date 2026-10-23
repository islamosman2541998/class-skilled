// Public entry point. Alpine is supplied by Livewire, never imported separately.
import { loadSwiper } from './sliders';

if (document.querySelector('[data-slider]')) {
    void loadSwiper();
}
