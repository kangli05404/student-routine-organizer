document.addEventListener('DOMContentLoaded', function () {
    /*
    |--------------------------------------------------------------------------
    | Profile Tabs
    |--------------------------------------------------------------------------
    */

    const tabs = document.querySelectorAll(
        '[data-profile-tab]'
    );

    const panels = document.querySelectorAll(
        '[data-profile-panel]'
    );

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            const selectedPanel =
                tab.dataset.profileTab;

            tabs.forEach(function (currentTab) {
                const isSelected =
                    currentTab === tab;

                currentTab.classList.toggle(
                    'active',
                    isSelected
                );

                currentTab.setAttribute(
                    'aria-selected',
                    isSelected ? 'true' : 'false'
                );
            });

            panels.forEach(function (panel) {
                const shouldShow =
                    panel.dataset.profilePanel
                    === selectedPanel;

                panel.classList.toggle(
                    'active',
                    shouldShow
                );
            });
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Password Visibility
    |--------------------------------------------------------------------------
    */

    const passwordButtons =
        document.querySelectorAll(
            '[data-password-toggle]'
        );

    passwordButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const inputId =
                button.dataset.passwordToggle;

            const passwordInput =
                document.getElementById(inputId);

            if (!passwordInput) {
                return;
            }

            const showPassword =
                passwordInput.type === 'password';

            passwordInput.type =
                showPassword
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
});