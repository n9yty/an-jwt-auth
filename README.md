# an-jwt-auth V1.0
## Аутентификация JWT для WP REST API

Расширяет API-интерфейс WP REST, используя аутентификацию веб-токенов JSON в качестве метода аутентификации.

 [JSON Web Token (JWT)](https://tools.ietf.org/html/rfc7519) — это открытый стандарт ([RFC 7519](https://tools.ietf.org/html/rfc7519)) для создания токенов доступа, основанный на формате JSON. Как правило, используется для передачи данных для аутентификации в клиент-серверных приложениях. Токены создаются сервером, подписываются секретным ключом и передаются клиенту, который в дальнейшем использует данный токен для подтверждения своей личности [http://jwt.io](http://jwt.io).

### ТРЕБОВАНИЯ

#### WP REST API V2

Этот плагин был задуман для расширения возможностей плагина WP REST API V2 и, конечно, был построен на его основе.

Итак, чтобы использовать wp-api-jwt-auth, вам нужно установить и активировать WP REST API .

### ВКЛЮЧИТЬ HTTP ЗАГОЛОВОК АВТОРИЗАЦИИ

На большинстве хостингов по умолчанию заголовок **авторизации HTTP** отключен .

Чтобы включить эту опцию, вам нужно отредактировать ваш файл **.htaccess**, добавив следующее

```
# BEGIN AN-JWT-AUTH
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteCond %{HTTP:Authorization} ^(.*)
RewriteRule ^(.*) - [E=HTTP_AUTHORIZATION:%1]
</IfModule>
# END AN-JWT-AUTH
```

#### WPEngine

Чтобы включить эту опцию, вам нужно отредактировать ваш файл **.htaccess**, добавив следующее

См. Https://github.com/Tmeister/wp-api-jwt-auth/issues/1.

```
SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1
```

#### НАСТРОЙКА ПОДДЕРЖКИ CORs

Плагин имеет возможность активировать [CORs](https://en.wikipedia.org/wiki/Cross-origin_resource_sharing) поддержку.

Чтобы включить поддержку CORs включите эту опцию в настройках плагина.

Finally activate the plugin within the plugin dashboard.

## Пространство имен и конечные точки

Когда плагин активирован, добавляется новое пространство имен.


```
/jwt-auth/v1
```


Кроме того, три новые конечные точки добавляются в это пространство имен.


| конечные точки                        | HTTP       |
| ------------------------------------- | ---------- |
| */wp-json/jwt-auth/v1/token*          | POST / GET |
| */wp-json/jwt-auth/v1/tokenRefresh*   | POST / GET |
| */wp-json/jwt-auth/v1/logout*         | POST / GET |

## Реализована следующая схема:

1. Клиент проходит аутентификацию с использованием логина и пароля
2. В случае успешной аутентификации, сервер отправляет клиенту **accessToken** и **refreshToken** .
3. При дальнейшем обращении к серверу, клиент отправляет **accessToken** в HTTP заголовке Authorization. Сервер проверяет токен на валидность и предоставляет клиенту доступ к ресурсам.
4. В случае, если **accessToken** становится не валидным, клиент отправляет **refreshToken** с параметром refreshToken, в ответ на который сервер предоставляет два обновленных токена.
5. В случае, если **refreshToken** становится не валидным, клиент опять должен пройти процесс аутентификации.

* **accessToken** — *это токен, который предоставляет доступ его владельцу к защищенным ресурсам сервера. Имеет короткий срок жизни.*

* **refreshToken** — *это токен, позволяющий клиентам запрашивать новый **accessToken** по истечении его времени жизни. Данные токены обычно выдаются на длительный срок.*

## Использование
### /wp-json/jwt-auth/v1/token

Это точка входа для аутентификации JWT.

Обязательные параметры:  **username** , **password**.

Проверяет учетные данные пользователя, **username** и **password**,   возвращает токен **accessToken** для использования в будущем запросе к API, если аутентификация правильная, или ошибку если аутентификация не удалась.
Также возвращается **refreshToken** и некоторые данные пользователя.
В качестве **username** можно передавать как логин пользавателя так и его электронную почту.

### /wp-json/jwt-auth/v1/tokenRefresh

Это точка для смены **accessToken**.

Обязательные параметры:  **refreshToken**.

Проверяет **refreshToken** и при успехе возвращает новую пару **accessToken** и **refreshToken** , а так же некоторые данные пользователя.

### /wp-json/jwt-auth/v1/logout

В HTTP заголовке Authorization необходимо передать  **accessToken** , обязательных параметров нет.

Это точка для выхода.

Удаляет **refreshToken** из памяти сервера.

##### Пример успешного ответа сервера
```
{
  "accessToken": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE1NTMzNDEzNjks...",
  "refreshToken": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE1NTMzNDEzNjk...",
  "data": {
     "user_id": 1,
     "user_email": "user@gmail.com",
     "user_nicename": "UserNicName",
     "user_display_name": "UserDisplayName",
     "client_ip": "127.0.0.1"
     }
}
```
##### Некоторые примеры ошибки ответа сервера
Истек срок действия ключа
```
{
  "code": "jwt_auth_expired_token",
  "message": "Expired token",
  "data":{
     "status": 419
    }
}
```
Отсутствуют необходимые параметры
```
{
  "code": "rest_missing_callback_param",
  "message": "Отсутствует параметр: refreshToken",
  "data":{
     "status": 400,
     "params":[
       "refreshToken"
       ]
     }
}
```
Провалилась авторизация
```
{
  "code": "wrong_login_or_password",
  "message": "Wrong login or password",
  "data":{
      "status": 401
    }
}
```
#### Далее для доступа к приватным API достаточно в HTTP заголовке Authorization передать accessToken

