<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * DELICACY - Homepage
 */
require_once __DIR__ . '/../config/config.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delicacy - Cardapio Digital para Delivery</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css">
    <style>
        :root {
            --landing-bg: #fbfaf8;
            --landing-soft: #fff0ed;
            --landing-card: #fffefe;
            --landing-border: #e6ddd6;
            --landing-red: #ef3d35;
            --landing-red-dark: #d92f28;
            --landing-orange: #ff5a1f;
            --landing-yellow: #ffc329;
            --landing-green: #2dcc75;
            --landing-ink: #161719;
            --landing-muted: #725e55;
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            background: var(--landing-bg);
            color: var(--landing-ink);
            font-family: Inter, "Segoe UI", Arial, sans-serif;
            line-height: 1.45;
        }

        .landing-shell {
            min-height: 100vh;
            overflow: hidden;
        }

        .landing-header {
            position: sticky;
            top: 0;
            z-index: 20;
            height: 48px;
            background: rgba(251, 250, 248, 0.96);
            border-bottom: 1px solid #e7dfd8;
            backdrop-filter: blur(12px);
        }

        .landing-nav {
            width: min(100%, 946px);
            height: 100%;
            margin: 0 auto;
            padding: 0 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            color: #2a2522;
            font-size: 1.25rem;
            font-weight: 800;
            line-height: 1;
            text-decoration: none;
        }

        .brand-mark {
            position: relative;
            display: inline-block;
            width: 15px;
            height: 15px;
            margin: 0 1px;
            color: var(--landing-red);
            transform: translateY(1px);
        }

        .brand-mark::before,
        .brand-mark::after {
            content: "";
            position: absolute;
            left: 6px;
            top: 0;
            width: 3px;
            height: 15px;
            border-radius: 999px;
            background: currentColor;
        }

        .brand-mark::before {
            transform: rotate(45deg);
        }

        .brand-mark::after {
            transform: rotate(-45deg);
        }

        .landing-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
            min-height: 36px;
            padding: 0.6rem 1.05rem;
            border: 0;
            border-radius: 999px;
            background: var(--landing-red);
            color: #fff;
            font-size: 0.88rem;
            font-weight: 800;
            text-decoration: none;
            transition: transform 0.2s, background 0.2s, box-shadow 0.2s;
        }

        .landing-btn:hover {
            color: #fff;
            background: var(--landing-red-dark);
            transform: translateY(-1px);
            box-shadow: 0 10px 22px rgba(239, 61, 53, 0.22);
        }

        .landing-main {
            width: min(100%, 946px);
            margin: 0 auto;
            padding: 1.35rem 1rem 0;
        }

        .hero-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.6rem;
            align-items: stretch;
        }

        .hero-panel {
            min-height: 386px;
            border-radius: 18px;
            box-shadow: 0 18px 26px rgba(58, 45, 38, 0.14);
        }

        .hero-panel-orange {
            padding: 2.1rem 2.15rem;
            background: linear-gradient(145deg, #ef3d35 0%, #ff5d18 100%);
            color: #fff;
        }

        .hero-kicker {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.28rem 0.75rem;
            margin-bottom: 1.1rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            font-size: 0.75rem;
            font-weight: 800;
        }

        .hero-title {
            max-width: 390px;
            margin: 0 0 1.7rem;
            color: #fff;
            font-size: clamp(2.15rem, 4vw, 2.75rem);
            line-height: 0.96;
            font-weight: 900;
            letter-spacing: 0;
        }

        .hero-benefits {
            display: grid;
            gap: 1.05rem;
            margin-top: 1.5rem;
        }

        .hero-benefit {
            display: grid;
            grid-template-columns: 36px 1fr;
            gap: 0.78rem;
            align-items: center;
        }

        .mini-icon {
            width: 36px;
            height: 36px;
            display: grid;
            place-items: center;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.2);
            color: currentColor;
        }

        .mini-icon svg,
        .round-icon svg,
        .model-icon svg {
            width: 20px;
            height: 20px;
            stroke: currentColor;
            stroke-width: 2.2;
            fill: none;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .hero-benefit h3,
        .advantage-copy h3 {
            margin: 0 0 0.15rem;
            color: inherit;
            font-size: 1rem;
            line-height: 1.2;
            font-weight: 900;
        }

        .hero-benefit p,
        .advantage-copy p {
            margin: 0;
            color: inherit;
            font-size: 0.78rem;
            opacity: 0.9;
        }

        .hero-panel-white {
            padding: 4.15rem 2.2rem;
            border: 1px solid var(--landing-border);
            background: var(--landing-card);
        }

        .hero-badge {
            width: 47px;
            height: 47px;
            display: grid;
            place-items: center;
            margin-bottom: 1.35rem;
            border-radius: 50%;
            background: #fff1cd;
            color: #ffb000;
        }

        .hero-badge svg {
            width: 25px;
            height: 25px;
            stroke: currentColor;
            stroke-width: 2.2;
            fill: none;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .hero-panel-white h2 {
            max-width: 350px;
            margin: 0 0 1rem;
            color: var(--landing-ink);
            font-size: clamp(1.75rem, 3.2vw, 2rem);
            line-height: 1.05;
            font-weight: 900;
        }

        .hero-panel-white p {
            max-width: 390px;
            margin: 0 0 1.85rem;
            color: var(--landing-muted);
            font-size: 1rem;
        }

        .models-section {
            padding: 8.7rem 0 1.05rem;
            text-align: center;
        }

        .section-title {
            margin: 0;
            color: var(--landing-ink);
            font-size: clamp(1.7rem, 3.3vw, 2rem);
            line-height: 1.15;
            font-weight: 900;
        }

        .section-subtitle {
            margin: 0.65rem 0 0;
            color: var(--landing-muted);
            font-size: 1rem;
        }

        .model-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 0.9rem;
            margin-top: 2.7rem;
        }

        .model-card {
            min-height: 128px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.85rem;
            border: 1px solid var(--landing-border);
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 1px 0 rgba(0, 0, 0, 0.02);
        }

        .model-icon {
            width: 55px;
            height: 55px;
            display: grid;
            place-items: center;
            border-radius: 13px;
        }

        .model-icon-red {
            background: #ffe8e7;
            color: var(--landing-red);
        }

        .model-icon-cream {
            background: #fff3d4;
            color: #28231f;
        }

        .model-icon-green {
            background: #dff6e8;
            color: #17b45f;
        }

        .model-icon-orange {
            background: #ffe7c7;
            color: #ff5d18;
        }

        .model-card strong {
            color: #0f0f10;
            font-size: 0.9rem;
        }

        .advantages-band {
            margin-top: 1rem;
            background: var(--landing-soft);
        }

        .advantages-inner {
            width: min(100%, 946px);
            margin: 0 auto;
            padding: 3.25rem 1rem 4.4rem;
            display: grid;
            grid-template-columns: 1fr 1.15fr;
            gap: 3.6rem;
            align-items: center;
        }

        .advantages-title {
            max-width: 390px;
            margin: 0 0 2rem;
            color: var(--landing-ink);
            font-size: clamp(2.25rem, 4vw, 2.8rem);
            line-height: 0.96;
            font-weight: 900;
        }

        .advantage-list {
            display: grid;
            gap: 1.45rem;
        }

        .advantage-item {
            display: grid;
            grid-template-columns: 40px 1fr;
            gap: 0.95rem;
            align-items: start;
        }

        .round-icon {
            width: 40px;
            height: 40px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: var(--landing-red);
            color: #fff;
            box-shadow: 0 8px 18px rgba(239, 61, 53, 0.18);
        }

        .advantage-copy h3 {
            color: var(--landing-ink);
            font-size: 1.06rem;
        }

        .advantage-copy p {
            max-width: 430px;
            color: var(--landing-muted);
            font-size: 0.9rem;
            line-height: 1.65;
        }

        .device-art {
            position: relative;
            min-height: 430px;
        }

        .phone {
            position: absolute;
            border: 3px solid #584f4a;
            border-radius: 22px;
            background: #fff;
            box-shadow: 0 26px 34px rgba(58, 45, 38, 0.16);
            overflow: hidden;
        }

        .phone-small {
            left: 50px;
            top: 150px;
            width: 145px;
            height: 274px;
            transform: rotate(-7deg);
        }

        .phone-large {
            right: 0;
            top: 50px;
            width: 250px;
            height: 322px;
            transform: rotate(7deg);
        }

        .phone-top {
            height: 44px;
            background: var(--landing-red);
        }

        .phone-small .phone-top {
            height: 106px;
            display: grid;
            place-items: center;
            background: var(--landing-yellow);
            color: #171717;
        }

        .phone-top::after {
            content: "";
            display: block;
            width: 38px;
            height: 5px;
            margin: 24px auto 0;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.34);
        }

        .phone-small .phone-top::after {
            display: none;
        }

        .phone-lines {
            padding: 0.85rem 0.9rem;
            display: grid;
            gap: 0.72rem;
        }

        .phone-line-card {
            height: 46px;
            border-radius: 10px;
            background: #e9e5e0;
            padding: 0.75rem;
        }

        .phone-line-card span {
            display: block;
            height: 6px;
            margin-bottom: 0.42rem;
            border-radius: 999px;
            background: #b8b2ac;
        }

        .phone-line-card span:nth-child(2) {
            width: 62%;
            background: #c8c2bc;
        }

        .phone-small .phone-line-card {
            height: 36px;
            padding: 0.55rem;
        }

        .phone-small .phone-line-card span:nth-child(2) {
            width: 34%;
            background: #ffada8;
        }

        .device-plus {
            position: absolute;
            right: 213px;
            top: 216px;
            width: 47px;
            height: 47px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: var(--landing-red);
            color: #fff;
            font-size: 1.55rem;
            font-weight: 900;
            box-shadow: 0 14px 24px rgba(239, 61, 53, 0.22);
        }

        .cta-section {
            padding: 3.9rem 1rem 2.2rem;
            background: var(--landing-bg);
            text-align: center;
        }

        .cta-section h2 {
            margin: 0;
            color: var(--landing-ink);
            font-size: clamp(1.75rem, 3vw, 2rem);
            font-weight: 900;
        }

        .cta-section p {
            margin: 0.85rem 0 1.85rem;
            color: var(--landing-muted);
        }

        .landing-footer {
            padding: 1.1rem;
            border-top: 1px solid #ebe3dc;
            background: #fff;
            color: #725e55;
            font-size: 0.88rem;
            text-align: center;
        }

        .landing-footer strong {
            color: var(--landing-red);
        }

        @media (max-width: 900px) {
            .landing-main,
            .landing-nav,
            .advantages-inner {
                width: min(100%, 720px);
            }

            .hero-grid,
            .advantages-inner {
                grid-template-columns: 1fr;
            }

            .models-section {
                padding-top: 4rem;
            }

            .model-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .device-art {
                min-height: 380px;
            }

            .phone-small {
                left: 12%;
            }

            .phone-large {
                right: 8%;
            }

            .device-plus {
                right: 43%;
            }
        }

        @media (max-width: 560px) {
            .landing-nav {
                padding: 0 0.85rem;
            }

            .landing-main {
                padding: 1rem 0.85rem 0;
            }

            .landing-btn {
                min-height: 34px;
                padding: 0.55rem 0.8rem;
                font-size: 0.8rem;
            }

            .hero-panel {
                min-height: auto;
                border-radius: 14px;
            }

            .hero-panel-orange,
            .hero-panel-white {
                padding: 1.65rem;
            }

            .hero-title {
                font-size: 2.25rem;
            }

            .models-section {
                padding-top: 3.2rem;
            }

            .model-grid {
                grid-template-columns: 1fr;
            }

            .advantages-inner {
                padding: 3rem 0.85rem;
                gap: 2rem;
            }

            .advantages-title {
                font-size: 2.15rem;
            }

            .device-art {
                min-height: 330px;
                transform: scale(0.82);
                transform-origin: top center;
            }

            .phone-small {
                left: 0;
                top: 138px;
            }

            .phone-large {
                right: 0;
            }

            .device-plus {
                right: 160px;
            }
        }
    </style>
