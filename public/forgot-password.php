<?php
/**
 * DELICACY - Recuperacao de Senha
 */

require_once __DIR__ . '/../config/config.php';

$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!validateCSRFToken($csrf_token)) {
        $message = [
            'type' => 'error',
            'text' => 'Token de seguranca invalido. Atualize a pagina e tente novamente.'
        ];
    } else {
        $email = sanitizeEmail($_POST['email'] ?? '');

        if (!validateEmail($email)) {
            $message = [
                'type' => 'error',
                'text' => 'Informe um email valido.'
            ];
        } else {
            $message = [
                'type' => 'success',
                'text' => 'Se este email estiver cadastrado, enviaremos um link de redefinicao.'
            ];
        }
    }
}

$csrf_token = generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar senha - Delicacy</title>
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
            <h1 id="auth-title" class="auth-title">Recuperar senha</h1>
            <p class="auth-subtitle">Informe seu email para receber o link de redefinição</p>
        </header>

        <?php if ($message): ?>
            <div class="auth-message message-<?php echo htmlspecialchars($message['type']); ?>">
                <?php echo htmlspecialchars($message['text']); ?>
            </div>
        <?php endif; ?>

        <form class="auth-form" method="POST" action="<?php echo BASE_URL; ?>/forgot-password.php">
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

            <button type="submit" class="auth-submit">Enviar link</button>
        </form>

        <footer class="auth-footer">
            <a class="auth-back" href="<?php echo BASE_URL; ?>/login.php">← Voltar para login</a>
        </footer>
    </main>
</body>
</html>
