// Sticky header: glass + shrink after 24px of scroll (§6.6), rAF-throttled.
// Desktop dropdowns: Alpine `navDropdown` (hover, click, Esc, arrow keys).
export function initHeader() {
    const header = document.querySelector('[data-header]');
    if (!header) return;

    let ticking = false;
    const apply = () => {
        header.classList.toggle('is-scrolled', window.scrollY > 24);
        ticking = false;
    };

    window.addEventListener(
        'scroll',
        () => {
            if (!ticking) {
                ticking = true;
                window.requestAnimationFrame(apply);
            }
        },
        { passive: true },
    );
    apply();
}

export function registerDropdown(Alpine) {
    Alpine.data('navDropdown', () => ({
        open: false,
        timer: null,
        hoverable: window.matchMedia('(hover: hover)').matches,
        items() {
            return [...this.$refs.panel.querySelectorAll('[data-dropdown-item]')];
        },
        enter() {
            if (!this.hoverable) return;
            clearTimeout(this.timer);
            this.timer = setTimeout(() => (this.open = true), 80);
        },
        leave() {
            if (!this.hoverable) return;
            clearTimeout(this.timer);
            this.timer = setTimeout(() => (this.open = false), 160);
        },
        toggle() {
            clearTimeout(this.timer);
            this.open = !this.open;
        },
        close(focus = false) {
            if (!this.open) return;
            this.open = false;
            if (focus) this.$refs.trigger.focus();
        },
        openAndFocus() {
            this.open = true;
            this.$nextTick(() => this.items()[0]?.focus());
        },
        move(step) {
            const items = this.items();
            const index = items.indexOf(document.activeElement);
            items[(index + step + items.length) % items.length]?.focus();
        },
        onFocusOut(event) {
            if (!this.$root.contains(event.relatedTarget)) this.open = false;
        },
    }));
}
