<?php

namespace SwagMigrationConnector\Service\Checker;

use Shopware_Components_Snippet_Manager as SnippetManager;

class PhpVersion implements CheckerInterface
{
    /**
     * @var \Enlight_Components_Snippet_Namespace
     */
    private $namespace;

    public function __construct(SnippetManager $manager)
    {
        $this->namespace = $manager->getNamespace('backend/swag_migration_connector/main');
    }

    public function validate(array $options)
    {
        $minPHPVersion = $options['minVersion'];

        $validVersion = (version_compare(PHP_VERSION, $minPHPVersion) >= 0);

        $successMessage = $this->namespace->get('check/phpversion_success', 'Min PHP Version: %s, your version %s');
        $failMessage = $this->namespace->get('check/phpversion_failure', 'Min PHP Version: %s, your version %s');

        if ($validVersion) {
            return [
                'validation' => CheckerInterface::VALIDATION_SUCCESS,
                'message' => sprintf(
                    $successMessage,
                    $minPHPVersion,
                    PHP_VERSION
                ),
            ];
        }

        return [
            'validation' => CheckerInterface::VALIDATION_FAILED,
            'message' => sprintf(
                $failMessage,
                $minPHPVersion,
                PHP_VERSION
            ),
        ];
    }
}
