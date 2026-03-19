<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Registration;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class PaymentService
{
    private NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function calculateTotal(Activity $activity): float
    {
        if (!$activity->is_paid) {
            return 0.00;
        }

        return (float) $activity->fee;
    }

    public function registerPayment(Registration $registration, float $amount, string $reference = null): Registration
    {
        if ($amount <= 0) {
            return $registration;
        }

        $registration->amount_paid += $amount;
        
        // Update payment metadata
        $meta = $registration->payment_metadata ?? [];
        $meta[] = [
            'amount' => $amount,
            'date' => now()->toIso8601String(),
            'reference' => $reference,
        ];
        $registration->payment_metadata = $meta;

        // Update status
        if ($registration->amount_paid >= $registration->fee_amount) {
            $registration->payment_status = Registration::PAYMENT_STATUS_PAID;
        } elseif ($registration->amount_paid > 0) {
            $registration->payment_status = Registration::PAYMENT_STATUS_PARTIAL;
        }

        $registration->save();
        
        return $registration;
    }

    /**
     * Generates an HTML invoice view for the registration.
     */
    public function getInvoiceView(Registration $registration)
    {
        return View::make('invoices.default', [
            'registration' => $registration->load(['activity', 'child.parent', 'user']),
        ]);
    }
}
