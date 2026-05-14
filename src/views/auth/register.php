<?php
/**
 * DELICACY - Pagina de Registro
 */

$pageTitle = 'Cadastro';
$message = getSessionMessage();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Delicacy</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css">
</head>
<body class="auth-page">
    <main class="auth-card auth-card-lg" aria-labelledby="auth-title">
        <header class="auth-brand">
            <span class="auth-logo" aria-hidden="true">
                <svg viewBox="0 0 24 24">
                    <path d="M7 3v7" />
                    <path d="M10 3v7" />
                    <path d="M7 7h3" />
                    <path d="M8.5 10v11" />
                    <path d="M14 3v8" />
                    <path d="M18 4c-2.2 1.6-3.4 3.9-3.4 6.8" />
                    <path d="M14 11l6 10" />
                    <path d="M4 20l16-16" />
                </svg>
            </span>
            <h1 id="auth-title" class="auth-title">Criar conta</h1>
            <p class="auth-subtitle">Junte-se à Delicacy</p>
        </header>

        <?php if ($message): ?>
            <div class="auth-message message-<?php echo htmlspecialchars($message['type']); ?>">
                <?php echo htmlspecialchars($message['text']); ?>
            </div>
        <?php endif; ?>

        <form class="auth-form" method="POST" action="<?php echo BASE_URL; ?>/register.php">
            <input type="hidden" name="action" value="register">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

            <div class="auth-field">
                <label for="name">Nome completo</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="João da Silva"
                >
            </div>

            <div class="auth-field">
                <label for="email">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    required
                    autocomplete="email"
                    placeholder="voce@email.com"
                >
            </div>

            <div class="auth-field">
                <label for="password">Senha</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    autocomplete="new-password"
                    placeholder="••••••••"
                    minlength="6"
                >
            </div>

            <div class="auth-requirements" aria-label="Requisitos da senha">
                <ul>
                    <li>Mínimo 8 caracteres</li>
                    <li>Uma letra maiúscula</li>
                    <li>Uma letra minúscula</li>
                    <li>Um número</li>
                </ul>
            </div>

            <div class="auth-field">
                <label for="confirm_password">Confirmar senha</label>
                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    required
                    autocomplete="new-password"
                    placeholder="••••••••"
                    minlength="6"
                >
            </div>

            <button type="submit" class="auth-submit">Criar conta</button>
        </form>

        <footer class="auth-footer">
            <p>Já tem conta? <a href="<?php echo BASE_URL; ?>/login.php">Entrar</a></p>
        </footer>
    </main>

    <script>
        const form = document.querySelector('form');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');

        form.addEventListener('submit', function(event) {
            if (passwordInput.value !== confirmPasswordInput.value) {
                event.preventDefault();
                alert('As senhas nao conferem.');
            }
        });
    </script>
</body>
</html>
