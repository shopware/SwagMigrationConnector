<?php

namespace SwagMigrationAssistant\Service;

use SwagMigrationAssistant\Service\Checker\LicenseCheck;
use SwagMigrationAssistant\Service\Checker\MysqlConfiguration;
use SwagMigrationAssistant\Service\Checker\MysqlVersion;
use SwagMigrationAssistant\Service\Checker\PhpConfiguration;
use SwagMigrationAssistant\Service\Checker\PhpExtensions;
use SwagMigrationAssistant\Service\Checker\PhpVersion;

class RequirementsValidatorService
{
    const CHECKS = [
        [
            'type' => PhpVersion::class,
            'minVersion' => '7.2'
        ],
        [
            'type' => MysqlVersion::class,
            'minVersion' => [
                'mysql' => '5.7',
                'maria' => '10.3',
            ]
        ],
        [
            'type' => PhpExtensions::class,
            'extensions' => [
                'dom',
                'fileinfo',
                'gd',
                'iconv',
                'json',
                'libxml',
                'openssl',
                'libxml',
                'mbstring',
                'pcre',
                'pdo',
                'pdo_mysql',
                'phar',
                'simplexml',
                'xml',
                'zip',
                'zlib',
            ]
        ],
        [
            'type' => PhpConfiguration::class,
            'config' => 'max_execution_time',
            'min' => 30
        ],
        [
            'type' => PhpConfiguration::class,
            'config' => 'memory_limit',
            'min' => '512M'
        ],
        [
            'type' => LicenseCheck::class,
            'keys' => [
                'SwagEnterprisePremium',
                'SwagEnterpriseCluster',
                'SwagEnterprise',
                'SwagCommercial',
                'SwagCore',
            ]
        ]
    ];
    /**
     * @var array
     */
    private $validators;

    public function __construct(array $validators)
    {
        $this->validators = $validators;
    }

    public function validate()
    {
        $result = [];

        foreach (self::CHECKS as $check) {
            foreach ($this->validators as $validator) {
                if ($validator instanceof $check['type']) {
                    $result[] = $validator->validate($check);
                }
            }
        }

        return $result;
    }
}
