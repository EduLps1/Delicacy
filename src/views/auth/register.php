<?php
/**
 * DELICACY - Pagina de Cadastro
 * Variaveis disponiveis via controller: $csrf_token
 */

$message = getSessionMessage();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar conta - Delicacy</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/auth.css">
</head>
<body class="auth-page auth-page-register">
    <main class="register-layout" aria-labelledby="auth-title">
        <img class="auth-brand-lockup register-brand" src="<?php echo BASE_URL; ?>/images/auth/delicacy-lockup.png" alt="Delicacy">

        <header class="auth-heading register-heading">
            <h1 id="auth-title">Criar conta</h1>
            <p>Comece a organizar sua operação com padrão Delicacy.</p>
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
                <input type="text" id="name" name="name" required autofocus autocomplete="name" placeholder="Eduardo Lopes">
            </div>

            <div class="auth-field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required autocomplete="email" placeholder="Seu Email">
            </div>

            <div class="auth-field">
                <label for="password">Senha</label>
                <div class="auth-password">
                    <input type="password" id="password" name="password" required autocomplete="new-password" placeholder="••••••••" minlength="8">
                    <button class="auth-password-toggle" type="button" data-password-toggle="password" aria-label="Mostrar senha" aria-pressed="false">
                        <svg class="eye-open" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.6-6 10-6 10 6 10 6-3.6 6-10 6-10-6-10-6Z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="eye-closed" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3l18 18"/><path d="M10.6 6.15A11.7 11.7 0 0 1 12 6c6.4 0 10 6 10 6a16.8 16.8 0 0 1-3.25 3.68"/><path d="M6.2 6.2C3.6 8.05 2 12 2 12s3.6 6 10 6c1.6 0 3-.38 4.25-.98"/><path d="M9.88 9.88a3 3 0 0 0 4.24 4.24"/></svg>
                    </button>
                </div>
            </div>

            <ul class="auth-requirements" aria-label="Requisitos da senha">
                <li data-password-rule="length">Mínimo de 8 caracteres</li>
                <li data-password-rule="letters">Uma letra maiúscula e uma minúscula</li>
                <li data-password-rule="number">Pelo menos um número</li>
            </ul>

            <div class="auth-field">
                <label for="confirm_password">Confirmar senha</label>
                <div class="auth-password">
                    <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password" placeholder="••••••••" minlength="8">
                    <button class="auth-password-toggle" type="button" data-password-toggle="confirm_password" aria-label="Mostrar senha" aria-pressed="false">
                        <svg class="eye-open" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.6-6 10-6 10 6 10 6-3.6 6-10 6-10-6-10-6Z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="eye-closed" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3l18 18"/><path d="M10.6 6.15A11.7 11.7 0 0 1 12 6c6.4 0 10 6 10 6a16.8 16.8 0 0 1-3.25 3.68"/><path d="M6.2 6.2C3.6 8.05 2 12 2 12s3.6 6 10 6c1.6 0 3-.38 4.25-.98"/><path d="M9.88 9.88a3 3 0 0 0 4.24 4.24"/></svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="auth-submit">Criar conta</button>
        </form>

        <footer class="auth-footer">
            <p>Já tem conta? <a href="<?php echo BASE_URL; ?>/login.php">Entrar</a></p>
        </footer>
    </main>
    <script src="<?php echo BASE_URL; ?>/js/auth.js"></script>
</body>
</html>
