/**
 * Elite Sports Connect — Frontend Scripts
 * Handles: photo upload preview, drag & drop, form validation UX,
 * filter bar scroll-into-view, submit loading state.
 */

(function ($) {
  'use strict';

  /* =========================================================================
     Photo Upload Zone
     ========================================================================= */
  function initUploadZone() {
    const zone    = document.getElementById('esc-upload-zone');
    if (!zone) return;

    const input   = zone.querySelector('.esc-upload-zone__input');
    const ui      = zone.querySelector('.esc-upload-zone__ui');
    const preview = document.getElementById('esc-photo-preview');
    const previewImg = document.getElementById('esc-photo-preview-img');
    const removeBtn  = document.getElementById('esc-photo-remove');

    function showPreview(file) {
      if (!file || !file.type.startsWith('image/')) return;
      const reader = new FileReader();
      reader.onload = function (e) {
        previewImg.src = e.target.result;
        ui.hidden      = true;
        preview.hidden = false;
      };
      reader.readAsDataURL(file);
    }

    function clearPreview() {
      input.value    = '';
      previewImg.src = '';
      ui.hidden      = false;
      preview.hidden = true;
      zone.classList.remove('is-dragging');
    }

    // File selected via input.
    input.addEventListener('change', function () {
      if (this.files && this.files[0]) {
        showPreview(this.files[0]);
      }
    });

    // Remove button.
    if (removeBtn) {
      removeBtn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        clearPreview();
      });
    }

    // Drag & Drop.
    zone.addEventListener('dragover', function (e) {
      e.preventDefault();
      zone.classList.add('is-dragging');
    });

    zone.addEventListener('dragleave', function (e) {
      if (!zone.contains(e.relatedTarget)) {
        zone.classList.remove('is-dragging');
      }
    });

    zone.addEventListener('drop', function (e) {
      e.preventDefault();
      zone.classList.remove('is-dragging');
      const file = e.dataTransfer.files[0];
      if (file) {
        // Assign to the file input via DataTransfer.
        try {
          const dt = new DataTransfer();
          dt.items.add(file);
          input.files = dt.files;
        } catch (_) {
          // DataTransfer not supported — just preview.
        }
        showPreview(file);
      }
    });
  }

  /* =========================================================================
     Form Validation UX
     Marks invalid fields inline before submit.
     ========================================================================= */
  function initFormValidation() {
    const forms = document.querySelectorAll('.esc-form');
    if (!forms.length) return;

    forms.forEach(function (form) {
      const inputs = form.querySelectorAll('.esc-form__input, .esc-form__select, .esc-form__textarea');

      inputs.forEach(function (input) {
        input.addEventListener('blur', function () {
          validateField(input);
        });
        input.addEventListener('input', function () {
          if (input.classList.contains('is-invalid')) {
            validateField(input);
          }
        });
      });

      form.addEventListener('submit', function (e) {
        let hasErrors = false;

        inputs.forEach(function (input) {
          if (!validateField(input)) {
            hasErrors = true;
          }
        });

        // Radio groups.
        const radioGroups = form.querySelectorAll('.esc-radio-group');
        radioGroups.forEach(function (group) {
          const checked = group.querySelector('input[type="radio"]:checked');
          const groupWrap = group.closest('.esc-form__group');
          if (!checked && group.closest('.esc-form__group')) {
            groupWrap.classList.add('has-error');
            hasErrors = true;
          } else if (groupWrap) {
            groupWrap.classList.remove('has-error');
          }
        });

        if (hasErrors) {
          e.preventDefault();
          // Scroll to first error.
          const firstError = form.querySelector('.is-invalid, .has-error');
          if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstError.focus && firstError.focus();
          }
          return;
        }

        // Show loading state on submit button.
        const btn = form.querySelector('button[type="submit"]');
        if (btn) {
          btn.classList.add('is-loading');
          btn.disabled = true;
        }
      });
    });
  }

  function validateField(input) {
    const group = input.closest('.esc-form__group');

    if (input.hasAttribute('required') && !input.value.trim()) {
      setInvalid(input, group, getRequiredMessage(input));
      return false;
    }

    if (input.type === 'email' && input.value.trim() && !isValidEmail(input.value.trim())) {
      setInvalid(input, group, 'Please enter a valid email address.');
      return false;
    }

    if (input.type === 'url' && input.value.trim() && !isValidUrl(input.value.trim())) {
      setInvalid(input, group, 'Please enter a valid URL (include https://).');
      return false;
    }

    setValid(input, group);
    return true;
  }

  function setInvalid(input, group, message) {
    input.classList.add('is-invalid');
    input.classList.remove('is-valid');
    input.setAttribute('aria-invalid', 'true');

    if (group) {
      group.classList.add('has-error');
      let err = group.querySelector('.esc-form__error');
      if (!err) {
        err = document.createElement('p');
        err.className = 'esc-form__error';
        err.setAttribute('role', 'alert');
        group.appendChild(err);
      }
      err.textContent = message;
    }
  }

  function setValid(input, group) {
    input.classList.remove('is-invalid');
    input.classList.add('is-valid');
    input.removeAttribute('aria-invalid');

    if (group) {
      group.classList.remove('has-error');
      const err = group.querySelector('.esc-form__error');
      if (err) err.remove();
    }
  }

  function getRequiredMessage(input) {
    const label = input.closest('.esc-form__group')?.querySelector('.esc-form__label');
    const name  = label ? label.textContent.replace('*', '').trim() : 'This field';
    return name + ' is required.';
  }

  function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }

  function isValidUrl(url) {
    try {
      new URL(url);
      return true;
    } catch (_) {
      return false;
    }
  }

  /* =========================================================================
     Inline Error Styles (injected once into <head>)
     ========================================================================= */
  function injectValidationStyles() {
    if (document.getElementById('esc-validation-styles')) return;
    const style = document.createElement('style');
    style.id = 'esc-validation-styles';
    style.textContent = `
      .esc-form__input.is-invalid,
      .esc-form__select.is-invalid,
      .esc-form__textarea.is-invalid {
        border-color: #DC2626 !important;
        background-color: #FFF5F5 !important;
      }
      .esc-form__input.is-valid,
      .esc-form__select.is-valid,
      .esc-form__textarea.is-valid {
        border-color: #059669 !important;
      }
      .esc-form__error {
        color: #DC2626;
        font-size: 0.8125rem;
        margin: 4px 0 0;
        font-weight: 500;
      }
      .has-error .esc-radio-group {
        padding: 8px;
        border: 1.5px solid #DC2626;
        border-radius: 8px;
        background: #FFF5F5;
      }
    `;
    document.head.appendChild(style);
  }

  /* =========================================================================
     Sport Filter — smooth anchor scroll
     ========================================================================= */
  function initFilterBar() {
    const bar = document.querySelector('.esc-filter-bar');
    if (!bar) return;

    // On page load with filter active, scroll the active pill into view within the bar.
    const active = bar.querySelector('.esc-filter-bar__pill.is-active');
    if (active && active.scrollIntoView) {
      active.scrollIntoView({ inline: 'nearest', block: 'nearest' });
    }

    // Allow horizontal scroll on touch.
    bar.addEventListener('wheel', function (e) {
      if (e.deltaY !== 0) {
        e.preventDefault();
        bar.scrollLeft += e.deltaY;
      }
    }, { passive: false });
  }

  /* =========================================================================
     Auto-dismiss notices after 8 seconds
     ========================================================================= */
  function initNotices() {
    const notices = document.querySelectorAll('.esc-notice--success');
    notices.forEach(function (n) {
      setTimeout(function () {
        n.style.transition = 'opacity 0.5s ease';
        n.style.opacity    = '0';
        setTimeout(function () { n.remove(); }, 500);
      }, 8000);
    });
  }

  /* =========================================================================
     Init
     ========================================================================= */
  document.addEventListener('DOMContentLoaded', function () {
    injectValidationStyles();
    initUploadZone();
    initFormValidation();
    initFilterBar();
    initNotices();
  });

}(jQuery));
