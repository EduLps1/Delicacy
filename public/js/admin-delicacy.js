(function () {
    var page = document.querySelector('.platform-admin');
    var toggle = document.querySelector('[data-admin-theme-toggle]');
    var label = document.querySelector('[data-admin-theme-label]');
    var glyph = document.querySelector('[data-admin-theme-glyph]');
    var themeKey = 'delicacy-admin-theme';

    if (!page || !toggle || !label) {
        return;
    }

    function setTheme(theme) {
        page.dataset.theme = theme;
        label.textContent = theme === 'dark' ? 'Dark Mode' : 'Light Mode';
        if (glyph) {
            glyph.textContent = theme === 'dark' ? '\u263e' : '\u263c';
        }
        toggle.setAttribute('aria-label', theme === 'dark' ? 'Ativar modo claro' : 'Ativar modo escuro');
        toggle.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');
        window.localStorage.setItem(themeKey, theme);
        page.dispatchEvent(new CustomEvent('admin-theme-change', { detail: { theme: theme } }));
    }

    var savedTheme = window.localStorage.getItem(themeKey);
    var defaultTheme = page.dataset.defaultTheme === 'dark' ? 'dark' : 'light';
    setTheme(savedTheme === 'dark' || savedTheme === 'light' ? savedTheme : defaultTheme);

    toggle.addEventListener('click', function () {
        setTheme(page.dataset.theme === 'dark' ? 'light' : 'dark');
    });

    var userMenus = document.querySelectorAll('.ad-user-menu');
    if (userMenus.length) {
        document.addEventListener('click', function (event) {
            userMenus.forEach(function (menu) {
                if (!menu.contains(event.target)) {
                    menu.open = false;
                }
            });
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                userMenus.forEach(function (menu) {
                    menu.open = false;
                });
            }
        });
    }
}());
