<?php

namespace App\Contract;

interface FinanceApiClientInterface
{
    public function fetchStockProfile(string $symbol, string $region);
}