/**
 * LiPayKriptoSDK JavaScript SDK
 * 
 * Bu SDK, LiPayKripto ödeme sisteminin JavaScript entegrasyonunu basitleştirir.
 * Kullanıcıların token alıp, ödeme talebi oluşturmasını ve ödeme durumunu sorgulamasını sağlar.
 * 
 * @version 1.0.0
 * @author LiPayKripto
 */

class LiPayKriptoSDK {
  /**
   * SDK'yı başlatır
   * 
   * @param {string} clientId Client ID
   * @param {string} clientSecret Client Secret
   * @param {Object} options Opsiyonel yapılandırma
   * @param {string} options.apiUrl API URL (default: "https://lipaykripto.com/api")
   */
  constructor(clientId, clientSecret, options = {}) {
    this.clientId = clientId;
    this.clientSecret = clientSecret;
    this.apiUrl = options.apiUrl || "https://lipaykripto.com/api";
  }

  /**
   * Ödeme talebi oluşturur
   * 
   * @param {number} amount Ödeme miktarı (TL cinsinden)
   * @param {string} paymentId İşlem referans numarası
   * @param {string} webhookUrl Webhook URL (zorunlu)
   * @returns {Promise<Object>} Ödeme bilgileri
   * @throws {Error} Token alınamaz veya ödeme talebi oluşturulamazsa
   */
  async createPayment(amount, paymentId, webhookUrl) {
    // Önce token al
    const tokenResponse = await this.apiRequest('/auth/token', {
      clientId: this.clientId,
      clientSecret: this.clientSecret
    });
    
    if (!tokenResponse.token) {
      throw new Error(`Token alınamadı: ${tokenResponse.error || 'Bilinmeyen hata'}`);
    }
    
    // Ödeme talebini oluştur
    const paymentData = {
      tryAmount: amount,
      paymentId: paymentId,
      webhookUrl: webhookUrl
    };
    
    const response = await this.apiRequest('/external-payment-request', paymentData, tokenResponse.token);
    
    if (response.success === true) {
      return response;
    } else {
      throw new Error(`Ödeme talebi oluşturulamadı: ${response.error || 'Bilinmeyen hata'}`);
    }
  }
  
  /**
   * Çekim talebi oluşturur
   * 
   * @param {number} amount Çekim miktarı (TL cinsinden)
   * @param {string} requestId İşlem referans numarası
   * @param {string} walletAddress Çekim yapılacak cüzdan adresi
   * @param {string} coinType Çekim yapılacak kripto para birimi (TRX, USDT veya ETH)
   * @param {string} webhookUrl Webhook URL (zorunlu)
   * @param {string} createdAt İşlemin oluşturulma tarihi (zorunlu, ISO 8601 formatında)
   * @returns {Promise<Object>} Çekim talebi bilgileri
   * @throws {Error} Çekim talebi oluşturulamazsa
   */
  async createWithdraw(amount, requestId, walletAddress, coinType, webhookUrl, createdAt) {
    // Çekim verilerini hazırla
    const withdrawData = {
      try_amount: amount,
      clientId: this.clientId,
      requestId: requestId,
      wallet_address: walletAddress,
      coin_type: coinType.toUpperCase(),
      webhook_url: webhookUrl,
      created_at: createdAt
    };
    
    // HMAC imzası oluştur
    const crypto = require('crypto');
    const signatureBase = `${amount}${this.clientId}${requestId}${walletAddress}${coinType.toUpperCase()}${webhookUrl}${createdAt}`;
    const hmac = crypto.createHmac('sha256', this.clientSecret);
    hmac.update(signatureBase);
    const signature = hmac.digest('hex');
    
    // İmzayı ekle
    withdrawData.signature = signature;
    
    // Çekim talebini gönder
    const response = await this.apiRequest('/withdraw', withdrawData, false);
    
    if (response.success === true) {
      return response;
    } else {
      throw new Error(`Çekim talebi oluşturulamadı: ${response.error || 'Bilinmeyen hata'}`);
    }
  }


  




  /**
   * API isteği yapar
   * 
   * @param {string} endpoint API endpoint'i
   * @param {Object} [data] İstek verisi
   * @param {string|boolean} [token=false] Kullanılacak token veya false
   * @returns {Promise<Object>} API yanıtı
   * @throws {Error} İstek başarısız olursa
   */
  async apiRequest(endpoint, data = {}, token = false) {
    const url = `${this.apiUrl}${endpoint}`;
    
    const headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    };
    
    if (token && typeof token === 'string') {
      headers['Authorization'] = `Bearer ${token}`;
    }
    
    const fetchOptions = {
      method: 'POST', // Sadece POST metodu kullanılıyor
      headers,
      mode: 'cors',
      cache: 'no-cache',
      credentials: 'same-origin',
      body: JSON.stringify(data)
    };
    
    try {
      const response = await fetch(url, fetchOptions);
      const responseData = await response.json();
      
      if (!response.ok) {
        throw new Error(`API hatası (HTTP ${response.status}): ${responseData.error || 'Bilinmeyen hata'}`);
      }
      
      return responseData;
    } catch (error) {
      throw new Error(`API isteği başarısız: ${error.message}`);
    }
  }
}

// Node.js ortamında kullanım için
if (typeof module !== 'undefined' && module.exports) {
  module.exports = LiPayKriptoSDK;
}