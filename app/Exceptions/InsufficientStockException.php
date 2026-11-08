<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when a stock change would take a variant below zero.
 */
class InsufficientStockException extends RuntimeException
{
    public function __construct(
        public readonly int $variantId,
        public readonly string $sku,
        public readonly int $requested,
        public readonly int $available,
    ) {
        parent::__construct("Insufficient stock for SKU [{$sku}]: requested {$requested}, available {$available}.");
    }
}
