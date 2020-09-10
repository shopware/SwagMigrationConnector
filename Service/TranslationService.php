<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Service;

use SwagMigrationConnector\Repository\TranslationRepository;

class TranslationService extends AbstractApiService
{
    /**
     * @var TranslationRepository
     */
    private $translationRepository;

    public function __construct(TranslationRepository $translationRepository)
    {
        $this->translationRepository = $translationRepository;
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public function getTranslations($offset = 0, $limit = 250)
    {
        $fetchedTranslations = $this->translationRepository->fetch($offset, $limit);

        $resultSet = $this->mapData(
            $fetchedTranslations, [], ['translation', 'locale', 'name', 'ordernumber']
        );

        return $this->cleanupResultSet($resultSet);
    }
}
