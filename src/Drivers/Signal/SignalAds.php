<?php

namespace Omalizadeh\SMS\Drivers\Signal;

use Illuminate\Support\Facades\Http;
use Omalizadeh\SMS\Drivers\Contracts\BulkSMSSender;
use Omalizadeh\SMS\Drivers\Contracts\Driver;
use Omalizadeh\SMS\Exceptions\InvalidSMSConfigurationException;
use Omalizadeh\SMS\Exceptions\InvalidSMSParameterException;
use Omalizadeh\SMS\Exceptions\SendingSMSFailedException;
use Omalizadeh\SMS\Requests\SendBulkSMSRequest;
use Omalizadeh\SMS\Requests\SendSMSRequest;
use Omalizadeh\SMS\Responses\SendBulkSMSResponse;
use Omalizadeh\SMS\Responses\SendSMSResponse;

class SignalAds extends Driver implements BulkSMSSender
{
    /**
     * @throws InvalidSMSConfigurationException
     * @throws SendingSMSFailedException
     */
    public function send(SendSMSRequest $request): SendSMSResponse
    {
        $data = [
            'from' => $request->getSender() ?: $this->getConfig('default_from'),
            'message' => $request->getMessage(),
            'numbers' => [$request->getPhoneNumber()],
        ];

        $responseJson = $this->callApi($this->getSMSSendingURL(), $data);

        return new SendSMSResponse(
            $this->extractMessageId($responseJson),
            null,
        );
    }

    /**
     * @throws InvalidSMSConfigurationException
     * @throws InvalidSMSParameterException
     * @throws SendingSMSFailedException
     */
    public function sendBulk(SendBulkSMSRequest $request): SendBulkSMSResponse
    {
        $phoneNumbers = $request->getPhoneNumbers();

        if (empty($phoneNumbers)) {
            throw new InvalidSMSParameterException('SignalAds bulk sms phone numbers can not be empty.');
        }

        $data = [
            'from' => $request->getSender() ?: $this->getConfig('default_from'),
            'message' => $request->getMessage(),
            'numbers' => $phoneNumbers,
        ];

        $responseJson = $this->callApi($this->getBulkSMSSendingURL(), $data);

        return new SendBulkSMSResponse(
            records: $this->extractBulkResponses($responseJson),
            totalCost: null,
        );
    }

    public function getSMSSendingURL(): string
    {
        return rtrim($this->getBaseUrl(), '/') . '/api_v1/send/simple';
    }

    public function getBulkSMSSendingURL(): string
    {
        return $this->getSMSSendingURL();
    }

    protected function getBaseUrl(): string
    {
        $baseUrl = $this->getConfig('base_url');

        if (empty($baseUrl)) {
            throw new InvalidSMSConfigurationException('Invalid SignalAds SMS provider base_url config.');
        }

        return $baseUrl;
    }

    /**
     * @throws InvalidSMSConfigurationException
     * @throws SendingSMSFailedException
     */
    protected function callApi(string $url, array $data): array
    {
        $token = $this->getConfig('token');

        if (empty($token)) {
            throw new InvalidSMSConfigurationException('invalid SignalAds token sms provider config');
        }

        $response = Http::asJson()
            ->acceptJson()
            ->withToken($token)
            ->post($url, $data);

        $responseJson = $response->json();
        $statusCode = $response->status();

        if (!is_array($responseJson)) {
            throw new SendingSMSFailedException(
                'Invalid response from SignalAds: ' . $response->body(),
                $statusCode,
            );
        }

        if (!$this->isDocumentedStatusCode($statusCode)) {
            throw new SendingSMSFailedException(
                'Invalid response from SignalAds: ' . $response->body(),
                $statusCode,
            );
        }

        if ($statusCode !== 200) {
            throw new SendingSMSFailedException(
                $this->getStatusMessage($statusCode),
                $statusCode,
            );
        }

        if (($responseJson['success'] ?? false) !== true) {
            throw new SendingSMSFailedException(
                $this->extractErrorMessage($responseJson),
                $statusCode,
            );
        }

        return $responseJson;
    }

    protected function isDocumentedStatusCode(int $statusCode): bool
    {
        return in_array($statusCode, [200, 400, 401, 422, 500], true);
    }

    protected function getStatusMessage(int $statusCode): string
    {
        return match ($statusCode) {
            200 => 'درخواست با موفقیت انجام شد',
            400 => 'درخواست نامعتبر است',
            401 => 'توکن احراز هویت نامعتبر یا منقضی شده است',
            422 => 'خطای اعتبارسنجی در پارامترهای ورودی',
            500 => 'خطای داخلی سرور',
            default => 'خطای ناشناخته رخ داده است.',
        };
    }

    protected function extractErrorMessage(array $responseJson): string
    {
        return $responseJson['message']
            ?? $responseJson['error']
            ?? $responseJson['detail'][0]['msg']
            ?? 'ارسال پیامک با سیگنال ناموفق بود.';
    }

    protected function extractMessageId(array $responseJson): ?int
    {
        return isset($responseJson['id']) ? (int) $responseJson['id'] : null;
    }

    protected function extractBulkResponses(array $responseJson): array
    {
        return [
            new SendSMSResponse(
                $this->extractMessageId($responseJson),
                null,
            ),
        ];
    }
}
