<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentRequest;
use App\Models\PaymentLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
    public function pay(Request $request)
    {
        $data = $request->validate([
            'control_number' => 'required|string|exists:payment_requests,control_number',
            'amount'         => 'required|numeric|min:1',
            'channel'        => 'required|string',
            'payment_ref'    => 'required|string|unique:payment_logs,payment_ref',
        ]);

        return DB::transaction(function () use ($data) {

            $paymentRequest = PaymentRequest::where(
                'control_number',
                $data['control_number']
            )->lockForUpdate()->firstOrFail();

            // Save payment log
            PaymentLog::create([
                'payment_request_id' => $paymentRequest->id,
                'control_number'     => $paymentRequest->control_number,
                'payment_ref'        => $data['payment_ref'],
                'amount'             => $data['amount'],
                'channel'            => $data['channel'],
                'paid_at'            => now(),
            ]);

            // Update amounts
            $paymentRequest->amount_paid += $data['amount'];
            $paymentRequest->refreshStatus();

            // Update school account
            $paymentRequest->school->account->increment(
                'total_received',
                $data['amount']
            );

            // 🔔 SEND WEBHOOK
            $this->sendWebhook($paymentRequest);

            return response()->json([
                'message' => 'Payment recorded successfully',
                'data'    => $paymentRequest
            ]);
        });
    }

    private function sendWebhook(PaymentRequest $paymentRequest): void
    {
        $payload = [
            'event'            => 'PAYMENT_UPDATE',
            'control_number'   => $paymentRequest->control_number,
            'school_code'      => $paymentRequest->school_code,
            'school_name'      => $paymentRequest->school_name,
            'student_id'       => $paymentRequest->student_id,
            'student_name'     => $paymentRequest->student_name,
            'amount_required'  => $paymentRequest->amount_required,
            'amount_paid'      => $paymentRequest->amount_paid,
            'balance'          => $paymentRequest->balance,
            'currency'         => $paymentRequest->currency,
            'payment_status'   => $paymentRequest->status,
            'updated_at'       => now()->toISOString(),
        ];


        Http::timeout(5)->post(
            config('services.saas.webhook_url'),
            $payload
        );
        
    }


}
