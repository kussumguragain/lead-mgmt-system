const ctx = document.getElementById('leadsLineChart').getContext('2d');
const leadsChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: chartDates,
        datasets: [{
            label: 'Leads Created',
            data: chartTotals,
            borderColor: 'rgba(0, 123, 255, 1)',
            backgroundColor: 'rgba(0, 123, 255, 0.2)',
            fill: true,
            tension: 0.3,
            pointRadius: 4
        }]
    },
    options: {
        responsive: true,
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Date'
                }
            },
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Number of Leads'
                }
            }
        }
    }
});
