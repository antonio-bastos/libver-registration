<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PaymentController extends Controller
{
    private PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function showInvoice(Registration $registration)
    {
        Gate::authorize('view', $registration);
        
        return $this->paymentService->getInvoiceView($registration);
    }

    public function processMockPayment(Request $request, Registration $registration)
    {
        Gate::authorize('update', $registration);
        
        // In reality, this would be a webhook from Stripe
        $amount = (float) $request->input('amount', $registration->fee_amount - $registration->amount_paid);

        $this->paymentService->registerPayment($registration, $amount, 'MOCK-PAYMENT');

        return back()->with('success', 'Payment recorded successfully.');
    }
}
