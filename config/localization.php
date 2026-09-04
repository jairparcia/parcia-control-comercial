<?php

return [
    'supported_locales' => ['en', 'es'],
    'default_locale'    => env('APP_LOCALE', 'en'),
    'session_key'       => 'locale',
    'cookie_name'       => 'parcia_locale',
    'cookie_minutes'    => 525600, // 1 year
];
