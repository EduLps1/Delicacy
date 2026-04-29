-- =============================================
-- DELICACY - Schema do Banco de Dados
-- =============================================
-- Versão: Release 1.0
-- Engine: MySQL 8.0+ (InnoDB)
-- Charset: utf8mb4 (suporte completo a Unicode/emojis)
-- =============================================

-- Criação do banco de dados (caso não exista)
CREATE DATABASE IF NOT EXISTS delicacy_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE delicacy_db;

-- =============================================
-- TABELA: users
-- =============================================
-- Armazena todos os usuários do sistema, independente do papel (role).
-- Um único registro de usuário pode ser admin_delicacy, admin_restaurant,
-- attendant ou customer. O campo 'role' define o nível de acesso.
--
-- SUGESTÃO APLICADA: Adicionado campo 'last_login_at' para auditoria
-- de segurança — permite detectar contas inativas e acessos suspeitos.
-- =============================================
CREATE TABLE IF NOT EXISTS users (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email       VARCHAR(255) NOT NULL UNIQUE COMMENT 'Email é o identificador de login',
    password    VARCHAR(255) NOT NULL COMMENT 'Hash bcrypt da senha (nunca armazenar texto plano)',
    name        VARCHAR(150) NOT NULL,
    role        ENUM('admin_delicacy', 'admin_restaurant', 'attendant', 'customer')
                NOT NULL DEFAULT 'customer'
                COMMENT 'Define o nível de acesso no sistema',
    status      ENUM('active', 'inactive')
                NOT NULL DEFAULT 'active'
                COMMENT 'Usuários inativos não podem fazer login',
    last_login_at DATETIME DEFAULT NULL COMMENT 'SUGESTÃO: Registro do último login para auditoria',
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Índice no role para consultas filtradas (ex: listar todos admin_restaurant)
    INDEX idx_users_role (role),
    -- Índice no status para consultas de usuários ativos
    INDEX idx_users_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================
-- TABELA: restaurants
-- =============================================
-- Cada restaurante está vinculado a um usuário (admin_restaurant).
-- Armazena dados comerciais, configurações de comissão e plano.
--
-- SUGESTÃO APLICADA: Adicionado UNIQUE no CNPJ para evitar cadastros
-- duplicados do mesmo estabelecimento. CNPJ armazenado SEM formatação
-- (apenas 14 dígitos) para facilitar validação e comparação.
--
-- SUGESTÃO: Considerar adicionar campos 'address', 'city', 'state', 'zip_code'
-- para localização do restaurante (útil para delivery e exibição no cardápio).
-- Não adicionados nesta release para manter o escopo.
-- =============================================
CREATE TABLE IF NOT EXISTS restaurants (
    id                      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id                 INT UNSIGNED NOT NULL COMMENT 'FK para o dono/admin do restaurante',
    name                    VARCHAR(200) NOT NULL,
    description             TEXT DEFAULT NULL,
    logo_url                VARCHAR(500) DEFAULT NULL COMMENT 'URL da logo (armazenamento externo)',
    phone                   VARCHAR(20) DEFAULT NULL COMMENT 'Telefone com DDD',
    email                   VARCHAR(255) DEFAULT NULL COMMENT 'Email comercial do restaurante',
    cnpj                    VARCHAR(14) NOT NULL UNIQUE COMMENT 'CNPJ sem formatação (14 dígitos)',
    total_revenue           DECIMAL(12, 2) NOT NULL DEFAULT 0.00 COMMENT 'Faturamento do último mês (cache)',
    commission_type         ENUM('plan_only', 'commission_only', 'hybrid')
                            NOT NULL DEFAULT 'hybrid'
                            COMMENT 'Modelo de monetização aplicado',
    active_commission_rate  DECIMAL(5, 2) NOT NULL DEFAULT 2.50 COMMENT 'Percentual de comissão ativo',
    plan_type               ENUM('basic', 'premium', 'custom')
                            NOT NULL DEFAULT 'basic',
    is_active               TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=ativo, 0=suspenso',
    created_at              DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- FK: cada restaurante pertence a um usuário
    CONSTRAINT fk_restaurants_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE RESTRICT   -- Impede deletar usuário que tem restaurante
        ON UPDATE CASCADE,

    -- Índice para busca por status (restaurantes ativos)
    INDEX idx_restaurants_active (is_active),
    -- Índice para busca por tipo de comissão
    INDEX idx_restaurants_commission (commission_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================
-- TABELA: menus
-- =============================================
-- Cardápios de cada restaurante. Um restaurante pode ter múltiplos
-- cardápios (ex: delivery, presencial, unidade 1, unidade 2).
--
-- O campo unit_number permite diferenciar cardápios por unidade/filial.
-- O campo is_published controla se o cardápio está visível publicamente.
-- =============================================
CREATE TABLE IF NOT EXISTS menus (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id   INT UNSIGNED NOT NULL COMMENT 'FK para o restaurante dono',
    name            VARCHAR(200) NOT NULL COMMENT 'Nome do cardápio (ex: Delivery Centro)',
    description     TEXT DEFAULT NULL,
    type            ENUM('online', 'presencial', 'both')
                    NOT NULL DEFAULT 'both'
                    COMMENT 'Modalidade de atendimento do cardápio',
    unit_number     SMALLINT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Número da unidade/filial',
    qr_code_url     VARCHAR(500) DEFAULT NULL COMMENT 'URL do QR Code gerado',
    is_published    TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0=rascunho, 1=publicado',
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_menus_restaurant
        FOREIGN KEY (restaurant_id) REFERENCES restaurants(id)
        ON DELETE CASCADE   -- Se o restaurante for deletado, seus cardápios também
        ON UPDATE CASCADE,

    INDEX idx_menus_restaurant (restaurant_id),
    INDEX idx_menus_published (is_published)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================
-- TABELA: menu_items
-- =============================================
-- Itens individuais de cada cardápio (pratos, bebidas, sobremesas).
-- O campo category usa ENUM para categorias pré-definidas.
--
-- SUGESTÃO: Para maior flexibilidade, considerar criar uma tabela
-- 'categories' separada no futuro. ENUM é adequado para o MVP.
-- =============================================
CREATE TABLE IF NOT EXISTS menu_items (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    menu_id     INT UNSIGNED NOT NULL COMMENT 'FK para o cardápio',
    name        VARCHAR(200) NOT NULL,
    description TEXT DEFAULT NULL,
    price       DECIMAL(10, 2) NOT NULL COMMENT 'Preço em BRL',
    category    VARCHAR(100) NOT NULL DEFAULT 'outros'
                COMMENT 'Categoria do item (bebida, prato_principal, entrada, sobremesa, outros)',
    image_url   VARCHAR(500) DEFAULT NULL COMMENT 'URL da imagem do item',
    is_active   TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=disponível, 0=indisponível',
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_menu_items_menu
        FOREIGN KEY (menu_id) REFERENCES menus(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    INDEX idx_menu_items_menu (menu_id),
    INDEX idx_menu_items_category (category),
    INDEX idx_menu_items_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================
-- TABELA: customers
-- =============================================
-- Clientes finais que fazem pedidos.
-- Separado da tabela users porque clientes podem fazer pedidos
-- de forma anônima (sem cadastro completo).
--
-- SUGESTÃO SOBRE loyalty_type: O campo loyalty_type nesta tabela
-- representa o tipo de fidelidade PADRÃO do cliente. Porém, como
-- cada restaurante define suas próprias regras de fidelidade
-- (tabela loyalty_rules), o tipo efetivo depende do restaurante.
-- Para a Release 1, mantemos aqui. No futuro, considerar uma
-- tabela junction customer_restaurant_loyalty para N:N.
-- =============================================
CREATE TABLE IF NOT EXISTS customers (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email           VARCHAR(255) NOT NULL UNIQUE,
    name            VARCHAR(150) NOT NULL,
    phone           VARCHAR(20) DEFAULT NULL COMMENT 'Telefone para WhatsApp',
    loyalty_points  INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Pontos de fidelidade acumulados',
    loyalty_type    ENUM('none', 'counting', 'points')
                    NOT NULL DEFAULT 'none',
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_customers_phone (phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================
-- TABELA: orders
-- =============================================
-- Pedidos realizados pelos clientes em um cardápio específico.
-- customer_id pode ser NULL para pedidos anônimos (sem cadastro).
-- customer_phone armazena o telefone informado no momento do pedido
-- (pode diferir do cadastro do cliente, por isso não é normalizado).
-- =============================================
CREATE TABLE IF NOT EXISTS orders (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    menu_id             INT UNSIGNED NOT NULL COMMENT 'FK para o cardápio onde o pedido foi feito',
    customer_id         INT UNSIGNED DEFAULT NULL COMMENT 'NULL para pedidos anônimos',
    status              ENUM('pending', 'paid', 'preparing', 'ready', 'delivered', 'cancelled')
                        NOT NULL DEFAULT 'pending',
    total_value         DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    payment_method      ENUM('card', 'pix', 'cash')
                        DEFAULT NULL COMMENT 'Definido no momento do pagamento',
    payment_date        DATETIME DEFAULT NULL COMMENT 'Data/hora em que o pagamento foi confirmado',
    payment_proof       VARCHAR(500) DEFAULT NULL COMMENT 'URL do comprovante de pagamento',
    delivery_address    TEXT DEFAULT NULL COMMENT 'Endereço de entrega (pedidos online)',
    customer_phone      VARCHAR(20) DEFAULT NULL COMMENT 'Telefone para contato/WhatsApp',
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_orders_menu
        FOREIGN KEY (menu_id) REFERENCES menus(id)
        ON DELETE RESTRICT   -- Não permitir deletar cardápio que tem pedidos
        ON UPDATE CASCADE,

    CONSTRAINT fk_orders_customer
        FOREIGN KEY (customer_id) REFERENCES customers(id)
        ON DELETE SET NULL   -- Se o cliente for deletado, o pedido permanece (anônimo)
        ON UPDATE CASCADE,

    -- Índice no status: consulta mais frequente (ex: pedidos pendentes)
    INDEX idx_orders_status (status),
    -- Índice composto para buscar pedidos por cardápio e data
    INDEX idx_orders_menu_date (menu_id, created_at),
    INDEX idx_orders_customer (customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================
-- TABELA: order_items
-- =============================================
-- Itens individuais de cada pedido.
-- price_at_moment registra o preço no momento do pedido para
-- manter o histórico correto mesmo se o preço do item mudar depois.
-- =============================================
CREATE TABLE IF NOT EXISTS order_items (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id            INT UNSIGNED NOT NULL,
    menu_item_id        INT UNSIGNED NOT NULL,
    quantity            SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    price_at_moment     DECIMAL(10, 2) NOT NULL COMMENT 'Preço unitário no momento do pedido',
    notes               TEXT DEFAULT NULL COMMENT 'Observações do cliente (ex: sem cebola)',

    CONSTRAINT fk_order_items_order
        FOREIGN KEY (order_id) REFERENCES orders(id)
        ON DELETE CASCADE   -- Se o pedido for deletado, seus itens também
        ON UPDATE CASCADE,

    CONSTRAINT fk_order_items_menu_item
        FOREIGN KEY (menu_item_id) REFERENCES menu_items(id)
        ON DELETE RESTRICT  -- Não deletar item do cardápio que está em pedidos
        ON UPDATE CASCADE,

    INDEX idx_order_items_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================
-- TABELA: loyalty_rules
-- =============================================
-- Regras de fidelidade definidas por cada restaurante.
-- Cada restaurante pode ter suas próprias regras de pontuação.
-- =============================================
CREATE TABLE IF NOT EXISTS loyalty_rules (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id       INT UNSIGNED NOT NULL,
    type                ENUM('counting', 'points') NOT NULL,
    name                VARCHAR(200) NOT NULL COMMENT 'Nome da regra (ex: A cada 10 pedidos, 1 grátis)',
    description         TEXT DEFAULT NULL,
    activation_date     DATE DEFAULT NULL COMMENT 'Data de início da regra',
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_loyalty_rules_restaurant
        FOREIGN KEY (restaurant_id) REFERENCES restaurants(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    INDEX idx_loyalty_rules_restaurant (restaurant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================
-- TABELA: loyalty_transactions
-- =============================================
-- Histórico de ganho/resgate de pontos de fidelidade.
-- Permite rastrear cada movimentação de pontos.
-- =============================================
CREATE TABLE IF NOT EXISTS loyalty_transactions (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id         INT UNSIGNED NOT NULL,
    restaurant_id       INT UNSIGNED NOT NULL,
    points_earned       INT NOT NULL DEFAULT 0 COMMENT 'Pontos ganhos nesta transação',
    points_redeemed     INT NOT NULL DEFAULT 0 COMMENT 'Pontos resgatados nesta transação',
    description         VARCHAR(500) DEFAULT NULL,
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_loyalty_trans_customer
        FOREIGN KEY (customer_id) REFERENCES customers(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_loyalty_trans_restaurant
        FOREIGN KEY (restaurant_id) REFERENCES restaurants(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    INDEX idx_loyalty_trans_customer (customer_id),
    INDEX idx_loyalty_trans_restaurant (restaurant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================
-- TABELA: commissions
-- =============================================
-- Registro de comissões por pedido.
-- Cada pedido gera uma comissão calculada automaticamente
-- com base na taxa ativa do restaurante no momento.
-- =============================================
CREATE TABLE IF NOT EXISTS commissions (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id       INT UNSIGNED NOT NULL,
    menu_id             INT UNSIGNED NOT NULL,
    order_id            INT UNSIGNED NOT NULL,
    commission_rate     DECIMAL(5, 2) NOT NULL COMMENT 'Taxa aplicada no momento do cálculo',
    commission_value    DECIMAL(10, 2) NOT NULL COMMENT 'Valor da comissão em BRL',
    billing_period      VARCHAR(7) NOT NULL COMMENT 'Período no formato YYYY-MM',
    status              ENUM('calculated', 'charged', 'paid')
                        NOT NULL DEFAULT 'calculated',
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_commissions_restaurant
        FOREIGN KEY (restaurant_id) REFERENCES restaurants(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_commissions_menu
        FOREIGN KEY (menu_id) REFERENCES menus(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_commissions_order
        FOREIGN KEY (order_id) REFERENCES orders(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    INDEX idx_commissions_restaurant (restaurant_id),
    INDEX idx_commissions_period (billing_period),
    INDEX idx_commissions_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================
-- TABELA: admin_logs
-- =============================================
-- Log de auditoria das ações administrativas.
-- Registra quem fez o quê, em qual entidade, e o que mudou.
-- O campo changes armazena um JSON com o antes/depois da alteração.
--
-- Essencial para:
-- - Auditoria de segurança
-- - Rastreamento de alterações
-- - Resolução de disputas
-- =============================================
CREATE TABLE IF NOT EXISTS admin_logs (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_id        INT UNSIGNED NOT NULL COMMENT 'FK para o admin que executou a ação',
    action          VARCHAR(50) NOT NULL COMMENT 'Tipo de ação (create, update, delete, login, etc)',
    entity_type     VARCHAR(50) NOT NULL COMMENT 'Tipo da entidade afetada (restaurant, menu, order, etc)',
    entity_id       INT UNSIGNED DEFAULT NULL COMMENT 'ID da entidade afetada (NULL para ações globais)',
    changes         JSON DEFAULT NULL COMMENT 'Detalhes da alteração em formato JSON',
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_admin_logs_admin
        FOREIGN KEY (admin_id) REFERENCES users(id)
        ON DELETE RESTRICT   -- Não permitir deletar admin que tem logs
        ON UPDATE CASCADE,

    -- Índice para buscar logs por admin
    INDEX idx_admin_logs_admin (admin_id),
    -- Índice para buscar logs por tipo de entidade
    INDEX idx_admin_logs_entity (entity_type, entity_id),
    -- Índice para buscar logs por data (relatórios)
    INDEX idx_admin_logs_date (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================
-- DADOS INICIAIS (Seed)
-- =============================================
-- Cria o primeiro admin da plataforma Delicacy.
-- Senha padrão: 'Admin@123' (hash bcrypt gerado com custo 12)
-- IMPORTANTE: Trocar a senha após o primeiro login!
-- =============================================
INSERT INTO users (email, password, name, role, status)
VALUES (
    'admin@delicacy.com.br',
    '$2y$12$LJ3m4ys7FP3Cv1PKZR8/0OJDfxqWcYBqLZHGOZCzxNz7MQFP.J9Wy',
    'Administrador Delicacy',
    'admin_delicacy',
    'active'
) ON DUPLICATE KEY UPDATE email = email;
