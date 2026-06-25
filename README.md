# Delicacy

Plataforma SaaS para gestao gastronomica, cardapios digitais, pedidos, editor visual e acompanhamento operacional de restaurantes.

## Visao geral

A Delicacy conecta tres experiencias principais:

- Landing page institucional para apresentacao e conversao.
- Admin Delicacy para gestao global da plataforma.
- Admin Contratante para gestao do restaurante, pedidos, cardapios e metricas.
- Editor Visual para criacao e publicacao de cardapios digitais.
- Cardapio Digital publico para o cliente final realizar pedidos, acessar promocoes, carrinho e historico.

## Status do projeto

Projeto em fase de desenvolvimento/MVP academico.

Itens ja presentes no codigo:

- Autenticacao por sessao PHP.
- Controle de roles: `admin_delicacy`, `admin_restaurant`, `attendant`, `customer`.
- Cadastro de usuarios restaurantes.
- Criacao automatica de restaurante tecnico em plano `test` para contas contratantes.
- Painel Admin Delicacy.
- Painel Admin Contratante.
- Editor visual de cardapio.
- Upload de imagens em `public/uploads/cardapios`.
- Publicacao de URL publica do cardapio.
- Fluxo publico de cardapio, promocoes, carrinho e pedidos em teste.
- Registro de pedidos para visualizacao no painel do contratante.

## Tecnologias

- PHP 8+
- MySQL 8+
- HTML5
- CSS3
- JavaScript puro
- Apache com `mod_rewrite` recomendado para rotas amigaveis

## Requisitos locais

Antes de executar o projeto, confirme que o ambiente possui:

- PHP 8.0 ou superior.
- Extensao `mysqli` habilitada.
- Extensao `fileinfo` recomendada para validacao de uploads.
- MySQL 8.0 ou superior.
- Apache com `mod_rewrite` para ambiente mais fiel ao deploy.

## Configuracao do ambiente

Crie um arquivo `.env` proprio na raiz do projeto. O `.env` principal usado pelo mantenedor nao deve ser publicado nem compartilhado em repositorio publico.

Modelo seguro para desenvolvimento local:

```env
APP_ENV=development
DEBUG_MODE=true
BASE_URL=http://localhost:8000

DB_HOST=localhost
DB_USER=root
DB_PASS=sua_senha
DB_NAME=delicacy_db
DB_PORT=3306
```

Observacoes:

- Cada developer deve criar o seu proprio `.env`.
- Nunca versionar senhas, tokens, credenciais de banco ou chaves de API.
- O arquivo `.env` esta no `.gitignore` e deve continuar fora do Git.
- Em producao, as variaveis devem ser configuradas diretamente no servidor/hospedagem.

## Banco de dados

O schema real do banco de dados e considerado conteudo sensivel do projeto.

Para novos developers ou avaliadores, existem tres caminhos seguros:

1. Solicitar ao mantenedor um dump/schema privado pelo canal autorizado.
2. Usar um banco local ja preparado pelo time.
3. Receber um pacote de demonstracao sem dados sensiveis, quando disponibilizado.

## Execucao local

### Opcao recomendada: Apache

Configure o DocumentRoot para a pasta:

```text
public/
```

O arquivo `public/.htaccess` redireciona rotas nao fisicas para `public/index.php`, permitindo rotas amigaveis como:

```text
/admin_contratante/cardapios/novo
/admin_contratante/cardapios/{ID}/editar
/{nome_do_restaurante}_{numero}
```

### Opcao rapida: PHP built-in server

Para testar rotas fisicas:

```bash
php -S localhost:8000 -t public
```

Acesse:

```text
http://localhost:8000
```

Observacao: o servidor embutido do PHP nao interpreta `.htaccess`. Para validar 100% das rotas amigaveis, prefira Apache ou configure um router local equivalente.

## Usuarios de teste

### Contas de restaurante

Novas contas podem ser criadas em:

```text
/register.php
```

Ao cadastrar um usuario restaurante, o sistema:

- cria o usuario com role `admin_restaurant`;
- cria automaticamente um restaurante tecnico de teste;
- configura o restaurante no plano `test`;
- libera a criacao de 1 cardapio para experimentacao.

Contas locais como `teste123` e `teste321`, quando existirem no banco da maquina de desenvolvimento, devem permanecer configuradas como plano `test`.

## Links da entrega final

Preencher antes da entrega:

| Item | Link |
| --- | --- |
| Landing page no GitHub Pages | Preparada em `docs/`; URL prevista: `https://edulps1.github.io/Delicacy/` |
| Produto final implantado | A preencher |
| Video demonstrativo | A preencher |
| Apresentacao final | A preencher |
| Repositorio do projeto | A preencher |
| Repositorio da disciplina | A preencher |

## Fluxo de uso para demonstracao

1. Acessar a landing em `/`.
2. Fazer login como Admin Delicacy.
3. Visualizar restaurantes, usuarios e metricas globais.
4. Criar ou acessar uma conta restaurante.
5. Entrar no Admin Contratante.
6. Criar um novo cardapio.
7. Editar categorias, produtos e promocoes no Editor Visual.
8. Publicar o cardapio.
9. Abrir a URL publica gerada.
10. Adicionar itens ao carrinho.
11. Finalizar pedido em modo teste.
12. Ver o pedido aparecer no painel do contratante.
13. Alterar status do pedido no painel.
14. Conferir reflexos em clientes, pedidos, dashboard e metricas.

## Seguranca

Implementado ou previsto no codigo:

- Senhas com bcrypt.
- Prepared statements via MySQLi.
- CSRF tokens em formularios.
- Sessao PHP com timeout.
- Separacao por roles.
- Validacao de senha forte no cadastro.
- Validacao de ownership em fluxos de cardapio e restaurante.
- Logs administrativos em `admin_logs`.

Pontos recomendados para evolucao:

- Rate limit no login.
- Revisao final de permissoes multi-tenant.
- Configuracao de ambiente de producao com `DEBUG_MODE=false`.
- Protecao para arquivos de upload em ambiente publico.

## Licenca

Projeto academico desenvolvido para fins de demonstracao e avaliacao.
