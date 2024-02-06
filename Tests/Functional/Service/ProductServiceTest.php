<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Tests\Functional\Service;

use PHPUnit\Framework\TestCase;
use SwagMigrationConnector\Tests\Functional\ContainerTrait;

class ProductServiceTest extends TestCase
{
    use ContainerTrait;

    /**
     * @return void
     */
    public function testReadProductsShouldBeSuccessful()
    {
        $productService = $this->getContainer()->get('swag_migration_connector.service.product_service');

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

    /**
     * @return void
     */
    public function testReadEsdProductShouldBeSuccessful()
    {
        $productService = $this->getContainer()->get('swag_migration_connector.service.product_service');

        $products = $productService->getProducts();

        static::assertCount(250, $products);

        static::assertArrayHasKey('esdFiles', $products[151]);
        static::assertSame([[
            'id' => '2',
            'name' => 'shopware_packshot_community_edition_72dpi_rgb.png',
            'path' => 's:33:"552211cce724117c3178e3d22bec532ec";',
        ]], $products[151]['esdFiles']);
    }

    /**
     * @return void
     */
    public function testReadProductsWithOffsetShouldBeSuccessful()
    {
        $productService = $this->getContainer()->get('swag_migration_connector.service.product_service');

        $products = $productService->getProducts(134);

        static::assertCount(250, $products);

        $product = $products[2];

        static::assertSame('170', $product['id']);
        static::assertSame('SW10170', $product['detail']['ordernumber']);
    }

    /**
     * @return void
     */
    public function testReadProductsWithLimitShouldBeSuccessful()
    {
        $productService = $this->getContainer()->get('swag_migration_connector.service.product_service');

        $products = $productService->getProducts(0, 2);

        static::assertCount(2, $products);

        $product = $products[1];

        static::assertSame('4', $product['detail']['id']);
        static::assertSame('SW10004', $product['detail']['ordernumber']);
    }

    /**
     * @return void
     */
    public function testReadProductsWithOffsetAndLimitShouldBeSuccessful()
    {
        $productService = $this->getContainer()->get('swag_migration_connector.service.product_service');

        $products = $productService->getProducts(350, 10);

        static::assertCount(10, $products);

        $product = $products[0];

        static::assertSame('563', $product['detail']['id']);
        static::assertSame('SW10202.15', $product['detail']['ordernumber']);
    }

    /**
     * @return void
     */
    public function testReadWithOutOfBoundsOffsetShouldOfferEmptyArray()
    {
        $productService = $this->getContainer()->get('swag_migration_connector.service.product_service');

        $products = $productService->getProducts(2000);

        static::assertEmpty($products);
    }
}
