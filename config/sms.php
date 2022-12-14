<?php

return [
    'default_driver' => env('DEFAULT_SMS_PROVIDER', 'kavenegar'),

    'kavenegar' => [
        'driver_class' => \Omalizadeh\Sms\Drivers\Kavenegar\Kavenegar::class,
        'api_key' => '',
    ],
    'sms_ir' => [
        'driver_class' => \Omalizadeh\Sms\Drivers\SmsIr\SmsIr::class,
        'api_key' => '',
    ],
];
