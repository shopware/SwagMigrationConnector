<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Tests\Unit\Repository\Result;

use PHPUnit\Framework\TestCase;
use SwagMigrationConnector\Repository\Result\ProductRepository\ProductVisibilityResult;

class ProductVisibilityResultTest extends TestCase
{
    const PRODUCT_ID_ONE = '1';
    const PRODUCT_ID_TWO = '2';
    const PRODUCT_ID_THREE = '3';
    const PRODUCT_ID_FOUR = '4';
    const PRODUCT_ID_FIVE = '5';

    const TEST_DATA = [
        self::PRODUCT_ID_ONE => ['1', '2'],
        self::PRODUCT_ID_TWO => ['3', '4'],
        self::PRODUCT_ID_THREE => ['5', '6'],
        self::PRODUCT_ID_FOUR => ['7', '8'],
        self::PRODUCT_ID_FIVE => ['9', '10'],
    ];

    /**
     * @return void
     */
    public function testAdd()
    {
        $productVisibilityResult = $this->createProductVisibilityResult();

        static::assertSame([], $productVisibilityResult->getShops('48'));

        $productVisibilityResult->add(48, ['11', '12', '11', '12']);

        static::assertSame(['11', '12'], $productVisibilityResult->getShops('48'));
    }

    /**
     * @return void
     */
    public function testGetShops()
    {
        $productVisibilityResult = $this->createProductVisibilityResult();

        static::assertSame(['1', '2'], $productVisibilityResult->getShops(self::PRODUCT_ID_ONE));
        static::assertSame(['3', '4'], $productVisibilityResult->getShops(self::PRODUCT_ID_TWO));
        static::assertSame(['5', '6'], $productVisibilityResult->getShops(self::PRODUCT_ID_THREE));
        static::assertSame(['7', '8'], $productVisibilityResult->getShops(self::PRODUCT_ID_FOUR));
        static::assertSame(['9', '10'], $productVisibilityResult->getShops(self::PRODUCT_ID_FIVE));
        static::assertSame([], $productVisibilityResult->getShops('6'));
        static::assertSame([], $productVisibilityResult->getShops('12'));
        static::assertSame([], $productVisibilityResult->getShops('24'));
        static::assertSame([], $productVisibilityResult->getShops('48'));
    }

    /**
     * @return ProductVisibilityResult
     */
    private function createProductVisibilityResult()
    {
        $productVisibilityResult = new ProductVisibilityResult();
        foreach (self::TEST_DATA as $productId => $shops) {
            $productVisibilityResult->add($productId, $shops);
        }

        return $productVisibilityResult;
    }
}
