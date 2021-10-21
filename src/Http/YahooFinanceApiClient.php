<?php

namespace App\Http;

use App\Contract\FinanceApiClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use App\http\YahooFinanceApiClient;

class YahooFinanceApiClient implements FinanceApiClientInterface
{
    private $httpClient;
    private const URL = 'https://yh-finance.p.rapidapi.com/stock/v2/get-profile';
    private const X_RAPID_API_HOST = 'yh-finance.p.rapidapi.com';
    private $rapidApiKey;

    public function __construct(HttpClientInterface $httpClient, $rapidApiKey)
    {
        $this->httpClient = $httpClient;
        $this->rapidApiKey = $rapidApiKey;
    }

    /**
     * @param string $symbol
     * @param string $region
     * @return JsonResponse
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function fetchStockProfile(string $symbol = 'AMZN', string $region = 'US'): JsonResponse
    {
        $response = $this->httpClient->request('GET', self::URL, [
            'query'   => [
                'symbol' => $symbol,
                'region' => $region
            ],
            'headers' => [
                'x-rapidapi-host' => self::X_RAPID_API_HOST,
                'x-rapidapi-key'  => $this->rapidApiKey
            ]
        ]);

        $statusCode = $response->getStatusCode();
        if ($statusCode !== 200) {
            return new JsonResponse('Finance API Client Error ', $statusCode);
        }

        $stockProfile = json_decode($response->getContent())->price;
        //dd($stockProfile);
        $stockProfileArray = [
            'symbol'        => $stockProfile->symbol,
            'shortName'     => $stockProfile->shortName,
            'region'        => $region,
            'exchangeName'  => $stockProfile->exchangeName,
            'currency'      => $stockProfile->currency,
            'price'         => $stockProfile->regularMarketPrice->raw,
            'previousClose' => $stockProfile->regularMarketPreviousClose->raw,
            'priceChange'   => $stockProfile->regularMarketPrice->raw - $stockProfile->regularMarketPreviousClose->raw
        ];

        return new JsonResponse($stockProfileArray, 200);
    }
}

/*
 * Response json object sample
 {#1172 ▼
    +"financialsTemplate": {#1163 ▶}
    +"price": {#1169 ▼
      +"quoteSourceName": "Delayed Quote"
      +"regularMarketOpen": {#1180 ▶}
      +"averageDailyVolume3Month": {#1171 ▶}
      +"exchange": "NYQ"
      +"regularMarketTime": 1634328132
      +"volume24Hr": {#1209}
      +"regularMarketDayHigh": {#1157 ▶}
      +"shortName": "Citigroup, Inc."
      +"averageDailyVolume10Day": {#806 ▶}
      +"longName": "Citigroup Inc."
      +"regularMarketChange": {#789 ▶}
      +"currencySymbol": "$"
      +"regularMarketPreviousClose": {#1154 ▶}
      +"postMarketTime": 1634342387
      +"preMarketPrice": {#1170}
      +"exchangeDataDelayedBy": 0
      +"toCurrency": null
      +"postMarketChange": {#1164 ▶}
      +"postMarketPrice": {#767 ▶}
      +"exchangeName": "NYSE"
      +"preMarketChange": {#1158}
      +"circulatingSupply": {#1159}
      +"regularMarketDayLow": {#1176 ▶}
      +"priceHint": {#1160 ▶}
      +"currency": "USD"
      +"regularMarketPrice": {#1161 ▶}
      +"regularMarketVolume": {#785 ▶}
      +"lastMarket": null
      +"regularMarketSource": "DELAYED"
      +"openInterest": {#786}
      +"marketState": "PREPRE"
      +"underlyingSymbol": null
      +"marketCap": {#795 ▶}
      +"quoteType": "EQUITY"
      +"volumeAllCurrencies": {#800}
      +"postMarketSource": "DELAYED"
      +"strikePrice": {#801}
      +"symbol": "C"
      +"postMarketChangePercent": {#815 ▶}
      +"preMarketSource": "FREE_REALTIME"
      +"maxAge": 1
      +"fromCurrency": null
      +"regularMarketChangePercent": {#847 ▶}
    }
    +"secFilings": {#1024 ▶}
    +"quoteType": {#1025 ▶}
    +"calendarEvents": {#1026 ▶}
    +"summaryDetail": {#1038 ▶}
    +"symbol": "C"
    +"assetProfile": {#1084 ▶}
    +"pageViews": {#1216 ▶}
  } */