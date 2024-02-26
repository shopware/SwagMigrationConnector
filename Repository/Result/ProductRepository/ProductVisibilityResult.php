<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Repository\Result\ProductRepository;

class ProductVisibilityResult
{
    /**
     * @var array<int, array<int, string>>
     */
    private $productVisibility = [];

    /**
     * @param int                $productId
     * @param array<int, string> $shops
     *
     * @return void
     */
    public function add($productId, array $shops)
    {
        $this->productVisibility[$productId] = array_values(
            array_unique(
                array_merge(
                    $shops,
                    !isset($this->productVisibility[$productId]) ? [] : $this->productVisibility[$productId]
                )
            )
        );
    }

    /**
     * @param string|int $productId
     *
     * @return array<int, string>
     */
    public function getShops($productId)
    {
        $productId = (int) $productId;
        if (!$this->hasShops($productId)) {
            return [];
        }

        return $this->productVisibility[$productId];
    }

    /**
     * @param int $productId
     *
     * @return bool
     */
    private function hasShops($productId)
    {
        return isset($this->productVisibility[$productId]);
    }
}
