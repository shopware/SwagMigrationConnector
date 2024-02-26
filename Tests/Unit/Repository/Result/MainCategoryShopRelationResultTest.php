<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Tests\Unit\Repository\Result;

use PHPUnit\Framework\TestCase;
use SwagMigrationConnector\Repository\Result\ProductRepository\MainCategoryShopRelationResult;
use SwagMigrationConnector\Repository\Result\ProductRepository\ShopCategoryRelation;

class MainCategoryShopRelationResultTest extends TestCase
{
    const CATEGORY_THREE_ID = '3';
    const CATEGORY_FOUR_ID = '4';
    const CATEGORY_FIVE_ID = '5';
    const CATEGORY_TWELVE_ID = '12';
    const TEST_DATA_SET = [
         1 => '3',
         2 => '12',
         3 => '3',
         4 => '12',
         5 => '3',
         6 => '12',
         7 => '4',
         8 => '4',
         9 => '5',
         10 => '5',
     ];

    /**
     * @return void
     */
    public function testGetShopIds()
    {
        $mainCategoryShopRelationResult = $this->createMainCategoryRelationResult();

        static::assertSame(['1', '3', '5'], $mainCategoryShopRelationResult->getShopIds(self::CATEGORY_THREE_ID));
        static::assertSame(['7', '8'], $mainCategoryShopRelationResult->getShopIds(self::CATEGORY_FOUR_ID));
        static::assertSame(['9', '10'], $mainCategoryShopRelationResult->getShopIds(self::CATEGORY_FIVE_ID));
        static::assertSame(['2', '4', '6'], $mainCategoryShopRelationResult->getShopIds(self::CATEGORY_TWELVE_ID));
    }

    /**
     * @return void
     */
    public function testContainsCategory()
    {
        $mainCategoryShopRelationResult = $this->createMainCategoryRelationResult();

        static::assertTrue($mainCategoryShopRelationResult->containsCategory(self::CATEGORY_THREE_ID));
        static::assertTrue($mainCategoryShopRelationResult->containsCategory(self::CATEGORY_FOUR_ID));
        static::assertTrue($mainCategoryShopRelationResult->containsCategory(self::CATEGORY_FIVE_ID));
        static::assertTrue($mainCategoryShopRelationResult->containsCategory(self::CATEGORY_TWELVE_ID));
        static::assertFalse($mainCategoryShopRelationResult->containsCategory('1'));
        static::assertFalse($mainCategoryShopRelationResult->containsCategory('6'));
        static::assertFalse($mainCategoryShopRelationResult->containsCategory('11'));
    }

    /**
     * @return MainCategoryShopRelationResult
     */
    private function createMainCategoryRelationResult()
    {
        $mainCategoryShopRelationResult = new MainCategoryShopRelationResult();

        foreach (self::TEST_DATA_SET as $shopId => $categoryId) {
            $mainCategoryShopRelationResult->add(
                new ShopCategoryRelation(['shopId' => (string) $shopId, 'categoryId' => $categoryId])
            );
        }

        return $mainCategoryShopRelationResult;
    }
}
