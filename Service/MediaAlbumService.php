<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\Column;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop\Shop;

class MediaAlbumService extends AbstractApiService
{
    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(ModelManager $modelManager)
    {
        $this->modelManager = $modelManager;
        $this->connection = $this->modelManager->getConnection();
    }

    /**
     * @return array
     */
    public function getAlbums()
    {
        $fetchedAlbums = $this->fetchAlbums();

        $mediaAlbums = $this->mapData(
            $fetchedAlbums, [], ['album']
        );

        $resultSet = $this->prepareMediaAlbums($mediaAlbums);

        return $this->cleanupResultSet($resultSet);
    }

    /**
     * @return array
     */
    private function fetchAlbums()
    {
        $query = $this->connection->createQueryBuilder();

        $query->from('s_media_album', 'album');
        $this->addTableSelection($query, 's_media_album', 'album');

        $query->leftJoin('album', 's_media_album_settings', 'setting', 'setting.albumID = album.id');
        $this->addTableSelection($query, 's_media_album_settings', 'setting');

        $query->orderBy('parentID');

        return $query->execute()->fetchAll();
    }

    /**
     * @return array
     */
    private function prepareMediaAlbums(array $mediaAlbums)
    {
        /** @var Shop $defaultShop */
        $defaultShop = $this->modelManager->getRepository(Shop::class)->getDefault();
        // represents the main language of the migrated shop
        $defaultLocale = str_replace('_', '-', $defaultShop->getLocale()->getLocale());

        $returnAlbums = [];
        foreach ($mediaAlbums as $key => $mediaAlbum) {
            if ($mediaAlbum['parentID'] !== null) {
                continue;
            }

            $mediaAlbum['_locale'] = $defaultLocale;
            $returnAlbums[] = [$mediaAlbum];
            unset($mediaAlbums[$key]);

            $childAlbums = $this->getChildAlbums($mediaAlbums, $mediaAlbum['id'], $defaultLocale);

            if (!empty($childAlbums)) {
                $returnAlbums[] = $childAlbums;
            }
        }
        unset($mediaAlbum);

        return array_merge(...$returnAlbums);
    }

    /**
     * @param array  $mediaAlbums
     * @param string $id
     * @param string $locale
     *
     * @return array
     */
    private function getChildAlbums(array &$mediaAlbums, $id, $locale)
    {
        $returnAlbums = [];
        foreach ($mediaAlbums as $key => $mediaAlbum) {
            if ($mediaAlbum['parentID'] !== $id) {
                continue;
            }

            $mediaAlbum['_locale'] = $locale;
            $returnAlbums[] = [$mediaAlbum];
            unset($mediaAlbums[$key]);

            $childAlbums = $this->getChildAlbums($mediaAlbums, $mediaAlbum['id'], $locale);

            if (!empty($childAlbums)) {
                $returnAlbums[] = $childAlbums;
            }
        }

        return array_merge(...$returnAlbums);
    }

    /**
     * @param QueryBuilder $query
     * @param $table
     * @param $tableAlias
     */
    private function addTableSelection(QueryBuilder $query, $table, $tableAlias)
    {
        $columns = $this->connection->getSchemaManager()->listTableColumns($table);

        /** @var Column $column */
        foreach ($columns as $column) {
            $selection = str_replace(
                ['#tableAlias#', '#column#'],
                [$tableAlias, $column->getName()],
                '`#tableAlias#`.`#column#` as `#tableAlias#.#column#`'
            );

            $query->addSelect($selection);
        }
    }
}
