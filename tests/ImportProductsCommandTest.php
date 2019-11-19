<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ImportProductsCommandTest extends KernelTestCase
{
    public function testExecute()
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        $command = $application->find('import:products');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'path'  => './tests/Resources/stock.csv',
            '--test' => true,
        ]);

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertContains(file_get_contents('./tests/Resources/ImportProductsCommand.out'), $output);
    }
}
