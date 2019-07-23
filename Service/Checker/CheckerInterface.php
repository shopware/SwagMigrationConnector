<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Service\Checker;

interface CheckerInterface
{
    const VALIDATION_SUCCESS = 1;
    const VALIDATION_FAILED = -1;
    const VALIDATION_WARNING = 0;

    /**
     * @param array $options
     *
     * @return array
     */
    public function validate(array $options);
}
