<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationAssistant\Repository;

use Doctrine\DBAL\Connection;

class ConfiguratorOptionRepository extends AbstractRepository
{
    /**
     * {@inheritdoc}
     */
    public function fetch($offset = 0, $limit = 250)
    {
        $ids = $this->fetchIdentifiers('s_article_configurator_options', $offset, $limit);

        $query = $this->connection->createQueryBuilder();

        $query->from('s_article_configurator_options', 'configuratorOption');
        $this->addTableSelection($query, 's_article_configurator_options', 'configuratorOption');

        $query->leftJoin('configuratorOption', 's_media', 'configuratorOption_media', 'configuratorOption.media_id = configuratorOption_media.id');
        $this->addTableSelection($query, 's_media', 'configuratorOption_media');

        $query->leftJoin('configuratorOption_media', 's_media_attributes', 'configuratorOption_media_attributes', 'configuratorOption_media.id = configuratorOption_media_attributes.mediaID');
        $this->addTableSelection($query, 's_media_attributes', 'configuratorOption_media_attributes');

        $query->leftJoin('configuratorOption_media', 's_media_album', 'configuratorOption_media_album', 'configuratorOption_media.albumID = configuratorOption_media_album.id');
        $this->addTableSelection($query, 's_media_album', 'configuratorOption_media_album');

        $query->leftJoin('configuratorOption_media_album', 's_media_album_settings', 'configuratorOption_media_album_settings', 'configuratorOption_media_album.id = configuratorOption_media_album_settings.albumID');
        $this->addTableSelection($query, 's_media_album_settings', 'configuratorOption_media_album_settings');

        $query->leftJoin('configuratorOption', 's_article_configurator_groups', 'configuratorOption_group', 'configuratorOption.group_id = configuratorOption_group.id');
        $this->addTableSelection($query, 's_article_configurator_groups', 'configuratorOption_group');

        $query->where('configuratorOption.id IN (:ids)');
        $query->setParameter('ids', $ids, Connection::PARAM_STR_ARRAY);

        $query->addOrderBy('configuratorOption.id');

        return $query->execute()->fetchAll();
    }
}