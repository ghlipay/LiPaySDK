"""
LiPayKriptoSDK Python SDK

Bu SDK, LiPayKripto ödeme sisteminin Python entegrasyonunu basitleştirir.
Kullanıcıların token alıp, ödeme talebi oluşturmasını ve ödeme durumunu sorgulamasını sağlar.

Kullanım Örneği:
-----------------
from lipaykripto_sdk import LiPayKriptoSDK

# SDK'yı başlat
lipay = LiPayKriptoSDK("YOUR_CLIENT_ID", "YOUR_CLIENT_SECRET")

# Ödeme talebi oluştur
payment = lipay.create_payment(100.00, "ORDER123", "https://example.com/webhook")
print(f"Ödeme URL: {payment['paymentUrl']}")

# Ödeme durumunu sorgula
status = lipay.get_payment_status("ORDER123")
print(f"Ödeme Durumu: {status['status']}")
"""

import json
import time
import requests
from typing import Dict, Optional, Any, Union


class LiPayKriptoSDK:
    """LiPayKripto API entegrasyonu için SDK."""

    def __init__(self, client_id: str, client_secret: str, api_url: str = "https://lipaykripto.com/api"):
        """
        LiPayKripto SDK'sını başlatır.

        Args:
            client_id: API entegrasyonu için Client ID
            client_secret: API entegrasyonu için Client Secret
            api_url: API endpoint'i (opsiyonel, varsayılan: "https://lipaykripto.com/api")
        """
        self.client_id = client_id
        self.client_secret = client_secret
        self.api_url = api_url

    def create_payment(self, amount: float, payment_id: str, webhook_url: str) -> Dict[str, Any]:
        """
        Ödeme talebi oluşturur.

        Args:
            amount: Ödeme miktarı (TL cinsinden)
            payment_id: İşlem referans numarası
            webhook_url: Ödeme durumu değiştiğinde bildirim yapılacak URL (zorunlu)

        Returns:
            Ödeme bilgilerini içeren sözlük

        Raises:
            Exception: Token alınamaz veya ödeme talebi oluşturulamazsa
        """
        # Önce token al
        token_response = self._api_request("/auth/token", {
            "clientId": self.client_id,
            "clientSecret": self.client_secret
        })

        if "token" not in token_response:
            raise Exception(f"Token alınamadı: {token_response.get('error', 'Bilinmeyen hata')}")
        
        token = token_response["token"]

        # Ödeme talebini oluştur
        payment_data = {
            "tryAmount": amount,
            "paymentId": payment_id,
            "webhookUrl": webhook_url
        }

        response = self._api_request("/external-payment-request", payment_data, token=token)

        if response.get("success") is True:
            return response
        else:
            raise Exception(f"Ödeme talebi oluşturulamadı: {response.get('error', 'Bilinmeyen hata')}")
            
    def create_withdraw(self, amount: float, request_id: str, wallet_address: str, coin_type: str, webhook_url: str, created_at: str) -> Dict[str, Any]:
        """
        Çekim talebi oluşturur.
        
        Args:
            amount: Çekim miktarı (TL cinsinden)
            request_id: İşlem referans numarası
            wallet_address: Çekim yapılacak cüzdan adresi
            coin_type: Çekim yapılacak kripto para birimi (TRX, USDT veya ETH)
            webhook_url: Çekim durumu değiştiğinde bildirim yapılacak URL (zorunlu)
            created_at: İşlemin oluşturulma tarihi (zorunlu, ISO 8601 formatında)
            
        Returns:
            Çekim talebi bilgilerini içeren sözlük
            
        Raises:
            Exception: Çekim talebi oluşturulamazsa
        """
        import hashlib
        import hmac
        
        # Çekim verilerini hazırla
        withdraw_data = {
            "try_amount": amount,
            "clientId": self.client_id,
            "requestId": request_id,
            "wallet_address": wallet_address,
            "coin_type": coin_type.upper(),
            "webhook_url": webhook_url,
            "created_at": created_at
        }
            
        # HMAC imzası oluştur
        signature_base = f"{amount}{self.client_id}{request_id}{wallet_address}{coin_type.upper()}{webhook_url}{created_at}"
        signature = hmac.new(
            self.client_secret.encode('utf-8'),
            signature_base.encode('utf-8'),
            hashlib.sha256
        ).hexdigest()
        
        # İmzayı ekle
        withdraw_data["signature"] = signature
        
        # Çekim talebini gönder
        response = self._api_request("/withdraw", withdraw_data)
        
        if response.get("success") is True:
            return response
        else:
            raise Exception(f"Çekim talebi oluşturulamadı: {response.get('error', 'Bilinmeyen hata')}")


            




    def _api_request(self, endpoint: str, data: Optional[Dict[str, Any]] = None, token: Union[str, bool] = False) -> Dict[str, Any]:
        """
        API isteği yapar.

        Args:
            endpoint: API endpoint'i
            data: İstek verisi (opsiyonel)
            token: Kullanılacak token veya False (varsayılan: False)

        Returns:
            API yanıtı

        Raises:
            Exception: İstek başarısız olursa
        """
        url = f"{self.api_url}{endpoint}"

        headers = {
            "Content-Type": "application/json",
            "Accept": "application/json"
        }

        if token and isinstance(token, str):
            headers["Authorization"] = f"Bearer {token}"

        try:
            # Sadece POST istekleri yapılıyor
            response = requests.post(url, headers=headers, json=data, timeout=30)

            response_data = response.json()

            if response.status_code >= 400:
                raise Exception(f"API hatası (HTTP {response.status_code}): {response_data.get('error', 'Bilinmeyen hata')}")

            return response_data
        except requests.RequestException as e:
            raise Exception(f"API isteği başarısız: {str(e)}")
        except json.JSONDecodeError:
            raise Exception("API yanıtı geçerli JSON formatında değil")