# 🍽️ Delicacy - Gestão de Cardápios Digitais

Plataforma SaaS completa para gestão de cardápios digitais com QR Code, sistema de pedidos e programa de fidelidade.

## 📋 Resumo do Projeto

**Delicacy** é uma solução inovadora que permite restaurantes gerenciar cardápios digitais acessíveis via QR Code, sem necessidade de clientes tocarem em menus físicos.

### Modelo de Negócio

1. **Comissão Variável**: Baseada no faturamento por cardápio
2. **Plano Mensal + Comissão**: Para restaurantes de baixo faturamento
3. **Comissão Pura**: Para restaurantes de alto faturamento

## 🏗️ Stack Técnico

- **Backend**: PHP 8+ (puro, sem framework)
- **Frontend**: HTML5 + CSS3 (sem framework JS)
- **Banco de Dados**: MySQL 8.0+
- **Hospedagem**: DigitalOcean VPS
- **QR Code**: biblioteca phpqrcode
- **Notificações**: Twilio (MVP) → Evolution API (produção)
- **Pagamentos**: MercadoPago / PagSeguro (futuro)

## 📁 Estrutura de Pastas

```
delicacy/
├── public/                 # Raiz web (index.php)
│   ├── index.php
│   ├── login.php
│   ├── register.php
│   ├── logout.php
│   ├── admin-delicacy/    # Admin Delicacy routes
│   ├── dashboard/         # Admin Contratante routes
│   ├── css/              # Estilos
│   ├── js/               # Scripts
│   └── uploads/          # Uploads de usuários
│
├── src/                   # Código-fonte
│   ├── controllers/       # Controllers
│   │   ├── AuthController.php
│   │   ├── AdminDelicacyController.php
│   │   └── AdminContratanteController.php
│   ├── models/           # Models (acesso BD)
│   │   ├── User.php
│   │   └── Restaurant.php
│   ├── services/         # Serviços (lógica)
│   │   ├── EmailService.php
│   │   └── NotificationService.php
│   └── utils/            # Utilitários
│
├── views/                # Templates HTML
│   ├── layouts/
│   │   └── base.php
│   ├── auth/
│   │   ├── login.php
│   │   └── register.php
│   ├── admin-delicacy/
│   │   ├── dashboard.php
│   │   ├── list-restaurants.php
│   │   └── list-users.php
│   └── dashboard/
│       ├── dashboard.php
│       ├── register-restaurant.php
│       └── edit-restaurant.php
│
├── config/               # Configurações
│   ├── config.php
│   ├── database.php
│   └── constants.php
│
├── database/            # Schema SQL
│   └── schema.sql
│
├── logs/                # Logs
├── sessions/            # Sessões PHP
│
├── .env                 # Variáveis de ambiente
├── .gitignore
├── README.md
└── composer.json (futuro)
```

## 🚀 Instalação & Setup

### 1. Clone o repositório

```bash
git clone https://github.com/seu-user/delicacy.git
cd delicacy
```

### 2. Configure variáveis de ambiente

```bash
cp .env.example .env
```

Edite `.env` com suas configurações:

```env
APP_ENV=development
DEBUG_MODE=true
BASE_URL=http://localhost

DB_HOST=localhost
DB_USER=root
DB_PASS=sua_senha
DB_NAME=delicacy_db
DB_PORT=3306
```

### 3. Crie o banco de dados

```bash
mysql -u root -p < database/schema.sql
```

Ou execute manualmente via MySQL:

```sql
CREATE DATABASE delicacy_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE delicacy_db;
SOURCE database/schema.sql;
```

### 4. Configure permissões

```bash
mkdir -p logs sessions public/uploads
chmod 755 logs sessions public/uploads
```

### 5. Inicie o servidor

#### Opção A: PHP Built-in (Desenvolvimento)

```bash
cd public
php -S localhost:8000
```

Acesse: `http://localhost:8000`

#### Opção B: Apache/Nginx

Configure o virtual host apontando para `public/` como document root.

## 👤 Usuários Padrão

Após criar o banco de dados, o admin padrão fica disponível:

**Email**: `admin@delicacy.com.br`  
**Senha**: `password`  
**Role**: `admin_delicacy`

⚠️ **IMPORTANTE**: Troque a senha após o primeiro login!

## 📚 Documentação das Rotas

### Autenticação

| Rota | Método | Descrição |
|------|--------|-----------|
| `/login.php` | GET/POST | Login de usuários |
| `/register.php` | GET/POST | Cadastro de novos restaurantes |
| `/logout.php` | POST | Logout |

### Admin Delicacy

| Rota | Método | Descrição |
|------|--------|-----------|
| `/admin-delicacy/dashboard.php` | GET | Dashboard principal |
| `/admin-delicacy/restaurants.php` | GET/POST | Listar/gerenciar restaurantes |
| `/admin-delicacy/users.php` | GET | Listar usuários |

### Admin Contratante (Restaurante)

| Rota | Método | Descrição |
|------|--------|-----------|
| `/dashboard/dashboard.php` | GET | Dashboard do restaurante |
| `/dashboard/cadastrar-restaurante.php` | GET/POST | Cadastrar primeiro restaurante |
| `/dashboard/cardapio.php` | GET/POST | Gerenciar cardápios |
| `/dashboard/pedidos.php` | GET | Ver pedidos |

## 🔐 Segurança

### Implementado

