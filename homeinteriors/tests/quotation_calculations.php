<?php

declare(strict_types=1);

require __DIR__ . '/../src/QuotationCalculator.php';

function assertSameFloat(float $expected, float $actual, string $label): void
{
    if (abs($expected - $actual) > 0.01) {
        throw new RuntimeException($label . ' expected ' . $expected . ' got ' . $actual);
    }
}

$line = QuotationCalculator::calculateLineItemAmount([
    'unit_type' => 'Per running ft',
    'quantity' => 10,
    'rate' => 18500,
    'discount_amount' => 5000,
    'gst_percentage' => 18,
    'vendor_cost' => 130000,
]);
assertSameFloat(180000.0, $line['amount'], 'line amount');
assertSameFloat(32400.0, $line['gst_amount'], 'line gst');
assertSameFloat(50000.0, $line['margin_amount'], 'line margin');

$quote = QuotationCalculator::calculateQuote(
    [
        'discount_amount' => 10000,
        'discount_percentage' => 5,
        'design_fee_percentage' => 3,
        'project_management_fee_percentage' => 5,
        'site_visit_fee' => 2500,
        'gst_percentage' => 18,
        'payment_schedule' => [
            ['label' => 'Booking', 'percentage' => 10],
            ['label' => 'Design freeze', 'percentage' => 40],
            ['label' => 'Dispatch', 'percentage' => 40],
            ['label' => 'Handover', 'percentage' => 10],
        ],
    ],
    [
        ['unit_type' => 'Per unit', 'quantity' => 1, 'rate' => 100000, 'vendor_cost' => 70000, 'gst_percentage' => 18],
        ['unit_type' => 'Per sq ft', 'quantity' => 100, 'rate' => 150, 'vendor_cost' => 9000, 'gst_percentage' => 18],
    ],
    ['default_platform_commission_percentage' => 5]
);

assertSameFloat(115000.0, $quote['subtotal'], 'subtotal');
assertSameFloat(15750.0, $quote['discount_amount'], 'discount');
assertSameFloat(11700.0, $quote['design_fee'] + $quote['project_management_fee'] + $quote['site_visit_fee'], 'fees');
assertSameFloat(19971.0, $quote['gst_amount'], 'gst');
assertSameFloat(130921.0, $quote['final_amount'], 'final amount');
assertSameFloat(36000.0, $quote['margin_amount'], 'margin');
assertSameFloat(round($quote['final_amount'] * 0.1, 2), $quote['payment_schedule'][0]['amount'], 'payment schedule');

echo "Quotation calculation tests passed\n";
