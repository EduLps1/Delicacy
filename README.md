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
