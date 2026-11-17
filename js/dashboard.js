document.addEventListener("DOMContentLoaded", () => {
  const chartInstances = {};

  const PALETTES = {
    age: ["#0D9488", "#2DD4BF", "#5EEAD4", "#99F6E4", "#115E59", "#94A3B8"],
    civil: ["#6366F1", "#A78BFA", "#F59E0B", "#EC4899", "#94A3B8", "#14B8A6"],
    gender: ["#2563EB", "#EC4899", "#8B5CF6", "#64748B"],
    employment: ["#16A34A", "#F59E0B", "#64748B", "#06B6D4", "#8B5CF6"],
    service: [
      "#7C3AED",
      "#8B5CF6",
      "#A78BFA",
      "#C4B5FD",
      "#6D28D9",
      "#9333EA",
      "#6366F1",
      "#D946EF",
      "#4F46E5",
    ],
  };

  function setText(id, value) {
    const element = document.getElementById(id);
    if (element) element.textContent = value;
  }

  function getTotal(data) {
    return (data?.data || []).reduce(
      (total, value) => total + (Number(value) || 0),
      0,
    );
  }

  function getColors(labels, paletteName) {
    const palette = PALETTES[paletteName] || PALETTES.age;

    const genderColors = {
      male: "#2563EB",
      female: "#EC4899",
      other: "#8B5CF6",
      others: "#8B5CF6",
      "not specified": "#94A3B8",
    };

    const employmentColors = {
      employed: "#16A34A",
      "self-employed": "#F59E0B",
      "self employed": "#F59E0B",
      unemployed: "#64748B",
      student: "#06B6D4",
      retired: "#8B5CF6",
      "not specified": "#94A3B8",
    };

    return labels.map((label, index) => {
      const normalized = String(label).trim().toLowerCase();

      if (paletteName === "gender") {
        return genderColors[normalized] || palette[index % palette.length];
      }

      if (paletteName === "employment") {
        return employmentColors[normalized] || palette[index % palette.length];
      }

      return palette[index % palette.length];
    });
  }

  function createMetric(label, count) {
    const item = document.createElement("div");
    item.className = "demographic-metric";

    const name = document.createElement("span");
    name.textContent = label;

    const value = document.createElement("strong");
    value.textContent = Number(count) || 0;

    item.append(name, value);
    return item;
  }

  function updateDemographicSummary(containerId, totalId, data) {
    const container = document.getElementById(containerId);
    if (!container) return;

    container.replaceChildren();

    const labels = data?.labels || [];
    const values = data?.data || [];

    if (!labels.length) {
      container.appendChild(createMetric("No data available", 0));
    } else {
      labels.forEach((label, index) => {
        container.appendChild(createMetric(label, values[index]));
      });
    }

    setText(totalId, `Total: ${getTotal(data)}`);
  }

  function updateChartMetrics(containerId, data) {
    const container = document.getElementById(containerId);
    if (!container) return;

    container.replaceChildren();

    const labels = data?.labels || [];
    const values = data?.data || [];

    const metricItems = document.createElement("div");
    metricItems.className = "chart-metric-items";

    if (!labels.length) {
      const empty = document.createElement("span");
      empty.className = "chart-empty";
      empty.textContent = "No data available";
      metricItems.appendChild(empty);
    } else {
      labels.forEach((label, index) => {
        const item = document.createElement("span");

        const name = document.createElement("span");
        name.textContent = `${label}: `;

        const count = document.createElement("strong");
        count.className = "metric-value";
        count.textContent = Number(values[index]) || 0;

        item.append(name, count);
        metricItems.appendChild(item);
      });
    }

    const total = document.createElement("div");
    total.className = "chart-metric-total";
    total.textContent = `Total: ${getTotal(data)}`;

    container.append(metricItems, total);
  }

  function renderChart(id, type, data, paletteName, extraOptions = {}) {
    const canvas = document.getElementById(id);

    if (!canvas || typeof Chart === "undefined") return;

    const labels = data?.labels || [];
    const values = data?.data || [];

    if (chartInstances[id]) {
      chartInstances[id].destroy();
      delete chartInstances[id];
    }

    const isBar = type === "bar";

    const baseOptions = {
      responsive: true,
      maintainAspectRatio: false,
      animation: false,

      interaction: {
        mode: "nearest",
        intersect: true,
      },

      plugins: {
        tooltip: {
          enabled: true,
          backgroundColor: "#1E293B",
          padding: 12,
          cornerRadius: 8,
        },

        legend: {
          display: !isBar,
          position: "bottom",
          labels: {
            boxWidth: 12,
            boxHeight: 12,
            padding: 14,
            usePointStyle: true,
            pointStyle: "circle",
            color: "#475569",
            font: {
              size: 12,
            },
          },
        },
      },
    };

    if (isBar) {
      baseOptions.scales = {
        x: {
          grid: {
            color: "#E2E8F0",
          },
          ticks: {
            color: "#64748B",
          },
        },
        y: {
          beginAtZero: true,
          grid: {
            color: "#F1F5F9",
          },
          ticks: {
            precision: 0,
            color: "#64748B",
          },
        },
      };
    }

    const finalOptions = {
      ...baseOptions,
      ...extraOptions,
      plugins: {
        ...baseOptions.plugins,
        ...(extraOptions.plugins || {}),
        legend: {
          ...baseOptions.plugins.legend,
          ...(extraOptions.plugins?.legend || {}),
        },
        tooltip: {
          ...baseOptions.plugins.tooltip,
          ...(extraOptions.plugins?.tooltip || {}),
        },
      },
      scales: extraOptions.scales || baseOptions.scales,
    };

    chartInstances[id] = new Chart(canvas, {
      type,
      data: {
        labels,
        datasets: [
          {
            label: "Count",
            data: values,
            backgroundColor: getColors(labels, paletteName),
            borderColor: isBar ? "transparent" : "#FFFFFF",
            borderWidth: isBar ? 0 : 2,
            borderRadius: isBar ? 6 : 0,
            maxBarThickness: isBar ? 46 : undefined,
            hoverOffset: isBar ? 0 : 8,
          },
        ],
      },
      options: finalOptions,
    });
  }

  async function loadDashboardData() {
    try {
      const dateFrom = document.getElementById("dateFrom")?.value || "";
      const dateTo = document.getElementById("dateTo")?.value || "";

      const params = new URLSearchParams();
      if (dateFrom) params.append("date_from", dateFrom);
      if (dateTo) params.append("date_to", dateTo);

      const url = `../api/get_dashboard_stats.php${params.toString() ? "?" + params.toString() : ""}`;

      const response = await fetch(url, {
        cache: "no-store",
      });

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
      }

      const result = await response.json();

      if (result.status !== "success") {
        throw new Error(result.message || "Unable to load dashboard.");
      }

      const tiles = result.tiles || {};
      const charts = result.charts || {};

      // OVERVIEW METRICS
      setText("valTotalPatients", tiles.total_patients ?? 0);
      setText("valWaiting", tiles.waiting ?? 0);
      setText("valInProgress", tiles.in_progress ?? 0);
      setText("valCompleted", tiles.completed ?? 0);

      // PATIENT DEMOGRAPHICS SUMMARY
      const summaries = [
        ["civilStatusSummary", "civilStatusTotal", "civil_status"],
        ["genderSummary", "genderTotal", "gender"],
        ["employmentSummary", "employmentTotal", "employment"],
        ["ageSummary", "ageTotal", "age_distribution"],
        ["philhealthSummary", "philhealthTotal", "philhealth"],
        ["yakapSummary", "yakapTotal", "yakap"],
      ];

      summaries.forEach(([containerId, totalId, key]) => {
        updateDemographicSummary(containerId, totalId, charts[key]);
      });

      // AGE DISTRIBUTION
      renderChart(
        "ageDistributionChart",
        "bar",
        charts.age_distribution,
        "age",
      );
      updateChartMetrics("ageDistributionMetrics", charts.age_distribution);

      // CIVIL STATUS
      renderChart(
        "civilStatusChart",
        "doughnut",
        charts.civil_status,
        "civil",
        { cutout: "48%" },
      );
      updateChartMetrics("civilStatusMetrics", charts.civil_status);

      // GENDER DISTRIBUTION
      renderChart(
        "genderDistributionChart",
        "doughnut",
        charts.gender,
        "gender",
        { cutout: "48%" },
      );
      updateChartMetrics("genderDistributionMetrics", charts.gender);

      // EMPLOYMENT STATUS
      renderChart(
        "employmentStatusChart",
        "doughnut",
        charts.employment,
        "employment",
        { cutout: "48%" },
      );
      updateChartMetrics("employmentStatusMetrics", charts.employment);

      // SERVICE DISTRIBUTION
      renderChart(
        "serviceDistributionChart",
        "bar",
        charts.service,
        "service",
        {
          indexAxis: "y",
          scales: {
            x: {
              beginAtZero: true,
              grid: { color: "#F1F5F9" },
              ticks: { precision: 0, color: "#64748B" },
            },
            y: {
              grid: { display: false },
              ticks: { autoSkip: false, color: "#475569" },
            },
          },
        },
      );
      updateChartMetrics("serviceDistributionMetrics", charts.service);
    } catch (error) {
      console.error("Dashboard loading error:", error);
    }
  }

  // Event Listeners for Filter Form
  const filterForm = document.getElementById("dashboardFilterForm");
  if (filterForm) {
    filterForm.addEventListener("submit", (e) => {
      e.preventDefault();
      loadDashboardData();
    });
  }

  const resetFilterBtn = document.getElementById("resetDashboardFilter");
  if (resetFilterBtn) {
    resetFilterBtn.addEventListener("click", () => {
      if (document.getElementById("dateFrom")) document.getElementById("dateFrom").value = "";
      if (document.getElementById("dateTo")) document.getElementById("dateTo").value = "";
      loadDashboardData();
    });
  }

  loadDashboardData();
});