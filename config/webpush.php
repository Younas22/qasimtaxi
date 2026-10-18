<?php

return [

    /*
    |--------------------------------------------------------------------------
    | VAPID Keys
    |--------------------------------------------------------------------------
    |
    | Identify this server to browser push services (FCM, Mozilla autopush,
    | etc.) so a push can be sent without a third-party SaaS in between. The
    | private key must never reach the frontend — only the public key is
    | exposed, to the browser's PushManager.subscribe() call.
    |
    | Generate a pair with:
    |   php -r "require 'vendor/autoload.php'; print_r(Minishlink\WebPush\VAPID::createVapidKeys());"
    |
    */

    'public_key' => env('VAPID_PUBLIC_KEY'),
    'private_key' => env('VAPID_PRIVATE_KEY'),
    'subject' => env('VAPID_SUBJECT', 'mailto:admin@example.com'),

    /*
    |--------------------------------------------------------------------------
    | Delivery Options
    |--------------------------------------------------------------------------
    |
    | TTL: how long a push service should retry delivery to an offline
    | browser before giving up (seconds). urgency/topic follow the Web Push
    | Protocol (RFC 8030 §5.3) — 'topic' lets a later push of the same type
    | replace an undelivered one instead of stacking up (not used by default;
    | PushNotificationService sets it per-notification only where replacing
    | makes sense, e.g. a dispatch/location ping).
    |
    */

    'ttl' => (int) env('VAPID_TTL', 2419200), // 4 weeks, the RFC 8030 default

    /*
    |--------------------------------------------------------------------------
    | Windows OpenSSL Config Path
    |--------------------------------------------------------------------------
    |
    | web-push signs a fresh VAPID JWT with an EC key for every push it
    | sends. On some Windows PHP builds (this local XAMPP-style stack
    | included), openssl_pkey_new()/openssl_sign() for EC keys fail with
    | "error:07000072:configuration file routines::no such file" because the
    | OpenSSL extension's compiled-in default config path
    | (C:\Program Files\Common Files\SSL\openssl.cnf) doesn't exist on this
    | machine. Setting the OPENSSL_CONF env var to a real openssl.cnf before
    | the first EC operation fixes it — see
    | bootstrap/app.php/AppServiceProvider for where this is applied.
    | Linux production servers almost always ship a valid default and need
    | none of this; leave WEBPUSH_OPENSSL_CONF unset there.
    |
    */

    'openssl_conf' => env('WEBPUSH_OPENSSL_CONF'),

];
