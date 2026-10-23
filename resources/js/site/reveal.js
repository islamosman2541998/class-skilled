// Scroll reveal (§6.6): one IntersectionObserver for every [data-reveal]
// (fade-up, fade, image, stagger, draw-line) plus count-up for [data-count]. Runs once.
// CSS holds the states (`.js [data-reveal]:not(.is-revealed)`), so no-JS shows everything.
const reduced = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function countUp(el) {
    const target = Number(el.dataset.count);
    if (!Number.isFinite(target) || reduced()) return;

    const decimals = (el.dataset.count.split('.')[1] ?? '').length;
    const duration = window.innerWidth < 640 ? 1000 : 1400;
    const start = performance.now();

    const frame = (now) => {
        const t = Math.min(1, (now - start) / duration);
        const eased = 1 - Math.pow(1 - t, 3);
        el.textContent = (target * eased).toFixed(decimals);
        if (t < 1) requestAnimationFrame(frame);
        else el.textContent = el.dataset.count;
    };

    // Reserve the final width so the layout does not shift while counting.
    el.style.display = 'inline-block';
    el.style.minWidth = `${el.getBoundingClientRect().width}px`;
    requestAnimationFrame(frame);
}

export function initReveal(root = document) {
    const targets = [...root.querySelectorAll('[data-reveal]:not(.is-revealed)')];
    const counters = [...root.querySelectorAll('[data-count]')];

    if (!('IntersectionObserver' in window) || reduced()) {
        targets.forEach((el) => el.classList.add('is-revealed'));
        return;
    }

    targets.forEach((el) => {
        if (el.dataset.reveal === 'stagger') {
            [...el.children].forEach((child, index) => {
                if (!child.style.getPropertyValue('--i')) child.style.setProperty('--i', String(index));
            });
        }
    });

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                const el = entry.target;
                observer.unobserve(el);
                if (el.hasAttribute('data-count')) {
                    countUp(el);
                } else {
                    el.classList.add('is-revealed');
                }
            });
        },
        { threshold: 0.15, rootMargin: '0px 0px -5% 0px' },
    );

    targets.forEach((el) => observer.observe(el));
    counters.forEach((el) => observer.observe(el));
}
