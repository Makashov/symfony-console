<?php

namespace App\Command;

use App\Command\ImportStrategies\CsvProductImport;
use App\Command\ImportStrategies\ProductImport;
use App\Exception\ProductDataImportException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ImportProductsCommand extends Command
{
    protected static $defaultName = 'import:products';

    /** @var ProductImport $import */
    protected $import;

    /** @var EntityManagerInterface $entityManager */
    protected $entityManager;

    /** @var SymfonyStyle $io */
    protected $io;

    /** @var ValidatorInterface $validator */
    protected $validator;

    public function __construct(ValidatorInterface $validator, EntityManagerInterface $entityManager)
    {
        $this->validator = $validator;
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Import products from a file.')
            ->addArgument('path', InputArgument::REQUIRED, 'Path to the input file.')
            ->addOption('format', null, InputOption::VALUE_OPTIONAL, 'File format currently supports only "csv".')
            ->addOption('test', null, InputOption::VALUE_NONE, 'Enable test mode.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $path = $input->getArgument('path');
        $format = $input->getOption('format');
        $testMode = $input->getOption('test');

        $this->import = $this->getProductImporter($format);

        if (!$this->import) {
            $this->io->error("Unknown file format.");
            return 1;
        }

        try {
            $this->import->loadProducts($path);

            $products = $this->import->getValidatedProducts();
            $skipped = $this->import->getFailedProducts();
            $failedLines = $this->import->getFailedLines();
            $total = $this->import->getTotal();

            $this->io->text('Items found: ' . $total);
            $this->io->text('Items stored: '. count($products));
            $this->io->text('Items skipped: ' . count($skipped));

            $this->displayStoredProducts($products);
            $this->displayFailedProducts($skipped);
            $this->displayFailedLines($failedLines);

            $this->saveItems($products, $testMode);

            return 0;
        } catch (ProductDataImportException $e) {
            $this->io->error(get_class($e).': '.$e->getMessage());
            return 1;
        }
    }

    private function saveItems(array $products, bool $testMode)
    {
        if ($testMode) {
            $this->io->title('Items will not be stored in test mode.');
            return;
        }

        foreach ($products as $item) {
            $this->entityManager->persist($item);
        }

        $this->entityManager->flush();
    }

    /**
     * @param array $storedProducts
     */
    private function displayStoredProducts(array $storedProducts)
    {
        $this->io->title('Products below will be stored:');

        foreach ($storedProducts as $line) {
            $this->io->block($line);
        }
    }

    /**
     * @param array $storedProducts
     */
    private function displayFailedProducts(array $storedProducts)
    {
        $this->io->title('Products below will be skipped:');

        foreach ($storedProducts as $line) {
            $this->io->block($line);
        }
    }

    /**
     * @param array $failedLines
     */
    private function displayFailedLines(array $failedLines)
    {
        $this->io->title('Failed to parse lines below:');

        foreach ($failedLines as $line) {
            $this->io->block(implode(', ', $line));
        }
    }

    /**
     * @param string $format
     * @return ProductImport|null
     */
    private function getProductImporter(string $format = null)
    {
        if (!$format) {
            $format = 'csv';
        }

        switch ($format) {
            case "csv":
                return new CsvProductImport($this->validator);
            default:
                return null;
        }
    }
}
