// Slider initialization belongs to the site phase; keep Swiper out of the initial bundle.
export function loadSwiper() {
    return Promise.all([
        import('swiper'),
        import('swiper/modules'),
        import('swiper/css'),
    ]);
}
