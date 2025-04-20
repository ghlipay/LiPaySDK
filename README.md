# LiPayKriptoSDK Entegrasyon Rehberi

Bu dokümantasyon, LiPayKripto ödeme ve çekim sistemininin entegrasyonu için kapsamlı bilgiler sunar.

## Entegrasyon Adımları

### 1. Gerekli Bilgileri Edinin
- Client ID (müşteri kimliği)
- Client Secret (gizli anahtar)
- SDK dosyası (PHP, JavaScript veya Python)

Bu bilgileri edinmek için [support@lipaykripto.com](mailto:support@lipaykripto.com) adresinden bizimle iletişime geçebilirsiniz.

### 2. SDK Dosyasını Yükleyin

Programlama dilinize göre SDK dosyasını projenize dahil edin:

**PHP için:**
```php
require_once 'LiPayKriptoSDK.php';
```

**JavaScript için:**
```javascript
// Web projelerinde
<script src="LiPayKriptoSDK.js"></script>

// Node.js projelerinde
const LiPayKriptoSDK = require('./LiPayKriptoSDK');
```

**Python için:**
```python
from lipaykripto_sdk import LiPayKriptoSDK
```

### 3. Ödeme Entegrasyonu

Aşağıdaki adımları izleyerek ödeme entegrasyonunu gerçekleştirin:

1. SDK'yı başlatın
2. Ödeme talebi oluşturun
3. Kullanıcıyı ödeme URL'sine yönlendirin
4. Webhook ile ödeme sonucunu alın

**PHP Örneği:**
```php
// SDK'yı başlat
$lipay = new LiPayKriptoSDK('YOUR_CLIENT_ID', 'YOUR_CLIENT_SECRET');

// Ödeme talebi oluştur
try {
    $payment = $lipay->createPayment(
        100.00,                       // Tutar (TL)
        'ORDER123',                   // Benzersiz sipariş numarası
        'https://example.com/webhook' // Webhook URL (zorunlu)
    );
    
    // Kullanıcıyı ödeme sayfasına yönlendir
    header('Location: ' . $payment['paymentUrl']);
    exit;
} catch (Exception $e) {
    echo "Ödeme hatası: " . $e->getMessage();
}
```

**JavaScript Örneği:**
```javascript
// SDK'yı başlat
const lipay = new LiPayKriptoSDK('YOUR_CLIENT_ID', 'YOUR_CLIENT_SECRET');

// Ödeme talebi oluştur
lipay.createPayment(100.00, 'ORDER123', 'https://example.com/webhook')
  .then(payment => {
    // Kullanıcıyı ödeme sayfasına yönlendir
    window.location.href = payment.paymentUrl;
  })
  .catch(error => {
    console.error(`Ödeme hatası: ${error.message}`);
  });
```

**Python Örneği:**
```python
# SDK'yı başlat
lipay = LiPayKriptoSDK("YOUR_CLIENT_ID", "YOUR_CLIENT_SECRET")

# Ödeme talebi oluştur
try:
    payment = lipay.create_payment(
        100.00,                       # Tutar (TL)
        "ORDER123",                   # Benzersiz sipariş numarası
        "https://example.com/webhook" # Webhook URL (zorunlu)
    )
    
    # Ödeme URL'ini yazdır veya kullanıcıyı yönlendir
    print(f"Ödeme URL: {payment['paymentUrl']}")
    # Web uygulamasında yönlendirme yapabilirsiniz
    # redirect(payment['paymentUrl'])
    
except Exception as e:
    print(f"Ödeme hatası: {str(e)}")
```

### 4. Çekim (Para Çekme) Entegrasyonu

Çekim talebi oluşturmak için aşağıdaki adımları izleyin:

1. SDK'yı başlatın
2. Çekim talebi oluşturun
3. Webhook ile çekim sonucunu alın

**PHP Örneği:**
```php
// SDK'yı başlat
$lipay = new LiPayKriptoSDK('YOUR_CLIENT_ID', 'YOUR_CLIENT_SECRET');

// Çekim talebi oluştur
try {
    $withdraw = $lipay->createWithdraw(
        250.00,                         // TL cinsinden tutar 
        'WITHDRAW123',                  // Benzersiz çekim referans numarası
        'TXxxxxxxxxxxxxxxxxxxxxxxxxx',  // Müşteri cüzdan adresi
        'TRX',                          // Kripto para birimi (TRX, USDT, ETH)
        'https://example.com/webhook',  // Webhook URL (zorunlu)
        date('Y-m-d\TH:i:s\Z')          // Oluşturulma tarihi (ISO 8601 formatında)
    );
    
    // Çekim talebinin sonucunu işle
    $withdrawId = $withdraw['data']['withdrawId'];
    echo "Çekim talebi oluşturuldu - ID: " . $withdrawId;
    
} catch (Exception $e) {
    echo "Çekim hatası: " . $e->getMessage();
}
```

