<?php

namespace SwagMigrationConnector\Service;

class ControllerReturnStruct implements \JsonSerializable
{
    /**
     * @var array
     */
    public $data;

    /**
     * @var boolean
     */
    public $isLastRequest;

    /**
     * @var boolean
     */
    public $success;

    /**
     * @param array $data
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
        return get_object_vars($this);
    }
}