</head>
<body>
    <div class="landing-shell">
        <header class="landing-header">
            <nav class="landing-nav" aria-label="Navegacao principal">
                <a class="brand" href="<?php echo BASE_URL; ?>/" aria-label="Delicacy">
                    de<span class="brand-mark" aria-hidden="true"></span>icacy
                </a>
                <a class="landing-btn" href="<?php echo BASE_URL; ?>/register.php">Criar cardápio <span aria-hidden="true">→</span></a>
            </nav>
        </header>

        <main>
            <section class="landing-main" aria-labelledby="hero-title">
                <div class="hero-grid">
                    <article class="hero-panel hero-panel-orange">
                        <span class="hero-kicker">
                            <span aria-hidden="true">✦</span>
                            Novo
                        </span>
                        <h1 id="hero-title" class="hero-title">Pedidos Automatizados com Cardápio Digital</h1>

                        <div class="hero-benefits" aria-label="Beneficios principais">
                            <div class="hero-benefit">
                                <span class="mini-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24">
                                        <path d="M4 15h4l3 3 5-9 4 4" />
                                        <path d="M17 9h3v3" />
                                    </svg>
                                </span>
                                <div>
                                    <h3>Redução nos custos operacionais</h3>
                                    <p>Menos garçons, menos erros, mais agilidade.</p>
                                </div>
                            </div>

                            <div class="hero-benefit">
                                <span class="mini-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24">
                                        <path d="M4 16l5-5 4 4 7-8" />
                                        <path d="M15 7h5v5" />
                                    </svg>
                                </span>
                                <div>
                                    <h3>Aumento do ticket médio</h3>
                                    <p>Sugestões inteligentes que vendem mais.</p>
                                </div>
                            </div>
                        </div>
                    </article>

                    <article class="hero-panel hero-panel-white">
                        <span class="hero-badge" aria-hidden="true">
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
                        <h2>Venha fazer parte da nossa equipe</h2>
                        <p>E automatize o seu atendimento com a tecnologia que está revolucionando o mercado de delivery.</p>
                        <a class="landing-btn" href="<?php echo BASE_URL; ?>/register.php">Começar agora <span aria-hidden="true">→</span></a>
                    </article>
                </div>

                <section class="models-section" aria-labelledby="models-title">
                    <h2 id="models-title" class="section-title">Modelos de cardápio para todo tipo de operação</h2>
                    <p class="section-subtitle">Escolha o formato ideal para o seu negócio</p>

                    <div class="model-grid">
                        <article class="model-card">
                            <span class="model-icon model-icon-red" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <rect x="4" y="5" width="16" height="11" rx="1.5" />
                                    <path d="M12 16v3" />
                                    <path d="M8 19h8" />
                                </svg>
                            </span>
                            <strong>Totem Mini Clover</strong>
                        </article>

                        <article class="model-card">
                            <span class="model-icon model-icon-cream" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <rect x="8" y="3" width="8" height="18" rx="2" />
                                    <path d="M12 18h.01" />
                                </svg>
                            </span>
                            <strong>Totem Mini</strong>
                        </article>

                        <article class="model-card">
                            <span class="model-icon model-icon-green" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <rect x="7" y="3" width="10" height="18" rx="1.5" />
                                    <path d="M12 18h.01" />
                                </svg>
                            </span>
                            <strong>Tablet</strong>
                        </article>

                        <article class="model-card">
                            <span class="model-icon model-icon-orange" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <path d="M3 6h11v10H3z" />
                                    <path d="M14 10h4l3 3v3h-7z" />
                                    <circle cx="7" cy="18" r="2" />
                                    <circle cx="17" cy="18" r="2" />
                                </svg>
                            </span>
                            <strong>Delivery</strong>
                        </article>

                        <article class="model-card">
                            <span class="model-icon model-icon-red" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <path d="M5 5h4v4H5z" />
                                    <path d="M15 5h4v4h-4z" />
                                    <path d="M5 15h4v4H5z" />
                                    <path d="M15 15h1v1h-1z" />
                                    <path d="M18 15h1v4h-4v-1" />
                                    <path d="M12 5v3" />
                                    <path d="M12 12h3" />
                                    <path d="M9 12h1" />
                                </svg>
                            </span>
                            <strong>QR Code</strong>
                        </article>
                    </div>
                </section>
            </section>

            <section class="advantages-band" aria-labelledby="advantages-title">
                <div class="advantages-inner">
                    <div>
                        <h2 id="advantages-title" class="advantages-title">Vantagens do Cardápio Digital para Delivery</h2>

                        <div class="advantage-list">
                            <article class="advantage-item">
                                <span class="round-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24">
                                        <path d="M21 11.5a8.4 8.4 0 0 1-9 8.35 8.5 8.5 0 1 1 7.22-4.02L21 21l-5.22-1.75" />
                                    </svg>
                                </span>
                                <div class="advantage-copy">
                                    <h3>Pedidos via WhatsApp</h3>
                                    <p>Organize pedidos e conte com atendente virtual para não deixar o cliente esperando por respostas.</p>
                                </div>
                            </article>

                            <article class="advantage-item">
                                <span class="round-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24">
                                        <path d="M7 4h10v16H7z" />
                                        <path d="M12 8v8" />
                                        <path d="M15 10.5c0-1.2-1.2-2-3-2s-3 .8-3 2 1.2 2 3 2 3 .8 3 2-1.2 2-3 2-3-.8-3-2" />
                                    </svg>
                                </span>
                                <div class="advantage-copy">
                                    <h3>Zero taxas</h3>
                                    <p>Livre-se das taxas por pedidos e tenha um link de vendas para compartilhar nas redes sociais e WhatsApp.</p>
                                </div>
                            </article>

                            <article class="advantage-item">
                                <span class="round-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24">
                                        <circle cx="7" cy="17" r="2" />
                                        <circle cx="17" cy="17" r="2" />
                                        <path d="M5 17H3v-5h3l2-4h5v9" />
                                        <path d="M13 11h4l3 3v3h-3" />
                                    </svg>
                                </span>
                                <div class="advantage-copy">
                                    <h3>Entregas sem motoboy</h3>
                                    <p>Economize tempo e dinheiro utilizando serviços parceiros para fazer entregas sem contratar motoboys.</p>
                                </div>
                            </article>

                            <article class="advantage-item">
                                <span class="round-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24">
                                        <rect x="4" y="4" width="6" height="6" rx="1.5" />
                                        <rect x="14" y="14" width="6" height="6" rx="1.5" />
                                        <path d="M10 7h4" />
                                        <path d="M17 10v4" />
                                        <path d="M7 10v4" />
                                    </svg>
                                </span>
                                <div class="advantage-copy">
                                    <h3>Operação totalmente integrada</h3>
                                    <p>Integre o Cardápio Digital com seu sistema PDV e envie pedidos diretamente ao KDS para ganhar agilidade.</p>
                                </div>
                            </article>
                        </div>
                    </div>

                    <div class="device-art" aria-hidden="true">
                        <div class="phone phone-small">
                            <div class="phone-top">
                                <svg viewBox="0 0 24 24" width="36" height="36">
                                    <path d="M7 3v7" stroke="currentColor" stroke-width="2.2" fill="none" stroke-linecap="round" />
                                    <path d="M10 3v7" stroke="currentColor" stroke-width="2.2" fill="none" stroke-linecap="round" />
                                    <path d="M7 7h3" stroke="currentColor" stroke-width="2.2" fill="none" stroke-linecap="round" />
                                    <path d="M8.5 10v11" stroke="currentColor" stroke-width="2.2" fill="none" stroke-linecap="round" />
                                    <path d="M14 3v8" stroke="currentColor" stroke-width="2.2" fill="none" stroke-linecap="round" />
                                    <path d="M18 4c-2.2 1.6-3.4 3.9-3.4 6.8" stroke="currentColor" stroke-width="2.2" fill="none" stroke-linecap="round" />
                                    <path d="M14 11l6 10" stroke="currentColor" stroke-width="2.2" fill="none" stroke-linecap="round" />
                                    <path d="M4 20l16-16" stroke="currentColor" stroke-width="2.2" fill="none" stroke-linecap="round" />
                                </svg>
                            </div>
                            <div class="phone-lines">
                                <div class="phone-line-card"><span></span><span></span></div>
                                <div class="phone-line-card"><span></span><span></span></div>
                                <div class="phone-line-card"><span></span><span></span></div>
                            </div>
                        </div>

                        <div class="phone phone-large">
                            <div class="phone-top"></div>
                            <div class="phone-lines">
                                <div class="phone-line-card"><span></span><span></span></div>
                                <div class="phone-line-card"><span></span><span></span></div>
                                <div class="phone-line-card"><span></span><span></span></div>
                                <div class="phone-line-card"><span></span><span></span></div>
                            </div>
                        </div>

                        <div class="device-plus">+</div>
                    </div>
                </div>
            </section>

            <section class="cta-section" aria-labelledby="cta-title">
                <h2 id="cta-title">Pronto para revolucionar seu atendimento?</h2>
                <p>Crie seu cardápio digital em minutos.</p>
                <a class="landing-btn" href="<?php echo BASE_URL; ?>/register.php">Criar meu cardápio <span aria-hidden="true">→</span></a>
            </section>
        </main>

        <footer class="landing-footer">
            desenvolvido por <strong>Delicacy</strong>
        </footer>
    </div>
</body>
</html>
