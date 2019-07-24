<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Service\Checker;

use Shopware\Components\MemoryLimit;
use Shopware_Components_Snippet_Manager as SnippetManager;

class PhpConfiguration implements CheckerInterface
{
    /**
     * @var \Enlight_Components_Snippet_Namespace
     */
    private $namespace;

    public function __construct(SnippetManager $manager)
    {
        $this->namespace = $manager->getNamespace('backend/swag_migration_connector/main');
    }

    public function validate(array $options)
    {
        $config = $options['config'];
        $min = $options['min'];
        $value = ini_get($config);

        if ($config === 'memory_limit') {
            $min = MemoryLimit::convertToBytes($min);
            $value = MemoryLimit::convertToBytes($value);
        }

        if ($value >= $min || $value === -1) {
            $successMessage = $this->namespace->get('check/php_config_success');

            return [
                'validation' => CheckerInterface::VALIDATION_SUCCESS,
                'message' => sprintf(
                    $successMessage,
                    $config,
                    $this->formatValue($min, $options),
                    $this->formatValue($value, $options)
                ),
            ];
        }

        $failMessage = $this->namespace->get('check/php_config_failure');

        return [
            'validation' => CheckerInterface::VALIDATION_FAILED,
            'message' => sprintf(
                $failMessage,
                $config,
                $this->formatValue($min, $options),
                $this->formatValue($value, $options)
            ),
        ];
    }

    private function formatValue($value, $options)
    {
        if ($options['config'] === 'memory_limit') {
            return $this->formatBytes($value);
        }

        return $value;
    }

    private function formatBytes($size)
    {
        $unit = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        return @round($size / pow(1024, $i = floor(log($size, 1024))), 2) . ' ' . $unit[$i];
    }
}
