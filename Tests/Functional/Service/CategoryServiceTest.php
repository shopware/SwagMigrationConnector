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
        // contains stripped results, e.g. unneeded parent categories
        $this->assertCount(60, $categories);

        $this->assertArrayHasKey('attributes', $categories[0]);
        $this->assertArrayHasKey('_locale', $categories[0]);

        $this->assertSame('5', $categories[0]['id']);
        $this->assertNull($categories[0]['parent']);
        $this->assertSame('Genusswelten', $categories[0]['description']);
        $this->assertSame('de_DE', $categories[0]['_locale']);
    }

    public function test_read_categories_with_offset_should_be_succesful()
    {
        $categoryService = Shopware()->Container()->get('swag_migration_assistant.service.category_service');

        $categories = $categoryService->getCategories(58);

        $this->assertInternalType('array', $categories);
        // no results will be stripped out
        $this->assertCount(4, $categories);

        $category = $categories[1];

        $this->assertArrayHasKey('_locale', $category);
        $this->assertSame('en_GB', $category['_locale']);
    }

    public function test_read_categories_with_limit_should_be_succesful()
    {
        $categoryService = Shopware()->Container()->get('swag_migration_assistant.service.category_service');

        $categories = $categoryService->getCategories(0, 5);

        $this->assertInternalType('array', $categories);
        // 2 results (Deutsch, English) will be stripped out
        $this->assertCount(3, $categories);

        $category = $categories[2];

        $this->assertArrayHasKey('_locale', $category);
        $this->assertSame('de_DE', $category['_locale']);
    }

    public function test_read_categories_with_offset_and_limit_should_be_succesful()
    {
        $categoryService = Shopware()->Container()->get('swag_migration_assistant.service.category_service');

        $categories = $categoryService->getCategories(50, 5);

        $this->assertInternalType('array', $categories);
        $this->assertCount(5, $categories);

        $category = $categories[4];
        $this->assertArrayHasKey('_locale', $category);
        $this->assertSame('en_GB', $category['_locale']);
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
