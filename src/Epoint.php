<?php

namespace AZPayments\Epoint;

use Illuminate\Support\Facades\Log;

class Epoint
{
    protected string $publicKey;
    protected string $privateKey;
    protected string $apiUrl;
    protected string $statusUrl;

    public function __construct()
    {
        $this->publicKey = config('epoint.public_key');
        $this->privateKey = config('epoint.private_key');
        $this->apiUrl = config('epoint.api_url');
        $this->statusUrl = config('epoint.status_url');
    }

    /**
     * Ödəniş yarat
     */
    public function createPayment(array $params): array
    {
        $data = [
            'public_key' => $this->publicKey,
            'amount' => $params['amount'],
            'currency' => $params['currency'] ?? config('epoint.currency'),
            'language' => $params['language'] ?? config('epoint.language'),
            'order_id' => $params['order_id'],
            'description' => $params['description'] ?? config('epoint.description'),
            'success_redirect_url' => $params['success_url'] ?? url(config('epoint.success_url')),
            'error_redirect_url' => $params['error_url'] ?? url(config('epoint.error_url')),
        ];

        return $this->sendRequest($data, $this->apiUrl);
    }

    /**
     * Ödəniş statusunu yoxla
     */
    public function getStatus(string $transactionId): array
    {
        $data = [
            'public_key' => $this->publicKey,
            'transaction' => $transactionId,
        ];

        return $this->sendRequest($data, $this->statusUrl);
    }

    /**
     * Callback-i verify et
     */
    public function verifyCallback(string $data, string $signature): bool
    {
        $expected = $this->generateSignature($data);
        return hash_equals($expected, $signature);
    }

    /**
     * Callback datasını decode et
     */
    public function decodeCallback(string $data): array
    {
        $decoded = base64_decode($data);
        return json_decode($decoded, true) ?? [];
    }

    /**
     * Signature yarat
     */
    protected function generateSignature(string $data): string
    {
        return base64_encode(sha1($this->privateKey . $data . $this->privateKey, true));
    }

    /**
     * API sorğusu göndər
     */
    protected function sendRequest(array $data, string $url): array
    {
        try {
            $dataBase64 = base64_encode(json_encode($data));
            $signature = $this->generateSignature($dataBase64);

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query([
                    'data' => $dataBase64,
                    'signature' => $signature,
                ]),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/x-www-form-urlencoded',
                ],
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                Log::error('Epoint CURL error: ' . $error);
                return ['status' => 'error', 'message' => $error];
            }

            $result = json_decode($response, true) ?? [];
            $result['http_code'] = $httpCode;

            Log::info('Epoint response', $result);

            return $result;

        } catch (\Exception $e) {
            Log::error('Epoint exception: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}