<?php


namespace App\Command\ImportStrategies;

use App\Exception\ProductDataImportException as Exception;

/**
 * Class CsvProductImport
 * @package App\Command\ImportStrategies
 */
class CsvProductImport implements ProductImport
{
    const COLUMNS = ['ProductCode', 'ProductName', 'ProductDescription', 'StockInGBP', 'Discontinued'];

    private $products = [];

    /**
     * @param string $path
     * @return mixed
     * @throws Exception
     */
    public function loadProducts(string $path)
    {
        $file =  new \SplFileObject($path);
        $file->setFlags(
            \SplFileObject::READ_CSV |
            \SplFileObject::SKIP_EMPTY |
            \SplFileObject::READ_AHEAD |
            \SplFileObject::DROP_NEW_LINE
        );
        $file->setCsvControl();
        $file->rewind();
        $file->seek(1);

        dd($file->current());

        return true;
    }

    /**
     * @param string $path
     * @return void
     * @throws Exception
     */
    private function validatePath(string $path)
    {
        if (!file_exists($path) || !is_readable($path)) {
            throw new Exception('Cannot open file.');
        }
    }
}
