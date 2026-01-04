# Epoint Laravel Payment Gateway

Laravel layihənizə Epoint.az ödəniş sistemini 10 dəqiqəyə qoşun.

Bu paket Azərbaycanda ən çox istifadə olunan Epoint ödəniş sistemini Laravel layihənizə asan şəkildə inteqrasiya etməyə imkan verir. Heç bir mürəkkəb konfiqurasiya tələb olunmur.

---

## Başlamazdan əvvəl nə lazımdır?

1. **PHP 8.1 və ya daha yuxarı versiya** - Terminalda `php -v` yazaraq yoxlaya bilərsiniz
2. **Laravel 10, 11 və ya 12** - İstənilən versiya işləyəcək
3. **Composer** - PHP paket meneceri
4. **Epoint.az merchant hesabı** - Epoint-dən public_key və private_key almalısınız

Epoint hesabınız yoxdursa, əvvəlcə https://epoint.az saytına daxil olub merchant hesabı açın.

---

## Quraşdırma

### Addım 1: Terminalı açın

Windows-da: `Win + R` basın, `cmd` yazın, Enter basın
Mac-da: `Cmd + Space` basın, `Terminal` yazın, Enter basın
Linux-da: `Ctrl + Alt + T` basın

### Addım 2: Laravel layihənizin qovluğuna keçin

```bash
cd /path/to/your/laravel/project
```

Məsələn:
```bash
cd C:\xampp\htdocs\my-shop
```

və ya

```bash
cd /var/www/my-shop
```

### Addım 3: Paketi yükləyin

Bu əmri terminalda yazın və Enter basın:

```bash
composer require azpayments/epoint-laravel
```

Yükləmə bitənə qədər gözləyin. "Successfully" yazısı görünməlidir.

### Addım 4: Config faylını yaradın

Bu əmri yazın:

```bash
php artisan vendor:publish --tag=epoint-config
```

Bu əmr `config/epoint.php` faylını yaradacaq.

### Addım 5: .env faylını redaktə edin

Laravel layihənizin ana qovluğunda `.env` adlı fayl var. Bu faylı istənilən mətn redaktoru ilə açın (Notepad, VS Code, Sublime Text və s.)

Faylın sonuna bu sətirləri əlavə edin:

```env
EPOINT_PUBLIC_KEY=sizin_public_key_buraya
EPOINT_PRIVATE_KEY=sizin_private_key_buraya
EPOINT_SUCCESS_URL=/payment/success
EPOINT_ERROR_URL=/payment/error
```

**Vacib:** `sizin_public_key_buraya` və `sizin_private_key_buraya` yerinə Epoint-dən aldığınız əsl açarları yazın.

Məsələn:
```env
EPOINT_PUBLIC_KEY=i000201133
EPOINT_PRIVATE_KEY=cXQ1m6dzpye7kN24Nks9OYGR
EPOINT_SUCCESS_URL=/payment/success
EPOINT_ERROR_URL=/payment/error
```

Faylı yadda saxlayın (Ctrl + S).

### Addım 6: Cache-i təmizləyin

Bu əmrləri ardıcıl yazın:

```bash
php artisan config:clear
```

```bash
php artisan cache:clear
```

Quraşdırma tamamlandı! İndi istifadəyə keçək.

---

## İstifadə - Addım-addım tam nümunə

İndi sizə ödəniş səhifəsi yaratmağı öyrədəcəyəm. Hər addımı diqqətlə izləyin.

### Addım 1: Controller yaradın

Terminalda bu əmri yazın:

```bash
php artisan make:controller PaymentController
```

Bu əmr `app/Http/Controllers/PaymentController.php` faylını yaradacaq.

### Addım 2: Controller faylını redaktə edin

`app/Http/Controllers/PaymentController.php` faylını açın.

İçindəki bütün kodu silin və bu kodu yapışdırın:

