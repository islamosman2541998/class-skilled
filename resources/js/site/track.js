// Client-side analytics beacon (works with the full-page cache): one page view per load and
// clicks on WhatsApp / phone / e-mail links, sent to /t and mirrored to GA4 / Meta Pixel when present.
const ENDPOINT = '/t';

function send(type) {
    const body = JSON.stringify({ type, path: window.location.pathname, locale: document.documentElement.lang });

    try {
        if (navigator.sendBeacon) {
            navigator.sendBeacon(ENDPOINT, new Blob([body], { type: 'application/json' }));
        } else {
            fetch(ENDPOINT, { method: 'POST', body, headers: { 'Content-Type': 'application/json' }, keepalive: true });
        }
    } catch {
        // Analytics must never break the page.
    }

    if (type !== 'page_view') {
        window.gtag?.('event', type, { page_path: window.location.pathname });
        window.fbq?.('trackCustom', type);
    }
}

function clickType(link) {
    const href = link.getAttribute('href') || '';
    if (link.dataset.track) return link.dataset.track;
    if (/^https:\/\/(wa\.me|api\.whatsapp\.com)\//i.test(href)) return 'whatsapp_click';
    if (href.startsWith('tel:')) return 'call_click';
    if (href.startsWith('mailto:')) return 'email_click';
    return null;
}

export function initTracking() {
    if (navigator.webdriver) return;

    send('page_view');

    document.addEventListener(
        'click',
        (event) => {
            const link = event.target.closest?.('a[href]');
            const type = link && clickType(link);
            if (type) send(type);
        },
        { capture: true },
    );
}
