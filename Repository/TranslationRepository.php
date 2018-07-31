<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationApi\Repository;

class TranslationRepository extends AbstractRepository
{
    /**
     * @param int $offset
     * @param int $limit
     */
    public function fetchTranslations($offset = 0, $limit = 250)
    {
        $query = $this->getConnection()->createQueryBuilder();

        $query->from('s_core_translations', 'translation');
        $this->addTableSelection($query, 's_core_translations', 'translation');

        $query->leftJoin('translation', 's_core_shops', 'shop', 'shop.id = translation.objectlanguage');
        $query->leftJoin('shop', 's_core_locales', 'locale', 'locale.id = shop.locale_id');
        $query->addSelect('locale.locale');

        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

        return $query->execute()->fetchAll();
    }
}
