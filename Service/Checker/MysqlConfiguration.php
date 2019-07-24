<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Service\Checker;

use Doctrine\DBAL\Connection;
use Shopware_Components_Snippet_Manager as SnippetManager;

class MysqlConfiguration implements CheckerInterface
{
    /**
     * @var \Enlight_Components_Snippet_Namespace
     */
    private $namespace;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(SnippetManager $manager, Connection $connection)
    {
        $this->namespace = $manager->getNamespace('backend/swag_migration_connector/main');
        $this->connection = $connection;
    }

    public function validate(array $options)
    {
        $config = $options['config'];
        $min = $options['min'];
        $value = $this->connection->fetchColumn('SELECT @@' . $config);

        if ($value >= $min || $value === -1) {
            $successMessage = $this->namespace->get('check/mysql_config_success');

            return [
                'validation' => CheckerInterface::VALIDATION_SUCCESS,
                'message' => sprintf(
                    $successMessage,
                    $config,
                    $min,
                    $value
                ),
            ];
        }

        $failMessage = $this->namespace->get('check/mysql_config_failure');

        return [
            'validation' => CheckerInterface::VALIDATION_FAILED,
            'message' => sprintf(
                $failMessage,
                $config,
                $min,
                $value
            ),
        ];
    }
}