```php
<?php

namespace App\Http\Controllers;

use AZPayments\Epoint\Facades\Epoint;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Ödəniş formasını göstərir
     * İstifadəçi bu səhifədə məbləği daxil edəcək
     */
    public function checkout()
    {
        return view('payment.checkout');
    }

    /**
     * Ödənişi başladır və istifadəçini Epoint səhifəsinə yönləndirir
     * Form göndərildikdə bu metod işə düşür
     */
    public function pay(Request $request)
    {
        // Daxil edilən məlumatları yoxlayırıq
        $request->validate([
            'amount' => 'required|numeric|min:0.1',
        ]);

        // Hər sifariş üçün unikal ID yaradırıq
        // Bu ID ilə sifarişi sonradan tapa biləcəksiniz
        $orderId = 'ORDER-' . time() . '-' . rand(1000, 9999);

        // Epoint-də ödəniş yaradırıq
        $result = Epoint::createPayment([
            'amount' => $request->amount,
            'order_id' => $orderId,
            'description' => 'Sifariş #' . $orderId,
        ]);

        // Epoint bizə redirect_url qaytarırsa, istifadəçini ora yönləndiririk
        // İstifadəçi bu linkdə kart məlumatlarını daxil edəcək
        if (isset($result['redirect_url'])) {
            return redirect($result['redirect_url']);
        }

        // Əgər xəta baş verdisə, istifadəçiyə bildiririk
        return back()->with('error', $result['message'] ?? 'Xəta baş verdi. Zəhmət olmasa yenidən cəhd edin.');
    }

    /**
     * Ödəniş uğurlu olduqda istifadəçi bu səhifəyə gəlir
     * Epoint ödəniş uğurlu olduqda bura yönləndirir
     */
    public function success(Request $request)
    {
        return view('payment.success', [
            'order_id' => $request->query('order_id')
        ]);
    }

    /**
     * Ödəniş uğursuz olduqda istifadəçi bu səhifəyə gəlir
     * Epoint ödəniş uğursuz olduqda və ya istifadəçi ləğv etdikdə bura yönləndirir
     */
    public function error(Request $request)
    {
        return view('payment.error', [
            'order_id' => $request->query('order_id')
        ]);
    }
}
```

Faylı yadda saxlayın (Ctrl + S).

### Addım 3: Route-ları əlavə edin

`routes/web.php` faylını açın.

Faylın sonuna bu kodu əlavə edin:

```php
use App\Http\Controllers\PaymentController;

// Ödəniş səhifəsi - istifadəçi məbləği daxil edir
Route::get('/checkout', [PaymentController::class, 'checkout'])->name('checkout');

// Ödənişi başlat - form göndərildikdə işə düşür
Route::post('/payment/pay', [PaymentController::class, 'pay'])->name('payment.pay');

// Uğurlu ödəniş səhifəsi - Epoint uğurlu ödənişdən sonra bura yönləndirir
Route::get('/payment/success', [PaymentController::class, 'success'])->name('payment.success');

// Uğursuz ödəniş səhifəsi - Epoint uğursuz ödənişdən sonra bura yönləndirir
Route::get('/payment/error', [PaymentController::class, 'error'])->name('payment.error');
```

**Diqqət:** Əgər faylın yuxarısında artıq `use` sətirləri varsa, `use App\Http\Controllers\PaymentController;` sətrini onların yanına əlavə edin.

Faylı yadda saxlayın.

### Addım 4: View qovluğunu yaradın

Terminalda bu əmri yazın:

Windows üçün:
```bash
mkdir resources\views\payment
```

Mac/Linux üçün:
```bash
mkdir -p resources/views/payment
```

### Addım 5: Checkout səhifəsini yaradın

`resources/views/payment/checkout.blade.php` adlı yeni fayl yaradın.

Bu kodu faylın içinə yapışdırın:

