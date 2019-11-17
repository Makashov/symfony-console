<?php

namespace App\Command;

use App\Command\ImportStrategies\CsvProductImport;
use App\Command\ImportStrategies\ProductImport;
use App\Entity\ProductData;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportProductsCommand extends Command
{
    protected static $defaultName = 'import:products';

    protected $import;

    public function __construct(ProductImport $import, string $name = null)
    {
        $this->import = $import;

        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setDescription('Import products from a file.')
            ->addArgument('path', InputArgument::REQUIRED, 'Path to the input file.')
            ->addOption('format', null, InputOption::VALUE_OPTIONAL, 'File format currently supports only "csv".')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $path = $input->getArgument('path');

        if ($format = $input->getOption('format')) {
            $this->import = $this->getProductImporter($format);
        }

        if (!$this->import) {
            $io->error("Unknown file format.");
            return 1;
        }

        try {
            $this->import->loadProducts($path);
        } catch (\App\Exception\ProductDataImportException $e) {
            $io->error(get_class($e).': '.$e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * @param string $format
     * @return CsvProductImport|null
     */
    public function getProductImporter(string $format)
    {
        switch ($format) {
            case "csv":
                return new CsvProductImport();
            default:
                return null;
        }
    }
}
