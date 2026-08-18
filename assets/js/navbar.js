document.addEventListener('DOMContentLoaded', function () {
    const header = document.querySelector('.site-header');

    if (!header) {
        return;
    }

    let previousScrollPosition = window.scrollY;

    window.addEventListener(
        'scroll',
        function () {
            const currentScrollPosition = window.scrollY;
            const difference =
                currentScrollPosition - previousScrollPosition;

            if (currentScrollPosition <= 10) {
                header.classList.remove('nav-hidden');
            } else if (difference > 4) {
                header.classList.add('nav-hidden');
            } else if (difference < -4) {
                header.classList.remove('nav-hidden');
            }

            previousScrollPosition = currentScrollPosition;
        },
        { passive: true }
    );
});