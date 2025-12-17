<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\PaymentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentRequestController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            
            'school_code'     => 'required|string',
            'school_name'     => 'required|string',
            'student_id'      => 'required|string',
            'student_name'    => 'required|string',
            'amount_required' => 'required|numeric|min:1',
            'currency'        => 'required|string',
            'description'     => 'nullable|string',
            'request_id'      => 'required|string|unique:payment_requests,request_id',

        ]);

        $school = School::where('school_code', $data['school_code'])
            ->where('is_active', true)
            ->firstOrFail();

        return DB::transaction(function () use ($school, $data) {

            $controlNumber = $this->generateControlNumber();

            $paymentRequest = PaymentRequest::create([
                'school_id'       => $school->id,
                'school_code'     => $data['school_code'],
                'school_name'     => $data['school_name'],
                'control_number'  => $controlNumber,
                'request_id'      => $data['request_id'],
                'student_id'      => $data['student_id'],
                'student_name'    => $data['student_name'],
                'amount_required' => $data['amount_required'],
                'amount_paid'     => 0,
                'currency'        => $data['currency'],
                'description'     => $data['description'],
                'status'          => 'PENDING',
            ]);

            // update school expected
            $school->account()->increment('total_expected', $data['amount_required']);

            return response()->json($paymentRequest, 201);
        });
    }

    private function generateControlNumber(): string
    {
        return '996' . now()->format('ymd') . random_int(1000, 9999);
    }


}

