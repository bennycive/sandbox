<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PaymentLog;
use App\Models\PaymentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class TestPaymentController extends Controller
{
    public function showForm()
    {
        return view('payments.test-payment');
    }

    public function submit(Request $request)
    {
        $data = $request->validate([
            'control_number' => 'required|exists:payment_requests,control_number',
            'amount' => 'required|numeric|min:1',
        ]);

        DB::transaction(function () use ($data) {
            $paymentRequest = PaymentRequest::where(
                'control_number',
                $data['control_number']
            )->lockForUpdate()->first();

            // Save payment log
            PaymentLog::create([
                'payment_request_id' => $paymentRequest->id,
                'control_number' => $paymentRequest->control_number,
                'payment_ref' => 'WEB-' . uniqid(),
                'amount' => $data['amount'],
                'channel' => 'WEB_TEST',
                'paid_at' => now(),
            ]);

            // Update request
            $paymentRequest->amount_paid += $data['amount'];
            $paymentRequest->refreshStatus();

            // Update school account
            $paymentRequest
                ->school
                ->account
                ->increment('total_received', $data['amount']);

            // 🔔 SEND WEBHOOK
            $this->sendWebhook($paymentRequest);
        });

        return redirect()
            ->back()
            ->with('success', 'Payment added and webhook sent!');
    }

    private function sendWebhook(PaymentRequest $paymentRequest): void
    {
        Http::post(config('services.saas.webhook_url'), [
            'event' => 'PAYMENT_UPDATE',
            'control_number' => $paymentRequest->control_number,
            'school_code' => $paymentRequest->school_code,
            'student_id' => $paymentRequest->student_id,
            'student_name' => $paymentRequest->student_name,
            'amount_required' => $paymentRequest->amount_required,
            'amount_paid' => $paymentRequest->amount_paid,
            'balance' => $paymentRequest->balance,
            'payment_status' => $paymentRequest->status,
            'updated_at' => now()->toISOString(),
        ]);

    }


}


