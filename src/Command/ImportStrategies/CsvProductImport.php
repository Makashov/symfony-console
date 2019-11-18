<?php


namespace App\Command\ImportStrategies;

use App\Entity\ProductData;
use App\Exception\ProductDataImportException;
use SplFileObject;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class CsvProductImport
 * @package App\Command\ImportStrategies
 */
class CsvProductImport implements ProductImport
{
    const COLUMNS = ['strProductCode', 'strProductName', 'strProductDesc', 'stock', 'cost', 'dtmDiscontinued'];
    const START_LINE = 1;

    /** @var ValidatorInterface $validator */
    private $validator;

    /** @var array Array of added products */
    private $validatedProducts = [];

    private $failedProducts = [];

    /** @var array Array of incorrect lines */
    private $failedLines = [];

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param string $path
     * @return void
     * @throws ProductDataImportException
     */
    public function loadProducts(string $path)
    {
        $file = $this->getReader($path);

        $this->parseFile($file);
    }

    /**
     * @return array
     */
    public function getValidatedProducts(): array
    {
        return $this->validatedProducts;
    }

    /**
     * @return array
     */
    public function getFailedProducts(): array
    {
        return $this->failedProducts;
    }

    /**
     * @return array
     */
    public function getFailedLines(): array
    {
        return $this->failedLines;
    }

    /**
     * Walk through the file and organize all products
     * to the $validatedProducts, $failedProducts
     * and $failedLines arrays.
     *
     * @param SplFileObject $file
     */
    private function parseFile(SplFileObject $file)
    {
        while(!$file->eof()) {
            $line = $file->current();

            $productArray = $this->parseLine($line);

            if (!$productArray) {
                $this->failedLines []= $file->getCurrentLine(); // Save line number with broken item.
                $file->next();
                continue;
            }

            $productData = $this->createEntity($productArray);

            if ($this->isValid($productData)) {
                $this->validatedProducts []= $productData; // Save correct products
            } else {
                $this->failedProducts []= $productData; // Save products with failed validation
            }

            $file->next();
        }
    }

    private function isValid(ProductData $productData): bool
    {
        return count($this->validator->validate($productData)) == 0;
    }

    /**
     * Create ProductData entity from array
     *
     * @param array $data
     * @return ProductData
     */
    private function createEntity(array $data): ProductData
    {
        $productData = new ProductData();
        $productData->createFromArray($data);

        return $productData;
    }

    /**
     * @param string $path
     * @return SplFileObject
     * @throws ProductDataImportException
     */
    private function getReader(string $path): SplFileObject
    {
        $this->validatePath($path);

        $file =  new SplFileObject($path);
        $file->setFlags(
            SplFileObject::READ_CSV |
            SplFileObject::SKIP_EMPTY |
            SplFileObject::READ_AHEAD |
            SplFileObject::DROP_NEW_LINE
        );
        $file->rewind();
        $file->seek(self::START_LINE);

        return $file;
    }

    /**
     * Set headers as keys to the line
     *
     * @param array $line
     * @return array|null
     */
    private function parseLine(array $line)
    {
        $line = array_pad($line, count(self::COLUMNS), null);

        // There is more column values than headers
        if (count($line) > count(self::COLUMNS)) {
            return null;
        }

        return array_combine(self::COLUMNS, $line);
    }

    /**
     * @param string $path
     * @return void
     * @throws ProductDataImportException
     */
    private function validatePath(string $path)
    {
        if (!file_exists($path) || !is_readable($path)) {
            throw new ProductDataImportException('Cannot open file.');
        }
    }
}
