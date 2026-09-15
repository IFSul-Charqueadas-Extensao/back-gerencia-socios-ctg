# Autenticação e níveis de permissão

Como o login e os perfis funcionam, e como testar.

---

## Resumo

Antes, a API era aberta: qualquer requisição chegava direto ao Controller.
Agora **toda rota exige um token JWT**, e o que cada usuário pode fazer depende
do seu perfil.

Quatro perfis:

| Perfil | Pode alterar |
|---|---|
| `admin` | tudo, inclusive os usuários do sistema |
| `socios` | sócios, dependentes, categorias e cartão tradicionalista |
| `financeiro` | mensalidades e pagamentos |
| `consulta` | nada — apenas visualiza |

**Ler é livre** para qualquer usuário autenticado, em todos os recursos.
A única exceção é `/usuarios`, que só o `admin` enxerga.

---

## Como funciona

### O caminho de uma requisição

```
index.php
   │
   ├─ 1. Autenticacao::ehPublica()   → é login/refresh/logout? passa direto
   ├─ 2. Autenticacao::autenticar()  → valida o token       (401 se falhar)
   ├─ 3. Permissao::exigir()         → confere o perfil      (403 se falhar)
   │
   └─ 4. switch → Controller → Service → Repository → banco
```

Os passos 2 e 3 ficam **uma única vez no `index.php`, antes do `switch`** —
não dentro dos Controllers. Como os perfis são definidos por recurso, e o
`index.php` já usa o recurso e o método HTTP para rotear, ele tem exatamente a
informação necessária. A vantagem prática: **um recurso novo já nasce
protegido**, sem depender de alguém lembrar de checar a permissão.

Ambos lançam `APIException`, que o handler do `src/config.php` já transforma
em JSON — nada mudou no tratamento de erros.

### Arquivos criados

| Arquivo | O que faz |
|---|---|
| `src/Util/Jwt.php` | Gera e valida o token (HS256, com `hash_hmac`) |
| `src/Util/Permissao.php` | A tabela de perfis acima, em código |
| `src/Util/Env.php` | Lê o `.env` (antes só o `Database` fazia isso) |
| `src/Http/Autenticacao.php` | Pega o token do cabeçalho e identifica o usuário |
| `src/Model/Usuario.php` | A entidade |
| `src/Repository/UsuarioRepository.php` | SQL dos usuários |
| `src/Repository/RefreshTokenRepository.php` | SQL dos refresh tokens |
| `src/Service/AuthService.php` | Login, refresh e logout |
| `src/Service/UsuarioService.php` | Regras de usuário (validações) |
| `src/Controller/AuthController.php` | Rotas `/auth/*` |
| `src/Controller/UsuarioController.php` | CRUD de `/usuarios` |

Segue o mesmo padrão em camadas do resto do projeto:
`Controller → Service → Repository → Database`.

### Detalhes que valem saber

**O token vai em `X-Auth-Token`, não em `Authorization`.**
O servidor do IFSul usa HTTP Basic Auth, que já ocupa o `Authorization`.
Mandar `Bearer` por cima colidiria com a autenticação do próprio Apache.
`Authorization: Bearer` é aceito como alternativa, útil no Postman local.

**JWT escrito à mão.** O projeto não usa Composer (ver `CLAUDE.md`), então
`Util/Jwt.php` implementa HS256 com `hash_hmac`, que é nativo do PHP. São
cerca de 70 linhas. A comparação da assinatura usa `hash_equals`, que compara
em tempo constante.

**O usuário é relido do banco a cada requisição**, em vez de confiar só no que
está escrito no token. Assim, mudar o perfil de alguém ou desativar a conta
vale na hora, sem esperar o token expirar.

**Refresh tokens são guardados como sha256**, nunca em texto puro. Se a tabela
vazar, os tokens não podem ser reutilizados.

**Senhas usam `password_hash` (bcrypt)** e nunca saem da API: o
`jsonSerialize()` do `Model/Usuario` simplesmente não inclui o campo.

### Tabelas novas

Criadas por `src/Database/migrations/001_auth.sql`:

- **`usuarios`** — nome, email (único), `senha_hash`, `role`, `ativo`
- **`refresh_tokens`** — o hash do token, prazo de validade e se foi revogado

> O `schema.sql` principal usa `CREATE TABLE` sem `IF NOT EXISTS` e falha se
> reaplicado num banco que já tem dados. Por isso a migração é um arquivo
> separado, feito para rodar sobre um banco em uso. O `schema.sql` também
> recebeu as tabelas, para instalações do zero.

### Configuração (`.env`)

```ini
JWT_SECRET=...              # gere o seu (ver abaixo)
JWT_EXPIRA_MINUTOS=60       # validade do access token
REFRESH_EXPIRA_DIAS=7       # validade do refresh token
```

Para gerar o segredo (funciona em qualquer sistema, basta ter o PHP no PATH):

```bash
php -r "echo bin2hex(random_bytes(32));"
```

O `.env` **não é versionado**: quem clonar ou der `git pull` precisa criar o seu
a partir do `.env.example`. Sem `JWT_SECRET`, o login responde
`500 Variável JWT_SECRET não definida no .env!`.

---

## Endpoints

```
POST   /api/auth/login     { email, senha }  → { access_token, refresh_token, usuario }
POST   /api/auth/refresh   { refresh_token } → { access_token }
POST   /api/auth/logout    { refresh_token } → 204
GET    /api/auth/me                          → dados do usuário logado

GET    /api/usuarios          (admin)
POST   /api/usuarios          (admin)
PUT    /api/usuarios/:id      (admin)
DELETE /api/usuarios/:id      (admin)
```

