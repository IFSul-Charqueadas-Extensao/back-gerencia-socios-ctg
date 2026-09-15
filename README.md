# 📊 Sistema de Gestao CTG Raizes da (Backend)

## Tecnologias necessarias

Xampp

## ⚙️ Configuração do Ambiente

> **Sobre os comandos `mysql` deste guia:** rode-os pelo **Shell do XAMPP**
> (botão "Shell" no painel), que já deixa o `mysql` pronto para uso, a partir da
> raiz deste repositório. Em Linux e macOS o `mysql` costuma já estar disponível
> no terminal comum.
>
> Se algum comando falhar, veja [Problemas comuns](#-problemas-comuns) no fim.

### 1. Clone o repositório

```bash
git clone <url-do-repositorio>
cd <nome-do-projeto>
```

---

### 2. Configurar as variáveis de ambiente

Copie o arquivo de exemplo e preencha com as suas credenciais locais:

```bash
# Linux, macOS, Git Bash ou PowerShell
cp .env.example .env
```

No **CMD do Windows**, use `copy .env.example .env`.

Edite o `.env` com os valores do seu ambiente:

```ini
DB_HOST=localhost
DB_PORT=3306
DB_NAME=ctg
DB_USER=ctg_user
DB_PASSWORD=1234
```

> O arquivo `.env` nunca deve ser commitado — ele já está no `.gitignore`.

---

### 3. Configurar o banco de dados

Acesse o MySQL:

```bash
mysql -u root -p
```

Crie o banco:

```sql
CREATE DATABASE ctg;
```

---

### 4. Criar usuário

```sql
CREATE USER 'ctg_user'@'localhost' IDENTIFIED BY '1234';
GRANT ALL PRIVILEGES ON ctg.* TO 'ctg_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

### 5. Importar o banco

```bash
mysql -u ctg_user -p ctg -e "source src/Database/schema.sql"
```

> **Banco criado antes da autenticação?** O `schema.sql` não pode ser
> reaplicado num banco com dados (ele usa `CREATE TABLE` sem `IF NOT EXISTS`).
> Rode só a migração, que cria as tabelas `usuarios` e `refresh_tokens`
> sem apagar nada:
>
> ```bash
> mysql -u ctg_user -p ctg -e "source src/Database/migrations/001_auth.sql"
> ```
>
> Pode ser executada mais de uma vez sem problema.

### 6. Popular banco de dados (PARA TESTES!)

Execute no terminal
```bash
mysql -u ctg_user -p1234 ctg -e "source src/Database/seed.sql"
```

Pode ser executado quantas vezes quiser: ele limpa os dados antes de inserir.

O seed também cria os **usuários de teste** para entrar no sistema:

| E-mail | Senha | Perfil | Pode alterar |
|---|---|---|---|
| `admin@ctg.local` | `admin123` | admin | tudo, inclusive usuários |
| `socios@ctg.local` | `socios123` | socios | sócios, dependentes, categorias, cartão |
| `financeiro@ctg.local` | `financeiro123` | financeiro | mensalidades e pagamentos |
| `consulta@ctg.local` | `consulta123` | consulta | nada — somente visualiza |

> Senhas públicas, apenas para desenvolvimento. **Antes de subir para
> produção**, troque a senha do admin e apague os demais usuários.

Detalhes da autenticação e como testá-la: [`docs/AUTENTICACAO.md`](docs/AUTENTICACAO.md).

Para limpar o banco de dados:
```bash
mysql -u ctg_user -p1234 ctg -e "source src/Database/cleanup.sql"
```

---

## ▶️ Executando o projeto

Na raiz do projeto:

```bash
php -S localhost:8000 index.php
```

> O parâmetro `index.php` é o router — sem ele, as rotas `/api/...` não funcionam localmente.

---

## 🌐 Endpoints

> **Todas as rotas exigem autenticação**, exceto `/api/auth/login`,
> `/api/auth/refresh`, `/api/auth/logout` e a raiz `/api/`.
> Envie o token no cabeçalho **`X-Auth-Token`** (não em `Authorization`, que em
> produção pertence ao Basic Auth do servidor). Sem token: **401**.
> Com perfil sem permissão para a operação: **403**.
> Veja [`docs/AUTENTICACAO.md`](docs/AUTENTICACAO.md).

| Rota                              | Método  | Descrição                                                    |
| --------------------------------- | ------- | ------------------------------------------------------------ |
| `/api/auth/login`                 | POST    | Faz login. Recebe `email` e `senha`, devolve os tokens.      |
| `/api/auth/refresh`               | POST    | Gera um novo access token a partir do refresh token.         |
| `/api/auth/logout`                | POST    | Encerra a sessão (revoga o refresh token).                   |
| `/api/auth/me`                    | GET     | Dados do usuário logado.                                     |
| `/api/usuarios`                   | GET     | Lista os usuários do sistema. **(admin)**                    |
| `/api/usuarios/:id`               | GET     | Busca 1 usuário por id. **(admin)**                          |
| `/api/usuarios`                   | POST    | Cria um usuário. **(admin)**                                 |
| `/api/usuarios/:id`               | PUT     | Atualiza um usuário ou só a senha. **(admin)**               |
| `/api/usuarios/:id`               | DELETE  | Exclui um usuário. **(admin)**                               |
| `/api/socios`                     | GET     | Mostra a lista com todos os socios.                          |
| `/api/socios?nome=nome`           | GET     | Busca 1 socio por nome.                                      |
| `/api/socios/:id`                 | GET     | Busca 1 socio por id.                                        |
| `/api/socios`                     | POST    | Adiciona o socio.                                            |
| `/api/socios/:id`                 | PUT     | Atualiza os dados do socio especifico (por id).              |
| `/api/socios/:id`                 | DELETE  | Deleta um socio.                                             |
| `/api/pagamentos`                 | GET     | Mostra a lista com todos os pagamentos.                      |
| `/api/pagamentos/:id`             | GET     | Busca 1 socio por id'                                        |
| `/api/pagamentos`                 | POST    | Adiciona um pagamento.                                       |
| `/api/pagamentos/:id`             | PUT     | Atualiza os dados do pagamento especifico (por id)'          |
| `/api/pagamentos/:id`             | DELETE  | Deleta um pagamento'                                         |
| `/api/mensalidades`               | GET     | Mostra a lista com todas as mensalidades.                    |
| `/api/mensalidades/:id`           | GET     | Busca 1 mensalidade por id.'                                 |
| `/api/mensalidades`               | POST    | Adiciona uma mensalidade.                                    |
| `/api/mensalidades/:id`           | PUT     | Atualiza os dados da mensalidade especifica (por id)'        |
| `/api/mensalidades/:id`           | DELETE  | Deleta uma mensalidade.'                                     |
| `/api/relatorios/socios`          | GET     | Mostra o numero total de socios.                             |
| `/api/relatorios/financeiro`      | GET     | Mostra o valor total pago e o valor total de mensalidades'   |
| `/api/relatorios/inadimplentes`   | GET     | Mostra lista de sócios com mensalidades não pagas.           |
| `/api/relatorios/receita-mensal`  | GET     | Mostra receita agrupada por mês dos pagamentos recebidos.    |
| `/api/relatorios/quantidade-status` | GET   | Mostra quantidade de sócios ativos e inativos com percentuais. |
---

## 🧪 Testando

Use ferramentas como:

* Postman
* Insomnia

Exemplo (lembre do token, senão a resposta é 401):

```
POST http://localhost:8000/api/auth/login
Content-Type: application/json

{ "email": "admin@ctg.local", "senha": "admin123" }
```

```
GET http://localhost:8000/api/socios/1
X-Auth-Token: <access_token devolvido pelo login>
```

Outro metodo:

Instale a extensão "REST Client" no VScode, e execute os testes com os arquivos http.
Para autenticação e permissões, use `test_auth.http` e `test_usuarios.http` —
rode os blocos de login do topo primeiro, eles preenchem as variáveis de token.

---

## 🩺 Problemas comuns

### `mysql` não é reconhecido / `command not found`

O MySQL está instalado, mas o terminal não sabe onde encontrá-lo.

- **Windows:** use o **Shell do XAMPP** (botão "Shell" no painel de controle) —
  ele já configura tudo. Se você usa um MySQL instalado separadamente, chame-o
  pelo caminho completo:

  ```powershell
  $mysql = 'C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe'
  & $mysql -u ctg_user -p ctg -e "source src/Database/schema.sql"
  ```

  Para não repetir o caminho, acrescente a pasta ao PATH:

  ```powershell
  # só nesta janela
  $env:PATH += ";C:\Program Files\MySQL\MySQL Server 8.0\bin"
  ```

  > No terminal integrado do VSCode, mudanças permanentes de PATH só valem
  > depois de **reiniciar o VSCode** — abrir aba nova não basta, porque as abas
  > herdam o ambiente dele.

- **Linux / macOS:** confira com `which mysql`. Se não achar, instale o cliente
  (`sudo apt install mysql-client` ou `brew install mysql-client`).

### `Operador '<' reservado para uso futuro` (PowerShell)

O PowerShell não aceita `<` para redirecionar entrada. Troque
`mysql ... < arquivo.sql` por:

```powershell
mysql -u ctg_user -p ctg -e "source src/Database/schema.sql"
```

Use **barras normais** (`/`) no caminho — funcionam em qualquer sistema.

### `ERROR 2002 ... (10061)` ao conectar

O servidor MySQL não está rodando, ou está em outra porta. No Windows, abra o
painel do XAMPP e clique em **Start** no MySQL.

### Login devolve `500 Variável JWT_SECRET não definida no .env!`

Seu `.env` não tem as variáveis de autenticação. Copie-as do `.env.example` —
veja o passo 2 e [`docs/AUTENTICACAO.md`](docs/AUTENTICACAO.md).

---

## 👨‍💻 Autores

Projeto desenvolvido em grupo para fins acadêmicos.
