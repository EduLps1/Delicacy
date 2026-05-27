(function () {
    document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
        var input = document.getElementById(button.dataset.passwordToggle);

        if (!input) {
            return;
        }

        button.addEventListener('click', function () {
            var showPassword = input.type === 'password';
            input.type = showPassword ? 'text' : 'password';
            button.setAttribute('aria-pressed', showPassword ? 'true' : 'false');
            button.setAttribute('aria-label', showPassword ? 'Ocultar senha' : 'Mostrar senha');
        });
    });

    var password = document.getElementById('password');
    var confirmation = document.getElementById('confirm_password');
    var form = confirmation ? confirmation.form : null;
    var rules = {
        length: function (value) { return value.length >= 8; },
        letters: function (value) { return /[A-Z]/.test(value) && /[a-z]/.test(value); },
        number: function (value) { return /\d/.test(value); }
    };

    function updateRules() {
        if (!password) {
            return;
        }

        Object.keys(rules).forEach(function (key) {
            var item = document.querySelector('[data-password-rule="' + key + '"]');
            if (item) {
                item.classList.toggle('valid', rules[key](password.value));
            }
        });
    }

    if (password && confirmation && form) {
        password.addEventListener('input', updateRules);

        form.addEventListener('submit', function (event) {
            if (password.value !== confirmation.value) {
                event.preventDefault();
                confirmation.setCustomValidity('As senhas não conferem.');
                confirmation.reportValidity();
                return;
            }

            confirmation.setCustomValidity('');
        });

        confirmation.addEventListener('input', function () {
            confirmation.setCustomValidity('');
        });
    }
}());
