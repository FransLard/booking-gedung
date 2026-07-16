import Chart from 'chart.js/auto';

document.addEventListener('DOMContentLoaded', () => {
    const revenueCanvas = document.getElementById('revenueChart');
    if (revenueCanvas) {
        const labels = JSON.parse(revenueCanvas.dataset.labels);
        const values = JSON.parse(revenueCanvas.dataset.values);

        new Chart(revenueCanvas, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: values,
                    borderColor: 'rgba(99, 102, 241, 1)',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 4,
                    pointBackgroundColor: 'rgba(99, 102, 241, 1)',
                }],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: v => 'Rp ' + v.toLocaleString('id-ID'),
                        },
                    },
                },
            },
        });
    }

    const statusCanvas = document.getElementById('statusChart');
    if (statusCanvas) {
        const labels = JSON.parse(statusCanvas.dataset.labels);
        const values = JSON.parse(statusCanvas.dataset.values);

        new Chart(statusCanvas, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    data: values,
                    backgroundColor: [
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(234, 179, 8, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                    ],
                    borderColor: [
                        'rgba(34, 197, 94, 1)',
                        'rgba(234, 179, 8, 1)',
                        'rgba(239, 68, 68, 1)',
                    ],
                    borderWidth: 1,
                }],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' },
                },
            },
        });
    }

    const plCanvas = document.getElementById('profitLossChart');
    if (plCanvas) {
        const labels = JSON.parse(plCanvas.dataset.labels);
        const profits = JSON.parse(plCanvas.dataset.profits);
        const losses = JSON.parse(plCanvas.dataset.losses);

        new Chart(plCanvas, {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Profit (Pendapatan)',
                        data: profits,
                        backgroundColor: 'rgba(34, 197, 94, 0.8)',
                        borderColor: 'rgba(34, 197, 94, 1)',
                        borderWidth: 1,
                        borderRadius: 4,
                    },
                    {
                        label: 'Loss (Pembatalan)',
                        data: losses,
                        backgroundColor: 'rgba(239, 68, 68, 0.8)',
                        borderColor: 'rgba(239, 68, 68, 1)',
                        borderWidth: 1,
                        borderRadius: 4,
                    },
                ],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: v => 'Rp ' + v.toLocaleString('id-ID'),
                        },
                    },
                },
            },
        });
    }

    const toast = document.getElementById('toast');
    if (toast) {
        setTimeout(() => toast.remove(), 5000);
    }
});