```html
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ödəniş Et</title>
    <style>
        /* Səhifənin ümumi görünüşü */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        /* Ödəniş formasının konteynerı */
        .payment-container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 420px;
        }
        
        /* Başlıq */
        .payment-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .payment-header h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .payment-header p {
            color: #666;
            font-size: 14px;
        }
        
        /* Xəta mesajı */
        .error-message {
            background: #fee2e2;
            border: 1px solid #ef4444;
            color: #dc2626;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        /* Form elementləri */
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
            font-size: 14px;
        }
        
        .form-group input {
            width: 100%;
            padding: 16px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 18px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-group input::placeholder {
            color: #9ca3af;
        }
        
        /* Ödəniş düyməsi */
        .pay-button {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .pay-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .pay-button:active {
            transform: translateY(0);
        }
        
        /* Təhlükəsizlik qeydi */
        .security-note {
            text-align: center;
            margin-top: 24px;
            color: #6b7280;
            font-size: 12px;
        }
        
        .security-note svg {
            width: 16px;
            height: 16px;
            vertical-align: middle;
            margin-right: 4px;
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="payment-header">
            <h1>Ödəniş Et</h1>
            <p>Təhlükəsiz ödəniş sistemi</p>
        </div>

        <!-- Xəta mesajı göstərmək üçün -->
        @if(session('error'))
            <div class="error-message">
                {{ session('error') }}
            </div>
        @endif

        <!-- Ödəniş forması -->
        <form action="{{ route('payment.pay') }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label for="amount">Ödəniş məbləği (AZN)</label>
                <input 
                    type="number" 
                    id="amount" 
                    name="amount" 
                    step="0.01" 
                    min="0.1"
                    max="10000"
                    placeholder="0.00"
                    required
                    autofocus
                >
            </div>

            <button type="submit" class="pay-button">
                Ödənişə keç
            </button>
        </form>

        <p class="security-note">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path fill-rule="evenodd" d="M12 1.5a5.25 5.25 0 00-5.25 5.25v3a3 3 0 00-3 3v6.75a3 3 0 003 3h10.5a3 3 0 003-3v-6.75a3 3 0 00-3-3v-3c0-2.9-2.35-5.25-5.25-5.25zm3.75 8.25v-3a3.75 3.75 0 10-7.5 0v3h7.5z" clip-rule="evenodd" />
            </svg>
            Ödənişiniz Epoint tərəfindən qorunur
        </p>
    </div>
</body>
</html>
```

Faylı yadda saxlayın.

### Addım 6: Uğurlu ödəniş səhifəsini yaradın

`resources/views/payment/success.blade.php` adlı yeni fayl yaradın.

Bu kodu faylın içinə yapışdırın:

```html
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ödəniş Uğurlu</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .result-container {
            background: white;
            padding: 50px 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            text-align: center;
            width: 100%;
            max-width: 420px;
        }
        
        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 24px;
            animation: scaleIn 0.5s ease-out;
        }
        
        @keyframes scaleIn {
            0% {
                transform: scale(0);
                opacity: 0;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        .success-icon svg {
            width: 50px;
            height: 50px;
            color: white;
        }
        
        h1 {
            color: #11998e;
            font-size: 28px;
            margin-bottom: 12px;
        }
        
        .message {
            color: #6b7280;
            font-size: 16px;
            margin-bottom: 24px;
            line-height: 1.6;
        }
        
        .order-info {
            background: #f3f4f6;
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 24px;
        }
        
        .order-info p {
            color: #374151;
            font-size: 14px;
        }
        
        .order-info strong {
            color: #111827;
        }
        
        .btn {
            display: inline-block;
            padding: 14px 32px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(17, 153, 142, 0.3);
        }
    </style>
</head>
<body>
    <div class="result-container">
        <div class="success-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path fill-rule="evenodd" d="M19.916 4.626a.75.75 0 01.208 1.04l-9 13.5a.75.75 0 01-1.154.114l-6-6a.75.75 0 011.06-1.06l5.353 5.353 8.493-12.739a.75.75 0 011.04-.208z" clip-rule="evenodd" />
            </svg>
        </div>
        
        <h1>Ödəniş Uğurlu!</h1>
        
        <p class="message">
            Ödənişiniz uğurla tamamlandı. Sifarişiniz qəbul edildi.
        </p>
        
        @if($order_id)
            <div class="order-info">
                <p>Sifariş nömrəsi: <strong>{{ $order_id }}</strong></p>
            </div>
        @endif
        
        <a href="/" class="btn btn-primary">Ana səhifəyə qayıt</a>
    </div>
</body>
</html>
```

