<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    #[Route('/products', name: 'set_products', methods: 'PUT')]
    public function products(Request $request): JsonResponse
    {
        $path = $this->getParameter('kernel.environment') === 'test' ? './public/productsTest.json' : 'products.json';
        file_put_contents($path, $request->getContent());
        return $this->json(['message' => 'products were successfully added!', 200]);
    }

    #[Route('/', name: 'index', methods: 'GET')]
    #[Route('/products', name: 'get_products', methods: 'GET')]
    public function topProducts(Request $request): JsonResponse
    {
        $path = $this->getParameter('kernel.environment') === 'test' ? './public/productsTest.json' : 'products.json';
        $discounts = [["sku"=>"000003", "value"=>15],["category"=>"boots", "value"=>30]];
        $filters['category'] = $request->get('category');
        $filters['priceLessThan'] = $request->get('priceLessThan');
        $products = json_decode(file_get_contents($path), true)['products'];
        $result = [];
        $resultCount = 0;
        foreach( $products as $i=>$product){
            if($resultCount>4){
                break;
            }
            if(
                $filters['category'] && $product['category'] !== $filters['category'] ||
                $filters['priceLessThan'] && $product['price'] > $filters['priceLessThan']
            ){
                continue;
            }
            $discountVal = null;
            foreach ($discounts as $discount){
                $discountApplies = true;
                foreach ($discount as $key=>$val){
                    if($key === 'value'){
                        continue;
                    }
                    if($product[$key] !== $val){
                        $discountApplies = false;
                    }
                }
                if($discountApplies && (is_null($discountVal) || $discount['value'] > $discountVal)){
                    $discountVal = $discount['value'];
                }
            }
            $result[$i] = $product;
            $result[$i]['price'] = [
                "original" => $product['price'],
                "final"=>round($product['price']*(100-$discountVal)/100),
                "discount\_percentage"=> is_null($discountVal) ? null : "$discountVal%",
                "currency"=> "EUR"
            ];
            $resultCount++;
        }
        return $this->json($result);
    }
}
