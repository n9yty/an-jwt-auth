=== JWT Authentication for WP REST API ===

Contributors: SashokNekulin
Tags: wp-json, jwt, json web authentication, wp-api
Requires at least: 4.2
Tested up to: 5.1
Requires PHP: 5.6.0
Stable tag: 0.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Extends the WP REST API using JSON Web Tokens Authentication as an authentication method.

== Description ==

Extends the WP REST API using JSON Web Tokens Authentication as an authentication method.

JSON Web Tokens are an open, industry standard [RFC 7519](https://tools.ietf.org/html/rfc7519) method for representing claims securely between two parties.

**Support and Requests please in Github:** https://github.com/SashokNekulin/an-jwt-auth

# an-jwt-auth
## Аутентификация JWT для WP REST API

Расширяет API-интерфейс WP REST, используя аутентификацию веб-токенов JSON в качестве метода аутентификации.

JSON Web Token (JWT) — это открытый стандарт ([RFC 7519](https://tools.ietf.org/html/rfc7519)) для создания токенов доступа, основанный на формате JSON. Как правило, используется для передачи данных для аутентификации в клиент-серверных приложениях. Токены создаются сервером, подписываются секретным ключом и передаются клиенту, который в дальнейшем использует данный токен для подтверждения своей личности.

### ТРЕБОВАНИЯ

#### WP REST API V2

Этот плагин был задуман для расширения возможностей плагина WP REST API V2 и, конечно, был построен на его основе.

Итак, чтобы использовать wp-api-jwt-auth, вам нужно установить и активировать WP REST API .

### ВКЛЮЧИТЬ HTTP ЗАГОЛОВОК АВТОРИЗАЦИИ

На большинстве хостингов по умолчанию заголовок **авторизации HTTP** отключен .

Чтобы включить эту опцию, вам нужно отредактировать ваш файл **.htaccess**, добавив следующее

`
RewriteEngine on
RewriteCond %{HTTP:Authorization} ^(.*)
RewriteRule ^(.*) - [E=HTTP_AUTHORIZATION:%1]
`

#### WPENGINE

Чтобы включить эту опцию, вам нужно отредактировать ваш файл **.htaccess**, добавив следующее

См. Https://github.com/Tmeister/wp-api-jwt-auth/issues/1.

`
SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1
`

#### НАСТРОЙКА ПОДДЕРЖКИ CORS

Плагин имеет возможность активировать Корс поддержку.

Чтобы включить поддержку CORs, отредактируйте файл wp-config.php и добавьте новую константу с именем **JWT_AUTH_CORS_ENABLE**

`
define('JWT_AUTH_CORS_ENABLE', true);
`
