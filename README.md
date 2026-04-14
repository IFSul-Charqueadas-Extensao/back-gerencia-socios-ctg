# 📊 Sistema de Gestao CTG Raizes da (Backend)

OBS: Foram mantidos os Endpoints/MVC anteriores para ter algo com que se basear. Conforme forem criando os Endpoints de verdade, removam os antigos (Que sao de outro projeto)

## ⚙️ Configuração do Ambiente

### 1. Clone o repositório

```bash
git clone <url-do-repositorio>
cd <nome-do-projeto>
```

---

### 2. Configurar o banco de dados

Acesse o MySQL:

```bash
mysql -u root -p
```

Crie o banco:

```sql
CREATE DATABASE ctg;
```

---

### 3. Criar usuário

```sql
CREATE USER 'ctg_user'@'localhost' IDENTIFIED BY '1234';
GRANT ALL PRIVILEGES ON ctg.* TO 'ctg_user'@'localhost';
FLUSH PRIVILEGES;
```

---

### 4. Importar o banco

```bash
mysql -u ctg_user -p ctg < database/schema.sql
```

---

## ▶️ Executando o projeto

Na raiz do projeto:

```bash
php -S localhost:8000 index.php
```

---

## 🌐 Endpoints

Conforme forem criando, adicionem aqui os endpoints

### Relatórios

* GET `/api/relatorios/socios`
* GET `/api/relatorios/financeiro`

---

## 🧪 Testando

Use ferramentas como:

* Postman
* Insomnia

Exemplo:

```
GET http://localhost:8000/api/relatorios/socios
```

---

## 👨‍💻 Autores

Projeto desenvolvido em grupo para fins acadêmicos.
