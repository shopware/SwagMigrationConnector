<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationApi\Service;

use Shopware\Bundle\MediaBundle\MediaService;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop\Shop;
use SwagMigrationApi\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\ParameterBag;

class ProductService extends AbstractApiService
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var MediaService
     */
    private $mediaService;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var ParameterBag
     */
    private $productMapping;

    /**
     * @var ParameterBag
     */
    private $reverseMapping;

    /**
     * @param ProductRepository $productRepository
     * @param MediaService      $mediaService
     */
    public function __construct(
        ProductRepository $productRepository,
        MediaService $mediaService,
        ModelManager $modelManager
    ) {
        $this->productRepository = $productRepository;
        $this->mediaService = $mediaService;
        $this->modelManager = $modelManager;

        /* @var ParameterBag */
        $this->productMapping = new ParameterBag();
        $this->reverseMapping = new ParameterBag();
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public function getProducts($offset = 0, $limit = 250)
    {
        $fetchedProducts = $this->productRepository->fetchProducts($offset, $limit);

        $this->buildProductMapping($fetchedProducts);

        $products = $this->mapData($fetchedProducts, [], ['product']);

        return $this->assignAssociatedData($products);
    }

    /**
     * @param array $products
     * @param array $detailIds
     * @param array $productIds
     *
     * @return array
     */
    protected function assignAssociatedData(array $products)
    {
        $categories = $this->getCategories();

        $prices = $this->getPrices();

        $productTranslations = $this->getProductTranslations();
        $variantTranslations = $this->getVariantTranslations();

        $assets = $this->getAssets();

        $options = $this->getConfiguratorOptions();

        /** @var Shop $defaultShop */
        $defaultShop = $this->modelManager->getRepository(Shop::class)->getDefault();

        // represents the main language of the migrated shop
        $locale = $defaultShop->getLocale()->getLocale();

        foreach ($products as $key => &$product) {
            $product['locale'] = $locale;

            if (isset($categories[$product['id']])) {
                $product['categories'] = $categories[$product['id']];
            }
            if (isset($prices[$product['detail']['id']])) {
                $product['prices'] = $prices[$product['detail']['id']];
            }
            if (isset($productTranslations[$product['id']])) {
                $product['translations'] = $productTranslations[$product['id']];
            }
            if (isset($variantTranslations[$product['detail']['id']])) {
                $product['detail']['translations'] = $variantTranslations[$product['detail']['id']];
            }
            if (isset($assets[$product['id']])) {
                $productAssets = $assets[$product['id']];
                $product['assets'] = $this->prepareAssets($productAssets);
            }
            if (isset($options[$product['detail']['id']])) {
                $product['configuratorOptions'] = $options[$product['detail']['id']];
            }
        }
        unset(
            $product, $categories,
            $prices, $assets, $options
        );

        $this->productMapping->replace([]);
        $this->reverseMapping->replace([]);

        return $products;
    }

    /**
     * @param array $fetchedProducts
     */
    private function buildProductMapping(array $fetchedProducts)
    {
        $reverseMapping = [];

        foreach ($fetchedProducts as $product) {
            $this->productMapping->set($product['product_detail.id'], $product['product.id']);
            if (!$reverseMapping[$product['product_detail.articleID']]) {
                $reverseMapping[$product['product_detail.articleID']] = [];
            }
            $reverseMapping[$product['product.id']][] = $product['product_detail.id'];
        }

        $this->reverseMapping->add($reverseMapping);
    }

    /**
     * @return array
     */
    private function getCategories()
    {
        $productIds = array_values(
            $this->productMapping->getIterator()->getArrayCopy()
        );
        $fetchedCategories = $this->productRepository->fetchProductCategories($productIds);

        return $this->mapData($fetchedCategories, [], ['category', 'id']);
    }

    /**
     * @return array
     */
    private function getPrices()
    {
        $variantIds = $this->productMapping->keys();
        $fetchedPrices = $this->productRepository->fetchProductPrices($variantIds);

        return $this->mapData($fetchedPrices, [], ['price']);
    }

    /**
     * @return array
     */
    private function getProductTranslations()
    {
        $productIds = array_values(
            $this->productMapping->getIterator()->getArrayCopy()
        );
        $fetchedProductTranslations = $this->productRepository->fetchProductTranslations($productIds);

        return $this->mapData($fetchedProductTranslations, [], ['translation', 'locale']);
    }

    /**
     * @return array
     */
    private function getVariantTranslations()
    {
        $variantIds = $this->productMapping->keys();
        $fetchedVariantTranslations = $this->productRepository->fetchVariantTranslations($variantIds);

        return $this->mapData($fetchedVariantTranslations, [], ['translation', 'locale']);
    }

    /**
     * @return array
     */
    private function getAssets()
    {
        $productIds = array_values(
            $this->productMapping->getIterator()->getArrayCopy()
        );
        $fetchedAssets = $this->productRepository->fetchProductAssets($productIds);

        $variantIds = $this->productMapping->keys();
        $fetchedVariantAssets = $this->productRepository->fetchVariantAssets($variantIds);

        foreach ($fetchedAssets as $productId => &$assets) {
            foreach ($assets as &$asset) {
                if ($fetchedVariantAssets[$asset['asset.id']]) {
                    $asset['children'] = $this->mapData($fetchedVariantAssets[$asset['asset.id']], [], ['asset']);
                }
            }
        }
        unset($assets, $asset);

        return $this->mapData($fetchedAssets, [], ['asset', 'children']);
    }

    /**
     * @return array
     */
    private function getConfiguratorOptions()
    {
        $variantIds = $this->productMapping->keys();
        $fetchedConfiguratorOptions = $this->productRepository->fetchProductConfiguratorOptions($variantIds);

        return $this->mapData($fetchedConfiguratorOptions, [], ['configurator', 'option']);
    }

    /**
     * @param array $assets
     *
     * @return array
     */
    private function prepareAssets(array $assets)
    {
        foreach ($assets as &$asset) {
            $asset['media']['uri'] = $this->mediaService->getUrl($asset['media']['path']);
        }
        unset($asset);

        return $assets;
    }
}
