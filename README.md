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

## Estrutura do projeto

```text
Delicacy/
|-- config/
|   |-- config.php
|   |-- constants.php
|   `-- database.php
|-- database/
|   `-- schema.sql        # privado/local, nao publicar em repositorio publico
|-- public/
|   |-- index.php
|   |-- login.php
|   |-- register.php
|   |-- logout.php
|   |-- admin-delicacy/
|   |-- admin-contratante/
|   |-- css/
|   |-- js/
|   |-- images/
|   `-- uploads/
|-- src/
|   |-- controllers/
|   |-- models/
|   |-- services/
|   `-- views/
|-- logs/
|-- tmp/
|-- .env                  # privado/local, nao publicar em repositorio publico
`-- README.md
```

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

Por isso, arquivos como:

```text
database/schema.sql
delicacy_schema.sql
```

nao devem ser publicados em repositorio publico.

Para novos developers ou avaliadores, existem tres caminhos seguros:

1. Solicitar ao mantenedor um dump/schema privado pelo canal autorizado.
2. Usar um banco local ja preparado pelo time.
3. Receber um pacote de demonstracao sem dados sensiveis, quando disponibilizado.

Fluxo local com schema privado:

```bash
mysql -u root -p < database/schema.sql
```

Ou manualmente, quando o arquivo privado estiver disponivel:

```sql
CREATE DATABASE delicacy_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE delicacy_db;
SOURCE database/schema.sql;
```

O schema cria as tabelas principais:

- `users`
- `restaurants`
- `menus`
- `menu_items`
- `customers`
- `orders`
- `order_items`
- `commissions`
- `admin_logs`

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

### Admin global

Em ambiente de demonstracao, o mantenedor pode disponibilizar um usuario admin global.

Credencial usada no ambiente local de desenvolvimento:

```text
Email: admin@delicacy.com.br
Senha: password
Role: admin_delicacy
```

Antes de publicar ou implantar o produto, essa senha deve ser alterada/removida e substituida por credenciais seguras do ambiente.

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

## Rotas principais

### Landing

| Rota | Metodo | Descricao |
| --- | --- | --- |
| `/` | GET | Landing page institucional |

### Autenticacao

| Rota | Metodo | Descricao |
| --- | --- | --- |
| `/login.php` | GET | Tela de login |
| `/login.php` | POST | Processa login |
| `/register.php` | GET | Tela de cadastro |
| `/register.php` | POST | Cria usuario restaurante |
| `/logout.php` | GET/POST | Encerra sessao |
| `/forgot-password.php` | GET/POST | Tela base de recuperacao de senha |

### Admin Delicacy

| Rota | Metodo | Descricao |
| --- | --- | --- |
| `/admin-delicacy/dashboard.php` | GET | Dashboard global da plataforma |
| `/admin-delicacy/restaurants.php` | GET/POST | Listagem e controle de restaurantes |
| `/admin-delicacy/users.php` | GET | Listagem de usuarios |
| `/admin-delicacy/financial.php` | GET | Visao financeira |
| `/admin-delicacy/settings.php` | GET | Configuracoes |
| `/admin-delicacy/restaurant-dashboard.php` | GET | Visualizacao de restaurante especifico |

### Admin Contratante

| Rota | Metodo | Descricao |
| --- | --- | --- |
| `/admin-contratante/` | GET | Dashboard do restaurante |
| `/admin-contratante/dashboard.php` | GET | Alias do dashboard |
| `/admin-contratante/pedidos.php` | GET | Lista pedidos recebidos |
| `/admin-contratante/pedidos.php` | POST | Atualiza status de pedido |
| `/admin-contratante/cardapios.php` | GET | Lista cardapios do restaurante |
| `/admin-contratante/cardapio.php` | GET/POST | Gerencia cardapios e acoes rapidas |
| `/admin-contratante/cardapio-novo.php` | GET/POST | Cria novo cardapio |
| `/admin-contratante/cardapio-editar.php` | GET/POST | Fluxo legado de edicao |
| `/admin-contratante/cadastrar-restaurante.php` | GET/POST | Cadastro manual de restaurante, quando usado |
| `/admin-contratante/editar-restaurante.php` | GET/POST | Edicao manual de restaurante |

### Editor Visual

Rotas amigaveis tratadas pelo front controller `public/index.php`:

| Rota | Metodo | Descricao |
| --- | --- | --- |
| `/admin_contratante/cardapios/novo` | GET | Abre editor visual em modo criacao |
| `/admin_contratante/cardapios/{ID}/editar` | GET | Abre editor visual para o cardapio informado |
| `/admin_contratante/cardapios/api` | POST | API do editor visual |

O `{ID}` do editor visual segue o padrao:

```text
[A-Z0-9]{5}
```

A API do editor usa o campo `action` no POST:

| Action | Descricao |
| --- | --- |
| `save_editor` | Salva estado do editor |
| `publish_editor` | Publica cardapio e gera URL publica |
| `upload_editor_asset` | Envia imagem do editor |

### Cardapio publico

| Rota | Metodo | Descricao |
| --- | --- | --- |
| `/menu.php?slug={slug}` | GET | Abre cardapio publico por slug |
| `/pedido.php` | POST | Cria pedido publico |
| `/pedido-confirmacao.php` | GET | Confirma pedido |
| `/{nome_do_restaurante}_{numero}` | GET | URL publica amigavel do cardapio publicado |

Exemplo de URL publica esperada:

```text
http://localhost:8000/anteiku_cafeteria_1
```

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

## Regras de negocio importantes

### Plano test

O plano `test` e usado para experimentacao local e demonstracao.

Regras atuais:

- O restaurante de teste e criado automaticamente no cadastro/login do contratante.
- O plano test permite 1 cardapio.
- Se o limite for atingido, o usuario deve publicar ou excluir o rascunho existente antes de criar outro.

### Exclusao segura de cardapio

No Admin Contratante:

- Cardapio em rascunho exige digitacao exata de `CONFIRMAR`.
- Cardapio publicado exige senha do usuario e digitacao exata de `CONFIRMAR`.

### Publicacao de cardapio

Ao publicar, o sistema gera:

- `publication_number`: numero sequencial por restaurante.
- `public_url`: URL amigavel no formato `{restaurante}_{numero}`.

## Uploads

Uploads de imagens ficam em:

```text
public/uploads/cardapios/
```

Tipos recomendados:

- JPG
- PNG
- WEBP

Tamanho maximo configurado:

```text
5 MB
```

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
- Testes automatizados para controllers e models.
- Configuracao de ambiente de producao com `DEBUG_MODE=false`.
- Protecao para arquivos de upload em ambiente publico.

## Preparacao para entrega academica

Checklist baseado no documento de orientacoes finais:

- [ ] Remover do versionamento publico arquivos sensiveis como `.env`, `database/schema.sql`, `delicacy_schema.sql` e dumps reais.
- [x] Preparar landing page estatica em `docs/`.
- [ ] Publicar landing page no GitHub Pages.
- [x] Inserir URL prevista da landing neste README.
- [ ] Implantar versao final do produto.
- [ ] Inserir URL do produto neste README.
- [ ] Gravar video demonstrativo do produto.
- [ ] Inserir link do video neste README.
- [ ] Criar apresentacao final.
- [ ] Adicionar arquivo da apresentacao ao repositorio.
- [ ] Garantir que os links no repositorio da disciplina apontem para artefatos internos ou oficiais do projeto.
- [ ] Revisar instrucoes de instalacao.
- [ ] Revisar credenciais de teste.

## Roteiro sugerido para video

1. Apresentar problema: restaurantes precisam centralizar pedidos, cardapio, promocoes e gestao.
2. Mostrar landing page.
3. Login no Admin Delicacy.
4. Mostrar visao global.
5. Login ou acesso ao Admin Contratante.
6. Mostrar dashboard do restaurante.
7. Criar/editar cardapio no Editor Visual.
8. Criar produto e promocao.
9. Publicar cardapio.
10. Abrir cardapio publico como cliente.
11. Adicionar item/promocao ao carrinho.
12. Finalizar pedido teste.
13. Voltar ao Admin Contratante e atualizar status do pedido.
14. Encerrar com os ganhos da plataforma.

## Roteiro sugerido para apresentacao final

### Parte 1 - Experiencia na disciplina

- Importancia das APG-1 e APG-2.
- Experiencia com Aprendizagem Baseada em Projetos.
- Aplicacao de metodologias ageis.
- Organizacao do time.
- Uso de Git, GitHub e GitFlow.
- Desafios enfrentados.
- Solucoes adotadas.
- Pontos positivos e negativos.
- Nota do grupo para a experiencia.
- Apresentacao do produto Delicacy.
- Sugestoes de melhoria para a disciplina.

### Parte 2 - Prova de conhecimento

- Como entender um problema novo.
- Como levantar requisitos.
- Como priorizar requisitos.
- Como validar requisitos.
- Como escolher tecnologias.
- Como planejar sprints.
- Como definir valor por iteracao.
- Importancia do MVC.
- Importancia de API REST.
- Importancia de testes unitarios.
- Importancia do controle de versao.
- Como funciona o GitFlow.
- O que e uma release.

## Observacoes para deploy

- Em producao, apontar o servidor web para `public/`.
- Definir `DEBUG_MODE=false`.
- Usar credenciais de banco fora do repositorio.
- Garantir permissao de escrita em `logs/` e `public/uploads/`.
- Revisar se `.env` esta fora do versionamento.
- Revisar se schemas/dumps reais estao fora do versionamento publico.
- Configurar HTTPS.

## Publicacao da landing no GitHub Pages

A landing publicada no GitHub Pages fica separada do backend PHP.

Arquivos preparados para publicacao:

```text
docs/
|-- index.html
|-- .nojekyll
|-- css/
`-- images/
```

