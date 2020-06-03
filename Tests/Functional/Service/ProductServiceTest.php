<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Tests\Functional\Service;

use PHPUnit\Framework\TestCase;

class ProductServiceTest extends TestCase
{
    public function test_read_products_should_be_successful()
    {
        $productService = Shopware()->Container()->get('swag_migration_connector.service.product_service');

        $products = $productService->getProducts();

        $this->assertCount(250, $products);

        $this->assertArrayHasKey('detail', $products[0]);
        $this->assertArrayHasKey('id', $products[0]);
        $this->assertArrayHasKey('unit', $products[0]);
        $this->assertArrayHasKey('tax', $products[0]);
        $this->assertArrayHasKey('attributes', $products[0]);
        $this->assertArrayHasKey('manufacturer', $products[0]);
        $this->assertArrayHasKey('_locale', $products[0]);
        $this->assertArrayHasKey('categories', $products[0]);
        $this->assertArrayHasKey('prices', $products[0]);
        $this->assertArrayHasKey('customergroup', $products[0]['prices'][0]);
        $this->assertArrayHasKey('assets', $products[0]);
        $this->assertArrayHasKey('media', $products[0]['assets'][0]);
        $this->assertArrayHasKey('uri', $products[0]['assets'][0]['media']);

        $this->assertSame('3', $products[0]['id']);
    }

    public function test_read_products_with_offset_should_be_successful()
    {
        $productService = Shopware()->Container()->get('swag_migration_connector.service.product_service');

        $products = $productService->getProducts(134);

        $this->assertCount(250, $products);

        $product = $products[2];

        $this->assertSame('170', $product['id']);
        $this->assertSame('SW10170', $product['detail']['ordernumber']);
    }

    public function test_read_products_with_limit_should_be_successful()
    {
        $productService = Shopware()->Container()->get('swag_migration_connector.service.product_service');

        $products = $productService->getProducts(0, 2);

        $this->assertCount(2, $products);

        $product = $products[1];

        $this->assertSame('4', $product['detail']['id']);
        $this->assertSame('SW10004', $product['detail']['ordernumber']);
    }

    public function test_read_products_with_offset_and_limit_should_be_successful()
    {
        $productService = Shopware()->Container()->get('swag_migration_connector.service.product_service');

        $products = $productService->getProducts(350, 10);

        $this->assertCount(10, $products);

        $product = $products[0];

        $this->assertSame('563', $product['detail']['id']);
        $this->assertSame('SW10202.15', $product['detail']['ordernumber']);
    }

    public function test_read_with_out_of_bounds_offset_should_offer_empty_array()
    {
        $productService = Shopware()->Container()->get('swag_migration_connector.service.product_service');

        $products = $productService->getProducts(2000);

        $this->assertEmpty($products);
    }
}
