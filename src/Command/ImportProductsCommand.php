<?php

namespace App\Command;

use App\Command\ImportStrategies\CsvProductImport;
use App\Command\ImportStrategies\ProductImport;
use App\Entity\ProductData;
use App\Exception\ProductDataImportException;
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

    /** @var ValidatorInterface $validator */
    protected $validator;

    public function __construct(ValidatorInterface $validator, string $name = null)
    {
        $this->validator = $validator;

        parent::__construct($name);
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
        $io = new SymfonyStyle($input, $output);
        $path = $input->getArgument('path');
        $this->import = $this->getProductImporter($input);

        if (!$this->import) {
            $io->error("Unknown file format.");
            return 1;
        }

        try {
            $this->import->loadProducts($path);

            $success = count($this->import->getValidatedProducts());
            $skipped = count($this->import->getFailedProducts());
            $failedLines = $this->import->getFailedLines();
            $total = $success+$skipped+count($failedLines);

            $io->text('Items found: ' . $total);
            $io->text('Items stored: '. $success);
            $io->text('Items skipped: ' . $skipped);

            $this->displayFailedLines($io, $failedLines);

            return 0;
        } catch (ProductDataImportException $e) {
            $io->error(get_class($e).': '.$e->getMessage());
            return 1;
        }
    }

    /**
     * @param SymfonyStyle $io
     * @param array $failedLines
     */
    private function displayFailedLines(SymfonyStyle $io, array $failedLines)
    {
        $io->title('Failed to parse lines below:');

        foreach ($failedLines as $line) {
            $io->block( implode($failedLines));
        }
    }

    /**
     * @param InputInterface $input
     * @return ProductImport|null
     */
    private function getProductImporter(InputInterface $input)
    {
        $format = 'csv';

        switch ($format) {
            case "csv":
                return new CsvProductImport($this->validator);
            default:
                return null;
        }
    }
}
