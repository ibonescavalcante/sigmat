# Relatório de alterações — segurança e hardening do painel

Este documento resume as mudanças aplicadas ao projeto **DMI-ADMIN** (roteamento, sessão, CSRF, APIs e correções relacionadas).

---

## 1. Roteamento e autenticação (`app/routes/Router.php`)

- Removida a lista ampla `$rotasPublicas` que tratava quase todo o painel e APIs como públicas.
- Implementada regra explícita: rotas **sem** sessão do painel apenas:
  - **GET:** `/`, `/login`, `/dashboard/login`, `/dashboard/logout`
  - **POST:** `/dashboard/login`
- Utilizador não autenticado:
  - em URIs que começam com `/api/`: resposta **401** JSON (`Não autenticado`);
  - demais casos: **redirect** para `/dashboard/login`.
- **CSRF:** para todo **POST** exceto `/dashboard/login`, com sessão ativa, validação do token (header `X-CSRF-Token` ou campo `csrf_token`). Falha: **403** JSON nas APIs ou redirect com mensagem em sessão nas páginas HTML.
- **Sessão única do painel:** após `estaLogadoDashboard()` em rotas que exigem autenticação do painel, validação do vínculo `SessionSecurity::validarVinculoSessaoDashboard()`; se falhar: destruição da sessão local e **redirect** para `/dashboard/login?sessao=substituida` ou **401** JSON em `/api/...` com mensagem de sessão substituída.
- **POST** `/configuracoes/usuario`: criação de novo utilizador do painel (`DashboardController::criar_usuario`), com JSON e CSRF.
- Tratamento de erros no `Router::load` e no `execute`: com `APP_DEBUG=false` mensagens genéricas; com `APP_DEBUG=true` detalhes para desenvolvimento.
- Códigos HTTP no `execute`: distinção aproximada entre 404 (rota / ficheiro) e 500 (outros erros) quando não está em modo debug.

---

## 2. Sessão e CSRF (`app/middleware/SessionSecurity.php`)

- `estaLogadoDashboard()`: considera autenticado o painel quando existe `$_SESSION['user']['id']` numérico.
- `ensureDashboardCsrfToken()` / `regenerateDashboardCsrfToken()` / `obterTokenCsrfDashboard()`: gestão do token CSRF do painel.
- `validarDashboardCsrf()`: comparação segura com `hash_equals` entre sessão e pedido (header ou POST).
- `validarVinculoSessaoDashboard()`: compara `$_SESSION['user']['dashboard_sessao_token']` com o token em `pss.usuario_sessao_dashboard` (sessão única); throttle de atualização de `ultimo_acesso` (~60 s). Se não há login no painel, retorna `true` (no-op).

---

## 3. Controllers

### `app/controllers/LoginDashboardController.php`

- No `index`, redirect para `/dashboard` se já autenticado; mensagem opcional quando `?sessao=substituida` (conta acedida doutro local).
- Após login bem-sucedido: `session_regenerate_id(true)`, token `dashboard_sessao_token`, UPSERT em `UsuarioSessaoDashboard`, `SessionSecurity::regenerateDashboardCsrfToken()`.
- **Logout:** remove a linha em `usuario_sessao_dashboard` apenas se o vínculo da sessão ainda for válido (evita apagar a sessão ativa de quem acabou de entrar).
- Tratamento de erro de base de dados no login com mensagem genérica ao utilizador.

### `app/controllers/DashboardController.php`

- Após validar `$_SESSION['user']`, chamada a `SessionSecurity::ensureDashboardCsrfToken()`.
- Import de `SessionSecurity`.
- **`configuracoes()`:** envia listagem de utilizadores e opções de perfil para a view.
- **`criar_usuario()`:** API JSON (POST) para criar utilizador com validação de campos, CSRF, e respostas 422/403/500 conforme o caso.

### `app/controllers/ApiController.php`

- Novo método **`setAvaliacaoTitulo()`**: grava pontuação de título via API JSON, usando `InscricaoDashboard::set_inscricao_pontuacao_titulo(..., true)` para erros em contexto API.
- **`set_inscricao_status`** e **`set_recursos_status`:** parâmetro opcional da rota (`$routeInscricaoId` / `$routeRecursoId`) para compatibilidade com o despacho do router.
- **`set_inscricao_pontuacao`:** validação de `usuario_id` antes de prosseguir.
- **`alterarSenha`:** removido o fallback inseguro `?? 24` no ID do utilizador.

---

## 4. Modelo e base de dados

### `app/models/InscricaoDashboard.php`

- `set_inscricao_pontuacao_titulo(..., bool $apiContext = false)`: em caso de erro, se `$apiContext === true`, relança a exceção em vez de fazer apenas redirect.

### `app/models/Usuario.php`

- `verificarSenha`: retorno seguro quando o utilizador não existe ou não há `senha_hash`.
- **`PERFIS_VALIDOS`**, **`listarParaPainel()`**, **`emailJaCadastrado()`**, **`criarUsuarioPainel()`:** suporte à tabela `pss.usuario` com `telefone`, `perfil` (enum `pss.perfil_usuario`), `ativo`, timestamps.
- **`buscarPorUauario` / `autentica_uauario`:** incluem `ativo` na leitura; login recusado para conta inativa; e-mail normalizado com `lower(trim(...))` na pesquisa.

### `app/models/UsuarioSessaoDashboard.php` (novo)

- UPSERT / SELECT / DELETE / `tocarUltimoAcesso` na tabela `pss.usuario_sessao_dashboard` (sessão única do painel).

### `app/core/Database.php`

- Em falha de conexão: `error_log` com detalhe; mensagem ao cliente depende de `APP_DEBUG` (genérica em produção).

---

