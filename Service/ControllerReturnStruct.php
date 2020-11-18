<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Service;

class ControllerReturnStruct implements \JsonSerializable
{
    /**
     * @var array
     */
    public $data;

    /**
     * @var bool
     */
    public $isLastRequest;

    /**
     * @var bool
     */
    public $success;

    /**
     * @param bool $isLastRequest
     * @param bool $success
     */
    public function __construct(array $data, $isLastRequest = false, $success = true)
    {
        $this->data = $data;
        $this->isLastRequest = $isLastRequest;
        $this->success = $success;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return \get_object_vars($this);
    }
}
