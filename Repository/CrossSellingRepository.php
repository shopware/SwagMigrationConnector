<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Repository;

use SwagMigrationConnector\Util\DefaultEntities;
use SwagMigrationConnector\Util\TotalStruct;

class CrossSellingRepository extends AbstractRepository
{
    /**
     * {@inheritdoc}
     */
    public function requiredForCount(array $entities)
    {
        return !in_array(DefaultEntities::CROSS_SELLING, $entities);
    }

    /**
     * {@inheritdoc}
     */
    public function getTotal()
    {
        $sql = <<<SQL
SELECT
    COUNT(*)
FROM
    (
        SELECT 'accessory' AS type, accessory.* FROM s_articles_relationships AS accessory
        UNION
        SELECT 'similar' AS type, similar.* FROM s_articles_similar AS similar
    ) AS result
SQL;

        $total = (int) $this->connection->executeQuery($sql)->fetchColumn();

        return new TotalStruct(DefaultEntities::CROSS_SELLING, $total);
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($offset = 0, $limit = 250)
    {
        $sql = <<<SQL
SELECT * FROM (
    SELECT
           :accessory AS type,
           acce.*
    FROM s_articles_relationships AS acce
    UNION
    SELECT
           :similar AS type,
           similar.*
    FROM s_articles_similar AS similar
) cross_selling
ORDER BY cross_selling.type, cross_selling.articleID LIMIT :limit OFFSET :offset
SQL;

        $statement = $this->connection->prepare($sql);
        $statement->bindValue('accessory', DefaultEntities::CROSS_SELLING_ACCESSORY, \PDO::PARAM_STR);
        $statement->bindValue('similar', DefaultEntities::CROSS_SELLING_SIMILAR, \PDO::PARAM_STR);
        $statement->bindValue('limit', $limit, \PDO::PARAM_INT);
        $statement->bindValue('offset', $offset, \PDO::PARAM_INT);
        $statement->setFetchMode(\PDO::FETCH_ASSOC);
        $statement->execute();

        return $statement->fetchAll();
    }
}
