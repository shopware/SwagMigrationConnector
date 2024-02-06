<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Tests\Functional\Service;

use PHPUnit\Framework\TestCase;
use SwagMigrationConnector\Tests\Functional\ContainerTrait;

class MainVariantRelationServiceTest extends TestCase
{
    use ContainerTrait;

    /**
     * @return void
     */
    public function testReadShouldBeSuccessful()
    {
        $service = $this->getContainer()->get('swag_migration_connector.service.main_variant_relation_service');

        $data = $service->getMainVariantRelations();

        static::assertCount(27, $data);

        $row = $data[0];

        static::assertSame('2', $row['id']);
        static::assertSame('SW10002.3', $row['ordernumber']);
    }

    /**
     * @return void
     */
    public function testReadWithOffsetShouldBeSuccessful()
    {
        $service = $this->getContainer()->get('swag_migration_connector.service.main_variant_relation_service');

        $data = $service->getMainVariantRelations(1);

        static::assertCount(26, $data);
    }

    /**
     * @return void
     */
    public function testReadWithLimitShouldBeSuccessful()
    {
        $service = $this->getContainer()->get('swag_migration_connector.service.main_variant_relation_service');

        $data = $service->getMainVariantRelations(0, 1);

        static::assertCount(1, $data);
    }

    /**
     * @return void
     */
    public function testReadWithLimitAndOffsetShouldBeSuccessful()
    {
        $service = $this->getContainer()->get('swag_migration_connector.service.main_variant_relation_service');

        $data = $service->getMainVariantRelations(1, 1);

        static::assertCount(1, $data);
    }

    /**
     * @return void
     */
    public function testReadWithOutOfBoundsOffsetShouldOfferEmptyArray()
    {
        $service = $this->getContainer()->get('swag_migration_connector.service.main_variant_relation_service');

        $data = $service->getMainVariantRelations(50);

        static::assertEmpty($data);
    }
}
