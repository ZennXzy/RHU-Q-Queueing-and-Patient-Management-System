
/* RHU-Q public account registration: only #accountRegistrationForm.
   Does not alter the admin-side registration validator. */
(() => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('accountRegistrationForm');
    if (!form) return;

    const step1 = document.getElementById('patientInfoStep');
    const step2 = document.getElementById('credentialsStep');
    const nextButton = document.getElementById('nextStepBtn');
    const backButton = document.getElementById('backStepBtn');

    const patientFields = [
      'lname', 'fname', 'address', 'date_of_birth', 'religion',
      'mlname', 'mfname', 'flname', 'ffname'
    ];

    const patientGroups = [
      'gender', 'employment_status', 'civilStatus',
      'philStatusInput', 'yakapmem'
    ];

    const credentialFields = [
      'username', 'password', 'confirm_password'
    ];

    const conditionalFields = [
      'slname', 'sfname', 'philHNumInput', 'prinMemInput'
    ];

    const visible = element =>
      !!element && element.getClientRects().length > 0;

    const field = id => form.querySelector(`#${id}`);

    const selected = name =>
      form.querySelector(`input[name="${name}"]:checked`)?.value;

    function targetFor(input) {
      return input.closest('.date, .input-select') || input;
    }

    function mark(input, invalid) {
      if (!input) return;

      targetFor(input).classList.toggle('required-invalid', invalid);
      input.setAttribute('aria-invalid', String(invalid));
    }

    function checkField(id) {
      const input = field(id);
      if (!input) return true;

      if (!visible(input)) {
        mark(input, false);
        return true;
      }

      let valid = input.value.trim().length > 0;

      if (id === 'password') {
        valid = input.value.length >= 6;
      }

      if (id === 'confirm_password') {
        valid = valid && input.value === field('password')?.value;
      }

      mark(input, !valid);
      return valid;
    }

    function checkGroup(name) {
      const options = [
        ...form.querySelectorAll(`input[name="${name}"]`)
      ];

      const shown = options.filter(visible);
      if (!shown.length) return true;

      const valid = shown.some(option => option.checked);

      const wrapper = shown[0].closest(
        '.input-container, .fieldset-group'
      );

      wrapper?.classList.toggle('required-invalid', !valid);

      shown.forEach(option => {
        option.setAttribute('aria-invalid', String(!valid));
      });

      return valid;
    }

    function validatePatient() {
      let valid = true;

      for (const id of patientFields) {
        if (!checkField(id)) valid = false;
      }

      for (const name of patientGroups) {
        if (!checkGroup(name)) valid = false;
      }

      const spouseNeeded = ['Married', 'Widow'].includes(
        selected('civilStatus')
      );

      const philStatus = selected('philStatusInput');

      const needed = {
        slname: spouseNeeded,
        sfname: spouseNeeded,
        philHNumInput: ['Member', 'Dependent'].includes(philStatus),
        prinMemInput: philStatus === 'Dependent'
      };

      for (const id of conditionalFields) {
        if (needed[id]) {
          if (!checkField(id)) valid = false;
        } else {
          mark(field(id), false);
        }
      }

      return valid;
    }

    function validateCredentials() {
      let valid = true;

      for (const id of credentialFields) {
        if (!checkField(id)) valid = false;
      }

      return valid;
    }

    function validateCurrent() {
      if (visible(step1)) return validatePatient();
      if (visible(step2)) return validateCredentials();

      return true;
    }

    function focusFirstInvalid(section) {
      const invalid = section?.querySelector('.required-invalid');
      if (!invalid) return;

      const control = invalid.matches('input, select')
        ? invalid
        : invalid.querySelector('input, select');

      control?.focus({ preventScroll: false });
    }

    function addStarAfterText(element) {
      if (!element || element.querySelector('.required-asterisk')) {
        return;
      }

      const star = document.createElement('span');
      star.className = 'required-asterisk';
      star.textContent = ' *';
      star.setAttribute('aria-hidden', 'true');

      element.appendChild(star);
    }

    function addMarkers() {
      // Red asterisk inside required inputs/selects.
      for (const id of [...patientFields, ...conditionalFields]) {
        if (id === 'date_of_birth') continue;

        const input = field(id);
        if (!input) continue;

        if (input.tagName === 'SELECT' && id !== 'religion') {
          continue;
        }

        const wrapper = targetFor(input) === input
          ? input.parentElement
          : targetFor(input);

        if (!wrapper) continue;

        if (wrapper.querySelector(
          `.required-marker[data-for="${id}"]`
        )) {
          continue;
        }

        wrapper.style.position = 'relative';

        const star = document.createElement('span');
        star.className = 'required-marker';
        star.dataset.for = id;
        star.textContent = '*';
        star.setAttribute('aria-hidden', 'true');

        wrapper.appendChild(star);
      }

      const headings = new Set([
        'Date of Birth:',
        'Sex',
        'Employment Status',
        'Civil Status',
        'PhilHealth Status',
        'PhilHealth Number',
        'Principal Member',
        'Yakap Member'
      ]);

      form.querySelectorAll('span, legend').forEach(el => {
        if (headings.has(el.textContent.trim())) {
          addStarAfterText(el);
        }
      });

      // Make credential-label asterisks red.
      for (const id of credentialFields) {
        const input = field(id);

        const label = input
          ?.closest('.form-group')
          ?.querySelector('label');

        if (!label || label.querySelector('.required-asterisk')) {
          continue;
        }

        const raw = label.textContent.trim();

        label.textContent = raw.replace(/\s*\*$/, '');

        addStarAfterText(label);
      }
    }

    // Add required indicators.
    addMarkers();

    // Pulse empty required fields immediately.
    validateCurrent();

    // Validate continuously.
    form.addEventListener('input', validateCurrent);

    form.addEventListener('change', () => {
      queueMicrotask(validateCurrent);
    });

    // Block Next Step when Step 1 is incomplete.
    nextButton?.addEventListener('click', event => {
      if (!validatePatient()) {
        event.preventDefault();
        event.stopImmediatePropagation();
        focusFirstInvalid(step1);
      }
    }, true);

    // Revalidate when the visible step changes.
    const observer = new MutationObserver(() => {
      validateCurrent();
    });

    if (step1) {
      observer.observe(step1, {
        attributes: true,
        attributeFilter: ['style', 'class']
      });
    }

    if (step2) {
      observer.observe(step2, {
        attributes: true,
        attributeFilter: ['style', 'class']
      });
    }

    backButton?.addEventListener('click', () => {
      queueMicrotask(validateCurrent);
    });

    // Prevent invalid form submission.
    form.addEventListener('submit', event => {
      const patientOK = validatePatient();
      const credentialsOK = validateCredentials();

      if (patientOK && credentialsOK) return;

      event.preventDefault();
      event.stopImmediatePropagation();

      if (!patientOK) {
        if (step1) step1.style.display = '';
        if (step2) step2.style.display = 'none';

        focusFirstInvalid(step1);
      } else {
        focusFirstInvalid(step2);
      }
    }, true);

    // Recheck after resetting the form.
    form.addEventListener('reset', () => {
      requestAnimationFrame(() => {
        validateCurrent();
      });
    });
  });
})();
