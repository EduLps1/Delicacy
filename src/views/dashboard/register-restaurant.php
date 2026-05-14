<?php
/**
 * DELICACY - Cadastro de Restaurante
 * Variaveis: $csrf_token
 */
$message = getSessionMessage();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Restaurante - Delicacy</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="header-logo">
                <a href="<?php echo BASE_URL; ?>" class="logo"><h1>Delicacy</h1></a>
            </div>
            <nav class="header-nav">
                <span class="user-info">Ola, <strong><?php echo htmlspecialchars(getAuthUser()['name']); ?></strong></span>
                <a href="<?php echo BASE_URL; ?>/logout.php" class="btn-logout">Sair</a>
            </nav>
        </div>
    </header>

    <div class="layout-with-sidebar">
        <aside class="sidebar">
            <nav class="sidebar-nav">
                <h3 class="nav-title">Restaurante</h3>
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>/admin-contratante/" class="nav-link">Dashboard</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin-contratante/cardapio.php" class="nav-link">Cardapio</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin-contratante/pedidos.php" class="nav-link">Pedidos</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <div class="dashboard">
                <div class="dashboard-header">
                    <h1>Cadastrar Restaurante</h1>
                    <p>Informe os dados iniciais para ativar o painel do restaurante.</p>
                </div>

                <?php if ($message): ?>
                    <div class="message-container">
                        <div class="message message-<?php echo htmlspecialchars($message['type']); ?>">
                            <span><?php echo htmlspecialchars($message['text']); ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <form method="POST" action="<?php echo BASE_URL; ?>/admin-contratante/cadastrar-restaurante.php">
                        <input type="hidden" name="action" value="register">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                        <div class="form-group">
                            <label for="name">Nome do restaurante *</label>
                            <input type="text" id="name" name="name" required>
                        </div>

                        <div class="form-group">
                            <label for="description">Descricao</label>
                            <textarea id="description" name="description"></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="cnpj">CNPJ *</label>
                                <input type="text" id="cnpj" name="cnpj" required placeholder="Somente numeros ou formatado">
                            </div>
                            <div class="form-group">
                                <label for="phone">Telefone</label>
                                <input type="text" id="phone" name="phone" placeholder="DDD + numero">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">Email do restaurante</label>
                                <input type="email" id="email" name="email">
                            </div>
                            <div class="form-group">
                                <label for="plan_type">Plano</label>
                                <select id="plan_type" name="plan_type">
                                    <option value="<?php echo PLAN_BASIC; ?>">Basico</option>
                                    <option value="<?php echo PLAN_PREMIUM; ?>">Premium</option>
                                    <option value="<?php echo PLAN_CUSTOM; ?>">Customizado</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="commission_type">Modelo de comissao</label>
                            <select id="commission_type" name="commission_type">
                                <option value="<?php echo COMMISSION_HYBRID; ?>">Plano + comissao</option>
                                <option value="<?php echo COMMISSION_ONLY; ?>">Comissao pura</option>
                                <option value="<?php echo COMMISSION_PLAN_ONLY; ?>">Somente plano</option>
                            </select>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Cadastrar Restaurante</button>
                            <a href="<?php echo BASE_URL; ?>/admin-contratante/" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
