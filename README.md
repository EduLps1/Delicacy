develop
# Engenharia_de_Software
Repositório destinado ao trabalho prático da matéria de Engenharia de Software.

# Backend PHP Nativo

API em PHP puro seguindo MVC para começar a migração do projeto.

## Estrutura (MVC)

- `app/Controllers`
- `app/Models`
- `app/Views`
- `config/database.php`
- `routes/web.php`
- `public/index.php`

## Requisitos

- PHP 8.1+

## Como rodar

No diretório `backend-php`:

```bash
php -S localhost:8000 -t public
```

## Endpoints

- `GET /api/health`
- `GET /api/menu`

## Próximos passos da migração

1. Trocar dados em memória por MySQL (PDO).
2. Criar endpoints de pedidos (`POST /api/orders`, `GET /api/orders/:id`).
3. Adicionar autenticação e validações.

# 📊 DELICACY - Especificação Detalhada Completa

## 1️⃣ Visão Geral do Projeto
**Delicacy** é uma plataforma **SaaS (Software as a Service)** para gestão e hospedagem de cardápios digitais de restaurantes.  
A plataforma permite que restaurantes criem, gerenciem e monetizem cardápios digitais (delivery e presencial) com sistema integrado de pedidos, métricas e fidelidade.

### 🎯 Modelo de Negócio
- **Comissão variável por cardápio** (baseada em faturamento)  
- **Plano mensal + comissão** (para baixo faturamento)  
- **Comissão pura** (para alto faturamento)  

---

## 2️⃣ Arquitetura Técnica

### 🖥️ Stack Definido
- **Backend:** PHP (puro, sem framework inicialmente)  
- **Frontend:** HTML + CSS (sem JavaScript framework)  
- **Banco de Dados:** MySQL  
- **Hospedagem MVP:** DigitalOcean VPS  

### 💳 Pagamentos
- Definir posteriormente: **MercadoPago / PagSeguro**

### 📲 Notificações WhatsApp
- **MVP:** Twilio  
- **Produção:** Evolution API  

### 🔗 QR Code
- Biblioteca PHP: **phpqrcode**

main