Faylı yadda saxlayın.

### Addım 7: Uğursuz ödəniş səhifəsini yaradın

`resources/views/payment/error.blade.php` adlı yeni fayl yaradın.

Bu kodu faylın içinə yapışdırın:

```html
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ödəniş Uğursuz</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .result-container {
            background: white;
            padding: 50px 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            text-align: center;
            width: 100%;
            max-width: 420px;
        }
        
        .error-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 24px;
            animation: shake 0.5s ease-out;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        .error-icon svg {
            width: 50px;
            height: 50px;
            color: white;
        }
        
        h1 {
            color: #eb3349;
            font-size: 28px;
            margin-bottom: 12px;
        }
        
        .message {
            color: #6b7280;
            font-size: 16px;
            margin-bottom: 24px;
            line-height: 1.6;
        }
        
        .order-info {
            background: #f3f4f6;
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 24px;
        }
        
        .order-info p {
            color: #374151;
            font-size: 14px;
        }
        
        .buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            display: inline-block;
            padding: 14px 28px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn-primary:hover {
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
    </style>
</head>
<body>
    <div class="result-container">
        <div class="error-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path fill-rule="evenodd" d="M5.47 5.47a.75.75 0 011.06 0L12 10.94l5.47-5.47a.75.75 0 111.06 1.06L13.06 12l5.47 5.47a.75.75 0 11-1.06 1.06L12 13.06l-5.47 5.47a.75.75 0 01-1.06-1.06L10.94 12 5.47 6.53a.75.75 0 010-1.06z" clip-rule="evenodd" />
            </svg>
        </div>
        
        <h1>Ödəniş Uğursuz</h1>
        
        <p class="message">
            Ödəniş zamanı xəta baş verdi. Kart məlumatlarınızı yoxlayın və ya başqa kart istifadə edin.
        </p>
        
        @if($order_id)
            <div class="order-info">
                <p>Sifariş nömrəsi: <strong>{{ $order_id }}</strong></p>
            </div>
        @endif
        
        <div class="buttons">
            <a href="/checkout" class="btn btn-primary">Yenidən cəhd et</a>
            <a href="/" class="btn btn-secondary">Ana səhifə</a>
        </div>
    </div>
</body>
</html>
```

Faylı yadda saxlayın.

### Addım 8: Layihəni işə salın və test edin

Terminalda bu əmri yazın:

```bash
php artisan serve
```

Bu əmr Laravel serverini işə salacaq. Terminalda belə bir mesaj görəcəksiniz:

```
INFO  Server running on [http://127.0.0.1:8000].
```

İndi brauzeri açın və bu ünvana daxil olun:

```
http://127.0.0.1:8000/checkout
```

Ödəniş formasını görəcəksiniz. Məbləğ daxil edib "Ödənişə keç" düyməsini basın.

---

## Callback - Ödəniş nəticəsini almaq

Epoint ödəniş tamamlandıqdan sonra sizin serverinizə məlumat göndərir. Bu, "callback" adlanır.

Paket avtomatik olaraq `/api/epoint/callback` ünvanında callback-i qəbul edir.

### Öz kodunuzu işlətmək üçün Event Listener yaradın

Ödəniş uğurlu və ya uğursuz olduqda öz kodunuzu işlətmək istəyirsinizsə (məsələn, sifarişi "ödənildi" statusuna keçirmək), aşağıdakı addımları izləyin:

