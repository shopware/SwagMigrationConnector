<?php

namespace SwagMigrationConnector\Service\Checker;

use Shopware_Components_Snippet_Manager as SnippetManager;

class PhpExtensions implements CheckerInterface
{
    /**
     * @var \Enlight_Components_Snippet_Namespace
     */
    private $namespace;

    public function __construct(SnippetManager $manager)
    {
        $this->namespace = $manager->getNamespace('backend/swag_update_check/main');
    }

    public function validate(array $options)
    {
        $extensions = $options['extensions'];
        $passed = [];


        foreach ($extensions as $extension) {
            if (extension_loaded($extension)) {
                $passed[] = $extension;
            }
        }

        if (count($passed) !== count($extensions)) {
            $failMessage = $this->namespace->get('check/missing_php_ext_failure');

            return [
                'validation' => CheckerInterface::VALIDATION_FAILED,
                'message' => sprintf(
                    $failMessage,
                    implode(',', array_diff($extensions, $passed))
                ),
            ];
        }

        return [
            'validation' => CheckerInterface::VALIDATION_SUCCESS,
            'message' => $this->namespace->get('check/missing_php_ext_success'),
        ];
    }
}
