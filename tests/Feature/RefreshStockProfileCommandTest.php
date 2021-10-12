<?php

namespace App\Tests\Feature;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Console\Tester\CommandTester;

use App\Entity\Stock;
use App\Tests\DatabasePrimer;

class RefreshStockProfileCommandTest extends KernelTestCase
{
    private $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        DatabasePrimer::prime($kernel);

        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }

    /** @test */
    public function test_whether_refresh_stock_profile_command_behaves_correctly(): void
    {
        //setup
        $stock = new Stock;
        $application = new Application(self::$kernel);

        //command
        $command = $application->find('app:refresh-stock-profile');

        $commandTester = new CommandTester($command);

        //Do something
        $commandTester->execute([
            'symbol' =>'AMZN',
            'region' => 'US'
        ]);

        //Make Assertions
        //DB assertions
        $repo->$this->entityManager->getRepository(Stock::class);

        /** @var Stock $stock */
        $stock = $repo->findOneBy(['symbol'=>'AMZN']);

        $this->assertSame('USD',$stock->getCurrency());
        $this->assertSame('NasdaqGS',$stock->getExchangeName());
        $this->assertSame('AMZN',$stock->getSymbol());
        $this->assertSame('Amazon.com, Inc.',$stock->getShortName());
        $this->assertSame('US',$stock->getRegion());
        $this->assertGreaterThan(50,$stock->getPrice());
        $this->assertGreaterThan(50,$stock->getPreviousClose());

    }
}
