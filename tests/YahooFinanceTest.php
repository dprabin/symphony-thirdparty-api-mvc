<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

use App\Entity\Stock;

class YahooFinanceTest extends KernelTestCase
{
    private $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        DatabasePrimer::prime($kernel);

        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
    }

    /** @test */
    public function test_if_stock_record_can_be_created_in_the_database(): void
    {
        //setup
        $stock = new Stock();
        $stock->setSymbol('AMZN');
        $stock->setShortName('Amazon Inc');
        $stock->setCurrency('USD');
        $stock->setExchangeName('Nasdaq');
        $stock->setRegion('US');
        $price=1000;
        $previousClose=1100;
        $priceChange = $price-$previousClose;
        $stock->setPrice($price);
        $stock->setPreviousClose($previousClose);
        $stock->setPriceChange($priceChange);
        
        //do something
        $this->entityManager->persist($stock);
        $this->entityManager->flush();

        $stockRepository = $this->entityManager->getRepository(Stock::class);
        $stockRecord = $stockRepository->findOneBy(['symbol'=>'AMZN']);

        //make assertions
        $this->assertEquals('Amazon Inc', $stockRecord->getShortName());
        $this->assertEquals('USD', $stockRecord->getCurrency());
        $this->assertEquals('Nasdaq', $stockRecord->getExchangeName());
        $this->assertEquals('US', $stockRecord->getRegion());
        $this->assertEquals(1000, $stockRecord->getPrice());
        $this->assertEquals(1100, $stockRecord->getPreviousClose());
        $this->assertEquals(-100, $stockRecord->getPriceChange());
    }

}
