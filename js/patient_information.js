document.addEventListener("DOMContentLoaded", async () => {
  const getVal = (obj, keys, defaultVal = "") => {
    for (const key of keys) {
      if (
        obj &&
        obj[key] !== undefined &&
        obj[key] !== null &&
        String(obj[key]).trim() !== ""
      ) {
        return obj[key];
      }
    }
    return defaultVal;
  };

  const cleanStr = (val) => String(val || "").toLowerCase().trim();

  const buildFullName = (obj, fKeys, mKeys, lKeys, sKeys = []) => {
    const f = getVal(obj, fKeys);
    const m = getVal(obj, mKeys);
    const l = getVal(obj, lKeys);
    const s = getVal(obj, sKeys);
    return [l, f, m, s].filter(Boolean).join(" ").trim();
  };

  // Helper function to update elements safely
  const setText = (id, value) => {
    const el = document.getElementById(id);
    if (el) el.textContent = value || "-";
  };

  try {
    // 1. Get logged-in patient ID from session storage or backend auth check
    const patientId = sessionStorage.getItem("patient_id") || sessionStorage.getItem("user_id") || "session";

    // 2. Fetch details from API
    const response = await fetch(`../api/get_patients.php?action=details&id=${patientId}`);
    if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);

    const result = await response.json();

    if (result.status === "success") {
      const p = result.patient;
      
      // Full Name
      const fullName = buildFullName(
        p,
        ["firstname"],
        ["middlename"],
        ["lastname"],
        ["suffix"]
      );
      setText("infoFullName", fullName);

      // Age and Gender Calculation
      const rawBirthDate = getVal(p, ["date_of_birth"], "");
      let calculatedAge = getVal(p, ["age"], null);

      if ((!calculatedAge || calculatedAge === 0 || calculatedAge === "0") && rawBirthDate) {
        const birth = new Date(rawBirthDate);
        if (!isNaN(birth.getTime())) {
          const today = new Date();
          calculatedAge = today.getFullYear() - birth.getFullYear();
          const m = today.getMonth() - birth.getMonth();
          if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) {
            calculatedAge--;
          }
        }
      }

      const gender = getVal(p, ["gender"], "-");
      const civilStatus = getVal(p, ["civil_status"], "-");

      setText("infoAgeGender", `${calculatedAge && calculatedAge !== 0 ? calculatedAge : "-"} / ${gender}`);
      setText("infoBirthDate", rawBirthDate);
      setText("infoCivilStatus", civilStatus);
      setText("infoReligion", getVal(p, ["religion"], "-"));

      // Conditional Spouse Group
      const spouseGroup = document.getElementById("infoSpouseGroup");
      const statusLower = cleanStr(civilStatus);
      if (spouseGroup) {
        if (statusLower.includes("married") || statusLower.includes("widow")) {
          spouseGroup.style.display = "block";
          setText("infoSpouseName", getVal(p, ["spouses_name"], "N/A"));
        } else {
          spouseGroup.style.display = "none";
        }
      }

      // Contact & Location
      setText("infoContact", getVal(p, ["contact_number"], "-"));
      setText("infoEmail", getVal(p, ["email"], "-"));
      setText("infoAddress", getVal(p, ["address"], "-"));
      setText("infoDateRegistered", getVal(p, ["date_registered"], "-"));

      // Family Background
      setText("infoMotherName", getVal(p, ["mothers_name"], "N/A"));
      setText("infoFatherName", getVal(p, ["fathers_name"], "N/A"));

      // Employment
      const rawEmpStatus = getVal(p, ["employment_status"], "");
      const empStatus = cleanStr(rawEmpStatus);
      setText("infoEmploymentStatus", rawEmpStatus);

      const occGroup = document.getElementById("infoOccupationGroup");
      const bizGroup = document.getElementById("infoBusinessGroup");

      const isEmployed = empStatus.includes("employed") && !empStatus.includes("unemployed") && !empStatus.includes("self");
      const isSelfEmployed = empStatus.includes("self");

      if (occGroup) {
        if (isEmployed) {
          occGroup.style.display = "block";
          setText("infoOccupation", getVal(p, ["occupation"], "N/A"));
        } else {
          occGroup.style.display = "none";
        }
      }

      if (bizGroup) {
        if (isSelfEmployed) {
          bizGroup.style.display = "block";
          setText("infoBusiness", getVal(p, ["business_name"], "N/A"));
        } else {
          bizGroup.style.display = "none";
        }
      }

      // PhilHealth & Yakap
      const philStatus = getVal(p, ["phil_status"], "");
      setText("infoPhilStatus", philStatus);

      const philNumGroup = document.getElementById("infoPhilNumberGroup");
      const prinGroup = document.getElementById("infoPrinMemberGroup");

      if (philNumGroup && prinGroup) {
        if (philStatus.includes("Member")) {
          philNumGroup.style.display = "block";
          prinGroup.style.display = "none";
        } else if (philStatus.includes("Dependent")) {
          philNumGroup.style.display = "block";
          prinGroup.style.display = "block";
        } else {
          philNumGroup.style.display = "none";
          prinGroup.style.display = "none";
        }
      }

      setText("infoPhilNumber", getVal(p, ["phil_number"], "N/A"));
      setText("infoPrinMember", getVal(p, ["principal_member"], "N/A"));
      setText("infoYakapMember", getVal(p, ["yakap_member"], "N/A"));
    } else {
      if (typeof showToast === "function") {
        showToast("Unable to load patient information.", "error");
      }
    }
  } catch (error) {
    console.error("Error fetching patient details:", error);
  }
});