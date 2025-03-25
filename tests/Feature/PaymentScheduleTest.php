<?php

namespace Feature;

use App\Services\CalculatePaymentService;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class PaymentScheduleTest extends TestCase
{
    public static function dataItCalculatesPaymentSchedule(): array
    {
        // Make first payment date 30 days away for all cases (standard)
        $firstPayment = now()->startOfDay()->addDays(30)->format('Y-m-d');

        return [
            [100, $firstPayment, 'weekly', 8, false],
            [123.42, $firstPayment, 'weekly', 8, false],
            [432.85, $firstPayment, CalculatePaymentService::PAYMENT_BI_WEEKLY, 20, false],
            [1423.68, $firstPayment, CalculatePaymentService::PAYMENT_MONTHLY, 12, false],
            [1423.60, $firstPayment, 'semi-monthly', 12, true],
        ];
    }

    /**
     * Test that CalculatePaymentSchedule works as expected
     * @test
     * @dataProvider dataItCalculatesPaymentSchedule
     * @param float $total
     * @param CarbonImmutable|string $firstPaymentDate
     * @param string $paymentFrequency
     * @param int $numberOfPayments
     * @param bool $expectException
     * @return void
     */
    public function itCalculatesPaymentSchedule(
        float $total,
        CarbonImmutable|string $firstPaymentDate,
        string $paymentFrequency,
        int $numberOfPayments,
        bool $expectException
    ): void {
        $paymentSvc = new CalculatePaymentService();

        if ($expectException) {
            $this->expectException(\RuntimeException::class);
        }

        $result = $paymentSvc->calculatePaymentSchedule(
            $total,
            $firstPaymentDate,
            $paymentFrequency,
            $numberOfPayments
        );

        $totalExpected = 0;

        foreach ($result as $item) {
            $totalExpected += (float) $item['amount'];
        }

        $totalExpected = (int) $totalExpected * 100;
        $total = (int) $total * 100;
        $this->assertEquals($totalExpected, $total);
        $this->assertTrue(1 > 0);
    }
}
