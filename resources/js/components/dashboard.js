import ApexCharts from 'apexcharts';

export function initDashboard() {
    document.querySelectorAll('[data-dashboard-chart]').forEach((element) => {
        const rows = JSON.parse(element.dataset.series);
        const country = element.dataset.dashboardChart === 'country';
        const chart = new ApexCharts(element, {
            chart: { type: country ? 'bar' : 'donut', height: 320, toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans, sans-serif', background: 'transparent' },
            colors: ['#465fff', '#12b76a', '#f79009', '#36bffa', '#9b8afb'],
            series: country ? [{ name: 'Page views', data: rows.map((row) => row.value) }] : rows.map((row) => row.value),
            labels: rows.map((row) => row.label),
            xaxis: { categories: rows.map((row) => row.label), labels: { style: { colors: '#98a2b3' } } },
            yaxis: { labels: { style: { colors: '#98a2b3' }, formatter: (value) => Math.round(value).toLocaleString() } },
            plotOptions: { bar: { borderRadius: 4, columnWidth: '45%' } },
            dataLabels: { enabled: !country },
            legend: { position: 'bottom', labels: { colors: '#98a2b3' } },
            stroke: { width: country ? 0 : 2 },
            theme: { mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light' },
        });
        chart.render();
        new MutationObserver(() => chart.updateOptions({ theme: { mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light' } })).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    });
}
