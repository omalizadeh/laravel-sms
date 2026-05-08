# Laravel SMS Package

This is a laravel sms package with multi driver support. Supports laravel **v11.0+** and requires php **v8.3+**

> Star! :star: if you used and liked this package.

## Supported SMS Providers

- [Kavenegar](https://kavenegar.com)
- [SMS.ir](https://sms.ir)
- [FarazSMS](https://farazsms.com)
- [Signal](https://signalads.com)

## Installation & Configuration

Install using composer

```bash 
  composer require omalizadeh/laravel-sms
```

Publish config file

```bash
  php artisan vendor:publish --provider=Omalizadeh\SMS\Providers\SMSServiceProvider
```

## Usage

Single message:

```php
    use Omalizadeh\SMS\Facades\SMS;
    use Omalizadeh\SMS\Requests\SendSMSRequest;

    SMS::send(new SendSMSRequest('+989123456789', 'Hello!'));
```

Template message:

```php
    use Omalizadeh\SMS\Facades\SMS;
    use Omalizadeh\SMS\Requests\SendTemplateSMSRequest;

    SMS::sendTemplate(new SendTemplateSMSRequest('+989123456789', 'template_code', [
    'param1' => 'coupon',
    ]));
```
