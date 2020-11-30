<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Tests\Functional\Service;

use PHPUnit\Framework\TestCase;

class MainVariantRelationServiceTest extends TestCase
{
    public function test_read_should_be_successful()
    {
        $service = Shopware()->Container()->get('swag_migration_connector.service.main_variant_relation_service');

        $data = $service->getMainVariantRelations();

        static::assertCount(27, $data);

        $row = $data[0];

        static::assertSame('2', $row['id']);
        static::assertSame('SW10002.3', $row['ordernumber']);
    }

    public function test_read_with_offset_should_be_successful()
    {
        $service = Shopware()->Container()->get('swag_migration_connector.service.main_variant_relation_service');

        $data = $service->getMainVariantRelations(1);

        static::assertCount(26, $data);
    }

    public function test_read_with_limit_should_be_successful()
    {
        $service = Shopware()->Container()->get('swag_migration_connector.service.main_variant_relation_service');

        $data = $service->getMainVariantRelations(0, 1);

        static::assertCount(1, $data);
    }

    public function test_read_with_limit_and_offset_should_be_successful()
    {
        $service = Shopware()->Container()->get('swag_migration_connector.service.main_variant_relation_service');

        $data = $service->getMainVariantRelations(1, 1);

        static::assertCount(1, $data);
    }

    public function test_read_with_out_of_bounds_offset_should_offer_empty_array()
    {
        $service = Shopware()->Container()->get('swag_migration_connector.service.main_variant_relation_service');

        $data = $service->getMainVariantRelations(50);

        static::assertEmpty($data);
    }
}