**JavaScript Örneği:**
```javascript
// SDK'yı başlat
const lipay = new LiPayKriptoSDK('YOUR_CLIENT_ID', 'YOUR_CLIENT_SECRET');

// Çekim talebi oluştur
lipay.createWithdraw(
    250.00,                        // TL cinsinden tutar
    'WITHDRAW123',                 // Benzersiz çekim referans numarası
    'TXxxxxxxxxxxxxxxxxxxxxxxxxx', // Müşteri cüzdan adresi
    'TRX',                         // Kripto para birimi
    'https://example.com/webhook', // Webhook URL (zorunlu)
    new Date().toISOString()       // Oluşturulma tarihi (ISO 8601)
)
  .then(withdraw => {
    const withdrawId = withdraw.data.withdrawId;
    console.log(`Çekim talebi oluşturuldu - ID: ${withdrawId}`);
  })
  .catch(error => {
    console.error(`Çekim hatası: ${error.message}`);
  });
```

**Python Örneği:**
```python
import datetime

# SDK'yı başlat
lipay = LiPayKriptoSDK("YOUR_CLIENT_ID", "YOUR_CLIENT_SECRET")

# Çekim talebi oluştur
try:
    withdraw = lipay.create_withdraw(
        250.00,                           # TL cinsinden tutar
        "WITHDRAW123",                    # Benzersiz çekim referans numarası
        "TXxxxxxxxxxxxxxxxxxxxxxxxxx",    # Müşteri cüzdan adresi
        "TRX",                            # Kripto para birimi
        "https://example.com/webhook",    # Webhook URL (zorunlu)
        datetime.datetime.now().isoformat() # Oluşturulma tarihi (ISO 8601)
    )
    
    withdraw_id = withdraw['data']['withdrawId']
    print(f"Çekim talebi oluşturuldu - ID: {withdraw_id}")
    
except Exception as e:
    print(f"Çekim hatası: {str(e)}")
```

### 5. Webhook İşleme

Webhook URL'niz, ödeme veya çekim durumu değiştiğinde sistemimiz tarafından bilgilendirilecektir.

## Webhook Bildirim Formatları

Sistemimiz, ödeme ve çekim işlemlerinin durumu değiştiğinde, entegrasyon sırasında belirttiğiniz Webhook URL'nize bildirimleri aşağıdaki formatlarda gönderir. Webhook URL'niz bu bildirimleri almak ve işlemek için uygun şekilde yapılandırılmalıdır.

### Ödeme İşlemi Bildirimleri

Ödeme durumu değiştiğinde, webhook URL'nize aşağıdaki formatta bir POST isteği gönderilir:

```json
{
  "try_amount": 100.00,
  "client_id": "YOUR_CLIENT_ID",
  "payment_id": "ORDER123",
  "status": "confirmed"  // veya "failed"
}
```

### Çekim İşlemi Bildirimleri

Çekim durumu değiştiğinde, webhook URL'nize aşağıdaki formatta bir POST isteği gönderilir:

```json
{
  "success": true,
  "clientId": "YOUR_CLIENT_ID",
  "requestId": "WITHDRAW123",
  "tryAmount": "250.00",
  "status": "completed"  // veya "failed", "pending", "processing", "manual"
}
```

Tüm webhook bildirimlerini aldığınızda, 200 HTTP durum kodunu döndürmelisiniz. Sunucunuz 200 durum kodu dönmezse, sistem belirli aralıklarla bildirimi tekrar göndermeye çalışacaktır.

## Önemli Notlar

1. Her ödeme işlemi için benzersiz bir `payment_id` kullanın
2. Her çekim işlemi için benzersiz bir `requestId` kullanın
3. Çekim işlemlerinde ISO 8601 formatında geçerli bir tarih gönderin (örn: `2025-04-20T03:28:36Z`)
4. Client Secret bilginizi asla istemci tarafında (front-end) kullanmayın
5. Webhook URL'nizin dışarıdan erişilebilir ve HTTPS olduğundan emin olun

## Teknik Destek

Entegrasyon sürecinde herhangi bir sorunla karşılaşırsanız, [support@lipaykripto.com](mailto:support@lipaykripto.com) adresinden bizimle iletişime geçebilirsiniz.