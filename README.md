# Epoint Laravel Payment Gateway

Laravel üçün Epoint.az ödəniş sistemi inteqrasiyası. Azərbaycan e-ticarət layihələri üçün asan inteqrasiya.

## 🚀 Quraşdırma

```bash
composer require azpayments/epoint-laravel
```

## ⚙️ Konfiqurasiya

`.env` faylına əlavə edin:

```env
EPOINT_PUBLIC_KEY=your_public_key
EPOINT_PRIVATE_KEY=your_private_key
EPOINT_SUCCESS_URL=/payment/success
EPOINT_ERROR_URL=/payment/error
```

Config faylını publish edin (istəyə bağlı):

```bash
php artisan vendor:publish --tag=epoint-config
```

## 📖 İstifadə

### Ödəniş yaratmaq

```php
use AZPayments\Epoint\Facades\Epoint;

$result = Epoint::createPayment([
    'amount' => 100.00,
    'order_id' => 'ORDER-123',
    'description' => 'Sifariş ödənişi',
]);

if (isset($result['redirect_url'])) {
    return redirect($result['redirect_url']);
}
```

### Ödəniş statusunu yoxlamaq

```php
$status = Epoint::getStatus($transactionId);
```

### Callback işləmək

Paket avtomatik olaraq `/api/epoint/callback` route qeydiyyat edir.

Callback hadisələrini dinləmək üçün `EventServiceProvider`-da:

```php
use AZPayments\Epoint\Events\PaymentSuccess;
use AZPayments\Epoint\Events\PaymentFailed;

protected $listen = [
    PaymentSuccess::class => [
        YourPaymentSuccessListener::class,
    ],
    PaymentFailed::class => [
        YourPaymentFailedListener::class,
    ],
];
```

### Listener nümunəsi

```php
<?php

namespace App\Listeners;

use AZPayments\Epoint\Events\PaymentSuccess;

class YourPaymentSuccessListener
{
    public function handle(PaymentSuccess $event)
    {
        $payload = $event->payload;
        
        // Sifarişi yenilə
        // Order::where('id', $payload['order_id'])->update(['status' => 'paid']);
    }
}
```

## 🔧 Mövcud metodlar

| Metod | Təsvir |
|-------|--------|
| `createPayment(array $params)` | Yeni ödəniş yarat |
| `getStatus(string $transactionId)` | Ödəniş statusunu yoxla |
| `verifyCallback(string $data, string $signature)` | Callback imzasını yoxla |
| `decodeCallback(string $data)` | Callback datasını decode et |

## 📋 createPayment parametrləri

| Parametr | Tip | Məcburi | Təsvir |
|----------|-----|---------|--------|
| amount | float | ✅ | Ödəniş məbləği |
| order_id | string | ✅ | Sifariş ID |
| description | string | ❌ | Ödəniş təsviri |
| currency | string | ❌ | Valyuta (default: AZN) |
| language | string | ❌ | Dil (default: az) |

## 📄 Lisenziya

MIT License

## 👨‍💻 Müəllif

Elgun Heydarli - elgunhaydarli@gmail.com