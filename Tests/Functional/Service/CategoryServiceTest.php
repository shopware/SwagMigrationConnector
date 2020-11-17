<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Tests\Functional\Service;

use PHPUnit\Framework\TestCase;

class CategoryServiceTest extends TestCase
{
    public function test_read_categories_should_be_successful()
    {
        $categoryService = Shopware()->Container()->get('swag_migration_connector.service.category_service');

        $categories = $categoryService->getCategories();

        static::assertCount(62, $categories);

        static::assertArrayHasKey('attributes', $categories[0]);
        static::assertArrayHasKey('_locale', $categories[0]);

        static::assertSame('3', $categories[0]['id']);
        static::assertNull($categories[0]['parent']);
        static::assertSame('Deutsch', $categories[0]['description']);
        static::assertSame('de-DE', $categories[0]['_locale']);
    }

    public function test_read_categories_with_offset_should_be_succesful()
    {
        $categoryService = Shopware()->Container()->get('swag_migration_connector.service.category_service');

        $categories = $categoryService->getCategories(58);

        static::assertCount(4, $categories);

        $category = $categories[1];

        static::assertArrayHasKey('_locale', $category);
        static::assertSame('en-GB', $category['_locale']);
    }

    public function test_read_categories_with_limit_should_be_succesful()
    {
        $categoryService = Shopware()->Container()->get('swag_migration_connector.service.category_service');

        $categories = $categoryService->getCategories(0, 5);

        static::assertCount(5, $categories);

        $category = $categories[2];

        static::assertArrayHasKey('_locale', $category);
        static::assertSame('de-DE', $category['_locale']);
    }

    public function test_read_categories_with_offset_and_limit_should_be_succesful()
    {
        $categoryService = Shopware()->Container()->get('swag_migration_connector.service.category_service');

        $categories = $categoryService->getCategories(50, 5);

        static::assertCount(5, $categories);

        $category = $categories[4];
        static::assertArrayHasKey('_locale', $category);
        static::assertSame('en-GB', $category['_locale']);
        static::assertSame('61', $category['parent']);
        static::assertSame('|61|39|', $category['path']);
    }

    public function test_read_with_out_of_bounds_offset_should_offer_empty_array()
    {
        $categoryService = Shopware()->Container()->get('swag_migration_connector.service.category_service');

        $categories = $categoryService->getCategories(200);

        static::assertEmpty($categories);
    }
}
