<?php

namespace SwagMigrationConnector\Service\Checker;

use Doctrine\DBAL\Connection;
use Shopware_Components_Snippet_Manager as SnippetManager;

class LicenseCheck implements CheckerInterface
{
    /**
     * @var \Enlight_Components_Snippet_Namespace
     */
    private $namespace;

    /**
     * @var string
     */
    private $endpoint;

    /**
     * @var string
     */
    private $shopwareVersion;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(SnippetManager $manager, $endpoint, $shopwareVersion, Connection $connection)
    {
        $this->namespace = $manager->getNamespace('backend/swag_migration_connector/main');
        $this->endpoint = $endpoint;
        $this->shopwareVersion = $shopwareVersion;
        $this->connection = $connection;
    }

    public function validate(array $options)
    {
        $licenseKeys = $options['keys'];

        if (empty($licenseKeys)) {
            return [
                'validation' => CheckerInterface::VALIDATION_WARNING,
                'message' => 'License check requested but no license key provided',
            ];
        }
        $licenseData = $this->getLicenseData($licenseKeys);

        if (empty($licenseData)) {
            return [
                'validation' => CheckerInterface::VALIDATION_SUCCESS,
                'message' => $this->namespace->get('check/license_nolicense'),
            ];
        }

        $url = $this->endpoint . '/licenseupgrades/permission';
        $client = new \Zend_Http_Client(
            $url, [
                'timeout' => 15,
            ]
        );

        foreach ($licenseData as $licenseDatum) {
            $client->setParameterPost('domain', $licenseDatum['host']);
            $client->setParameterPost('licenseKey', $licenseDatum['license']);
            $client->setParameterPost('version', $this->shopwareVersion);

            try {
                $response = $client->request(\Zend_Http_Client::POST);
            } catch (\Zend_Http_Client_Exception $e) {
                // Do not show exception to user if request times out
                return null;
            }

            try {
                $body = $response->getBody();
                if ($body != '') {
                    $json = \Zend_Json::decode($body, true);
                } else {
                    $json = null;
                }
            } catch (\Exception $e) {
                // Do not show exception to user if SBP returns an error
                return null;
            }

            if ($json === true) {
                return [
                    'validation' => CheckerInterface::VALIDATION_SUCCESS,
                    'message' => $this->namespace->get('check/license_success'),
                ];
            }
        }

        return [
            'validation' => CheckerInterface::VALIDATION_FAILED,
            'message' => $this->namespace->get('check/license_failure'),
        ];
    }

    /**
     * Returns existing license data for the provided keys
     *
     * @param array $licenseKeys
     *
     * @return array
     */
    private function getLicenseData($licenseKeys)
    {
        /** @var \Doctrine\DBAL\Query\QueryBuilder $queryBuilder */
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->select(['host', 'license'])
            ->from('s_core_licenses', 'license')
            ->where('license.active = 1')
            ->andWhere('license.module IN (:modules)')
            ->setParameter(':modules', $licenseKeys, Connection::PARAM_INT_ARRAY);

        return $queryBuilder->execute()->fetchAll();
    }
}
