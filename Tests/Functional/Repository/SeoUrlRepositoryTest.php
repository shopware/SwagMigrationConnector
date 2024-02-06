<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Tests\Functional\Repository;

use PHPUnit\Framework\TestCase;
use SwagMigrationConnector\Repository\SeoUrlRepository;
use SwagMigrationConnector\Tests\Functional\DatabaseTransactionTrait;

class SeoUrlRepositoryTest extends TestCase
{
    use DatabaseTransactionTrait;

    /**
     * @return void
     */
    public function testFetchShouldLowerTheSeoUrls()
    {
        $this->setRouterToLowerValue(true);
        $repository = $this->getSeoUrlRepository();

        $result = $repository->fetch(200, 2);

        static::assertSame('wohnwelten/kuechenaccessoires/81/backform-pink', $result[0]['url.path']);
        static::assertSame('genusswelten/edelbraende/2/muensterlaender-lagerkorn-32', $result[1]['url.path']);
    }

    /**
     * @return void
     */
    public function testFetchShouldNotLowerTheSeoUrls()
    {
        $this->setRouterToLowerValue(false);
        $repository = $this->getSeoUrlRepository();

        $result = $repository->fetch(200, 2);

        static::assertSame('Wohnwelten/Kuechenaccessoires/81/Backform-pink', $result[0]['url.path']);
        static::assertSame('Genusswelten/Edelbraende/2/Muensterlaender-Lagerkorn-32', $result[1]['url.path']);
    }

    /**
     * @return SeoUrlRepository
     */
    private function getSeoUrlRepository()
    {
        return new SeoUrlRepository($this->getContainer()->get('dbal_connection'));
    }

    /**
     * @return void
     */
    private function setRouterToLowerValue(bool $value)
    {
        $serializedValue = \serialize($value);
        $connection = $this->getContainer()->get('dbal_connection');

        $elementId = $connection->executeQuery(
            'SELECT `id` FROM `s_core_config_elements` WHERE `name` = "routerToLower";'
        )->fetchColumn();

        $value = $connection->executeQuery(
            'SELECT `value` FROM `s_core_config_values` WHERE `element_id` = :elementId;',
            ['elementId' => (int) $elementId]
        )->fetchColumn();

        if (!\is_string($value)) {
            $connection->executeQuery(
                'INSERT INTO `s_core_config_values` (`element_id`, `shop_id`, `value`) VALUES (:elementId, :shopId, :value)',
                ['elementId' => $elementId, 'shopId' => 1, 'value' => $serializedValue]
            );

            return;
        }

        $connection->executeQuery(
            'UPDATE `s_core_config_values` SET `value` = :value WHERE `element_id` = :elementId AND `shop_id` = 1;',
            ['elementId' => $elementId, 'value' => $serializedValue]
        );
    }
}
