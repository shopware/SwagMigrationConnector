<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Shopware\Models\Shop\Shop;

class Shopware_Controllers_Api_SwagMigrationProducts extends Shopware_Controllers_Api_Rest
{
    public function indexAction()
    {
        $offset = (int) $this->Request()->getParam('start', 0);
        $limit = (int) $this->Request()->getParam('limit', 250);
        $productService = $this->container->get('swag_migration_api.service.product_service');

        $products = $productService->getProducts($offset, $limit);
        /** @var Shop $defaultShop */
        $defaultShop = $this->container->get('models')->getRepository(Shop::class)->getDefault();

        $this->view->assign([
            'success' => true,
            'data' => $products,
            'default_locale' => $defaultShop->getLocale()->getLocale(),
        ]);
    }
}