#### Addım 1: Listener fayllarını yaradın

Terminalda bu əmrləri yazın:

```bash
php artisan make:listener HandlePaymentSuccess
```

```bash
php artisan make:listener HandlePaymentFailed
```

#### Addım 2: HandlePaymentSuccess listener-ini redaktə edin

`app/Listeners/HandlePaymentSuccess.php` faylını açın və bu kodu yazın:

```php
<?php

namespace App\Listeners;

use AZPayments\Epoint\Events\PaymentSuccess;
use Illuminate\Support\Facades\Log;

class HandlePaymentSuccess
{
    /**
     * Ödəniş uğurlu olduqda bu metod işə düşür
     */
    public function handle(PaymentSuccess $event): void
    {
        // Epoint-dən gələn məlumatlar
        $payload = $event->payload;
        
        // Log-a yazırıq (storage/logs/laravel.log faylında görə bilərsiniz)
        Log::info('Ödəniş uğurlu oldu', $payload);
        
        // Burada öz kodunuzu yazın:
        // Məsələn, sifarişi tapıb statusunu dəyişin:
        //
        // $order = Order::where('order_number', $payload['order_id'])->first();
        // if ($order) {
        //     $order->update([
        //         'status' => 'paid',
        //         'paid_at' => now(),
        //         'transaction_id' => $payload['transaction'] ?? null,
        //     ]);
        //
        //     // Müştəriyə email göndərin
        //     Mail::to($order->customer_email)->send(new OrderPaidMail($order));
        // }
    }
}
```

#### Addım 3: HandlePaymentFailed listener-ini redaktə edin

`app/Listeners/HandlePaymentFailed.php` faylını açın və bu kodu yazın:

```php
<?php

namespace App\Listeners;

use AZPayments\Epoint\Events\PaymentFailed;
use Illuminate\Support\Facades\Log;

class HandlePaymentFailed
{
    /**
     * Ödəniş uğursuz olduqda bu metod işə düşür
     */
    public function handle(PaymentFailed $event): void
    {
        // Epoint-dən gələn məlumatlar
        $payload = $event->payload;
        
        // Log-a yazırıq
        Log::warning('Ödəniş uğursuz oldu', $payload);
        
        // Burada öz kodunuzu yazın:
        // Məsələn:
        //
        // $order = Order::where('order_number', $payload['order_id'])->first();
        // if ($order) {
        //     $order->update(['status' => 'payment_failed']);
        // }
    }
}
```

#### Addım 4: Listener-ləri qeydiyyatdan keçirin

`app/Providers/AppServiceProvider.php` faylını açın.

`boot` metodunun içinə bu kodu əlavə edin:

```php
use AZPayments\Epoint\Events\PaymentSuccess;
use AZPayments\Epoint\Events\PaymentFailed;
use App\Listeners\HandlePaymentSuccess;
use App\Listeners\HandlePaymentFailed;
use Illuminate\Support\Facades\Event;

public function boot(): void
{
    Event::listen(PaymentSuccess::class, HandlePaymentSuccess::class);
    Event::listen(PaymentFailed::class, HandlePaymentFailed::class);
}
```

**Qeyd:** `use` sətirlərini faylın yuxarısındakı digər `use` sətirlərinə əlavə edin.

---

## Production-da callback URL-i qeyd etmək

Local kompüterdə (localhost-da) callback işləməyəcək, çünki Epoint sizin kompüterinizə çata bilmir.

Production-da (real serverdə) işlətmək üçün:

1. Epoint.az hesabınıza daxil olun
2. Merchant parametrlərini açın
3. Callback URL yerinə yazın: `https://sizin-domain.com/api/epoint/callback`
4. Yadda saxlayın

**Vacib:** Callback URL mütləq HTTPS olmalıdır.

---

## Local-da test etmək üçün ngrok istifadə edin

Local kompüterdə callback-i test etmək istəyirsinizsə, ngrok istifadə edə bilərsiniz.

