<?php
/**
 * DELICACY - Landing page
 */
$documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '';
$publicRoot = realpath(PUBLIC_PATH) ?: '';
$isPublicDocumentRoot = str_replace('\\', '/', $documentRoot) === str_replace('\\', '/', $publicRoot);
$publicBaseUrl = rtrim(BASE_URL . ($isPublicDocumentRoot ? '' : '/public'), '/');

$plans = [
    ['name' => 'Starter', 'price' => 'R$ 97', 'description' => 'Para estruturar seu canal próprio e vender com mais autonomia.', 'items' => ['Cardápio digital responsivo', 'Pedidos via QR Code', 'Gestão básica de pedidos', 'Relatórios essenciais']],
    ['name' => 'Growth', 'price' => 'R$ 197', 'description' => 'Para operações que querem automatizar atendimento e acelerar vendas.', 'items' => ['Tudo do Starter', 'WhatsApp automatizado', 'CRM e fidelidade', 'Métricas avançadas'], 'featured' => true],
    ['name' => 'Scale', 'price' => 'Sob consulta', 'description' => 'Para operações robustas, multiunidade ou com integrações especiais.', 'items' => ['Gestão avançada', 'Integrações externas', 'Onboarding assistido', 'Suporte prioritário']],
];

$featureTabs = [
    'atendimento' => [
        'title' => 'Atendimento Automático',
        'subtitle' => 'Venda sem deixar ninguém esperando',
        'icon' => 'message',
        'items' => [
            ['ChatBot automatizado', 'Robozinho de WhatsApp com Inteligência Artificial para atendimento humanizado de delivery.', 'bot'],
            ['Cardápio Digital (Mesa/Balcão/Delivery)', 'Um site rápido para receber pedidos online de delivery, mesas e balcão.', 'menu'],
            ['PDV integrado dentro do WhatsApp', 'Atenda clientes e adicione pedidos ao sistema sem sair do próprio WhatsApp.', 'phone'],
            ['Cardápio rápido e fácil de usar', 'Seu cliente pede sem criar conta, baixar aplicativo ou enfrentar etapas desnecessárias.', 'speed'],
            ['Pagamentos online (PIX/Cartão)', 'Receba pagamentos direto pelo cardápio, com cartão ou PIX.', 'card'],
            ['Agendamento de pedidos', 'Permita que o cliente escolha a melhor data e horário para receber o pedido.', 'calendar'],
        ],
    ],
    'vendas' => [
        'title' => 'Vendas',
        'subtitle' => 'Transforme relacionamento em receita',
        'icon' => 'trend',
        'items' => [
            ['Mensagens em massa no WhatsApp', 'Envie campanhas personalizadas para aumentar vendas e faturamento de forma previsível.', 'send'],
            ['Integração com ferramentas de anúncio', 'Conecte Meta Ads, Google Ads, Google Analytics, GTM e outras ferramentas.', 'target'],
            ['Programa de fidelidade com pontuação', 'Permita que clientes acumulem pontos e troquem por benefícios relevantes.', 'award'],
            ['Cupons e descontos para o cliente', 'Crie ofertas personalizadas para atrair clientes de forma estratégica e persuasiva.', 'ticket'],
            ['Filtros avançados de clientes', 'Identifique quem mais pede, recência de compra e oportunidades de reativação.', 'filter'],
            ['Relatórios de vendas e conversões', 'Analise conversão do cardápio, ticket médio e indicadores que orientam decisões.', 'chart'],
        ],
    ],
    'gestao' => [
        'title' => 'Sistema de Gestão',
        'subtitle' => 'Controle completo da operação',
        'icon' => 'grid',
        'items' => [
            ['PDV (Sistema de Ponto de Venda)', 'Registre e receba pedidos de forma profissional, organizada e centralizada.', 'monitor'],
            ['Controle de Caixa com histórico', 'Controle vendas, suprimentos, sangrias e movimentações financeiras.', 'wallet'],
            ['Emissão e gestão de Nota Fiscal', 'Emita notas fiscais integradas ao sistema de gestão e cardápio digital.', 'document'],
            ['Integração com iFood e Entrega Fácil', 'Receba pedidos do iFood no gestor e acione entregadores parceiros.', 'delivery'],
            ['KDS (Kitchen Display System)', 'Organize a produção em tempo real e substitua comandas impressas por prioridades claras.', 'kitchen'],
            ['Gestão de mesas e comandas', 'Acompanhe mesas abertas, fechadas e em pagamento em tempo real.', 'table'],
            ['Gestão de avaliações', 'Receba feedbacks contínuos e acompanhe a qualidade do serviço automaticamente.', 'star'],
            ['Dashboards de gestão', 'Visualize gráficos e tabelas para decisões mais rápidas e assertivas.', 'dashboard'],
            ['Histórico de pedidos', 'Consulte o histórico completo de pedidos delivery e presenciais ao longo dos anos.', 'history'],
        ],
    ],
];

