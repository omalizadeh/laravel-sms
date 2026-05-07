<?php

use Omalizadeh\SMS\Drivers\FarazSMS\FarazSMS;
use Omalizadeh\SMS\Drivers\Kavenegar\Kavenegar;
use Omalizadeh\SMS\Drivers\SMSIr\SMSIr;
use Omalizadeh\SMS\Drivers\Signal\SignalAds;

return [
    'default_provider' => env('DEFAULT_SMS_PROVIDER', 'kavenegar'),

    'kavenegar' => [
        'driver' => Kavenegar::class,
        'api_key' => env('KAVENEGAR_API_KEY'),
        'default_sender' => env('KAVENEGAR_DEFAULT_SENDER'),
    ],

    'sms_ir' => [
        'driver' => SMSIr::class,
        'api_key' => env('SMS_IR_API_KEY'),
        'default_line_number' => env('SMS_IR_DEFAULT_LINE_NUMBER'),
    ],

    'signal_ads' => [
        'driver' => SignalAds::class,
        'token' => env('SIGNAL_ADS_TOKEN'),
        'default_from' => env('SIGNAL_ADS_DEFAULT_FROM'),
        'base_url' => env('SIGNAL_ADS_BASE_URL', 'https://panel.signalads.com'),
    ],

    'faraz_sms' => [
        'driver' => FarazSMS::class,
        'token' => env('FARAZ_SMS_TOKEN'),
        'default_from_number' => env('FARAZ_SMS_DEFAULT_FROM_NUMBER', '+983000505'),
    ],
];
