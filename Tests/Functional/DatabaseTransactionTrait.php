<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Tests\Functional;

trait DatabaseTransactionTrait
{
    use ContainerTrait;

    /**
     * @before
     *
     * @return void
     */
    public function startTransactionBefore()
    {
        $this->getContainer()->get('dbal_connection')->beginTransaction();
    }

    /**
     * @after
     *
     * @return void
     */
    public function stopTransactionAfter()
    {
        $this->getContainer()->get('dbal_connection')->rollBack();
        $this->getContainer()->get('models')->clear();
    }
}
