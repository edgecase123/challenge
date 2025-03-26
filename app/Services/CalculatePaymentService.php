<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;

class CalculatePaymentService
{
    const PAYMENT_WEEKLY = 'weekly';
    const PAYMENT_BI_WEEKLY = 'bi-weekly';
    const PAYMENT_MONTHLY = 'monthly';


    public function calculatePaymentSchedule(
        float $total,
        CarbonImmutable|string $firstPaymentDate,
        string $paymentFrequency,
        int $numberOfPayments,
    ): array {
        // Convert to carbon date object if string passed in
        if (is_string($firstPaymentDate)) {
            $firstPaymentDate = CarbonImmutable::parse($firstPaymentDate);
        }

        $result = [];

        // Calculate payment amount
        $payment = round($total / $numberOfPayments, 2);
        $paymentDate = $firstPaymentDate->startOfDay();

        for ($i = 1; $i <= $numberOfPayments; $i++) {
            $paymentData = [
                'amount' => $payment,
            ];

            // Figure out payments
            switch ($paymentFrequency) {
                case self::PAYMENT_BI_WEEKLY:
                    $paymentDate = $paymentDate->addWeeks(2);
                    $paymentData['date'] = $paymentDate->format('m-d-Y');
                    break;
                case self::PAYMENT_WEEKLY:
                    $paymentDate = $paymentDate->addWeek();
                    $paymentData['date'] = $paymentDate->format('m-d-Y');
                    break;
                case self::PAYMENT_MONTHLY:
                    $paymentDate = $paymentDate->addMonth();
                    $paymentData['date'] = $paymentDate->format('m-d-Y');
                    break;
                default:
                    throw new \RuntimeException('Invalid payment frequency provided');
            }

            $result[] = $paymentData;
        }

        // Take care of remainder in last payment
        $sumPayments = $payment * $numberOfPayments;

        if ($sumPayments !== $total) {
            $result[count($result) -1]['amount'] = round($payment + ($total - $sumPayments), 2);
        }

        return $result;
    }
}
