# 🚀 Configuração Reverb WebSocket - PassosEstilo API

## ✅ O que foi instalado:

1. **Laravel Reverb** - Servidor WebSocket nativo do Laravel
2. **Evento UserTokenExpired** - Dispara quando o token expira
3. **Middleware atualizado** - Envia notificação via WebSocket quando token expira
4. **AuthController atualizado** - Retorna `user_id` e `expires_in` no login

---

## 🔧 Configurações no Backend (.env):

```dotenv
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=808950
REVERB_APP_KEY=gju2rurm3nzm5tpeezsw
REVERB_APP_SECRET=nruxjkrbeoxg7jb6m82q
REVERB_HOST="localhost"
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

---

## 🚀 Como iniciar o servidor WebSocket:

```bash
# Terminal 1: Iniciar o Reverb
php artisan reverb:start

# Terminal 2: Iniciar a API
php artisan serve
```

Você verá algo como:
```
  INFO  Broadcasting on 0.0.0.0:8080.
  INFO  Reverb server started.
```

---

## 🎯 Como funciona:

1. **Usuário faz login** → API retorna:
   ```json
   {
     "token": "eyJ0eXAiOiJKV...",
     "user_id": 123,
     "expires_in": 604800  // 7 dias em segundos
   }
   ```

2. **Front conecta no WebSocket** usando o `user_id`

3. **Token expira após 7 dias** → Middleware detecta e:
   - Retorna erro 419 (token expirado)
   - **Dispara evento WebSocket** para o canal `user.123`

4. **Front recebe o evento** e desloga automaticamente

---

## 📱 Configuração no Frontend:

### 1. Instalar dependências:
```bash
npm install --save laravel-echo pusher-js
```

### 2. Criar arquivo `echo.js`:
```javascript
// src/echo.js
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const echo = new Echo({
    broadcaster: 'reverb',
    key: 'gju2rurm3nzm5tpeezsw',
    wsHost: 'localhost',
    wsPort: 8080,
    wssPort: 8080,
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
});

export default echo;
```

### 3. Escutar evento no componente principal:
```javascript
import echo from './echo';
import { useEffect } from 'react';

function App() {
  useEffect(() => {
    const userId = localStorage.getItem('userId');
    
    if (userId) {
      echo.channel(`user.${userId}`)
        .listen('.token.expired', (event) => {
          console.log('🔴 Token expirado:', event.message);
          
          // Limpar dados
          localStorage.removeItem('token');
          localStorage.removeItem('userId');
          
          // Redirecionar para login
          window.location.href = '/login';
          
          // Ou mostrar modal
          alert(event.message);
        });
    }

    return () => {
      if (userId) {
        echo.leave(`user.${userId}`);
      }
    };
  }, []);

  return <YourApp />;
}
```

### 4. Salvar userId no login:
```javascript
const handleLogin = async (credentials) => {
  const response = await api.post('/api/auth/login', credentials);
  
  const { token, user_id, expires_in } = response.data.data;
  
  // Salvar no localStorage
  localStorage.setItem('token', token);
  localStorage.setItem('userId', user_id);
  
  // Opcional: calcular data de expiração
  const expiresAt = Date.now() + (expires_in * 1000);
  localStorage.setItem('expiresAt', expiresAt);
};
```

---

## 🧪 Como testar:

### Teste 1: Token expirado via requisição
```bash
# 1. Faça login e pegue o token
# 2. Espere 7 dias OU altere JWT_TTL=1 no .env para 1 minuto
# 3. Tente fazer uma requisição
# 4. Deve receber 419 + notificação WebSocket
```

### Teste 2: WebSocket em tempo real
```bash
# Terminal 1: Reverb
php artisan reverb:start

# Terminal 2: Testar evento manualmente
php artisan tinker

# No tinker:
broadcast(new App\Events\UserTokenExpired(123, 'Teste de logout'));
```

---

## 📦 Arquivos modificados:

- ✅ `/app/Events/UserTokenExpired.php` - Evento criado
- ✅ `/app/Http/Middleware/RequireJWT.php` - Dispara evento ao expirar
- ✅ `/app/Http/Controllers/AuthController.php` - Retorna user_id no login
- ✅ `/.env` - Configurações do Reverb

---

## 🔥 Comandos úteis:

```bash
# Iniciar Reverb
php artisan reverb:start

# Iniciar Reverb em background
php artisan reverb:start &

# Ver logs do Reverb
tail -f storage/logs/laravel.log

# Limpar cache
php artisan config:clear
php artisan cache:clear

# Ver rotas de broadcasting
php artisan route:list | grep broadcast
```

---

## 🌐 Para produção:

### Servidor (comandos adicionais):

```bash
# 1. Usar Supervisor para manter Reverb rodando
sudo apt-get install supervisor

# 2. Criar arquivo /etc/supervisor/conf.d/reverb.conf:
[program:reverb]
command=php /path/to/project/artisan reverb:start
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/path/to/project/storage/logs/reverb.log

# 3. Reiniciar supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start reverb
```

### Frontend (.env.production):

```env
VITE_REVERB_APP_KEY=gju2rurm3nzm5tpeezsw
VITE_REVERB_HOST=api.seudominio.com
VITE_REVERB_PORT=8080
VITE_REVERB_SCHEME=https  # Use https em produção
```

---

## 🎉 Pronto!

Agora quando um token expirar, o usuário será deslogado automaticamente em **tempo real** via WebSocket! 🚀
