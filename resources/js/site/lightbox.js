// Lightweight lightbox (Alpine `lightbox`): images, uploaded videos and YouTube/Vimeo embeds.
// Items come from a JSON <script x-ref="items">; keyboard (Esc, arrows), swipe, focus return.
export function registerLightbox(Alpine) {
    Alpine.data('lightbox', () => ({
        items: [],
        index: 0,
        main: 0,
        shown: false,
        opener: null,
        touchX: null,
        init() {
            try {
                this.items = JSON.parse(this.$refs.items?.textContent || '[]');
            } catch {
                this.items = [];
            }
        },
        get current() {
            return this.items[this.index] ?? {};
        },
        open(index) {
            this.opener = document.activeElement;
            this.index = index;
            this.shown = true;
            document.body.style.overflow = 'hidden';
            this.$nextTick(() => document.querySelector('[data-lightbox-close]:not([hidden])')?.focus());
        },
        close() {
            this.shown = false;
            document.body.style.overflow = '';
            this.opener?.focus();
        },
        step(delta) {
            if (this.items.length < 2) return;
            this.index = (this.index + delta + this.items.length) % this.items.length;
        },
        // Arrow keys follow the reading direction.
        key(event) {
            if (!this.shown) return;
            const rtl = document.documentElement.dir === 'rtl';
            if (event.key === 'Escape') this.close();
            if (event.key === 'ArrowRight') this.step(rtl ? -1 : 1);
            if (event.key === 'ArrowLeft') this.step(rtl ? 1 : -1);
        },
        touchStart(event) {
            this.touchX = event.changedTouches[0].clientX;
        },
        touchEnd(event) {
            if (this.touchX === null) return;
            const dx = event.changedTouches[0].clientX - this.touchX;
            const rtl = document.documentElement.dir === 'rtl';
            if (Math.abs(dx) > 50) this.step((dx < 0) !== rtl ? 1 : -1);
            this.touchX = null;
        },
        counter(template) {
            return template.replace(':current', this.index + 1).replace(':total', this.items.length);
        },
    }));

    // "Lite" embed: the iframe is created only after the visitor clicks play.
    Alpine.data('liteEmbed', (src) => ({
        playing: false,
        src,
    }));
}
