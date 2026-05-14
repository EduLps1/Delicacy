<?php
/**
 * DELICACY - Pagina de Login
 * Variaveis disponiveis via controller: $csrf_token
 */

$pageTitle = 'Login';
$message = getSessionMessage();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Delicacy</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css">
</head>
<body class="auth-page">
    <main class="auth-card" aria-labelledby="auth-title">
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
            <h1 id="auth-title" class="auth-title">Delicacy</h1>
            <p class="auth-subtitle">Entre na sua conta</p>
        </header>

        <?php if ($message): ?>
            <div class="auth-message message-<?php echo htmlspecialchars($message['type']); ?>">
                <?php echo htmlspecialchars($message['text']); ?>
            </div>
        <?php endif; ?>

        <form class="auth-form" method="POST" action="<?php echo BASE_URL; ?>/login.php">
            <input type="hidden" name="action" value="login">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

            <div class="auth-field">
                <label for="email">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    required
                    autofocus
                    autocomplete="email"
                    placeholder="voce@email.com"
                >
            </div>

            <div class="auth-field">
                <div class="auth-field-row">
                    <label for="password">Senha</label>
                    <a class="auth-link" href="<?php echo BASE_URL; ?>/forgot-password.php">Esqueceu a senha?</a>
                </div>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                >
            </div>

            <button type="submit" class="auth-submit">Entrar</button>
        </form>

        <footer class="auth-footer">
            <p>Não tem conta? <a href="<?php echo BASE_URL; ?>/register.php">Cadastre-se</a></p>
        </footer>
    </main>
</body>
</html>