### Addım 1: ngrok yükləyin

https://ngrok.com saytından ngrok yükləyin və quraşdırın.

### Addım 2: ngrok işə salın

Yeni terminal pəncərəsində:

```bash
ngrok http 8000
```

ngrok sizə HTTPS ünvanı verəcək, məsələn: `https://abc123.ngrok.io`

### Addım 3: Epoint-də callback URL-i dəyişin

Epoint hesabınızda callback URL olaraq ngrok ünvanını yazın:

```
https://abc123.ngrok.io/api/epoint/callback
```

İndi local-da da callback işləyəcək.

---

## Əlavə metodlar

### Ödəniş statusunu yoxlamaq

Əgər sifarişin ödəniş statusunu bilmək istəyirsinizsə:

```php
use AZPayments\Epoint\Facades\Epoint;

$status = Epoint::getStatus('transaction_id_buraya');

// $status array qaytarır:
// [
//     'status' => 'success', // və ya 'failed', 'pending'
//     'amount' => 100.00,
//     'order_id' => 'ORDER-123',
//     ...
// ]
```

### Callback datasını manual decode etmək

```php
use AZPayments\Epoint\Facades\Epoint;

// Callback-dən gələn data və signature
$data = $request->input('data');
$signature = $request->input('signature');

// Signature-in düzgün olduğunu yoxlayın
$isValid = Epoint::verifyCallback($data, $signature);

if ($isValid) {
    // Data-nı decode edin
    $payload = Epoint::decodeCallback($data);
    
    // İndi $payload array-ında məlumatlar var
    // $payload['order_id']
    // $payload['status']
    // və s.
}
```

---

## createPayment parametrləri

| Parametr | Tip | Məcburi | Default dəyər | Açıqlama |
|----------|-----|---------|---------------|----------|
| amount | float | Bəli | - | Ödəniş məbləği (AZN) |
| order_id | string | Bəli | - | Unikal sifariş ID-si |
| description | string | Xeyr | "Online ödəniş" | Ödənişin təsviri |
| currency | string | Xeyr | "AZN" | Valyuta |
| language | string | Xeyr | "az" | Dil (az, en, ru) |
| success_url | string | Xeyr | .env-dən | Uğurlu ödəniş URL-i |
| error_url | string | Xeyr | .env-dən | Uğursuz ödəniş URL-i |

---

## Tez-tez verilən suallar

### "Merchant not found" xətası alıram

Bu xəta public_key və ya private_key səhv olduqda baş verir.

**Həll yolu:**
1. Epoint hesabınızdan açarları yenidən kopyalayın
2. `.env` faylında açarları dəyişin
3. Açarları yapışdırarkən əlavə boşluq olmadığından əmin olun
4. `php artisan config:clear` əmrini yazın

### Config dəyişiklikləri işləmir

Laravel config-i cache-ləyir. Dəyişiklikdən sonra:

```bash
php artisan config:clear
php artisan cache:clear
```

### Callback işləmir

1. Callback URL düzgün qeydiyyatdan keçib? (Epoint hesabında yoxlayın)
2. URL HTTPS ilə başlayır? (HTTP işləmir)
3. Server ictimai şəbəkədədir? (localhost işləmir)

### Local-da necə test edə bilərəm?

ngrok istifadə edin (yuxarıda izah edilib).

---

## Problemlər və dəstək

Problem yarandıqda:

1. GitHub-da issue açın: https://github.com/ElgunHeydarli/epoint-laravel/issues
2. Xətanın tam mətnini əlavə edin
3. Laravel versiyanızı qeyd edin
4. Hansı addımda xəta baş verdiyini izah edin

---

## Lisenziya

MIT License - İstədiyiniz layihədə pulsuz istifadə edə bilərsiniz.

---

## Müəllif

Elgun Heydarli
- Email: elgunhaydarli@gmail.com
- GitHub: https://github.com/ElgunHeydarli