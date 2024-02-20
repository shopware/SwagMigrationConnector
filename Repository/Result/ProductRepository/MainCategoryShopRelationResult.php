<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Repository\Result\ProductRepository;

class MainCategoryShopRelationResult
{
    /**
     * @var array<int, ShopCategoryRelation>
     */
    private $shopCategoryRelation = [];

    /**
     * @return void
     */
    public function add(ShopCategoryRelation $shopCategoryRelation)
    {
        $this->shopCategoryRelation[] = $shopCategoryRelation;
    }

    /**
     * @param string $categoryId
     *
     * @return array<int, string>
     */
    public function getShopIds($categoryId)
    {
        $result = array_filter($this->shopCategoryRelation, function ($shopCategoryRelation) use ($categoryId) {
            return $shopCategoryRelation->getCategoryId() === $categoryId;
        });

        return \array_values(
            \array_map(function (ShopCategoryRelation $shopCategoryRelation) {
                return $shopCategoryRelation->getShopId();
            }, $result)
        );
    }

    /**
     * @param string $categoryId
     *
     * @return bool
     */
    public function containsCategory($categoryId)
    {
        foreach ($this->shopCategoryRelation as $shopCategoryRelation) {
            if ($shopCategoryRelation->isCategory($categoryId)) {
                return true;
            }
        }

        return false;
    }
}
