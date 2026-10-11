document.addEventListener("DOMContentLoaded", () => {
  const filterForm = document.getElementById('dashboardFilterForm');
  
  loadDashboardData();

  if (filterForm) {
    filterForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const formData = new FormData(filterForm);
      loadDashboardData(new URLSearchParams(formData));
    });
  }

  let chartsInstances = {};

  async function loadDashboardData(params = new URLSearchParams()) {
    try {
      const response = await fetch(`../api/get_dashboard_stats.php?${params.toString()}`);
      const result = await response.json();

      if (result.status === 'success') {
        // 1. Update Tiles
        document.getElementById('valTotalPatients').textContent = result.tiles.total_patients;
        document.getElementById('valWaiting').textContent = result.tiles.waiting;
        document.getElementById('valInProgress').textContent = result.tiles.in_progress;
        document.getElementById('valCompleted').textContent = result.tiles.completed;

        const c = result.charts;

        // Helper to render or update charts with tooltips enabled
        function renderChart(id, type, chartData, options = {}) {
        if (chartsInstances[id]) {
          chartsInstances[id].destroy();
          delete chartsInstances[id];
        }

        const canvas = document.getElementById(id);
        if (!canvas) return;
        const ctx = canvas.getContext('2d');

        chartsInstances[id] = new Chart(ctx, {
          type: type,
          data: chartData,
          options: Object.assign({
            responsive: true,
            maintainAspectRatio: false,
            animation: {
              duration: 400 // Optional: slightly faster animations prevent overlap race conditions
            },
            plugins: {
              tooltip: { enabled: true }
            }
          }, options)
        });
      }

        // 2. Registration Trend (Line)
        renderChart('registrationTrendChart', 'line', {
          labels: c.registration_trend.labels,
          datasets: [{ label: 'Registrations', data: c.registration_trend.data, borderColor: '#8b5cf6', backgroundColor: 'rgba(139,92,246,0.1)', fill: true, tension: 0.3 }]
        });

        // 3. Queue Trend (Line)
        renderChart('queueTrendChart', 'line', {
          labels: c.queue_trend.labels,
          datasets: [{ label: 'Queue Volume', data: c.queue_trend.data, borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.1)', fill: true, tension: 0.3 }]
        });

        // 4. Age Distribution (Bar)
        renderChart('ageDistributionChart', 'bar', {
          labels: c.age_distribution.labels,
          datasets: [{ label: 'Patients', data: c.age_distribution.data, backgroundColor: '#10b981' }]
        }, { plugins: { legend: { display: false } } });

        // 5. Civil Status (Pie)
        renderChart('civilStatusChart', 'pie', {
          labels: c.civil_status.labels,
          datasets: [{ data: c.civil_status.data, backgroundColor: ['#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6', '#64748b'] }]
        });

        // 6. Gender Distribution (Pie)
        renderChart('genderDistributionChart', 'pie', {
          labels: c.gender.labels,
          datasets: [{ data: c.gender.data, backgroundColor: ['#ec4899', '#3b82f6', '#10b981'] }]
        });

        // 7. Service Distribution (Horizontal Bar Graph styled like the reference)
        renderChart('serviceDistributionChart', 'bar', {
          labels: c.service.labels,
          datasets: [{
            label: 'Patients',
            data: c.service.data,
            backgroundColor: '#8b5cf6', // Soft purple matching your reference image
            borderRadius: 6,            // Rounded bar edges
            barThickness: 18            // Controls the thickness of each bar
          }]
        }, {
          indexAxis: 'y', // Makes it a horizontal bar chart
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: true,
              position: 'bottom',
              labels: {
                boxWidth: 12,
                boxHeight: 12,
                borderRadius: 6,
                useBorderRadius: true,
                font: { family: 'inherit', size: 12 }
              }
            },
            tooltip: { enabled: true }
          },
          scales: {
            x: {
              beginAtZero: true,
              grid: { color: '#f3f4f6' },
              ticks: { precision: 0 } // Keeps numbers clean integers (0, 2, 4, 6, 8)
            },
            y: {
              grid: { display: false }, // Hides vertical grid lines for a cleaner look
              ticks: { font: { size: 11 } }
            }
          }
        });

        // 8. Employment Status (Pie)
        renderChart('employmentStatusChart', 'pie', {
          labels: c.employment.labels,
          datasets: [{ data: c.employment.data, backgroundColor: ['#10b981', '#f59e0b', '#64748b'] }]
        });

        // 9. Generate Text-Type Data Summary
        generateTextSummary(result.tiles, c);
      }
    } catch (err) {
      console.error('Error loading dashboard:', err);
    }
  }

  function generateTextSummary(tiles, c) {
    const container = document.getElementById('textDataSummary');
    container.innerHTML = `
      <div class="summary-box">
        <h4>Overview Metrics</h4>
        <p><strong>Total Registered Patients:</strong> ${tiles.total_patients}</p>
        <p><strong>Today's Waiting Queue:</strong> ${tiles.waiting}</p>
        <p><strong>Today's In-Progress:</strong> ${tiles.in_progress}</p>
        <p><strong>Today's Completed:</strong> ${tiles.completed}</p>
      </div>
      <div class="summary-box">
        <h4>Civil Status Breakdown</h4>
        <p>${c.civil_status.labels.map((l, i) => `${l}: <strong>${c.civil_status.data[i]}</strong>`).join(', ') || 'No data'}</p>
      </div>
      <div class="summary-box">
        <h4>Gender Demographics</h4>
        <p>${c.gender.labels.map((l, i) => `${l}: <strong>${c.gender.data[i]}</strong>`).join(', ') || 'No data'}</p>
      </div>
      <div class="summary-box">
        <h4>Employment Status</h4>
        <p>${c.employment.labels.map((l, i) => `${l}: <strong>${c.employment.data[i]}</strong>`).join(', ') || 'No data'}</p>
      </div>
    `;
  }
});