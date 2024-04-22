<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Tests\Functional\Controllers\ControllerFactory;

use Enlight_Controller_Action as Controller;
use Enlight_Controller_Request_RequestTestCase as Request;
use Enlight_Controller_Response_ResponseTestCase as Response;
use Enlight_Template_Manager as TemplateManager;
use Enlight_View_Default as View;

class ControllerFactory
{
    /**
     * @template T of Controller
     *
     * @param class-string<T> $controllerName
     *
     * @return T
     */
    public static function createController($controllerName, Arguments $arguments)
    {
        $request = $arguments->getRequest();
        $response = $arguments->getResponse();
        $view = $arguments->getView();

        if ($request === null) {
            $request = new Request();
        }

        if ($response === null) {
            $response = new Response();
        }

        if ($view === null) {
            $view = new View(new TemplateManager());
        }

        foreach ($arguments->getContainerServices() as $id => $service) {
            $arguments->getContainer()->set($id, $service);
        }

        $controller = \Enlight_Class::Instance($controllerName, [$request, $response]);
        $controller->setRequest($request);
        $controller->setResponse($response);
        $controller->setView($view);
        $controller->setContainer($arguments->getContainer());

        return $controller;
    }
}
