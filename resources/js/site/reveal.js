// Scroll reveal (§6.6): one IntersectionObserver for every [data-reveal]
// (fade-up, fade, head, stagger, slides, image, bg, draw-line) plus count-up for [data-count]. Runs once.
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

    const items = { stagger: (el) => [...el.children], slides: (el) => [...el.querySelectorAll('.swiper-slide')] };
    targets.forEach((el) => {
        items[el.dataset.reveal]?.(el).forEach((child, index) => {
            if (!child.style.getPropertyValue('--i')) child.style.setProperty('--i', String(index));
        });
    });

    // A fully clipped image (curtain wipe) has no visible area, so the observer would never
    // see it: watch its parent instead and reveal every image waiting on that parent.
    const waiting = new Map();
    const watch = (el) => {
        const target = el.dataset.reveal === 'image' && el.parentElement ? el.parentElement : el;
        if (!waiting.has(target)) waiting.set(target, []);
        waiting.get(target).push(el);
    };

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                observer.unobserve(entry.target);
                waiting.get(entry.target)?.forEach((el) => {
                    if (el.hasAttribute('data-count')) {
                        countUp(el);
                    } else {
                        el.classList.add('is-revealed');
                    }
                });
            });
        },
        { threshold: 0.15, rootMargin: '0px 0px -5% 0px' },
    );

    targets.forEach(watch);
    counters.forEach(watch);
    waiting.forEach((_, target) => observer.observe(target));
}
