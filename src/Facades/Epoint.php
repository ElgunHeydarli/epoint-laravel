<?php

namespace AZPayments\Epoint\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array createPayment(array $params)
 * @method static array getStatus(string $transactionId)
 * @method static bool verifyCallback(string $data, string $signature)
 * @method static array decodeCallback(string $data)
 *
 * @see \AZPayments\Epoint\Epoint
 */
class Epoint extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'epoint';
    }
}