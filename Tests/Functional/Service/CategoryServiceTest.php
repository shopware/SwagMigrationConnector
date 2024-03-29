<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Tests\Functional\Service;

use PHPUnit\Framework\TestCase;
use SwagMigrationConnector\Tests\Functional\ContainerTrait;

class CategoryServiceTest extends TestCase
{
    use ContainerTrait;

    /**
     * @return void
     */
    public function setUpOwn()
    {
        $sql = \file_get_contents(__DIR__ . '/_fixtures/category_media.sql');

        static::assertTrue(\is_string($sql));
        $this->getContainer()->get('dbal_connection')->exec($sql);
    }

    /**
     * @return void
     */
    public function testReadCategoriesShouldBeSuccessful()
    {
        $this->setUpOwn();
        $categoryService = $this->getContainer()->get('swag_migration_connector.service.category_service');

        $categories = $categoryService->getCategories();

        static::assertCount(62, $categories);

        static::assertArrayHasKey('attributes', $categories[0]);
        static::assertArrayHasKey('_locale', $categories[0]);

        static::assertSame('3', $categories[0]['id']);
        static::assertNull($categories[0]['parent']);
        static::assertSame('Deutsch', $categories[0]['description']);
        static::assertSame('de-DE', $categories[0]['_locale']);
        static::assertTrue(\strpos($categories[2]['asset']['uri'], '/media/image/ab/7f/4f/Muensterlaender_Lagerkorn_Ballons_Hochformat.jpg') !== false);
    }

    /**
     * @return void
     */
    public function testReadCategoriesWithOffsetShouldBeSuccesful()
    {
        $categoryService = $this->getContainer()->get('swag_migration_connector.service.category_service');

        $categories = $categoryService->getCategories(58);

        static::assertCount(4, $categories);

        $category = $categories[1];

        static::assertArrayHasKey('_locale', $category);
        static::assertSame('en-GB', $category['_locale']);
    }

    /**
     * @return void
     */
    public function testReadCategoriesWithLimitShouldBeSuccesful()
    {
        $categoryService = $this->getContainer()->get('swag_migration_connector.service.category_service');

        $categories = $categoryService->getCategories(0, 5);

        static::assertCount(5, $categories);

        $category = $categories[2];

        static::assertArrayHasKey('_locale', $category);
        static::assertSame('de-DE', $category['_locale']);
    }

    /**
     * @return void
     */
    public function testReadCategoriesWithOffsetAndLimitShouldBeSuccesful()
    {
        $categoryService = $this->getContainer()->get('swag_migration_connector.service.category_service');

        $categories = $categoryService->getCategories(50, 5);

        static::assertCount(5, $categories);

        $category = $categories[4];
        static::assertArrayHasKey('_locale', $category);
        static::assertSame('en-GB', $category['_locale']);
        static::assertSame('61', $category['parent']);
        static::assertSame('|61|39|', $category['path']);
    }

    /**
     * @return void
     */
    public function testReadWithOutOfBoundsOffsetShouldOfferEmptyArray()
    {
        $categoryService = $this->getContainer()->get('swag_migration_connector.service.category_service');

        $categories = $categoryService->getCategories(200);

        static::assertEmpty($categories);
    }
}