$faqs = [
    ['O que é a Delicacy?', 'A Delicacy é a solução de tecnologia certa para seu restaurante crescer. Integra pedidos, cardápio digital personalizável, operação e campanhas de venda em uma plataforma que acompanha cada etapa, do pedido ao pagamento.'],
    ['A Delicacy é para restaurantes de todos os portes?', 'Sim. Oferecemos planos adaptados à realidade de diferentes negócios. O essencial é melhorar o atendimento, organizar a operação e criar uma base sólida para crescer, independentemente do tamanho atual.'],
    ['Quais são as vantagens de um cardápio digital?', 'A principal vantagem é oferecer uma experiência ágil e agradável. Clientes pedem com autonomia, a equipe reduz erros e desperdícios, e a interface pode sugerir itens adicionais estrategicamente para aumentar o ticket médio.'],
];

function landingIcon($name)
{
    $icons = [
        'arrow' => '<path d="M5 12h14"/><path d="m13 6 6 6-6 6"/>',
        'check' => '<path d="m5 12 4 4L19 6"/>',
        'message' => '<path d="M21 15a4 4 0 0 1-4 4H8l-5 3v-7a4 4 0 0 1-1-2.6V7a4 4 0 0 1 4-4h11a4 4 0 0 1 4 4z"/>',
        'trend' => '<path d="m3 17 6-6 4 4 8-9"/><path d="M15 6h6v6"/>',
        'grid' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
        'bot' => '<rect x="4" y="7" width="16" height="12" rx="3"/><path d="M12 3v4M8 12h.01M16 12h.01M8 16h8"/>',
        'menu' => '<path d="M5 4h14v16H5zM8 8h8M8 12h8M8 16h5"/>',
        'phone' => '<rect x="7" y="2" width="10" height="20" rx="2"/><path d="M11 18h2"/>',
        'speed' => '<path d="M5 16a7 7 0 1 1 14 0M12 12l4-4M4 20h16"/>',
        'card' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18M7 15h3"/>',
        'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18M8 14h.01M12 14h.01M16 14h.01"/>',
        'send' => '<path d="m22 2-7 20-4-9-9-4zM22 2 11 13"/>',
        'target' => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1"/>',
        'award' => '<circle cx="12" cy="8" r="5"/><path d="M8 12 6 22l6-3 6 3-2-10"/>',
        'ticket' => '<path d="M3 8a3 3 0 0 0 0 6v3h18v-3a3 3 0 0 0 0-6V5H3z"/><path d="M13 5v12"/>',
        'filter' => '<path d="M4 5h16M7 12h10M10 19h4"/>',
        'chart' => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
        'monitor' => '<rect x="3" y="4" width="18" height="13" rx="2"/><path d="M8 21h8M12 17v4"/>',
        'wallet' => '<path d="M4 6h14a2 2 0 0 1 2 2v11H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h13"/><path d="M16 12h6v4h-6z"/>',
        'document' => '<path d="M6 2h9l5 5v15H6zM14 2v6h6M9 13h8M9 17h8"/>',
        'delivery' => '<path d="M3 6h11v10H3zM14 10h4l3 3v3h-7z"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/>',
        'kitchen' => '<path d="M4 3v8a3 3 0 0 0 6 0V3M7 3v18M15 3v18M15 3c4 2 5 8 0 11"/>',
        'table' => '<path d="M4 10h16M6 10v10M18 10v10M7 4h10v6H7z"/>',
        'star' => '<path d="m12 2 3 6 7 .9-5 4.8 1.2 7-6.2-3.3-6.2 3.3 1.2-7-5-4.8L9 8z"/>',
        'dashboard' => '<path d="M4 13a8 8 0 1 1 16 0M12 13l4-4M5 20h14"/>',
        'history' => '<path d="M3 12a9 9 0 1 0 3-6.7L3 8M3 3v5h5M12 7v6l4 2"/>',
    ];
    return '<svg viewBox="0 0 24 24" aria-hidden="true">' . ($icons[$name] ?? $icons['check']) . '</svg>';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Delicacy integra atendimento, pedidos, cozinha, vendas e gestão financeira em uma plataforma para restaurantes.">
    <title>Delicacy | O fluxo perfeito para seu restaurante</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Cormorant+Garamond:wght@600;700&family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $publicBaseUrl; ?>/css/landing.css">
</head>
<body class="landing-page">
    <div class="landing-curtain">
        <header class="landing-header" id="landingHeader">
            <nav class="landing-nav" aria-label="Navegação principal">
                <a class="landing-brand" href="<?php echo $publicBaseUrl; ?>/" aria-label="Delicacy">
                    <img src="<?php echo $publicBaseUrl; ?>/images/auth/delicacy-symbol-cropped.png" alt="">
                    <span>Delicacy</span>
                </a>
                <div class="landing-nav-links"><a href="#plataforma">Plataforma</a><a href="#funcionalidades">Funcionalidades</a><a href="#planos">Planos</a><a href="#duvidas">Dúvidas</a></div>
                <a class="button button-primary" href="<?php echo $publicBaseUrl; ?>/register.php">Começar agora <?php echo landingIcon('arrow'); ?></a>
            </nav>
        </header>

        <main>
            <section class="hero section-shell" aria-labelledby="hero-title">
                <table class="split-table" role="presentation"><tr>
                    <td class="split-copy">
                        <h1 id="hero-title"><span class="title-line">O Fluxo Perfeito:</span><span class="title-line">Do pedido na mesa</span><span class="title-line">Ao fechamento do dia.</span></h1>
                        <p class="lead">Conecte salão, cozinha, delivery e financeiro em uma única operação fluida, previsível e pronta para crescer.</p>
                        <ul class="hero-checks">
                            <li><?php echo landingIcon('check'); ?><span>PDV ultrarrápido, confiável e preparado para operar offline</span></li>
                            <li><?php echo landingIcon('check'); ?><span>Controle rígido da operação e DRE financeiro em tempo real</span></li>
                            <li><?php echo landingIcon('check'); ?><span>QR Code, delivery, CRM e fidelidade totalmente integrados</span></li>
                        </ul>
                        <div class="button-row"><a class="button button-primary" href="<?php echo $publicBaseUrl; ?>/register.php">Organizar minha operação <?php echo landingIcon('arrow'); ?></a><a class="button button-secondary" href="#funcionalidades">Explorar recursos</a></div>
                    </td>
                    <td class="split-media"><figure class="hero-figure"><img src="<?php echo $publicBaseUrl; ?>/images/landing/delicacy-hero-premium.png" alt="Laptop e smartphone exibindo o sistema Delicacy em um restaurante sofisticado"><figcaption><strong>Visão unificada</strong><span>Um sistema, todos os canais.</span></figcaption></figure></td>
                </tr></table>
            </section>

            <section id="plataforma" class="command-section">
                <div class="section-shell">
                    <table class="split-table command-table" role="presentation"><tr>
                        <td class="split-copy">
                            <h2><span class="title-line">A Central de Comando</span><span class="title-line">do seu Ecossistema Gastronômico</span></h2>
                            <p class="lead">A Delicacy integra salão, cozinha e delivery em uma plataforma inteligente, projetada especificamente para o ritmo do seu food service.</p>
                        </td>
                        <td class="command-list">
                            <div class="command-grid">
                                <article><?php echo landingIcon('table'); ?><div><h3>Salão mais produtivo</h3><p>Mesas, comandas e equipe sincronizadas para atender mais, com menos espera e retrabalho.</p></div></article>
                                <article><?php echo landingIcon('speed'); ?><div><h3>Pedidos sem atrito</h3><p>Digital, balcão e delivery entram na mesma fila operacional, com prioridade e contexto.</p></div></article>
                                <article><?php echo landingIcon('chart'); ?><div><h3>Financeiro visível</h3><p>Caixa, vendas, ticket médio e indicadores essenciais disponíveis em tempo real.</p></div></article>
                                <article><?php echo landingIcon('award'); ?><div><h3>Integração que fideliza</h3><p>Centralize canais, dados de clientes, campanhas e recompensas em uma jornada contínua.</p></div></article>
                            </div>
                        </td>
                    </tr></table>
                </div>
            </section>

            <section id="funcionalidades" class="features-section section-shell" aria-labelledby="features-title">
                <div class="section-heading centered"><span class="eyebrow">Funcionalidades</span><h2 id="features-title">Conheça as funcionalidades da Delicacy</h2><p>Tudo que sua operação precisa, integrado.</p></div>
                <div class="feature-tabs" role="tablist" aria-label="Categorias de funcionalidades">
                    <?php foreach ($featureTabs as $key => $tab): ?>
                        <button type="button" class="feature-tab <?php echo $key === 'atendimento' ? 'is-active' : ''; ?>" role="tab" aria-selected="<?php echo $key === 'atendimento' ? 'true' : 'false'; ?>" data-tab="<?php echo $key; ?>"><?php echo landingIcon($tab['icon']); ?><span><strong><?php echo htmlspecialchars($tab['title']); ?></strong><small><?php echo htmlspecialchars($tab['subtitle']); ?></small></span></button>
                    <?php endforeach; ?>
                </div>
                <div class="feature-panels">
                    <?php foreach ($featureTabs as $key => $tab): ?>
                        <div class="feature-panel <?php echo $key === 'atendimento' ? 'is-active' : ''; ?>" data-panel="<?php echo $key; ?>">
                            <?php foreach ($tab['items'] as $item): ?>
                                <article class="feature-card"><span class="feature-icon"><?php echo landingIcon($item[2]); ?></span><h3><?php echo htmlspecialchars($item[0]); ?></h3><p><?php echo htmlspecialchars($item[1]); ?></p></article>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <section id="planos" class="plans-section">
                <div class="section-shell"><div class="section-heading centered"><span class="eyebrow">Planos</span><h2>Comece com o plano certo para o seu momento.</h2><p>Evolua a tecnologia conforme sua operação cresce.</p></div>
                    <div class="plans-grid"><?php foreach ($plans as $plan): ?><article class="plan-card <?php echo !empty($plan['featured']) ? 'is-featured' : ''; ?>"><?php if (!empty($plan['featured'])): ?><span class="plan-badge">Mais escolhido</span><?php endif; ?><h3><?php echo htmlspecialchars($plan['name']); ?></h3><strong class="plan-price"><?php echo htmlspecialchars($plan['price']); ?><small><?php echo $plan['price'] !== 'Sob consulta' ? '/mês' : ''; ?></small></strong><p><?php echo htmlspecialchars($plan['description']); ?></p><ul><?php foreach ($plan['items'] as $item): ?><li><?php echo landingIcon('check'); ?><?php echo htmlspecialchars($item); ?></li><?php endforeach; ?></ul><a class="button <?php echo !empty($plan['featured']) ? 'button-primary' : 'button-secondary'; ?>" href="<?php echo $publicBaseUrl; ?>/register.php">Começar agora</a></article><?php endforeach; ?></div>
                </div>
            </section>

            <section id="duvidas" class="faq-section">
                <div class="section-shell faq-shell">
                    <div class="faq-heading"><h2>Dúvidas sobre a Delicacy</h2><p>A gente responde</p></div>
                    <div class="faq-list"><?php foreach ($faqs as $faq): ?><details><summary><span><?php echo htmlspecialchars($faq[0]); ?></span><i aria-hidden="true"></i></summary><p><?php echo htmlspecialchars($faq[1]); ?></p></details><?php endforeach; ?></div>
                </div>
</section>
        </main>
    </div>

    <footer class="reveal-footer">
        <div class="footer-brand-large"><img src="<?php echo $publicBaseUrl; ?>/images/landing/delicacy-footer-lockup.png" alt="Delicacy"></div>
        <div class="footer-meta"><span>© Delicacy - 2026 • Todos os direitos reservados</span><img class="footer-dev-logo" src="<?php echo $publicBaseUrl; ?>/images/landing/edudev-logo-gold.png" alt="EDUDEV"><nav><a href="#">Termos</a><a href="#">Privacidade</a><a href="mailto:contato@delicacy.com.br">Contato</a></nav></div>
    </footer>

    <script>
        var landingHeader = document.getElementById('landingHeader');
        function syncHeaderState() {
            landingHeader.classList.toggle('is-scrolled', window.scrollY > 16);
        }
        syncHeaderState();
        window.addEventListener('scroll', syncHeaderState, { passive: true });

        document.querySelectorAll('.feature-tab').forEach(function (tab) {
            tab.addEventListener('click', function () {
                var target = tab.dataset.tab;
                document.querySelectorAll('.feature-tab').forEach(function (item) {
                    var active = item === tab;
                    item.classList.toggle('is-active', active);
                    item.setAttribute('aria-selected', active ? 'true' : 'false');
                });
                document.querySelectorAll('.feature-panel').forEach(function (panel) {
                    panel.classList.toggle('is-active', panel.dataset.panel === target);
                });
            });
        });
    </script>
</body>
</html>
