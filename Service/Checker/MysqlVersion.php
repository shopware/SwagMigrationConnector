<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Service\Checker;

use Doctrine\DBAL\Connection;
use Shopware_Components_Snippet_Manager as SnippetManager;

class MysqlVersion implements CheckerInterface
{
    /**
     * @var \Enlight_Components_Snippet_Namespace
     */
    private $namespace;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection, SnippetManager $manager)
    {
        $this->namespace = $manager->getNamespace('backend/swag_migration_connector/main');
        $this->connection = $connection;
    }

    public function validate(array $options)
    {
        $version = $this->connection->fetchColumn('SELECT VERSION()');

        $minMysqlVersion = $options['minVersion']['mysql'];

        $successMessage = $this->namespace->get('check/mysqlversion_success', 'Min MySQL Version: %s, your version %s');
        $failMessage = $this->namespace->get('check/mysqlversion_failure', 'Min MySQL Version %s, your version %s');

        if (\stripos($version, 'maria')) {
            $minMysqlVersion = $options['minVersion']['maria'];
            $successMessage = \str_replace('MySQL', 'MariaDB', $successMessage);
            $failMessage = \str_replace('MySQL', 'MariaDB', $failMessage);
        }

        $validVersion = \version_compare($version, $minMysqlVersion) >= 0;

        if ($validVersion) {
            return [
                'validation' => CheckerInterface::VALIDATION_SUCCESS,
                'message' => \sprintf(
                    $successMessage,
                    $minMysqlVersion,
                    $version
                ),
            ];
        }

        return [
            'validation' => CheckerInterface::VALIDATION_FAILED,
            'message' => \sprintf(
                $failMessage,
                $minMysqlVersion,
                $version
            ),
        ];
    }
}
