<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Tests\Unit\Repository\Result;

use PHPUnit\Framework\TestCase;
use SwagMigrationConnector\Repository\Result\ProductRepository\ShopCategoryRelation;

class ShopCategoryRelationTest extends TestCase
{
    /**
     * @return void
     */
    public function testIsCategory()
    {
        $relation = new ShopCategoryRelation(['shopId' => '1', 'categoryId' => '2']);

        static::assertSame('1', $relation->getShopId());
        static::assertSame('2', $relation->getCategoryId());

        static::assertTrue($relation->isCategory('2'));

        static::assertFalse($relation->isCategory('1'));
        static::assertFalse($relation->isCategory('3'));
        static::assertFalse($relation->isCategory('42'));
    }
}
