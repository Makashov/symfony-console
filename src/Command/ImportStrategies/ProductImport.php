<?php


namespace App\Command\ImportStrategies;

/**
 * Interface ProductImport
 * @package App\Command\ImportStrategies
 */
interface ProductImport
{
    public function loadProducts(string $string);
}
