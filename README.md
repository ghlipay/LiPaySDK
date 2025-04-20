# LiPayKriptoSDK Integration Guide

This documentation provides comprehensive information for integrating the LiPayKripto payment and withdrawal system.

## Integration Steps

### 1. Obtain Required Information
- Client ID
- Client Secret
- SDK file (PHP, JavaScript, or Python)

To obtain this information, please contact us at [support@lipaykripto.com](mailto:support@lipaykripto.com).

### 2. Load the SDK File

Include the SDK file in your project according to your programming language:

**For PHP:**
```php
require_once 'LiPayKriptoSDK.php';
```

**For JavaScript:**
```javascript
// In web projects
<script src="LiPayKriptoSDK.js"></script>

// In Node.js projects
const LiPayKriptoSDK = require('./LiPayKriptoSDK');
```

**For Python:**
```python
from lipaykripto_sdk import LiPayKriptoSDK
```

### 3. Payment Integration

Follow these steps to implement payment integration:

1. Initialize the SDK
2. Create a payment request
3. Redirect the user to the payment URL
4. Receive payment result via webhook

**PHP Example:**
```php
// Initialize SDK
$lipay = new LiPayKriptoSDK('YOUR_CLIENT_ID', 'YOUR_CLIENT_SECRET');

// Create payment request
try {
    $payment = $lipay->createPayment(
        100.00,                       // Amount (TRY)
        'ORDER123',                   // Unique order number
        'https://example.com/webhook' // Webhook URL (required)
    );
    
    // Redirect user to payment page
    header('Location: ' . $payment['paymentUrl']);
    exit;
} catch (Exception $e) {
    echo "Payment error: " . $e->getMessage();
}
```

**JavaScript Example:**
```javascript
// Initialize SDK
const lipay = new LiPayKriptoSDK('YOUR_CLIENT_ID', 'YOUR_CLIENT_SECRET');

// Create payment request
lipay.createPayment(100.00, 'ORDER123', 'https://example.com/webhook')
  .then(payment => {
    // Redirect user to payment page
    window.location.href = payment.paymentUrl;
  })
  .catch(error => {
    console.error(`Payment error: ${error.message}`);
  });
```

**Python Example:**
```python
# Initialize SDK
lipay = LiPayKriptoSDK("YOUR_CLIENT_ID", "YOUR_CLIENT_SECRET")

# Create payment request
try:
    payment = lipay.create_payment(
        100.00,                       # Amount (TRY)
        "ORDER123",                   # Unique order number
        "https://example.com/webhook" # Webhook URL (required)
    )
    
    # Print payment URL or redirect user
    print(f"Payment URL: {payment['paymentUrl']}")
    # In a web application, you can redirect
    # redirect(payment['paymentUrl'])
    
except Exception as e:
    print(f"Payment error: {str(e)}")
```

### 4. Withdrawal Integration

Follow these steps to implement withdrawal:

1. Initialize the SDK
2. Create a withdrawal request
3. Receive withdrawal result via webhook

**PHP Example:**
```php
// Initialize SDK
$lipay = new LiPayKriptoSDK('YOUR_CLIENT_ID', 'YOUR_CLIENT_SECRET');

// Create withdrawal request
try {
    $withdraw = $lipay->createWithdraw(
        250.00,                         // Amount in TRY
        'WITHDRAW123',                  // Unique withdrawal reference number
        'TXxxxxxxxxxxxxxxxxxxxxxxxxx',  // Customer wallet address
        'TRX',                          // Cryptocurrency (TRX, USDT, ETH)
        'https://example.com/webhook',  // Webhook URL (required)
        date('Y-m-d\TH:i:s\Z')          // Creation date (ISO 8601 format)
    );
    
    // Process withdrawal request result
    $withdrawId = $withdraw['data']['withdrawId'];
    echo "Withdrawal request created - ID: " . $withdrawId;
    
} catch (Exception $e) {
    echo "Withdrawal error: " . $e->getMessage();
}
```

**JavaScript Example:**
```javascript
// Initialize SDK
const lipay = new LiPayKriptoSDK('YOUR_CLIENT_ID', 'YOUR_CLIENT_SECRET');

// Create withdrawal request
lipay.createWithdraw(
    250.00,                        // Amount in TRY
    'WITHDRAW123',                 // Unique withdrawal reference number
    'TXxxxxxxxxxxxxxxxxxxxxxxxxx', // Customer wallet address
    'TRX',                         // Cryptocurrency
    'https://example.com/webhook', // Webhook URL (required)
    new Date().toISOString()       // Creation date (ISO 8601)
)
  .then(withdraw => {
    const withdrawId = withdraw.data.withdrawId;
    console.log(`Withdrawal request created - ID: ${withdrawId}`);
  })
  .catch(error => {
    console.error(`Withdrawal error: ${error.message}`);
  });
```

**Python Example:**
```python
import datetime

# Initialize SDK
lipay = LiPayKriptoSDK("YOUR_CLIENT_ID", "YOUR_CLIENT_SECRET")

# Create withdrawal request
try:
    withdraw = lipay.create_withdraw(
        250.00,                           # Amount in TRY
        "WITHDRAW123",                    # Unique withdrawal reference number
        "TXxxxxxxxxxxxxxxxxxxxxxxxxx",    # Customer wallet address
        "TRX",                            # Cryptocurrency
        "https://example.com/webhook",    # Webhook URL (required)
        datetime.datetime.now().isoformat() # Creation date (ISO 8601)
    )
    
    withdraw_id = withdraw['data']['withdrawId']
    print(f"Withdrawal request created - ID: {withdraw_id}")
    
except Exception as e:
    print(f"Withdrawal error: {str(e)}")
```

### 5. Webhook Processing

Your webhook URL will be notified by our system when the payment or withdrawal status changes.

## Webhook Notification Formats

Our system sends notifications to your webhook URL (specified during integration) in the following formats when payment and withdrawal transaction statuses change. Your webhook URL should be properly configured to receive and process these notifications.

### Payment Transaction Notifications

When payment status changes, a POST request is sent to your webhook URL in the following format:

```json
{
  "try_amount": 100.00,
  "client_id": "YOUR_CLIENT_ID",
  "payment_id": "ORDER123",
  "status": "confirmed"  // or "failed"
}
```

### Withdrawal Transaction Notifications

When withdrawal status changes, a POST request is sent to your webhook URL in the following format:

```json
{
  "success": true,
  "clientId": "YOUR_CLIENT_ID",
  "requestId": "WITHDRAW123",
  "tryAmount": "250.00",
  "status": "completed"  // or "failed", "pending", "processing", "manual"
}
```

When you receive any webhook notification, you should return an HTTP status code 200. If your server does not return a 200 status code, the system will attempt to resend the notification at specific intervals.

## Important Notes

1. Use a unique `payment_id` for each payment transaction
2. Use a unique `requestId` for each withdrawal transaction
3. Send a valid date in ISO 8601 format for withdrawal transactions (e.g., `2025-04-20T03:28:36Z`)
4. Never use your Client Secret on the client side (front-end)
5. Ensure your webhook URL is externally accessible and uses HTTPS

## Technical Support

If you encounter any issues during the integration process, please contact us at [support@lipaykripto.com](mailto:support@lipaykripto.com).
