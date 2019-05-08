<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop\Shop;
use SwagMigrationConnector\Repository\AttributeRepository;

class AttributeService
{
    /**
     * @var AttributeRepository
     */
    private $attributeRepository;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(AttributeRepository $attributeRepository, ModelManager $manager)
    {
        $this->attributeRepository = $attributeRepository;
        $this->modelManager = $manager;
        $this->connection = $manager->getConnection();
    }

    /**
     * @param string $table
     *
     * @return array
     */
    public function getAttributeConfiguration($table)
    {
        $columns = $this->getTableColumns($table);
        $foreignKeys = $this->getTableForeignKeys($table);
        $columns = $this->cleanupColumns($columns, $foreignKeys);
        
        $attributeConfiguration = $this->attributeRepository->getAttributeConfiguration($table);
        $attributeConfigTranslations = $this->attributeRepository->getAttibuteConfigurationTranslations($table);

        /** @var Shop $defaultShop */
        $defaultShop = $this->modelManager->getRepository(Shop::class)->getDefault();

        // represents the main language of the migrated shop
        $locale = str_replace('_', '-', $defaultShop->getLocale()->getLocale());

        // extract field translations and add them to config
        foreach ($attributeConfigTranslations as $translation) {
            $name = str_replace($table . '_', '', $translation['name']);
            $field = substr($translation['name'], strrpos($translation['name'], '_') + 1);
            $column = substr($name, 0, strrpos($name, '_'));

            if (!isset($attributeConfiguration[$column]['translations'][$field])) {
                $attributeConfiguration[$column]['translations'][$field] = [];
            }
            $attributeConfiguration[$column]['translations'][$field][$translation['locale']] = $translation['value'];
        }
        
        $resultSet = [];

        /** @var Column $column */
        foreach ($columns as $column) {
            $columnData = [
                'name'          => $column->getName(),
                'type'          => $column->getType()->getName(),
                '_locale'       => $locale,
                'configuration' => null
            ];

            if (isset($attributeConfiguration[$column->getName()])) {
                $columnData['configuration'] =  $attributeConfiguration[$column->getName()];
            }
            $resultSet[] = $columnData;
        }

        return $resultSet;
    }

    /**
     * @param string $table
     * 
     * @return Column[]
     */
    private function getTableColumns($table)
    {
        return $this->connection->getSchemaManager()->listTableColumns($table);
    }

    /**
     * @param $table
     * 
     * @return ForeignKeyConstraint[]
     */
    private function getTableForeignKeys($table)
    {
        return $this->connection->getSchemaManager()->listTableForeignKeys($table);
    }

    /**
     * Filter autoincrement and FK constraint columns
     *
     * @param Column[] $columns
     * @param ForeignKeyConstraint[] $foreignKeys
     *
     * @return Column[]
     */
    private function cleanupColumns(array $columns, array $foreignKeys)
    {
        $result = [];
        $fks = [];

        foreach ($foreignKeys as $foreignKey) {
            $fks[] = $foreignKey->getLocalColumns();
        }
        $fks = array_merge(...$fks);

        foreach ($columns as $column) {
            if ($column->getAutoincrement() === true || in_array($column->getName(), $fks)) {
                continue;
            }
            $result[] = $column;
        }

        return $result;
    }
}
