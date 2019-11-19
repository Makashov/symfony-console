<?php


namespace App\Command\ImportStrategies;

use App\Exception\ProductDataImportException;

/**
 * Interface ProductImport
 * @package App\Command\ImportStrategies
 */
interface ProductImport
{
    /**
     * @param string $string
     * @return void
     * @throws ProductDataImportException
     */
    public function loadProducts(string $string);

    /** Get successfully checked products */
    public function getValidatedProducts(): array;

    /** Get products that will be skipped */
    public function getFailedProducts(): array;

    /** Get lines of csv files with broken data */
    public function getFailedLines(): array;

    /** Get total amount of items */
    public function getTotal(): int;
}
