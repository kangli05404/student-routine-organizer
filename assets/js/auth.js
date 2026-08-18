document
    .querySelectorAll('[data-password-toggle]')
    .forEach(function (button) {
        button.addEventListener('click', function () {
            const input = document.getElementById(
                button.dataset.passwordToggle
            );

            if (!input) {
                return;
            }

            const showPassword =
                input.type === 'password';

            input.type = showPassword
                ? 'text'
                : 'password';

            button.classList.toggle(
                'is-visible',
                showPassword
            );

            button.setAttribute(
                'aria-pressed',
                showPassword ? 'true' : 'false'
            );

            button.setAttribute(
                'aria-label',
                showPassword
                    ? 'Hide password'
                    : 'Show password'
            );
        });
    });