Como publicar:

1. Subir a pasta `docs/` para o GitHub.
2. No repositorio GitHub, abrir `Settings`.
3. Entrar em `Pages`.
4. Em `Build and deployment`, escolher `Deploy from a branch`.
5. Selecionar a branch principal da entrega.
6. Selecionar a pasta `/docs`.
7. Salvar.

URL esperada apos ativacao:

```text
https://edulps1.github.io/Delicacy/
```

Observacao: a versao estatica usa CTAs apontando para `#planos`, porque o GitHub Pages nao executa PHP. Quando a URL do produto implantado estiver pronta, os CTAs da landing podem ser apontados para esse ambiente final.

## Arquivos sensiveis e Git

Os arquivos abaixo nao devem ser publicados:

- `.env`
- `database/schema.sql`
- `delicacy_schema.sql`
- dumps reais de banco (`*.sql`)
- uploads reais de usuarios/clientes
- logs de ambiente

Se algum desses arquivos ja estiver rastreado pelo Git, remova apenas do indice antes de publicar:

```bash
git rm --cached .env database/schema.sql delicacy_schema.sql
```

Depois confirme com:

```bash
git status
```

Importante: `git rm --cached` remove o arquivo do versionamento, mas mantem a copia local na maquina.

## Licenca

Projeto academico desenvolvido para fins de demonstracao e avaliacao.
