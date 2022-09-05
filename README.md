Implemented a REST API endpoint that given a list of products, applies some discounts to them and can be filtered. 
Language chosen is PHP with Symfony framework.
To run it you need to have Composer, SymfonyCLI (https://symfony.com/download) and makeGNU (`sudo apt install make`) installed

to install project run once: <br/>
`make install` <br/>


- Code structure/architecture was simplified to the extreme.
- The project must be runnable with 1 simple command from any machine. Run with `make start`
- Because of simplistic version only Api tests are written. Run tests with `make test`
- Was not sure if you want a simple or verbose solution so prepared 2. This one is oversimplified show my ability to make shitty code fast. 
The other one is unnecessary complex to show my knowledge. 

Given this list of products found in productsDefault.json that can hold up to 20000 products and given that:

- Products in the boots category have a 30% discount. 
- The product with sku = 000003 has a 15% discount. 
- When multiple discounts collide, the biggest discount must be applied. 

Provided a single endpoint 

GET /products 

- Can be filtered by category as a query string parameter 
- (optional) Can be filtered by priceLessThan as a query string parameter, this filter applies before discounts are applied and will show products with prices lesser than or equal the value provided. 
- Returns a list of Product with the given discounts applied when necessary 
- Must return at most 5 elements. (The order does not matter) 

Product model 

- price.currency is always EUR 
- When a product does not have a discount, price.final and price.original should be the same number and discount\_percentage should be null. 
- When a product has a discount price.original is the original price, price.final is the amount with the discount applied and discount\_percentage represents the applied discount with the % sign. 

Example product with a discount of 30% applied: 

{ 

"sku": "000001", 

"name": "BV Lean leather ankle boots", "category": "boots", 

"price": { 

"original": 89000, 

"final": 62300, "discount\_percentage": "30%", "currency": "EUR" 

} 

} 

Example product without a discount 

{ 

"sku": "000001", 

"name": "BV Lean leather ankle boots", "category": "boots", 

"price": {  

"original": 89000, 

"final": 89000, "discount\_percentage": null, "currency": "EUR" 

} }
