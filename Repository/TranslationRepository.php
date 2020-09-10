<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Repository;

use Doctrine\DBAL\Connection;
use SwagMigrationConnector\Util\DefaultEntities;
use SwagMigrationConnector\Util\TotalStruct;

class TranslationRepository extends AbstractRepository
{
    /**
     * {@inheritdoc}
     */
    public function requiredForCount(array $entities)
    {
        return !in_array(DefaultEntities::TRANSLATION, $entities);
    }

    /**
     * {@inheritdoc}
     */
    public function getTotal()
    {
        $total = (int) $this->connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->from('s_core_translations')
            ->execute()
            ->fetchColumn();

        return new TotalStruct(DefaultEntities::TRANSLATION, $total);
    }

    /**
     * @param int $offset
     * @param int $limit
     */
    public function fetch($offset = 0, $limit = 250)
    {
        $ids = $this->fetchIdentifiers('s_core_translations', $offset, $limit);

        $query = $this->connection->createQueryBuilder();

        $query->from('s_core_translations', 'translation');
        $this->addTableSelection($query, 's_core_translations', 'translation');

        $query->innerJoin('translation', 's_core_shops', 'shop', 'shop.id = translation.objectlanguage');
        $query->leftJoin('shop', 's_core_locales', 'locale', 'locale.id = shop.locale_id');
        $query->addSelect('REPLACE(locale.locale, "_", "-") as locale');

        $query->leftJoin('translation', 's_articles_supplier', 'manufacturer', 'translation.objecttype = "supplier" AND translation.objectkey = manufacturer.id');
        $query->addSelect('manufacturer.name');

        $query->leftJoin('translation', 's_articles_details', 'variant', 'translation.objecttype = "variant" AND translation.objectkey = variant.id');
        $query->addSelect('variant.ordernumber');

        $query->where('translation.id IN (:ids)');
        $query->setParameter('ids', $ids, Connection::PARAM_STR_ARRAY);

        $query->addOrderBy('translation.id');

        return $query->execute()->fetchAll();
    }
}
