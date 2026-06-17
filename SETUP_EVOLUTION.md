# Configuração da Evolution API 🚀

Este guia detalha o passo a passo completo para configurar e executar a **Evolution API** localmente usando Docker para integrar o envio de mensagens de WhatsApp ao sistema Barberfy.

---

## 🛠️ Passo a Passo para Instalação e Execução

### 1. Instalar o Docker
Para rodar os serviços, você precisa do Docker instalado em seu dispositivo:
*   Baixe e instale o **[Docker Desktop](https://www.docker.com/products/docker-desktop/)** (Windows / macOS) ou instale o **Docker Engine** (Linux).

### 2. Criar o arquivo de configuração `docker.env`
Crie uma cópia do arquivo de exemplo `docker.env.example` na raiz do diretório `barberfy-api` com o nome `docker.env`:

```bash
cp docker.env.example docker.env
```

### 3. Ajustar as Variáveis de Ambiente
Abra o arquivo `docker.env` recém-criado e configure as seguintes variáveis:

*   **`POSTGRES_USER`**: Usuário do banco de dados (ex: `postgres`).
*   **`POSTGRES_PASSWORD`**: Senha do banco de dados (ex: `postgres`).
*   **`POSTGRES_DB`**: Nome do banco de dados (ex: `evolution_db`).
*   **`AUTHENTICATION_API_KEY`**: Chave de autenticação global. 
    > 💡 **Recomendação:** Use o gerador de senhas do **[LastPass](https://www.lastpass.com/pt/features/password-generator)** para gerar esta chave. Lembre-se de **remover a opção de símbolos** nas configurações de geração do site.

### 4. Iniciar os Containers
Com as variáveis configuradas, abra o terminal na pasta `barberfy-api` e execute o comando:

```bash
docker compose up -d
```

### 5. Acessar a Porta no Docker Desktop
1. Abra o aplicativo **Docker Desktop** no seu computador.
2. Acesse a aba **Containers** no menu lateral esquerdo.
3. Clique para abrir o dropdown da stack da evolution (normalmente chamado de `barberfy-api` ou `evolution-api`).
4. Clique no link correspondente à porta **8080:8080** para abri-la no navegador.

### 6. Copiar a URL do Manager
Ao acessar a porta no navegador, a API retornará um JSON de boas-vindas com a seguinte estrutura:

```json
{
  "status": 200,
  "message": "Welcome to the Evolution API, it is working!",
  "version": "2.3.7",
  "clientName": "evolution_exchange",
  "manager": "http://localhost:8080/manager",
  "documentation": "https://doc.evolution-api.com",
  "whatsappWebVersion": "2.3000.1041640711"
}
```

Copie a URL que aparece na chave `"manager"` (geralmente `http://localhost:8080/manager`).

### 7. Autenticar no Evolution Manager
1. Cole a URL copiada no navegador para abrir o painel do Evolution Manager.
2. Nos campos de autenticação, insira:
   * **Server URL:** `http://localhost:8080`
   * **API Key Global:** A chave gerada pelo LastPass que você inseriu em `AUTHENTICATION_API_KEY` no arquivo `docker.env`.

### 8. Criar uma Nova Instância
1. No menu do painel do Manager, clique em **Criar Instância**.
2. Defina um nome de sua escolha para a instância.
3. Selecione o canal (**Channel**) como **Baileys**.
4. Clique em **Salvar**.

### 9. Conectar o WhatsApp (QR Code)
1. Clique no ícone de **engrenagem** ao lado da instância recém-criada.
2. Clique na opção **Get QR Code**.
3. Abra o WhatsApp no seu celular, vá em **Aparelhos Conectados** -> **Conectar um Aparelho** e escaneie o código exibido na tela.
4. Pronto! A instância estará conectada e pronta para enviar mensagens.

### 🚀 10. Voltar ao README.md e finalizar no Backend
Agora que a Evolution API está rodando e conectada ao seu WhatsApp, você deve voltar ao arquivo [README.md](file:///c:/workspace/Unify/apps/app-barberfy/barberfy-api/README.md) principal para terminar a configuração.

Abra o arquivo `.env` da sua API Laravel (`barberfy-api`) e preencha as variáveis correspondentes com os dados mostrados no manager:

*   **`EVOLUTION_API_URL`**: A URL com a porta onde a API está rodando (normalmente `http://localhost:8080`).
*   **`EVOLUTION_API_KEY`**: A mesma chave de autenticação que você gerou no LastPass e configurou no seu `docker.env`.
*   **`EVOLUTION_INSTANCE`**: O nome da instância que você criou no manager (ex: `teste_api`).

Depois disso, prossiga para a inicialização dos serviços do Laravel.

---

## ⚠️ Possíveis Erros e Soluções

### O QR Code não é gerado (Fica carregando infinitamente)

Se você tiver problemas para carregar ou exibir o QR Code, você pode:
*   📺 Assistir ao vídeo explicativo com a solução: **[Assistir no YouTube](https://youtu.be/ZkYQU0-3RwA)**
*   🛠️ Ou seguir o passo a passo abaixo:

1. No terminal do projeto, pare a execução dos containers:
   ```bash
   docker compose down
   ```
2. Abra o seguinte link no navegador para verificar as últimas versões suportadas do WhatsApp Web:
   👉 **[WPPConnect WhatsApp Versions](https://wppconnect.io/pt-BR/whatsapp-versions/)**
3. Copie apenas os números da versão mais recente listada (ex: `2.3000.1014.0` ou similar).
4. Abra o seu arquivo `docker.env`.
5. Localize a variável `CONFIG_SESSION_PHONE_VERSION`.
6. Altere o valor dela colocando a versão que você acabou de copiar do site.
7. Execute o comando para recriar o container forçando a build:
   ```bash
   docker compose up --build -d
   ```
8. Recarregue o painel do manager da Evolution API (pode levar alguns instantes para subir totalmente).
9. Tente gerar o QR Code novamente.
