# SSO-Server на Laravel

Этот проект реализует SSO-сервер с использованием Laravel, JWT, Redis и Nats Jetstream. Предоставляет API для авторизации, регистрации, обновления токенов, OAuth2 через Google и отправки событий в Nats.

---

## Установка и запуск

### Требования:
- Docker
- Docker Compose
- Git

### Установка:

```bash
git clone <ваш-репозиторий>
cd sso-server
cp .env.example .env
docker-compose up -d --build
docker exec -it sso-app php artisan migrate
```

### Конфигураця:
#### В .env укажите:
```env
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI=http://localhost:8000/api/auth/google/callback

NATS_SERVER=nats_server_ip
NATS_PORT=4222
NATS_NKEY=nats_nkey
NATS_SUBJECT=some_sunject(topic)
NATS_STREAM=some_stream
```

## API Документация


### 1. Регистрация пользователя
**POST** `/api/auth/register`

**Запрос**
```json
{
  "email": "user@example.com",
  "password": "password"
}
```
**Ответ**
```json
{
  "token_type": "Bearer",
  "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.xxxxx",
  "refresh_token": "a1b2c3d4-e5f6-7890-g1h2-i3j4k5l6m7n8",
  "expires_in": 3600
}
```

### 2. Авторизация пользователя
**POST** `/api/auth/login`

**Запрос**
```json
{
  "email": "user@example.com",
  "password": "password"
}
```
**Ответ**
```json
{
  "token_type": "Bearer",
  "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.xxxxx",
  "refresh_token": "a1b2c3d4-e5f6-7890-g1h2-i3j4k5l6m7n8",
  "expires_in": 3600
}
```

### 3. Обновление Access Token
**POST** `/api/auth/refresh`

**Запрос**
```json
{
  "refresh_token": "a1b2c3d4-e5f6-7890-g1h2-i3j4k5l6m7n8"
}
```
**Ответ**
```json
{
  "token_type": "Bearer",
  "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.xxxxx",
  "expires_in": 3600
}
```

### 4. Выход из системы
**POST** `/api/auth/refresh`

**Заголовок**
```header
X-Refresh-Token: refresh_token
```
**Ответ**
```json
{
  "message": "Successfully logged out"
}
```
### 5. Авторизация через Google

**GET** `/api/auth/google/redirect` — перенаправляет на Google. 

**GET** `/api/auth/google/callback` — обрабатывает ответ от Google.

### 6. Роут, с достпуом по токену.

**GET** `/api/protected`

**Заголовок**
```header
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.xxxxx
```
**Ответ**
```json
{
    "message": "You are authenticated!",
    "user": {
        "id": 4,
        "email": "test@test.ru",
        "created_at": "2025-06-08T10:24:56.000000Z",
        "updated_at": "2025-06-08T10:24:56.000000Z"
    }
}
```