Não exigem token: `/auth/login`, `/auth/refresh`, `/auth/logout` e a raiz `/`.

> `logout` é público de propósito: quem se autentica é o próprio refresh token
> enviado no corpo. Exigir um access token válido impediria encerrar a sessão
> depois que ele expirasse — o refresh token ficaria ativo até o prazo acabar.

---

## Como testar

### 1. Preparar

Rode a partir da raiz do repositório do back, pelo **Shell do XAMPP** no
Windows (ou pelo terminal comum em Linux/macOS):

```bash
# banco (uma vez): cria as tabelas e os usuários de teste
mysql -u ctg_user -p ctg -e "source src/Database/migrations/001_auth.sql"
mysql -u ctg_user -p ctg -e "source src/Database/seed.sql"

# servidor
php -S localhost:8000 index.php
```

Os dois arquivos podem ser reexecutados quantas vezes quiser: a migração usa
`CREATE TABLE IF NOT EXISTS` e o seed limpa os dados antes de inserir.

> Usamos `-e "source arquivo.sql"` em vez de `< arquivo.sql` porque o `<` não
> existe no PowerShell. Se algum comando falhar, veja
> [Problemas comuns](../README.md#-problemas-comuns) no README.

### 2. Usuários de teste

Criados pelo `src/Database/seed.sql`:

| E-mail | Senha | Perfil |
|---|---|---|
| `admin@ctg.local` | `admin123` | admin |
| `socios@ctg.local` | `socios123` | socios |
| `financeiro@ctg.local` | `financeiro123` | financeiro |
| `consulta@ctg.local` | `consulta123` | consulta |

> Senhas públicas, só para desenvolvimento. **Troque antes de subir para
> produção** e apague os usuários que não forem usados.

### 3. Rodar os testes

Instale a extensão **REST Client** no VSCode e abra:

- **`test_auth.http`** — login, sessão e a matriz de permissões
- **`test_usuarios.http`** — CRUD de usuários e validações

**Rode primeiro os blocos de LOGIN no topo do arquivo.** Eles preenchem as
variáveis de token (`@adminToken`, `@sociosToken`, ...) que os testes seguintes
usam. Depois é só clicar em "Send Request" em cada bloco — o código esperado
está no comentário acima de cada um.

### 4. O que conferir

**Autenticação**

| Teste | Esperado |
|---|---|
| `GET /api/socios` sem token | 401 |
| Token inválido ou adulterado | 401 |
| Token expirado | 401 |
| Senha errada no login | 401 `Credenciais inválidas!` |
| E-mail inexistente no login | 401 **com a mesma mensagem** |

> As duas últimas mensagens são iguais de propósito: se fossem diferentes,
> daria para descobrir quais e-mails estão cadastrados.

**Permissões**

| Teste | Esperado |
|---|---|
| `GET /api/socios` com qualquer perfil | 200 |
| `POST /api/socios` com `consulta` | 403 |
| `POST /api/socios` com `financeiro` | 403 |
| `POST /api/socios` com `socios` | 201 |
| `POST /api/pagamentos` com `socios` | 403 |
| `POST /api/pagamentos` com `financeiro` | 201 |
| `GET /api/usuarios` sem ser admin | 403 |

**Segurança**

| Teste | Esperado |
|---|---|
| Resposta de `/auth/me` e `/usuarios` | sem o campo `senha_hash` |
| Refresh depois do logout | 401 (foi revogado) |
| Usar o token de alguém que foi desativado | 403 na hora |

### 5. Teste manual rápido pela linha de comando

**Linux, macOS ou Git Bash** — aspas simples no corpo JSON:

```bash
curl -X POST http://localhost:8000/api/auth/login -H "Content-Type: application/json" -d '{"email":"consulta@ctg.local","senha":"consulta123"}'

# use o access_token devolvido acima
curl http://localhost:8000/api/socios -H "X-Auth-Token: COLE_O_TOKEN_AQUI"
```

**PowerShell** — ali `curl` é apelido de `Invoke-WebRequest` e tem outra
sintaxe, então use os cmdlets nativos:

```powershell
$r = Invoke-RestMethod -Uri http://localhost:8000/api/auth/login -Method Post -ContentType 'application/json' -Body '{"email":"consulta@ctg.local","senha":"consulta123"}'

# perfil consulta lendo -> funciona
Invoke-RestMethod -Uri http://localhost:8000/api/socios -Headers @{ 'X-Auth-Token' = $r.access_token }

# perfil consulta tentando gravar -> 403
Invoke-RestMethod -Uri http://localhost:8000/api/categorias -Method Post -Headers @{ 'X-Auth-Token' = $r.access_token } -ContentType 'application/json' -Body '{"nome":"Teste","valor_sociedade":1,"valor_instrutor":1}'
```

> Quem preferir não lidar com shell: os arquivos `.http` do passo 3 fazem os
> mesmos testes dentro do VSCode, igual em qualquer sistema.

---

## No frontend

O front tem uma cópia da tabela de perfis em `src/utils/permissoes.js`, mas ela
serve **apenas para esconder botões**. Quem decide de fato é o backend, que
valida token e perfil em toda requisição.

Por isso vale testar contornando a interface (com o `curl` acima, por exemplo):
mesmo sem o botão na tela, a API recusa a operação.

**Ao mudar a tabela de perfis, altere os dois arquivos:**
`src/Util/Permissao.php` (aqui) e `src/utils/permissoes.js` (no front).
