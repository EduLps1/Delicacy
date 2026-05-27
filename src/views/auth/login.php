<?php
/**
 * DELICACY - Pagina de Login
 * Variaveis disponiveis via controller: $csrf_token
 */

$message = getSessionMessage();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Delicacy</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/auth.css">
</head>
<body class="auth-page auth-page-login">
    <main class="login-layout">
        <section class="auth-story" aria-label="Delicacy para restaurantes">
            <img class="auth-story-photo" src="<?php echo BASE_URL; ?>/images/auth/login-kitchen.jpeg" alt="">
            <div class="auth-story-shade"></div>
            <div class="auth-story-copy">
                <h1>Gestão que<br>transforma<br>experiências.</h1>
                <span class="auth-accent" aria-hidden="true"></span>
                <p>Ferramentas inteligentes para restaurantes que buscam<br>excelência em cada detalhe.</p>
            </div>
            <div class="auth-story-footer">
                <span class="auth-story-logo" aria-hidden="true">
                    <img src="<?php echo BASE_URL; ?>/images/auth/delicacy-symbol-cropped.png" alt="">
                </span>
                <p>Delicacy. Tecnologia que impulsiona<br>o seu negócio.</p>
            </div>
        </section>

        <section class="auth-panel" aria-labelledby="auth-title">
            <div class="auth-panel-inner">
                <img class="auth-brand-lockup" src="<?php echo BASE_URL; ?>/images/auth/delicacy-lockup.png" alt="Delicacy">

                <header class="auth-heading">
                    <h2 id="auth-title">Bem-vindo de volta!</h2>
                    <p>Entre na sua conta para continuar.</p>
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
                            placeholder="Seu Email"
                        >
                    </div>

                    <div class="auth-field">
                        <div class="auth-field-row">
                            <label for="password">Senha</label>
                            <a class="auth-link" href="<?php echo BASE_URL; ?>/forgot-password.php">Esqueceu a senha?</a>
                        </div>
                        <div class="auth-password">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••"
                            >
                            <button class="auth-password-toggle" type="button" data-password-toggle="password" aria-label="Mostrar senha" aria-pressed="false">
                                <svg class="eye-open" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.6-6 10-6 10 6 10 6-3.6 6-10 6-10-6-10-6Z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg class="eye-closed" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3l18 18"/><path d="M10.6 6.15A11.7 11.7 0 0 1 12 6c6.4 0 10 6 10 6a16.8 16.8 0 0 1-3.25 3.68"/><path d="M6.2 6.2C3.6 8.05 2 12 2 12s3.6 6 10 6c1.6 0 3-.38 4.25-.98"/><path d="M9.88 9.88a3 3 0 0 0 4.24 4.24"/></svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="auth-submit">Entrar</button>
                </form>

                <div class="auth-divider"><span>OU</span></div>
                <button class="auth-social" type="button" aria-disabled="true">
                    <span aria-hidden="true">G</span>
                    Entrar com Google
                </button>

                <footer class="auth-footer">
                    <p>Não tem conta? <a href="<?php echo BASE_URL; ?>/register.php">Cadastre-se</a></p>
                </footer>
            </div>
        </section>
    </main>
    <script src="<?php echo BASE_URL; ?>/js/auth.js"></script>
</body>
</html>