## 5. Views (painel)

### `app/views/dashboard/header.php`

- Garantia de token CSRF e `<meta name="csrf-token">` para utilizadores autenticados.
- Função JS global `getDashboardCsrfToken()`.
- Pedido `fetch` a `/api/alterar-senha` com `credentials: "same-origin"` e header `X-CSRF-Token`.

### `app/views/dashboard/inscricoes/page.php` e `recursos/page.php`

- `fetch` POST com `credentials: "same-origin"` e `X-CSRF-Token`.

### `app/views/dashboard/inscricoes/detalhes.php`

- `fetch` a `/api/avaliacao-titulo` com CSRF e `credentials`.
- Campos hidden `csrf_token` nos formulários POST (pontuação, status, documento, exclusão de pontuação).

### `app/views/dashboard/recursos/detalhes.php`

- Campo hidden `csrf_token` no formulário POST.

### `app/views/dashboard/login/page.php`

- Formulário com `action="/dashboard/login"` e `method="post"`, id `loginForm`.
- Removido o JavaScript que interceptava o submit com validação fictícia (impedia o login real).
- Mantidos alternar senha / “lembrar-me” de forma compatível com envio real do formulário.

### `app/views/dashboard/configuracoes/page.php`

- Aba **Usuários:** tabela alimentada pela base de dados; botão **Novo utilizador** com modal (nome, e-mail, telefone, perfil, senha, confirmação, ativo).
- Envio por `fetch` POST para `/configuracoes/usuario` com `Content-Type: application/json` e header `X-CSRF-Token` (via `getDashboardCsrfToken()`).
- Remoção de fechos duplicados `</body></html>`; correção na restauração da última aba (Bootstrap Tab).

---

## 6. Configuração de exemplo

### `.env.exemple`

- Adicionada variável **`APP_DEBUG=false`** com comentário explicativo.

---

## 7. Limpeza anterior (sessão de refatoração)

- Remoção de imports/propriedade não usados em `DashboardController` e `ApiController`.
- Remoção de entradas órfãs em listas de rotas públicas e `use` não utilizado no `Router` (antes da reescrita completa do ficheiro).
- Eliminação de ficheiros de view duplicados: `detalhes copy.php` e `detalhes copy 2.php` em `app/views/dashboard/inscricoes/`.

---

## 8. Sessão única do painel (resumo)

- **SQL:** `database/sql/001_usuario_sessao_dashboard.sql` — criar a tabela antes do login do painel em ambientes novos.
- **Documentação detalhada:** [docs/MODIFICACOES-SESSAO-UNICA-DASHBOARD.md](docs/MODIFICACOES-SESSAO-UNICA-DASHBOARD.md).

---

## 9. Criação de utilizadores em Configurações (resumo)

- **Rota:** POST `/configuracoes/usuario` (alinhada à página GET `/configuracoes`).
- **Requisitos na base:** colunas em `pss.usuario` conforme o modelo (`nome`, `email`, `telefone`, `perfil`, `senha_hash`, `ativo`, `criado_em`, `atualizado_em`); o enum `pss.perfil_usuario` deve incluir os valores usados em `Usuario::PERFIS_VALIDOS` (por defeito: `comissao`, `administrador`, `visualizador`).
- **Apenas administrador:** criar utilizador (`criar_usuario`) exige perfil `administrador` na BD (`Usuario::perfilPorId`); caso contrário resposta **403** JSON.

---

## 10. Perfil na sessão, edição de utilizadores e restrição administrador

- **Login:** `Usuario::buscarPorUauario` passa a incluir `perfil::text` em `pss.usuario`; `$_SESSION['user']['perfil']` é preenchido no login.
- **Modelo:** `Usuario::perfilPorId`, `Usuario::buscarParaEdicaoPainel`, `Usuario::atualizarUsuarioPainel` (senha opcional no update).
- **Controller:** `DashboardController::garantirAdministradorPainelApi()` e `usuarioLogadoEhAdministrador()` (revalidação via BD); `configuracoes()` envia `pode_gerir_usuarios` à view; `atualizar_usuario()` para alterações.
- **Rotas:** POST `/configuracoes/usuario/atualizar` → `DashboardController::atualizar_usuario`.
- **View:** em `/configuracoes`, utilizadores não administradores veem aviso informativo e lista só leitura; administradores têm “Novo utilizador”, modal de criação, botão editar e modal de edição (com `fetch` e CSRF).

---

## Como validar

1. Definir `APP_DEBUG=false` em produção.
2. Login em `/dashboard/login`, navegar no painel e confirmar que listagens (`/api/inscricoes`, `/api/recursos`, etc.) e formulários funcionam.
3. Sem sessão, aceder a `/dashboard` ou `/api/...` e confirmar redirect ou 401 JSON.
4. Opcional: remover o meta CSRF no browser e submeter um POST — deve falhar com CSRF inválido.
5. **Sessão única:** aplicar o SQL da tabela `usuario_sessao_dashboard`; login com o mesmo utilizador noutro browser e confirmar que o primeiro perde a sessão (redirect com `sessao=substituida` ou 401 nas APIs).
6. **Novo utilizador:** em `/configuracoes`, aba Usuários, criar utilizador e confirmar que aparece na tabela e que o login funciona (e que inativo não entra).
7. **Permissões:** com perfil comissão ou visualizador, confirmar que não aparecem botões de criar/editar e que `POST /configuracoes/usuario` devolve **403** JSON.
8. **Edição:** como administrador, editar um utilizador (e opcionalmente alterar senha) e confirmar persistência após reload.

---

*Documento gerado para acompanhar o hardening do painel administrativo e rotas associadas. Atualizado com sessão única e gestão de utilizadores em Configurações.*
