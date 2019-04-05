<?php

namespace SwagMigrationAssistant\Service\Checker;

interface CheckerInterface
{
    const VALIDATION_SUCCESS = 1;
    const VALIDATION_FAILED = -1;
    const VALIDATION_WARNING = 0;

    /**
     * @param array $options
     * @return array
     */
    public function validate(array $options);
}
