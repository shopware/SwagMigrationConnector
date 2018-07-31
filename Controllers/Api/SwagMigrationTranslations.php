<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Shopware_Controllers_Api_SwagMigrationTranslations extends Shopware_Controllers_Api_Rest
{
    public function indexAction()
    {
        $offset = (int) $this->Request()->getParam('offset', 0);
        $limit = (int) $this->Request()->getParam('limit', 250);
        $translationService = $this->container->get('swag_migration_api.service.translation_service');

        $translations = $translationService->getTranslations($offset, $limit);

        $this->View()->assign([
            'success' => true,
            'data' => $translations,
        ]);
    }
}
