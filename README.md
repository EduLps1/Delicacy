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
