<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Tests\Functional\Service;

use PHPUnit\Framework\TestCase;
use SwagMigrationConnector\Tests\Functional\ContainerTrait;

class NumberRangeServiceTest extends TestCase
{
    use ContainerTrait;

    /**
     * @return void
     */
    public function testGetNumberRanges()
    {
        $service = $this->getContainer()->get('swag_migration_connector.service.number_range_service');

        foreach ($service->getNumberRanges() as $numberRange) {
            if ($numberRange['name'] === 'articleordernumber') {
                static::assertSame('SW', $numberRange['prefix']);
            } else {
                static::assertSame('', $numberRange['prefix'], sprintf('Prefix should be empty for number range: %s', $numberRange['name']));
            }
        }
    }
}
