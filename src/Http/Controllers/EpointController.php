<?php

namespace AZPayments\Epoint\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use AZPayments\Epoint\Facades\Epoint;
use AZPayments\Epoint\Events\PaymentSuccess;
use AZPayments\Epoint\Events\PaymentFailed;

class EpointController extends Controller
{
    public function callback(Request $request)
    {
        $data = $request->input('data');
        $signature = $request->input('signature');

        Log::info('Epoint callback received', [
            'data' => $data,
            'signature' => $signature,
        ]);

        // Signature yoxla
        if (!Epoint::verifyCallback($data, $signature)) {
            Log::warning('Epoint callback: invalid signature');
            return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 400);
        }

        // Data decode et
        $payload = Epoint::decodeCallback($data);

        Log::info('Epoint callback payload', $payload);

        // Status yoxla
        if (isset($payload['status']) && $payload['status'] === 'success') {
            event(new PaymentSuccess($payload));
        } else {
            event(new PaymentFailed($payload));
        }

        return response()->json(['status' => 'ok']);
    }
}