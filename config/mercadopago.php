<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Mercado Pago Access Token
    |--------------------------------------------------------------------------
    |
    | Seu Access Token do Mercado Pago. Você pode obter no painel do
    | Mercado Pago em: https://www.mercadopago.com.br/developers/panel
    |
    | Para testes, use o Access Token de TEST
    | Para produção, use o Access Token de PRODUCTION
    |
    */
    'access_token' => env('MERCADOPAGO_ACCESS_TOKEN', ''),

    /*
    |--------------------------------------------------------------------------
    | Mercado Pago Public Key
    |--------------------------------------------------------------------------
    |
    | Sua Public Key do Mercado Pago (necessária para Checkout Transparente)
    |
    */
    'public_key' => env('MERCADOPAGO_PUBLIC_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | URL de Notificação (Webhook)
    |--------------------------------------------------------------------------
    |
    | URL que o Mercado Pago vai chamar quando houver mudanças no pagamento
    | Esta URL deve ser pública e acessível pela internet
    |
    | Exemplo: https://seusite.com/api/webhook/mercadopago
    |
    */
    'notification_url' => env('MERCADOPAGO_NOTIFICATION_URL', env('APP_URL') . '/api/webhook/mercadopago'),

    /*
    |--------------------------------------------------------------------------
    | URLs de Retorno
    |--------------------------------------------------------------------------
    |
    | URLs para onde o usuário será redirecionado após o pagamento
    |
    */
    'back_urls' => [
        'success' => env('MERCADOPAGO_SUCCESS_URL', env('APP_URL') . '/payment/success'),
        'failure' => env('MERCADOPAGO_FAILURE_URL', env('APP_URL') . '/payment/failure'),
        'pending' => env('MERCADOPAGO_PENDING_URL', env('APP_URL') . '/payment/pending'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Planos de Assinatura
    |--------------------------------------------------------------------------
    |
    | Defina os planos disponíveis e seus preços
    |
    */
    'plans' => [
        'monthly' => [
            'name' => 'Plano Mensal',
            'description' => 'Acesso completo por 1 mês',
            'price' => 59.90,
            'duration_months' => 1,
            'discount_percent' => 0,
        ],
        'quarterly' => [
            'name' => 'Plano Trimestral',
            'description' => 'Acesso completo por 3 meses',
            'price' => 159.90, // R$ 53,30/mês - Economize ~11%
            'duration_months' => 3,
            'discount_percent' => 11,
        ],
        'semiannual' => [
            'name' => 'Plano Semestral',
            'description' => 'Acesso completo por 6 meses',
            'price' => 299.90, // R$ 49,98/mês - Economize ~17%
            'duration_months' => 6,
            'discount_percent' => 17,
        ],
        'annual' => [
            'name' => 'Plano Anual',
            'description' => 'Acesso completo por 1 ano',
            'price' => 549.90, // R$ 45,82/mês - Economize ~24%
            'duration_months' => 12,
            'discount_percent' => 24,
        ],
    ],
];
