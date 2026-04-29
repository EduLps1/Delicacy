<?php
/**
 * =============================================
 * DELICACY - Constantes do Sistema
 * =============================================
 * 
 * Centraliza TODAS as constantes usadas na aplicação.
 * Organizado por domínio/entidade para facilitar manutenção.
 * 
 * Raciocínio: Usar constantes em vez de strings soltas no código
 * previne erros de digitação (typos) e facilita refatoração.
 * Se o valor mudar, altera-se em um único lugar.
 */

// =============================================
// 1. STATUS DE USUÁRIO
// =============================================

/** Usuário com acesso ativo ao sistema */
define('USER_ACTIVE', 'active');

/** Usuário desativado — não pode fazer login */
define('USER_INACTIVE', 'inactive');

// =============================================
// 2. ROLES (PAPÉIS DE USUÁRIO)
// =============================================

/**
 * Cada role define o nível de acesso do usuário na plataforma:
 * 
 * - admin_delicacy: Administrador da plataforma Delicacy (super admin)
 * - admin_restaurant: Administrador/dono do restaurante (contratante)
 * - attendant: Atendente do restaurante (acesso limitado)
 * - customer: Cliente final (faz pedidos)
 */
define('ROLE_ADMIN_DELICACY', 'admin_delicacy');
define('ROLE_ADMIN_RESTAURANT', 'admin_restaurant');
define('ROLE_ATTENDANT', 'attendant');
define('ROLE_CUSTOMER', 'customer');

// =============================================
// 3. STATUS DO RESTAURANTE
// =============================================

/** Restaurante ativo e visível na plataforma */
define('RESTAURANT_ACTIVE', 1);

/** Restaurante suspenso/desativado pelo admin */
define('RESTAURANT_INACTIVE', 0);

// =============================================
// 4. TIPOS DE COMISSÃO
// =============================================

/**
 * Modelo de monetização da plataforma Delicacy:
 * 
 * - plan_only: Restaurante paga apenas mensalidade fixa (sem comissão por pedido)
 * - commission_only: Restaurante paga apenas comissão percentual sobre pedidos
 * - hybrid: Combinação de mensalidade reduzida + comissão menor
 * 
 * Raciocínio: O tipo de comissão é definido com base no faturamento
 * do restaurante. Restaurantes de baixo faturamento se beneficiam
 * do modelo híbrido, enquanto os de alto faturamento preferem comissão pura.
 */
define('COMMISSION_PLAN_ONLY', 'plan_only');
define('COMMISSION_ONLY', 'commission_only');
define('COMMISSION_HYBRID', 'hybrid');

// =============================================
// 5. TIPOS DE PLANO
// =============================================

define('PLAN_BASIC', 'basic');
define('PLAN_PREMIUM', 'premium');
define('PLAN_CUSTOM', 'custom');

// =============================================
// 6. TIPOS DE CARDÁPIO
// =============================================

define('MENU_TYPE_ONLINE', 'online');
define('MENU_TYPE_PRESENCIAL', 'presencial');
define('MENU_TYPE_BOTH', 'both');

// =============================================
// 7. STATUS DE PEDIDO
// =============================================

/**
 * Fluxo do pedido:
 * pending → paid → preparing → ready → delivered
 *                                    └→ cancelled (pode ocorrer em qualquer etapa)
 */
define('ORDER_PENDING', 'pending');
define('ORDER_PAID', 'paid');
define('ORDER_PREPARING', 'preparing');
define('ORDER_READY', 'ready');
define('ORDER_DELIVERED', 'delivered');
define('ORDER_CANCELLED', 'cancelled');

// =============================================
// 8. MÉTODOS DE PAGAMENTO
// =============================================

define('PAYMENT_CARD', 'card');
define('PAYMENT_PIX', 'pix');
define('PAYMENT_CASH', 'cash');

// =============================================
// 9. STATUS DE COMISSÃO
// =============================================

define('COMMISSION_CALCULATED', 'calculated');
define('COMMISSION_CHARGED', 'charged');
define('COMMISSION_PAID', 'paid');

// =============================================
// 10. TIPOS DE FIDELIDADE
// =============================================

define('LOYALTY_NONE', 'none');
define('LOYALTY_COUNTING', 'counting');
define('LOYALTY_POINTS', 'points');

// =============================================
// 11. AÇÕES DE LOG (ADMIN)
// =============================================

define('LOG_ACTION_CREATE', 'create');
define('LOG_ACTION_UPDATE', 'update');
define('LOG_ACTION_DELETE', 'delete');
define('LOG_ACTION_LOGIN', 'login');
define('LOG_ACTION_LOGOUT', 'logout');
define('LOG_ACTION_SUSPEND', 'suspend');
define('LOG_ACTION_ACTIVATE', 'activate');

// =============================================
// 12. ENTIDADES DE LOG
// =============================================

define('LOG_ENTITY_RESTAURANT', 'restaurant');
define('LOG_ENTITY_MENU', 'menu');
define('LOG_ENTITY_ORDER', 'order');
define('LOG_ENTITY_USER', 'user');
define('LOG_ENTITY_COMMISSION', 'commission');

// =============================================
// 13. CÓDIGOS HTTP
// =============================================

define('HTTP_OK', 200);
define('HTTP_CREATED', 201);
define('HTTP_BAD_REQUEST', 400);
define('HTTP_UNAUTHORIZED', 401);
define('HTTP_FORBIDDEN', 403);
define('HTTP_NOT_FOUND', 404);
define('HTTP_METHOD_NOT_ALLOWED', 405);
define('HTTP_CONFLICT', 409);
define('HTTP_INTERNAL_ERROR', 500);

// =============================================
// 14. CONFIGURAÇÕES DE SEGURANÇA
// =============================================

/** Número máximo de tentativas de login antes de bloquear temporariamente */
define('MAX_LOGIN_ATTEMPTS', 5);

/** Tempo de bloqueio por tentativas excedidas (em segundos) — 15 minutos */
define('LOGIN_LOCKOUT_TIME', 900);

/** Custo do algoritmo bcrypt para hash de senha (maior = mais seguro, mais lento) */
define('BCRYPT_COST', 12);

/** Tamanho mínimo exigido para senhas */
define('MIN_PASSWORD_LENGTH', 8);
