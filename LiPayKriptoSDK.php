<?php
/**
 * LiPayKriptoSDK PHP SDK
 * 
 * Bu SDK, LiPayKripto ödeme sisteminin PHP entegrasyonunu basitleştirir.
 * Kullanıcıların token alıp, ödeme talebi oluşturmasını ve ödeme durumunu sorgulamasını sağlar.
 * 
 * @version 1.0.0
 * @author LiPayKripto
 */

class LiPayKriptoSDK {
    /**
     * API endpoint'leri
     */
    const API_URL = "https://lipaykripto.com/api";
    
    /**
     * Client ID
     * @var string
     */
    private $clientId;
    
    /**
     * Client Secret
     * @var string
     */
    private $clientSecret;
    
    /**
     * API Token
     * @var string|null
     * @deprecated Her istek için yeni token alınacağından kullanılmıyor
     */
    private $token = null;
    
    /**
     * SDK'yı başlatır
     * 
     * @param string $clientId Client ID
     * @param string $clientSecret Client Secret
     */
    public function __construct(string $clientId, string $clientSecret) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }
    
    /**
     * Ödeme talebi oluşturur
     * 
     * @param float $amount Ödeme miktarı (TL cinsinden)
     * @param string $paymentId İşlem referans numarası
     * @param string $webhookUrl Webhook URL (zorunlu)
     * @return array Ödeme bilgileri
     * @throws Exception Token alınamaz veya ödeme talebi oluşturulamazsa
     */
    public function createPayment(float $amount, string $paymentId, string $webhookUrl): array {
        // Önce token al
        $tokenResponse = $this->apiRequest('/auth/token', [
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret
        ]);
        
        if (!isset($tokenResponse['token'])) {
            throw new Exception('Token alınamadı: ' . ($tokenResponse['error'] ?? 'Bilinmeyen hata'));
        }
        
        $token = $tokenResponse['token'];
        
        // Ödeme talebini oluştur
        $paymentData = [
            'tryAmount' => $amount,
            'paymentId' => $paymentId,
            'webhookUrl' => $webhookUrl
        ];
        
        $response = $this->apiRequest('/external-payment-request', $paymentData, $token);
        
        if (isset($response['success']) && $response['success'] === true) {
            return $response;
        } else {
            throw new Exception('Ödeme talebi oluşturulamadı: ' . ($response['error'] ?? 'Bilinmeyen hata'));
        }
    }
    
    /**
     * Çekim talebi oluşturur
     * 
     * @param float $amount Çekim miktarı (TL cinsinden)
     * @param string $requestId İşlem referans numarası
     * @param string $walletAddress Çekim yapılacak cüzdan adresi
     * @param string $coinType Çekim yapılacak kripto para birimi (TRX, USDT veya ETH)
     * @param string $webhookUrl Webhook URL (zorunlu)
     * @param string $createdAt İşlemin oluşturulma tarihi (zorunlu, ISO 8601 formatında)
     * @return array Çekim talebi bilgileri
     * @throws Exception Çekim talebi oluşturulamazsa
     */
    public function createWithdraw(float $amount, string $requestId, string $walletAddress, string $coinType, string $webhookUrl, string $createdAt): array {
        // Çekim verilerini hazırla
        $withdrawData = [
            'try_amount' => $amount,
            'clientId' => $this->clientId,
            'requestId' => $requestId,
            'wallet_address' => $walletAddress,
            'coin_type' => strtoupper($coinType),
            'webhook_url' => $webhookUrl,
            'created_at' => $createdAt
        ];
        
        // HMAC imzası oluştur
        $signatureBase = $amount . $this->clientId . $requestId . $walletAddress . strtoupper($coinType) . $webhookUrl . $createdAt;
        $hmac = hash_hmac('sha256', $signatureBase, $this->clientSecret);
        
        // İmzayı ekle
        $withdrawData['signature'] = $hmac;
        
        // Çekim talebini gönder
        $response = $this->apiRequest('/withdraw', $withdrawData, false);
        
        if (isset($response['success']) && $response['success'] === true) {
            return $response;
        } else {
            throw new Exception('Çekim talebi oluşturulamadı: ' . ($response['error'] ?? 'Bilinmeyen hata'));
        }
    }
    

    


    
    /**
     * API isteği yapar
     * 
     * @param string $endpoint API endpoint'i
     * @param array $data İstek verisi
     * @param string|bool $token Kullanılacak token veya false
     * @return array API yanıtı
     * @throws Exception İstek başarısız olursa
     */
    private function apiRequest(string $endpoint, array $data = [], $token = false): array {
        $url = self::API_URL . $endpoint;
        
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        
        if ($token && is_string($token)) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        // Sadece POST işlemleri yapılıyor
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception('API isteği başarısız: ' . $error);
        }
        
        $decodedResponse = json_decode($response, true);
        
        if ($httpCode >= 400) {
            throw new Exception('API hatası (HTTP ' . $httpCode . '): ' . ($decodedResponse['error'] ?? 'Bilinmeyen hata'));
        }
        
        return $decodedResponse;
    }
}