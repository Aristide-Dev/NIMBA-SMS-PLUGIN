<?php

declare(strict_types=1);

return [

    'base_url' => env('NIMBASMS_BASE_URL', 'https://api.nimbasms.com/v1'),

    'service_id' => env('NIMBASMS_SERVICE_ID'),

    'secret_token' => env('NIMBASMS_SECRET_TOKEN'),

    'sender_name' => env('NIMBASMS_SENDER_NAME'),

    'timeout' => env('NIMBASMS_TIMEOUT', 20),

];
