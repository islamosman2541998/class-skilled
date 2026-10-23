// Dashboard charts (Chart.js), loaded on demand:
// <canvas x-data="chart(@js($config))"></canvas>
export function registerCharts(Alpine) {
    Alpine.data('chart', (config) => {
        let chart = null;

        return {
            async init() {
                const { Chart, registerables } = await import('chart.js');
                Chart.register(...registerables);
                Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
                Chart.defaults.color = '#5d6a80';
                chart = new Chart(this.$el, config);
            },
            update(next) {
                if (!chart) return;
                chart.data = next.data;
                chart.update();
            },
            destroy() {
                chart?.destroy();
                chart = null;
            },
        };
    });
}
