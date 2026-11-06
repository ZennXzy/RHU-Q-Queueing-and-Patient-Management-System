document.addEventListener("DOMContentLoaded", () => {
  const $ = (id) => document.getElementById(id);

  const form = $("report-filters");

  let page = 1;
  let total = 0;
  let current = null;
  let requestId = 0;
  // Unique patients state variables
  let uniquePage = 1;
  let uniqueTotal = 0;
  let activePatientId = null;

  const nf = new Intl.NumberFormat();

  const params = () => {
    return new URLSearchParams(new FormData(form));
  };

  const query = () => {
    const p = params();

    const search = $("searchReports").value.trim();

    if (search) {
      p.set("search", search);
    }

    if (current) {
      p.set("service_label", current);
    }

    return p;
  };

  // API REQUEST
  const api = async (action, p = new URLSearchParams()) => {
    p.set("action", action);

    const res = await fetch("../api/get_reports.php?" + p.toString(), {
      credentials: "same-origin",
    });

    const data = await res.json();

    if (!res.ok || data.status !== "success") {
      throw new Error(data.message || "Unable to load report");
    }

    return data.data;
  };

  const status = (msg) => {
    $("report-status").textContent = msg;
  };

  // POPULATE FILTER OPTIONS
  const fill = (id, rows, idKey = "value", labelKey = "value") => {
    const select = $(id);

    rows.forEach((r) => {
      const option = document.createElement("option");

      option.value = typeof r === "string" ? r : r[idKey];

      option.textContent = typeof r === "string" ? r : r[labelKey];

      select.appendChild(option);
    });
  };

  async function loadOptions() {
    const data = await api("options");

    fill("filter-service", data.services, "id", "name");

    fill("filter-barangay", data.barangays, "id", "name");

    [
      "gender",
      "employment_status",
      "civil_status",
      "religion",
      "phil_status",
      "yakap_member",
    ].forEach((key) => {
      fill("filter-" + key, data[key]);
    });
  }

  // RENDER HORIZONTAL BAR CHART
  function renderBars(id, rows, onClick = null, maxRows = 10) {
    const el = $(id);

    el.replaceChildren();

    if (!rows.length) {
      el.textContent = "No matching records";
      return;
    }

    const max = Math.max(...rows.map((r) => Number(r.visits)), 1);

    rows.slice(0, maxRows).forEach((r) => {
      const item = document.createElement(onClick ? "button" : "div");

      item.className = "bar-item" + (onClick ? " clickable" : "");

      if (onClick) {
        item.type = "button";

        item.addEventListener("click", () => onClick(r));
      }

      const line = document.createElement("div");
      line.className = "bar-label";

      const name = document.createElement("span");
      name.textContent = r.label;

      const count = document.createElement("span");

      count.textContent =
        nf.format(r.visits) +
        " visits · " +
        nf.format(r.patients) +
        " patients";

      line.append(name, count);

      const track = document.createElement("div");
      track.className = "bar-track";

      const bar = document.createElement("div");
      bar.className = "bar-fill";

      bar.style.width = (Number(r.visits) / max) * 100 + "%";

      track.append(bar);

      item.append(line, track);

      el.append(item);
    });
  }

  // LOAD DASHBOARD STATISTICS
  async function loadDashboard() {
    const data = await api("dashboard", query());

    $("kpi-visits").textContent = nf.format(data.kpi.visits);

    $("kpi-patients").textContent = nf.format(data.kpi.patients);

    $("kpi-barangays").textContent = nf.format(data.kpi.barangays);

    $("kpi-service").textContent =
      data.groups.services[0]?.label || "No records";

    // SERVICE CHART
    renderBars("service-chart", data.groups.services, (row) => {
      current = row.label;

      $("filter-service").value = "";

      page = 1;

      reload();

      $("patient-records").scrollIntoView({
        behavior: "smooth",
      });
    });

    // BARANGAY CHART
    renderBars("barangay-chart", data.groups.barangays, (row) => {
      const option = Array.from($("filter-barangay").options).find(
        (o) => o.textContent === row.label,
      );

      if (option) {
        $("filter-barangay").value = option.value;

        current = null;
        page = 1;

        reload();

        $("patient-records").scrollIntoView({
          behavior: "smooth",
        });
      }
    });

    // DEMOGRAPHIC CHART
    const renderDemographics = () => {
      const group = $("demographic-group").value;

      renderBars("demographic-chart", data.groups[group] || []);
    };

    $("demographic-group").onchange = renderDemographics;

    renderDemographics();

    // MONTHLY ACTIVITY
    const months = data.groups.months
      .slice()
      .sort((a, b) => a.label.localeCompare(b.label))
      .slice(-12);

    renderBars("monthly-chart", months);
  }

  // LOAD MATCHING PATIENT VISITS
  async function loadRecords() {
    const p = query();

    p.set("page", page);

    const data = await api("patients", p);

    total = data.total;

    const tbody = $("reports-table-body");

    tbody.replaceChildren();

    if (!data.rows.length) {
      const tr = document.createElement("tr");
      const td = document.createElement("td");

      td.colSpan = 6;
      td.textContent = "No matching records found.";

      tr.append(td);
      tbody.append(tr);
    }

    data.rows.forEach((record) => {
      const tr = document.createElement("tr");

      const values = [
        record.transaction_date,
        record.patient_name,
        (record.age_at_visit ?? "—") + " / " + (record.gender || "—"),
        record.service_name,
        record.barangay,
      ];

      values.forEach((value) => {
        const td = document.createElement("td");

        td.textContent = value ?? "—";

        tr.append(td);
      });

      const actionCell = document.createElement("td");

      const button = document.createElement("button");

      button.className = "details-btn";
      button.textContent = "View";
      button.type = "button";

      button.addEventListener("click", () =>
        showDetails(record.transaction_id),
      );

      actionCell.append(button);
      tr.append(actionCell);

      tbody.append(tr);
    });

    $("records-count").textContent =
      nf.format(total) + " matching service visits";

    $("page-label").textContent =
      "Page " + page + " of " + Math.max(1, Math.ceil(total / 25));

    $("prev-page").disabled = page <= 1;

    $("next-page").disabled = page * 25 >= total;
  }

  // LOAD UNIQUE PATIENTS LIST
  async function loadUniquePatients() {
    const p = query();
    p.set("page", uniquePage);

    const searchUnique = $("searchUniquePatients").value.trim();
    if (searchUnique) {
      p.set("unique_search", searchUnique);
    }

    const data = await api("unique_patients", p);
    uniqueTotal = data.total;

    const tbody = $("unique-patients-table-body");
    tbody.replaceChildren();

    if (!data.rows.length) {
      const tr = document.createElement("tr");
      const td = document.createElement("td");
      td.colSpan = 6;
      td.textContent = "No matching unique patients found.";
      tr.append(td);
      tbody.append(tr);
      return;
    }

    data.rows.forEach((patient) => {
      const tr = document.createElement("tr");

      const values = [
        patient.patient_id,
        patient.patient_name,
        (patient.current_age ?? "—") + " / " + (patient.gender || "—"),
        patient.barangay,
        nf.format(patient.total_visits) + " visits"
      ];

      values.forEach((value) => {
        const td = document.createElement("td");
        td.textContent = value ?? "—";
        tr.append(td);
      });

      const actionCell = document.createElement("td");
      const button = document.createElement("button");
      button.className = "details-btn";
      button.textContent = "View History";
      button.type = "button";
      button.addEventListener("click", () => showPatientHistory(patient.patient_id, patient.patient_name));

      actionCell.append(button);
      tr.append(actionCell);
      tbody.append(tr);
    });

    $("unique-patients-count").textContent = nf.format(uniqueTotal) + " matching distinct patients";
    $("unique-page-label").textContent = "Page " + uniquePage + " of " + Math.max(1, Math.ceil(uniqueTotal / 25));
    $("unique-prev-page").disabled = uniquePage <= 1;
    $("unique-next-page").disabled = uniquePage * 25 >= uniqueTotal;
  }

  // OPEN PATIENT TRANSACTION HISTORY MODAL
  async function showPatientHistory(patientId, patientName) {
    activePatientId = patientId;
    $("patientHistoryModalTitle").textContent = "Transactions for: " + patientName;
    const tbody = $("patient-history-tbody");
    tbody.replaceChildren();

    const loadingTr = document.createElement("tr");
    const loadingTd = document.createElement("td");
    loadingTd.colSpan = 5;
    loadingTd.textContent = "Loading transaction history...";
    loadingTr.append(loadingTd);
    tbody.append(loadingTr);

    $("patient-history-modal").showModal();

    try {
      const data = await api("patient_history", new URLSearchParams({ patient_id: patientId }));
      tbody.replaceChildren();

      if (!data.transactions.length) {
        const tr = document.createElement("tr");
        const td = document.createElement("td");
        td.colSpan = 5;
        td.textContent = "No recorded transactions found.";
        tr.append(td);
        tbody.append(tr);
        return;
      }

      data.transactions.forEach((tx) => {
        const tr = document.createElement("tr");
        const values = [
          tx.transaction_date,
          tx.queue_code || "—",
          tx.service_name,
          tx.barangay
        ];

        values.forEach((val) => {
          const td = document.createElement("td");
          td.textContent = val;
          tr.append(td);
        });

        const actionTd = document.createElement("td");
        const viewBtn = document.createElement("button");
        viewBtn.className = "details-btn";
        viewBtn.textContent = "Details";
        viewBtn.type = "button";
        viewBtn.addEventListener("click", () => {
          $("patient-history-modal").close();
          showDetails(tx.transaction_id);
        });

        actionTd.append(viewBtn);
        tr.append(actionTd);
        tbody.append(tr);
      });
    } catch (err) {
      tbody.replaceChildren();
      const tr = document.createElement("tr");
      const td = document.createElement("td");
      td.colSpan = 5;
      td.textContent = "Error loading history: " + err.message;
      tr.append(td);
      tbody.append(tr);
    }
  }

  // RELOAD DASHBOARD AND RECORDS
  async function reload() {
    const seq = ++requestId;

    status("Loading reports…");

    try {
      await loadDashboard();

      if (seq !== requestId) {
        return;
      }

      await loadRecords();
      await loadUniquePatients();

      if (seq !== requestId) {
        return;
      }

      status(current ? "Selected service: " + current : "Filters applied");
    } catch (error) {
      if (seq === requestId) {
        status("Error: " + error.message);

        console.error(error);
      }
    }
  }

  // VIEW TRANSACTION DETAILS
  async function showDetails(id) {
    try {
      const data = await api("details", new URLSearchParams({ id }));

      const record = data.record;

      const container = $("recordDetailsContent");

      container.replaceChildren();

      const fields = [
          ["Patient", "fullname"],
          ["Service", "service_type"],
          ["Transaction Date", "transaction_date"],
          ["Queue Code", "queue_code"],
          ["Barangay", "address"],
          ["Gender", "gender"],
          ["Blood Pressure", "blood_pressure"],
          ["Respiratory Rate", "respiratory_rate"],
          ["Temperature", "temperature"],
          ["Pulse Rate", "pulse_rate"],
          ["Weight", "weight"],
          ["Height", "height"],
          ["Diagnosis", "diagnosis"],
          ["Remarks", "remarks"],
      ];

      fields.forEach(([label, key]) => {
          const row = document.createElement("div");
          row.className = "detail-row";

          const heading = document.createElement("strong");
          heading.textContent = label;

          const value = document.createElement("span");
          
          // Get the raw value from the record, default to "—" if empty
          let valText = record[key] !== undefined && record[key] !== null && record[key] !== "" ? record[key] : "—";

          // Concat units based on the label string
          if (valText !== "—") {
              if (label === 'Blood Pressure') valText += " (mmHg)";
              else if (label === 'Respiratory Rate') valText += " (cpm)";
              else if (label === 'Temperature') valText += " (°C)";
              else if (label === 'Pulse Rate') valText += " (bpm)";
              else if (label === 'Weight') valText += " (kg)";
              else if (label === 'Height') valText += " (cm)";
          }

          value.textContent = valText;

          row.append(heading, value);
          container.append(row);
      });

      $("view-record-modal").showModal();
    } catch (error) {
      status("Unable to open record: " + error.message);
    }
  }

  // APPLY FILTERS
  form.addEventListener("submit", (event) => {
    event.preventDefault();
    current = null;
    page = 1;
    uniquePage = 1; 
    reload();
  });

  // RESET FILTERS
  $("reset-filters").addEventListener("click", () => {
    form.reset();
    $("searchReports").value = "";
    $("searchUniquePatients").value = ""; 
    current = null;
    page = 1;
    uniquePage = 1; 
    reload();
  });

  // UNIQUE PATIENTS CARD CLICK
  $("kpi-patients-card").addEventListener("click", () => {
    $("unique-patients-panel").scrollIntoView({ behavior: "smooth" });
  });

  // UNIQUE PATIENTS SEARCH INPUT
  let uniqueDebounce;
  $("searchUniquePatients").addEventListener("input", () => {
    clearTimeout(uniqueDebounce);
    uniqueDebounce = setTimeout(() => {
      uniquePage = 1;
      loadUniquePatients().catch((error) => status(error.message));
    }, 350);
  });

  // UNIQUE PATIENTS PAGINATION
  $("unique-prev-page").addEventListener("click", () => {
    if (uniquePage > 1) {
      uniquePage--;
      loadUniquePatients().catch((error) => status(error.message));
    }
  });

  $("unique-next-page").addEventListener("click", () => {
    if (uniquePage * 25 < uniqueTotal) {
      uniquePage++;
      loadUniquePatients().catch((error) => status(error.message));
    }
  });

  // CLOSE PATIENT HISTORY MODAL
  $("close-patient-history").addEventListener("click", () =>
    $("patient-history-modal").close()
  );

  // SEARCH RECORDS
  let debounce;

  $("searchReports").addEventListener("input", () => {
    clearTimeout(debounce);

    debounce = setTimeout(() => {
      page = 1;

      reload();
    }, 350);
  });

  // PAGINATION
  $("prev-page").addEventListener("click", () => {
    if (page > 1) {
      page--;

      loadRecords().catch((error) => status(error.message));
    }
  });

  $("next-page").addEventListener("click", () => {
    if (page * 25 < total) {
      page++;

      loadRecords().catch((error) => status(error.message));
    }
  });

  // CLOSE DETAILS
  $("close-record").addEventListener("click", () =>
    $("view-record-modal").close(),
  );

  // CSV EXPORT
  const exportCsv = (mode) => {
    const p = query();

    p.set("mode", mode);

    window.location.href = "../php/export_reports.php?" + p.toString();
  };

  $("export-visits").addEventListener("click", () => exportCsv("visits"));

  $("export-patients").addEventListener("click", () => exportCsv("patients"));

  $("export-summary").addEventListener("click", () => exportCsv("summary"));

  // INITIALIZE
  loadOptions()
    .then(reload)
    .catch((error) => {
      status("Initialization failed: " + error.message);
    });
});
