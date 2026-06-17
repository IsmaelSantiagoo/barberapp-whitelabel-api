# Barberfy API 💈

Este é o backend (API) da aplicação Barberfy, desenvolvido em Laravel. Ele gerencia toda a lógica de negócios, banco de dados, autenticação, agendamentos, filas de processamento e eventos em tempo real.

---

## 🚀 Como Iniciar a API

Siga as etapas abaixo para configurar e executar a API localmente.

### 📋 Pré-requisitos

Para executar este projeto, você precisará ter em seu ambiente:
- **PHP** (Versão 8.2 ou superior recomendada)
- **Composer** (Gerenciador de dependências PHP)
- Um banco de dados suportado (ex: **MySQL**, **MariaDB**, **PostgreSQL** ou **SQLite**)

---

### 🛠️ Passo a Passo para Execução

#### 1. Criar o Banco de Dados Local
Crie um banco de dados vazio em seu gerenciador de banco de dados (ex: MySQL/Localhost) com o nome de sua preferência (ex: `barberfy`).

#### 2. Configurar o arquivo `.env`
Crie um arquivo `.env` a partir do arquivo de exemplo `.env.example`:

```bash
cp .env.example .env
```

Abra o arquivo `.env` recém-criado e configure as credenciais de acesso ao seu banco de dados local nas seguintes variáveis:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=barberfy       # Nome do banco de dados que você criou no passo 1
DB_USERNAME=seu_usuario    # Seu usuário de banco de dados (ex: root)
DB_PASSWORD=sua_senha      # Sua senha do banco de dados
```

#### 3. Atualizar as Dependências
Execute o comando do Composer para instalar e atualizar todas as dependências do Laravel:

```bash
composer install
```

#### 4. Gerar a Chave da Aplicação (App Key)
Gere a chave única de criptografia da sua aplicação no arquivo `.env`:

```bash
php artisan key:generate
```

#### 5. Configurar/Gerar Credenciais do Reverb (WebSockets)
Caso as credenciais do Reverb não estejam configuradas em seu `.env`, você pode gerá-las executando:

```bash
php artisan reverb:install
```

#### 6. Executar Migrations e Seeders
Rode as migrations para criar a estrutura das tabelas no banco de dados e os seeders para popular com dados iniciais de teste:

```bash
php artisan migrate --seed
```

#### 7. Configurar a Evolution API (WhatsApp)
Antes de iniciar os serviços, você deve configurar a Evolution API seguindo o guia detalhado:
👉 **[Configuração da Evolution API (SETUP_EVOLUTION.md)](file:///c:/workspace/Unify/apps/app-barberfy/barberfy-api/SETUP_EVOLUTION.md)**

Retorne a este arquivo assim que concluir a configuração da instância do WhatsApp no painel.

#### 8. Executar os Serviços da API
Para o funcionamento completo da aplicação, você precisará rodar três processos paralelos no terminal (ou utilizar um gerenciador de processos de sua preferência):

*   **Servidor Web da API:**
    ```bash
    php artisan serve
    ```
    *(Geralmente inicia em `http://localhost:8000`)*

*   **Serviço de WebSockets (Laravel Reverb):**
    ```bash
    php artisan reverb:start
    ```

*   **Processamento da Fila (Queue Worker):**
    ```bash
    php artisan queue:work
    ```

---

## 🧰 Tecnologias Principais
- **Laravel 11**
- **PHP 8.2+**
- **Laravel Reverb** (Para comunicação via WebSocket em tempo real)
- **Database Migrations & Seeders**
