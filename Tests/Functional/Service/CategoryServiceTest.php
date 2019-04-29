<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationAssistant\Tests\Functional\Service;

use PHPUnit\Framework\TestCase;

class CategoryServiceTest extends TestCase
{
    public function test_read_categories_should_be_successful()
    {
        $categoryService = Shopware()->Container()->get('swag_migration_assistant.service.category_service');

        $categories = $categoryService->getCategories();

        $this->assertInternalType('array', $categories);
        $this->assertCount(62, $categories);

        $this->assertArrayHasKey('attributes', $categories[0]);
        $this->assertArrayHasKey('_locale', $categories[0]);

        $this->assertSame('3', $categories[0]['id']);
        $this->assertNull($categories[0]['parent']);
        $this->assertSame('Deutsch', $categories[0]['description']);
        $this->assertSame('de-DE', $categories[0]['_locale']);
    }

    public function test_read_categories_with_offset_should_be_succesful()
    {
        $categoryService = Shopware()->Container()->get('swag_migration_assistant.service.category_service');

        $categories = $categoryService->getCategories(58);

        $this->assertInternalType('array', $categories);
        $this->assertCount(4, $categories);

        $category = $categories[1];

        $this->assertArrayHasKey('_locale', $category);
        $this->assertSame('en-GB', $category['_locale']);
    }

    public function test_read_categories_with_limit_should_be_succesful()
    {
        $categoryService = Shopware()->Container()->get('swag_migration_assistant.service.category_service');

        $categories = $categoryService->getCategories(0, 5);

        $this->assertInternalType('array', $categories);
        $this->assertCount(5, $categories);

        $category = $categories[2];

        $this->assertArrayHasKey('_locale', $category);
        $this->assertSame('de-DE', $category['_locale']);
    }

    public function test_read_categories_with_offset_and_limit_should_be_succesful()
    {
        $categoryService = Shopware()->Container()->get('swag_migration_assistant.service.category_service');

        $categories = $categoryService->getCategories(50, 5);

        $this->assertInternalType('array', $categories);
        $this->assertCount(5, $categories);

        $category = $categories[4];
        $this->assertArrayHasKey('_locale', $category);
        $this->assertSame('en-GB', $category['_locale']);
        $this->assertSame('61', $category['parent']);
        $this->assertSame('|61|39|', $category['path']);
    }

    public function test_read_with_out_of_bounds_offset_should_offer_empty_array()
    {
        $categoryService = Shopware()->Container()->get('swag_migration_assistant.service.category_service');

        $categories = $categoryService->getCategories(200);

        $this->assertEmpty($categories);
    }
}
