<?php

namespace Omalizadeh\SMS\Tests;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Omalizadeh\SMS\Drivers\Signal\SignalAds;
use Omalizadeh\SMS\Exceptions\SendingSMSFailedException;
use Omalizadeh\SMS\Requests\SendSMSRequest;

class SignalAdsTest extends TestCase
{
    protected SignalAds $signalSms;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'sms.signal_ads.token' => 'test_token',
            'sms.signal_ads.default_from' => '985000439800',
            'sms.signal_ads.base_url' => 'https://panel.signalads.com',
        ]);

        $this->signalSms = new SignalAds(config('sms.signal_ads'));
    }

    public function test_sms_can_be_sent_successfully_by_signal_driver(): void
    {
        Http::fake([
            'https://panel.signalads.com/api_v1/send/simple' => Http::response([
                'id' => 12345,
                'encoding' => 'UTF-8',
                'pages' => 1,
                'success' => true,
            ]),
        ]);

        $response = $this->signalSms->send(
            new SendSMSRequest(
                '09915519423',
                'تست سیگنال',
                null,
            ),
        );

        $this->assertEquals(12345, $response->getMessageId());
        $this->assertNull($response->getCost());

        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://panel.signalads.com/api_v1/send/simple'
                && $request->hasHeader('Authorization', 'Bearer test_token')
                && $request['from'] === '985000439800'
                && $request['message'] === 'تست سیگنال'
                && $request['numbers'] === ['09915519423'];
        });
    }

    public function test_sms_throws_exception_when_signal_ads_returns_unauthorized_error(): void
    {
        Http::fake([
            'https://panel.signalads.com/api_v1/send/simple' => Http::response([
                'message' => 'Unauthorized',
            ], 401),
        ]);

        $this->expectException(SendingSMSFailedException::class);
        $this->expectExceptionMessage('توکن احراز هویت نامعتبر یا منقضی شده است');

        $this->signalSms->send(
            new SendSMSRequest(
                '09915519423',
                'تست سیگنال',
                null,
            ),
        );
    }

    public function test_sms_throws_exception_when_provider_returns_failed_response_structure(): void
    {
        Http::fake([
            'https://panel.signalads.com/api_v1/send/simple' => Http::response([
                'success' => false,
                'message' => 'Invalid token',
            ], 200),
        ]);

        $this->expectException(SendingSMSFailedException::class);
        $this->expectExceptionMessage('Invalid token');

        $this->signalSms->send(
            new SendSMSRequest(
                '09915519423',
                'تست سیگنال',
                null,
            ),
        );
    }

    public function test_sms_throws_exception_when_signal_ads_returns_invalid_html_response(): void
    {
        Http::fake([
            'https://panel.signalads.com/api_v1/send/simple' => Http::response(
                '<html>Service Unavailable</html>',
                503
            ),
        ]);

        $this->expectException(SendingSMSFailedException::class);
        $this->expectExceptionMessage('Invalid response from SignalAds');

        $this->signalSms->send(
            new SendSMSRequest(
                '09915519423',
                'تست سیگنال',
                null,
            ),
        );
    }
}
