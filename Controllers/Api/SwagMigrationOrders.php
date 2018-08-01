<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Shopware\Models\Order\Document\Document;

/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Shopware_Controllers_Api_SwagMigrationOrders extends Shopware_Controllers_Api_Rest
{
    public function indexAction()
    {
        $offset = (int) $this->Request()->getParam('offset', 0);
        $limit = (int) $this->Request()->getParam('limit', 250);
        $orderService = $this->container->get('swag_migration_api.service.order_service');

        $orders = $orderService->getOrders($offset, $limit);

        $this->view->assign(['success' => true, 'data' => $orders]);
    }

    /**
     * Delivers an order document by its hash for download.
     *
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function getAction()
    {
        $documentHash = $this->Request()->getParam('id', null);
        $filesystem = $this->container->get('shopware.filesystem.private');
        $file = sprintf('documents/%s.pdf', basename($documentHash));

        if ($filesystem->has($file) === false) {
            $this->View()->assign([
                'success' => false,
                'data' => $this->Request()->getParams(),
                'message' => 'File not exist',
            ]);

            return;
        }

        $orderId = (int) $this->container->get('dbal_connection')
            ->createQueryBuilder()
            ->select('docID')
            ->from('s_order_documents', 'document')
            ->where('document.hash = :hash')
            ->setParameter('hash', $documentHash)
            ->execute()
            ->fetchColumn()
        ;

        $response = $this->Response();
        $response->setHeader('Cache-Control', 'public');
        $response->setHeader('Content-Description', 'File Transfer');
        $response->setHeader('Content-disposition', 'attachment; filename=' . $orderId . '.pdf');
        $response->setHeader('Content-Type', 'application/pdf');
        $response->setHeader('Content-Transfer-Encoding', 'binary');
        $response->setHeader('Content-Length', $filesystem->getSize($file));
        $response->sendHeaders();
        $response->sendResponse();

        $upstream = $filesystem->readStream($file);
        $downstream = fopen('php://output', 'rb');

        while (!feof($upstream)) {
            fwrite($downstream, fread($upstream, 4096));
        }
    }
}
