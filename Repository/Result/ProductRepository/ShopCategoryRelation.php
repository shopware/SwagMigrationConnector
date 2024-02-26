<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Repository\Result\ProductRepository;

class ShopCategoryRelation
{
    /**
     * @var string
     */
    private $shopId;

    /**
     * @var string
     */
    private $categoryId;

    /**
     * @param array<string, string> $relation
     */
    public function __construct(array $relation)
    {
        $this->shopId = $relation['shopId'];
        $this->categoryId = $relation['categoryId'];
    }

    /**
     * @param string $categoryId
     *
     * @return bool
     */
    public function isCategory($categoryId)
    {
        return $this->categoryId === $categoryId;
    }

    /**
     * @return string
     */
    public function getShopId()
    {
        return $this->shopId;
    }

    /**
     * @return string
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }
}
