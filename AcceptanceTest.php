<?php

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class AcceptanceTest extends ApiTestCase
{
    private array $defaultValues;
    public function setUp(): void
    {
        $this->defaultValues = json_decode(file_get_contents('./productsDefault.json'), true);
        $this->writeData($this->defaultValues);
        parent::setUp();
    }

    public function testStatus(): void
    {
        static::createClient()->request('GET', '/products');
        $this->assertResponseIsSuccessful();
    }

    public function testAdding(): void
    {
        $response = static::createClient()->request('PUT', '/products', [
            'json' => $this->defaultValues,
            'headers' => ['content-type' => ['application/json']],
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['message' => 'products were successfully added!']);
        $this->assertResponseStatusCodeSame(200);
    }

    public function testMaxLengthProducts():void
    {
        $products = [];
        for($i=0; $i<20000; $i++){
            $product = [
                "sku"=>"0".($i+1),
                "name"=>'lala',
                "category"=>"boots",
                "price"=>89000
            ];
            $products['products'][] = $product;
        }
        $response = static::createClient()->request('PUT', '/products', [
            'json' => $products,
            'headers' => ['content-type' => ['application/json']],
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['message' => 'products were successfully added!']);
        $this->assertResponseStatusCodeSame(200);
    }

    public function testAdded(): void
    {
        $single = ["products"=>[
            [
                "sku"=>"000001",
                "name"=>"BV Lean leather ankle boots",
                "category"=>"boots",
                "price"=>89000
            ]
        ]];
        $response = static::createClient()->request('PUT', '/products', [
            'json' => $single,
            'headers' => ['content-type' => ['application/json']],
        ]);
        $added = json_decode(file_get_contents('./public/productsTest.json'),true);
        $this->assertEqualsCanonicalizing($single, $added);
    }

    /** GET endpoint **/

    public function testCategoryFilter(): void
    {
        $arr = $this->getResponseData('/products?category=boots');
        $expectedSkus = ['000001','000002','000003'];
        $unexpectedSkus = ['000004','000005'];
        foreach ($arr as $product){
            $this->assertContains($product['sku'], $expectedSkus);
            $this->assertNotContains($product['sku'], $unexpectedSkus);
            if (($key = array_search($product['sku'], $expectedSkus)) !== false) {
                unset($expectedSkus[$key]);
            }
        }
    }

    public function testPriceFilter(): void
    {
        $arr = $this->getResponseData('/products?priceLessThan=75000');
        $expectedSkus = ['000005','000003'];
        $unexpectedSkus = ['000001','000004','000002'];
        foreach ($arr as $product){
            $this->assertContains($product['sku'], $expectedSkus);
            $this->assertNotContains($product['sku'], $unexpectedSkus);
            if (($key = array_search($product['sku'], $expectedSkus)) !== false) {
                unset($expectedSkus[$key]);
            }
        }
    }

    public function testBothFilters(): void
    {
        $arr = $this->getResponseData('/products?priceLessThan=75000&category=boots');
        $this->assertEquals(array_values($arr)[0]['sku'], '000003');
        $this->assertCount(1, $arr);
    }

    public function testReturnsNoMoreThanFive(): void
    {
        $this->defaultValues['products'][] = [
            "sku"=>"000006",
            "name"=>"Fool Plate Boots",
            "category"=>"boots",
            "price"=>99000
        ];
        $this->writeData($this->defaultValues);
        $arr = $this->getResponseData('/products');
        $this->assertCount(5, $arr);
    }

    public function testCurrency(): void
    {
        $arr = $this->getResponseData('/products');
        foreach ($arr as $product){
            $this->assertEquals('EUR', $product['price']['currency']);
        }
    }

    public function testDiscountsApplied():void
    {
        $arr = $this->getResponseData('/products');
        $this->assertEquals('30%', $arr['000001']['price']['discount\_percentage'] );
        $this->assertEquals('30%', $arr['000002']['price']['discount\_percentage'] );
    }

    public function testBiggerDiscountApplied():void
    {
        $arr = $this->getResponseData('/products');
        $this->assertEquals('30%', $arr['000003']['price']['discount\_percentage'] );
    }

    public function testDiscountsNotAppliedForSandalsAndSneakers():void
    {
        $arr = $this->getResponseData('/products');
        $this->assertNull($arr['000004']['price']['discount\_percentage'] );
        $this->assertEquals($arr['000004']['price']['original'], $arr['000004']['price']['final']);
        $this->assertNull($arr['000005']['price']['discount\_percentage'] );
        $this->assertEquals($arr['000005']['price']['original'], $arr['000005']['price']['final']);
    }

    public function testSkuDiscountApplied():void
    {
        foreach($this->defaultValues['products'] as $i=>$whatever){
            $this->defaultValues['products'][$i]['category'] = 'greaves';
        }
        $this->writeData($this->defaultValues);
        $arr = $this->getResponseData('/products');
        $this->assertEquals('15%', $arr['000003']['price']['discount\_percentage'] );
        $this->assertEquals('60350', $arr['000003']['price']['final'] );
    }

//- When a product has a discount price.original is the original price, price.final is the amount with the discount applied and discount\_percentage represents the applied discount with the % sign.

    private function getResponseData(string $url): array
    {
        $result = [];
        $response = static::createClient()->request('GET', $url);
        foreach (json_decode($response->getContent(),true) as $product){
            $result[$product['sku']] = $product;
        }
        return $result;
    }

    private function writeData(array $data)
    {
        file_put_contents('./public/productsTest.json', json_encode($data, JSON_PRETTY_PRINT));
    }
}
