// Off-canvas mobile menu from the start side: focus trap, body scroll lock,
// Esc / backdrop to close, accordion sub-menus. Hides the bottom action bar while open.
const FOCUSABLE = 'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])';

export function initMobileNav() {
    const nav = document.querySelector('[data-mobile-nav]');
    const opener = document.querySelector('[data-mobile-nav-open]');
    if (!nav || !opener) return;

    const panel = nav.querySelector('[data-mobile-nav-panel]');
    const backdrop = nav.querySelector('[data-mobile-nav-backdrop]');
    const bar = document.querySelector('[data-bottom-bar]');
    const hiddenClass = document.documentElement.dir === 'rtl' ? 'rtl:translate-x-full' : 'ltr:-translate-x-full';

    const onKey = (event) => {
        if (event.key === 'Escape') {
            close();
            return;
        }
        if (event.key !== 'Tab') return;
        const items = [...panel.querySelectorAll(FOCUSABLE)].filter((el) => el.offsetParent !== null);
        const first = items[0];
        const last = items[items.length - 1];
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    };

    function open() {
        nav.hidden = false;
        document.body.style.overflow = 'hidden';
        opener.setAttribute('aria-expanded', 'true');
        bar?.classList.add('is-hidden');
        requestAnimationFrame(() => {
            backdrop.classList.replace('opacity-0', 'opacity-100');
            panel.classList.remove(hiddenClass);
        });
        document.addEventListener('keydown', onKey);
        setTimeout(() => panel.querySelector('[data-mobile-nav-close]')?.focus(), 50);
    }

    function close() {
        backdrop.classList.replace('opacity-100', 'opacity-0');
        panel.classList.add(hiddenClass);
        document.removeEventListener('keydown', onKey);
        document.body.style.overflow = '';
        opener.setAttribute('aria-expanded', 'false');
        bar?.classList.remove('is-hidden');
        setTimeout(() => {
            nav.hidden = true;
            opener.focus();
        }, 300);
    }

    opener.addEventListener('click', open);
    backdrop.addEventListener('click', close);
    nav.querySelector('[data-mobile-nav-close]')?.addEventListener('click', close);

    nav.querySelectorAll('[data-accordion]').forEach((button) => {
        button.addEventListener('click', () => {
            const sub = document.getElementById(button.getAttribute('aria-controls'));
            const expanded = button.getAttribute('aria-expanded') === 'true';
            button.setAttribute('aria-expanded', String(!expanded));
            button.querySelector('svg')?.classList.toggle('rotate-180', !expanded);
            if (sub) sub.hidden = expanded;
        });
    });

    // Close when the viewport grows to the desktop layout.
    window.matchMedia('(min-width: 1024px)').addEventListener('change', (event) => {
        if (event.matches && !nav.hidden) close();
    });

    // Hide the bottom bar while the on-screen keyboard is up (form fields focused).
    document.addEventListener('focusin', (event) => {
        if (event.target.matches?.('input, textarea, select')) bar?.classList.add('is-hidden');
    });
    document.addEventListener('focusout', (event) => {
        if (event.target.matches?.('input, textarea, select') && nav.hidden) bar?.classList.remove('is-hidden');
    });
}
