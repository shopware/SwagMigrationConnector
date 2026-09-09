<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Probe for the JUnit report check — DO NOT MERGE.
 */
class ProbeKillerTest extends TestCase
{
    /**
     * @return void
     */
    public function testExitZeroKillsThePhpunitProcess()
    {
        exit(0);
    }
}
