<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Payment Gateways
    |--------------------------------------------------------------------------
    |
    | Static metadata for each supported gateway: display name and the
    | labels/placeholders for its two credential fields. Stripe uses a
    | Publishable/Secret key pair, PayPal uses a Client ID/Secret pair —
    | the "key_1" / "key_2" columns are generic enough to hold either.
    |
    */

    'gateways' => [
        'stripe' => [
            'name' => 'Stripe',
            'key_1_label' => 'Publishable Key',
            'key_2_label' => 'Secret Key',
            'sandbox_key_1_placeholder' => 'pk_test_...',
            'sandbox_key_2_placeholder' => 'sk_test_...',
            'live_key_1_placeholder' => 'pk_live_...',
            'live_key_2_placeholder' => 'sk_live_...',
        ],
        'paypal' => [
            'name' => 'PayPal',
            'key_1_label' => 'Client ID',
            'key_2_label' => 'Client Secret',
            'sandbox_key_1_placeholder' => 'Sandbox Client ID',
            'sandbox_key_2_placeholder' => 'Sandbox Client Secret',
            'live_key_1_placeholder' => 'Live Client ID',
            'live_key_2_placeholder' => 'Live Client Secret',
        ],
    ],

];
