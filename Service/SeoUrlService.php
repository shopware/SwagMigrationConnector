<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Service;

use SwagMigrationConnector\Repository\ApiRepositoryInterface;
use SwagMigrationConnector\Repository\SeoUrlRepository;

class SeoUrlService extends AbstractApiService
{
    /**
     * @var SeoUrlRepository
     */
    private $seoUrlRepository;

    public function __construct(
        ApiRepositoryInterface $seoUrlRepository
    ) {
        $this->seoUrlRepository = $seoUrlRepository;
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public function getSeoUrls($offset = 0, $limit = 250)
    {
        $fetchedSeoUrls = $this->seoUrlRepository->fetch($offset, $limit);
        $seoUrls = $this->mapData($fetchedSeoUrls, [], ['url']);
        $seoUrls = $this->extractTypeInformation($seoUrls);

        foreach ($seoUrls as &$seoUrl) {
            $seoUrl['_locale'] = \str_replace('_', '-', $seoUrl['_locale']);
        }

        return $this->cleanupResultSet($seoUrls);
    }

    private function extractTypeInformation(array $seoUrls)
    {
        foreach ($seoUrls as &$seoUrl) {
            \parse_str($seoUrl['org_path'], $output);
            $seoUrl['type'] = $output['sViewport'];
            if ($output['sViewport'] === 'cat') {
                $seoUrl['typeId'] = $output['sCategory'];
            }
            if ($output['sViewport'] === 'detail') {
                $seoUrl['typeId'] = $output['sArticle'];
            }
        }
        unset($seoUrl);

        return $seoUrls;
    }
}
