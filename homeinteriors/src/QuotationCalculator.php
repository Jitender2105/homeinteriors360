<?php

declare(strict_types=1);

final class QuotationCalculator
{
    public static function calculateLineItemAmount(array $item): array
    {
        $quantity = max(0.0, (float)($item['quantity'] ?? 0));
        $length = max(0.0, (float)($item['length'] ?? 0));
        $width = max(0.0, (float)($item['width'] ?? 0));
        $height = max(0.0, (float)($item['height'] ?? 0));
        $rate = max(0.0, (float)($item['rate'] ?? 0));
        $discount = max(0.0, (float)($item['discount_amount'] ?? 0));
        $unitType = strtolower(trim((string)($item['unit_type'] ?? 'Per unit')));

        $calculatedArea = 0.0;
        if (str_contains($unitType, 'sq ft')) {
            $calculatedArea = $length > 0 && $width > 0 ? $length * $width : ($length * $height);
            $basis = $quantity > 0 ? $quantity : $calculatedArea;
        } elseif (str_contains($unitType, 'running')) {
            $basis = $quantity > 0 ? $quantity : $length;
        } else {
            $basis = $quantity;
        }

        $amount = max(0.0, ($basis * $rate) - $discount);
        $gstPercentage = max(0.0, (float)($item['gst_percentage'] ?? 0));
        $gstAmount = round($amount * $gstPercentage / 100, 2);
        $vendorCost = max(0.0, (float)($item['vendor_cost'] ?? 0));

        return [
            'quantity' => round($quantity, 2),
            'length' => round($length, 2),
            'width' => round($width, 2),
            'height' => round($height, 2),
            'calculated_area' => round($calculatedArea, 2),
            'amount' => round($amount, 2),
            'gst_amount' => $gstAmount,
            'margin_amount' => round($amount - $vendorCost, 2),
        ];
    }

    public static function calculateRoomTotal(array $items, string $room): float
    {
        return round(array_reduce($items, static function (float $carry, array $item) use ($room): float {
            return $carry + ((string)($item['room_name'] ?? '') === $room ? (float)($item['amount'] ?? 0) : 0.0);
        }, 0.0), 2);
    }

    public static function calculateCategoryTotal(array $items, string $category): float
    {
        return round(array_reduce($items, static function (float $carry, array $item) use ($category): float {
            return $carry + ((string)($item['category'] ?? '') === $category ? (float)($item['amount'] ?? 0) : 0.0);
        }, 0.0), 2);
    }

    public static function calculateSubtotal(array $items): float
    {
        return round(array_reduce($items, static fn(float $carry, array $item): float => $carry + (float)($item['amount'] ?? 0), 0.0), 2);
    }

    public static function calculateDiscount(float $subtotal, float $discountAmount, float $discountPercentage): float
    {
        $percentageDiscount = $discountPercentage > 0 ? $subtotal * $discountPercentage / 100 : 0.0;
        return round(min($subtotal, max(0.0, $discountAmount + $percentageDiscount)), 2);
    }

    public static function calculateFees(float $subtotal, array $quote): array
    {
        $designFee = isset($quote['design_fee_override']) && $quote['design_fee_override'] !== ''
            ? (float)$quote['design_fee_override']
            : $subtotal * max(0.0, (float)($quote['design_fee_percentage'] ?? 0)) / 100;
        $projectFee = isset($quote['project_management_fee_override']) && $quote['project_management_fee_override'] !== ''
            ? (float)$quote['project_management_fee_override']
            : $subtotal * max(0.0, (float)($quote['project_management_fee_percentage'] ?? 0)) / 100;

        return [
            'design_fee' => round(max(0.0, $designFee), 2),
            'project_management_fee' => round(max(0.0, $projectFee), 2),
            'site_visit_fee' => round(max(0.0, (float)($quote['site_visit_fee'] ?? 0)), 2),
        ];
    }

    public static function calculateGST(float $taxableAmount, float $gstPercentage): float
    {
        return round(max(0.0, $taxableAmount) * max(0.0, $gstPercentage) / 100, 2);
    }

    public static function calculateFinalAmount(float $subtotal, array $fees, float $gstAmount, float $discount): float
    {
        return round(max(0.0, $subtotal + array_sum($fees) + $gstAmount - $discount), 2);
    }

    public static function calculateMargin(float $subtotal, float $vendorCost): array
    {
        $margin = round($subtotal - max(0.0, $vendorCost), 2);
        return [
            'margin_amount' => $margin,
            'margin_percentage' => $subtotal > 0 ? round($margin * 100 / $subtotal, 2) : 0.0,
        ];
    }

    public static function calculatePaymentSchedule(float $finalAmount, array $milestones): array
    {
        $schedule = [];
        foreach ($milestones as $milestone) {
            $label = trim((string)($milestone['label'] ?? 'Payment milestone'));
            $percentage = max(0.0, (float)($milestone['percentage'] ?? 0));
            $amount = isset($milestone['amount']) && (float)$milestone['amount'] > 0
                ? (float)$milestone['amount']
                : $finalAmount * $percentage / 100;
            $schedule[] = [
                'label' => $label,
                'percentage' => round($percentage, 2),
                'amount' => round($amount, 2),
            ];
        }
        return $schedule;
    }

    public static function calculateQuote(array $quote, array $items, array $settings = []): array
    {
        $normalizedItems = [];
        foreach ($items as $item) {
            if (!empty($item['include_in_proposal']) || !array_key_exists('include_in_proposal', $item)) {
                $calc = self::calculateLineItemAmount($item);
                $normalizedItems[] = array_merge($item, $calc);
            }
        }

        $subtotal = self::calculateSubtotal($normalizedItems);
        $discount = self::calculateDiscount($subtotal, (float)($quote['discount_amount'] ?? 0), (float)($quote['discount_percentage'] ?? 0));
        $fees = self::calculateFees($subtotal, $quote);
        $taxable = max(0.0, $subtotal + array_sum($fees) - $discount);
        $gst = self::calculateGST($taxable, (float)($quote['gst_percentage'] ?? $settings['default_gst_percentage'] ?? 18));
        $vendorCost = round(array_reduce($normalizedItems, static fn(float $carry, array $item): float => $carry + (float)($item['vendor_cost'] ?? 0), 0.0), 2);
        $margin = self::calculateMargin($subtotal, $vendorCost);
        $finalAmount = self::calculateFinalAmount($subtotal, $fees, $gst, $discount);
        $commissionPercentage = max(0.0, (float)($quote['platform_commission_percentage'] ?? $settings['default_platform_commission_percentage'] ?? 5));

        return [
            'items' => $normalizedItems,
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'design_fee' => $fees['design_fee'],
            'project_management_fee' => $fees['project_management_fee'],
            'site_visit_fee' => $fees['site_visit_fee'],
            'gst_amount' => $gst,
            'final_amount' => $finalAmount,
            'vendor_cost' => $vendorCost,
            'margin_amount' => $margin['margin_amount'],
            'margin_percentage' => $margin['margin_percentage'],
            'platform_commission' => round($finalAmount * $commissionPercentage / 100, 2),
            'payment_schedule' => self::calculatePaymentSchedule($finalAmount, $quote['payment_schedule'] ?? $settings['default_payment_schedule'] ?? []),
        ];
    }
}
