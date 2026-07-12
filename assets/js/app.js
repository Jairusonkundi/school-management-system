document.addEventListener('submit', function (event) {
    const form = event.target;
    const amount = form.querySelector('[data-max-balance]');
    if (!amount) {
        return;
    }
    const value = Number(amount.value);
    const max = Number(amount.dataset.maxBalance);
    if (value <= 0 || value > max) {
        event.preventDefault();
        amount.classList.add('is-invalid');
    }
});

document.addEventListener('click', function (event) {
    const toggle = event.target.closest('[data-sidebar-toggle]');
    if (toggle) {
        document.body.classList.toggle('sidebar-open');
    }

    const passwordToggle = event.target.closest('[data-password-toggle]');
    if (passwordToggle) {
        const selector = passwordToggle.getAttribute('data-password-toggle');
        const input = document.querySelector(selector);
        if (input) {
            input.type = input.type === 'password' ? 'text' : 'password';
            passwordToggle.textContent = input.type === 'password' ? 'Show' : 'Hide';
        }
    }
});

document.addEventListener('change', function (event) {
    if (event.target.name === 'password_mode' || event.target.name === 'reset_password_mode') {
        const form = event.target.closest('form');
        const manual = form ? form.querySelector('[data-manual-password-fields]') : null;
        if (manual) {
            manual.classList.toggle('d-none', event.target.value !== 'manual');
        }
    }
});
