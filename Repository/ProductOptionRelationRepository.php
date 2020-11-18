<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Repository;

use SwagMigrationConnector\Util\DefaultEntities;
use SwagMigrationConnector\Util\TotalStruct;

class ProductOptionRelationRepository extends AbstractRepository
{
    public function requiredForCount(array $entities)
    {
        return !\in_array(DefaultEntities::PRODUCT_OPTION_RELATION, $entities);
    }

    public function getTotal()
    {
        $total = (int) $this->connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->from('s_article_configurator_options', 'po')
            ->leftJoin('po', 's_article_configurator_option_relations', 'por', 'por.option_id = po.id')
            ->innerJoin('por', 's_articles_details', 'product_detail', 'por.article_id = product_detail.id')
            ->execute()
            ->fetchColumn();

        return new TotalStruct(DefaultEntities::PRODUCT_OPTION_RELATION, $total);
    }

    public function fetch($offset = 0, $limit = 250)
    {
        $query = $this->connection->createQueryBuilder();

        $query->from('s_article_configurator_options', 'configurator_option');
        $query->addSelect('"option" AS type');
        $query->addSelect('MD5(CONCAT(configurator_option.id, product_detail.articleID)) AS identifier');
        $this->addTableSelection($query, 's_article_configurator_options', 'configurator_option');

        $query->leftJoin('configurator_option', 's_article_configurator_option_relations', 'option_relation', 'option_relation.option_id = configurator_option.id');
        $query->innerJoin('option_relation', 's_articles_details', 'product_detail', 'product_detail.id = option_relation.article_id');
        $query->addSelect('product_detail.articleID as productId');

        $query->leftJoin('configurator_option', 's_article_configurator_groups', 'configurator_option_group', 'configurator_option.group_id = configurator_option_group.id');
        $this->addTableSelection($query, 's_article_configurator_groups', 'configurator_option_group');

        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

        return $query->execute()->fetchAll();
    }
}