- ✅ Hash de senha com bcrypt (cost: 12)
- ✅ Prepared statements para prevenir SQL injection
- ✅ CSRF tokens em todos os formulários
- ✅ Session timeout (1 hora)
- ✅ Validação de entrada (sanitization)
- ✅ Headers de segurança (X-Frame-Options, etc)
- ✅ Autenticação baseada em roles (RBAC)
- ✅ Logs de auditoria em admin_logs

### A Implementar

- ⏳ Rate limiting em login/API
- ⏳ 2FA (Two-Factor Authentication)
- ⏳ Criptografia de dados sensíveis
- ⏳ WAF (Web Application Firewall)

## 📊 Banco de Dados

### Tabelas Principais

| Tabela | Descrição |
|--------|-----------|
| `users` | Usuários (admin, restaurantes, clientes) |
| `restaurants` | Dados dos restaurantes |
| `menus` | Cardápios dos restaurantes |
| `menu_items` | Itens de cada cardápio |
| `orders` | Pedidos dos clientes |
| `order_items` | Itens de cada pedido |
| `commissions` | Cálculo de comissões |
| `customers` | Dados dos clientes finais |
| `loyalty_rules` | Regras de fidelidade |
| `loyalty_transactions` | Histórico de pontos |
| `admin_logs` | Auditoria de ações |

## 🔄 Fluxo de Autenticação

1. **Login**
   - Usuário insere email + senha
   - Valida CSRF token
   - Verifica credenciais (authenticate no User model)
   - Cria sessão `$_SESSION['user_id']` e `$_SESSION['user']`
   - Registra `last_login_at`
   - Redireciona conforme role

2. **Autorização**
   - Funções: `requireAuth()`, `requireRole()`, `hasRole()`
   - Controllers verificam `requireRole(ROLE_ADMIN_RESTAURANT)`
   - Redireciona para `/login.php` se não autenticado
   - Redireciona para `/access-denied.php` se role inválido

3. **Logout**
   - Registra ação em admin_logs
   - Destroi sessão com `session_destroy()`
   - Redireciona para `/login.php`

## 📞 API Endpoints (Futuro - Sprint 2)

```
GET  /api/v1/menus/{id}           - Detalhes cardápio
POST /api/v1/orders               - Criar pedido
GET  /api/v1/orders/{id}          - Status pedido
POST /api/v1/restaurants/{id}/qr   - Gerar QR Code
```

## 🧪 Testes

### Testar Login Admin

```bash
Email: admin@delicacy.com.br
Senha: password

```

### Testar Novo Cadastro

1. Acesse `/register.php`
2. Preencha dados (nome, email, senha)
3. Clique em "Criar Conta"
4. Faça login com as credenciais

### Testar Dashboard Restaurante

1. Faça login como `admin_restaurant`
2. Se não tiver restaurante, cadastre um
3. Veja dashboard com dados do restaurante

## 🐛 Troubleshooting

### "Erro ao conectar ao banco de dados"

- Verifique `.env` (DB_HOST, DB_USER, DB_PASS)
- Confirme que MySQL está rodando
- Teste conexão: `mysql -u root -p -h localhost delicacy_db`

### "Token de segurança inválido"

- Limpe cookies/cache
- Verifique se sessões estão habilitadas no PHP
- Checke permissões da pasta `sessions/`

### "Sem permissão para acessar"

- Confirme seu role em `$_SESSION['user']`
- Verifique função `hasRole()` no controller
- Check logs em `logs/error.log`

## 📝 Logs

- **Aplicação**: `logs/error.log`
- **Auditoria**: Tabela `admin_logs` (SQL)
- **Últimos logins**: Campo `last_login_at` em `users`

## 🤝 Contribuindo

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/amazing-feature`)
3. Commit suas mudanças (`git commit -m 'Add amazing feature'`)
4. Push para a branch (`git push origin feature/amazing-feature`)
5. Abra um Pull Request

## 📄 Licença

Este projeto está sob licença MIT. Veja `LICENSE` para detalhes.

## 👨‍💻 Autor

**Seu Nome**  
Email: seu.email@delicacy.com  
LinkedIn: linkedin.com/in/seu-perfil

## 🚧 Roadmap

### ✅ Sprint 1 (MVP)
- [x] Autenticação básica
- [x] Admin Delicacy dashboard
- [x] Admin Contratante dashboard
- [x] Cadastro de restaurantes
- [x] Estrutura de BD

### ⏳ Sprint 2
- [ ] Sistema de cardápios (CRUD)
- [ ] Geração de QR Code
- [ ] Sistema de pedidos
- [ ] Cálculo de comissões

### ⏳ Sprint 3
- [ ] Programa de fidelidade
- [ ] Notificações WhatsApp
- [ ] Dashboard de analytics
- [ ] API REST

### ⏳ Sprint 4+
- [ ] Integração com PagSeguro/MercadoPago
- [ ] App Mobile
- [ ] Multi-unidade
- [ ] Relatórios avançados

## ❓ FAQ

**P: Preciso de licença?**  
R: Não, é open-source MIT.

**P: Como adiciono novos usuários?**  
R: Através do formulário de cadastro em `/register.php`.

**P: Como mudo a senha padrão?**  
R: Login com admin@delicacy.com.br, vá para configurações (futuro).

**P: Onde vejo os logs?**  
R: Em `logs/error.log` e na tabela `admin_logs`.

## 📞 Suporte

Para dúvidas ou problemas:
- 📧 Email: support@delicacy.com
- 💬 GitHub Issues: [github.com/seu-user/delicacy/issues](https://github.com)
- 📱 WhatsApp: +55 (XX) XXXXX-XXXX

---

**Última atualização**: Janeiro 2024  
**Versão**: 1.0.0-alpha