# 📊 Sistema de Gestao CTG Raizes da (Backend)

## Tecnologias necessarias

Xampp

## ⚙️ Configuração do Ambiente

### 1. Clone o repositório

```bash
git clone <url-do-repositorio>
cd <nome-do-projeto>
```

---

### 2. Configurar as variáveis de ambiente

Copie o arquivo de exemplo e preencha com as suas credenciais locais:

```bash
cp .env.example .env
```

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
mysql -u ctg_user -p ctg < src/Database/schema.sql
```

### 6. Popular banco de dados (PARA TESTES!)

Execute no terminal
```bash
mysql -u ctg_user -p1234 ctg < src/Database/seed.sql
```

Para limpar o banco de dados:
```bash
mysql -u ctg_user -p1234 ctg < src/Database/cleanup.sql
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

| Rota                              | Método  | Descrição                                                    |
| --------------------------------- | ------- | ------------------------------------------------------------ |
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

Exemplo:

```
GET http://localhost:8000/api/socios/1
```

Outro metodo:

Instale a extensão "REST Client" no VScode, e execute os testes com os arquivos http

---

## 👨‍💻 Autores

Projeto desenvolvido em grupo para fins acadêmicos.
