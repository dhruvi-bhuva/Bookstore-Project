/**
 * BookVerse - Online Bookstore Frontend Interactions
 */

document.addEventListener('DOMContentLoaded', () => {
  // 1. Mobile Navigation Toggle
  const navToggle = document.querySelector('.mobile-nav-toggle');
  const navLinks = document.querySelector('.nav-links');
  if (navToggle && navLinks) {
    navToggle.addEventListener('click', () => {
      navLinks.classList.toggle('open');
      const icon = navToggle.querySelector('i');
      if (icon) {
        icon.classList.toggle('fa-bars');
        icon.classList.toggle('fa-xmark');
      }
    });
  }

  // 2. User Account Dropdown
  const userBtn = document.querySelector('.user-menu-btn');
  const userDropdown = document.querySelector('.user-dropdown');
  if (userBtn && userDropdown) {
    userBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      userDropdown.classList.toggle('show');
    });

    document.addEventListener('click', (e) => {
      if (!userDropdown.contains(e.target) && !userBtn.contains(e.target)) {
        userDropdown.classList.remove('show');
      }
    });
  }

  // 3. Flash Message Auto-dismiss & Close Button
  const alertCloses = document.querySelectorAll('.alert-close');
  alertCloses.forEach(btn => {
    btn.addEventListener('click', () => {
      const alert = btn.closest('.alert');
      if (alert) {
        alert.style.opacity = '0';
        alert.style.transform = 'translateY(-10px)';
        alert.style.transition = 'all 0.3s ease';
        setTimeout(() => alert.remove(), 300);
      }
    });
  });

  // Auto-dismiss after 6 seconds
  setTimeout(() => {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
      alert.style.opacity = '0';
      alert.style.transition = 'opacity 0.5s ease';
      setTimeout(() => alert.remove(), 500);
    });
  }, 6000);

  // 4. Quantity Adjuster
  document.querySelectorAll('.qty-selector').forEach(wrap => {
    const minusBtn = wrap.querySelector('.qty-minus');
    const plusBtn = wrap.querySelector('.qty-plus');
    const input = wrap.querySelector('.qty-input');
    const max = parseInt(input.getAttribute('max')) || 99;
    const min = parseInt(input.getAttribute('min')) || 1;

    if (minusBtn && input) {
      minusBtn.addEventListener('click', () => {
        let val = parseInt(input.value) || 1;
        if (val > min) {
          input.value = val - 1;
          input.dispatchEvent(new Event('change'));
        }
      });
    }

    if (plusBtn && input) {
      plusBtn.addEventListener('click', () => {
        let val = parseInt(input.value) || 1;
        if (val < max) {
          input.value = val + 1;
          input.dispatchEvent(new Event('change'));
        }
      });
    }
  });

  // 5. Confirm deletion prompts
  document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', (e) => {
      const msg = el.getAttribute('data-confirm') || 'Are you sure you want to proceed?';
      if (!confirm(msg)) {
        e.preventDefault();
      }
    });
  });

  // 6. Quick Demo Account Fillers for easy testing
  window.fillDemoAccount = function(type) {
    const emailField = document.querySelector('input[name="email"]');
    const passField = document.querySelector('input[name="password"]');
    if (!emailField || !passField) return;

    if (type === 'admin') {
      emailField.value = 'admin@bookstore.com';
      passField.value = 'admin123';
    } else if (type === 'user') {
      emailField.value = 'john@example.com';
      passField.value = 'user123';
    }
  };
});
