# TWITCH-ANALYTICS REST API
## Versión: 1.0.0

---
El servidor está alojado en una VPS de Oracle en la IP 158.179.222.160

Las funcionalidades se prueban de manera local añadiendolas al programa twitchAnalytics.php antes de continuar en desarrollo en producción.

Para el desarrollo en producción realizamos conexiones ssh a la VPS indicada. Las credenciales están en el grupo de Slack.
### Organización de ficheros:
+  ***analytics:*** Directorio con los endpoints implementados
    + ***streams:*** Directorio para los casos de uso 2-3
      + *enriched.php:* Caso de uso 3 implementado
      + *index.php:* Caso de uso 2 implementado
    + *.htaccess:* Fichero encargado de redirigir las peticiones sin extensión .php
    + *prueba.php:* Plantilla de end-point
    + *token.php:* Programa encargado de generar el token de Twitch
    + *user.php:* Caso de uso 1 implementado
+ ***twitchAnalytics.php:*** Aplicación de escritorio para probar funcionalidades en local
---
# Como levantar el proyecto
Obtener credenciales de Twitch Developers: https://dev.twitch.tv/docs/api/get-started/

Introducir las credenciales obtenidas en *token.php* donde se configura las opciones de POSTFIELDS

Obtener y configurar un servidor web de OracleCloud: https://www.oracle.com/cloud/free/

Dependencias a instalar en el servidor: 
+ PHP
+ HTTPD
+ JSON
+ CURL

Clonar el repositorio:

    // IMPORTANTE CAMBIAR AL DIRECTORIO ADECUADO
    cd /var/www/html
    git clone https://github.com/aritzhu/V-V.git

Listo! Ahora deberías poder hacer peticiones a tu API como en los ejemplos especificados abajo cambiando el Host por el de tu servidor.

---

# Endpoints implementados:
Host: 158.179.222.160

## GET User
### Request:
`GET /analytics/user/`

    curl -X GET http://158.179.222.160/analytics/user?id=12
### Response example: 

    {
    "id": "12",
    "login": "stephenswun",
    "display_name": "stephenswun",
    "type": "",
    "broadcaster_type": "",
    "description": "",
    "profile_image_url": "https:\/\/static-cdn.jtvnw.net\/user-default-pictures-uv\/ebe4cd89-b4f4-4cd9-adac-2f30151b4209-profile_image-300x300.png",
    "offline_image_url": "",
    "view_count": 0,
    "created_at": "2007-05-22T10:37:47Z"
    }

## GET Streams
### Request:
`GET /analytics/streams/`
    
    curl -X GET  http://158.179.222.160/analytics/streams/

### Response example:

    {
    "title": "🔴LCK COSTREAM BROOOO VS NS ELIMINATION SERIES #LCKWatchParty🔴!discord !youtube",
    "user_name": "Caedrel"
    },
    {
    "title": "雑談",
    "user_name": "加藤純一うん〇ちゃん"
    },
    {
    "title": "DRX vs DNF - NS vs BRO | 2025 LCK CUP Play-Ins",
    "user_name": "LCK"
    },
    {
    "title": "ナイトを上げたくてあげるんじゃない上がってしまう者がナイト",
    "user_name": "fps_shaka"
    }

## GET Enriched Streams
### Request:
`GET /analytics/streams/enriched`

    curl -X GET  http://158.179.222.160/analytics/streams/enriched?limit=3

### Response example:

    [
    {
    "stream_id": "315377620985",
    "user_id": "92038375",
    "user_name": "caedrel",
    "viewer_count": 36692,
    "title": "🔴LCK COSTREAM BROOOO VS NS ELIMINATION SERIES #LCKWatchParty🔴!discord !youtube",
    "user_display_name": "Caedrel",
    "profile_image_url":
    "https:\/\/static-cdn.jtvnw.net\/jtv_user_pictures\/483a37ac-58fd-4e2f-8dc3-2c68a0164112-profile_image-300x300.png"
    },
    {
    "stream_id": "315704795260",
    "user_id": "545050196",
    "user_name": "kato_junichi0817",
    "viewer_count": 26147,
    "title": "雑談",
    "user_display_name": "加藤純一うん〇ちゃん",
    "profile_image_url":
    "https:\/\/static-cdn.jtvnw.net\/jtv_user_pictures\/a4977cfd-1962-41ec-9355-ab2611b97552-profile_image-300x300.png"
    },
    {
    "stream_id": "314536173176",
    "user_id": "124425501",
    "user_name": "lck",
    "viewer_count": 17404,
    "title": "DRX vs DNF - NS vs BRO | 2025 LCK CUP Play-Ins",
    "user_display_name": "LCK",
    "profile_image_url":
    "https:\/\/static-cdn.jtvnw.net\/jtv_user_pictures\/04b097ac-9a71-409e-b30e-570175b39caf-profile_image-300x300.png"
    }
    ]



