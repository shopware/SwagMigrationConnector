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

        static::assertCount(250, $products);

        static::assertArrayHasKey('detail', $products[0]);
        static::assertArrayHasKey('id', $products[0]);
        static::assertArrayHasKey('unit', $products[0]);
        static::assertArrayHasKey('tax', $products[0]);
        static::assertArrayHasKey('attributes', $products[0]);
        static::assertArrayHasKey('manufacturer', $products[0]);
        static::assertArrayHasKey('_locale', $products[0]);
        static::assertArrayHasKey('categories', $products[0]);
        static::assertArrayHasKey('prices', $products[0]);
        static::assertArrayHasKey('customergroup', $products[0]['prices'][0]);
        static::assertArrayHasKey('assets', $products[0]);
        static::assertArrayHasKey('media', $products[0]['assets'][0]);
        static::assertArrayHasKey('uri', $products[0]['assets'][0]['media']);

        static::assertSame('3', $products[0]['id']);
    }

    public function test_read_products_with_offset_should_be_successful()
    {
        $productService = Shopware()->Container()->get('swag_migration_connector.service.product_service');

        $products = $productService->getProducts(134);

        static::assertCount(250, $products);

        $product = $products[2];

        static::assertSame('170', $product['id']);
        static::assertSame('SW10170', $product['detail']['ordernumber']);
    }

    public function test_read_products_with_limit_should_be_successful()
    {
        $productService = Shopware()->Container()->get('swag_migration_connector.service.product_service');

        $products = $productService->getProducts(0, 2);

        static::assertCount(2, $products);

        $product = $products[1];

        static::assertSame('4', $product['detail']['id']);
        static::assertSame('SW10004', $product['detail']['ordernumber']);
    }

    public function test_read_products_with_offset_and_limit_should_be_successful()
    {
        $productService = Shopware()->Container()->get('swag_migration_connector.service.product_service');

        $products = $productService->getProducts(350, 10);

        static::assertCount(10, $products);

        $product = $products[0];

        static::assertSame('563', $product['detail']['id']);
        static::assertSame('SW10202.15', $product['detail']['ordernumber']);
    }

    public function test_read_with_out_of_bounds_offset_should_offer_empty_array()
    {
        $productService = Shopware()->Container()->get('swag_migration_connector.service.product_service');

        $products = $productService->getProducts(2000);

        static::assertEmpty($products);
    }
}
