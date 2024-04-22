<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Tests\Functional\Controllers\ControllerFactory;

use Enlight_Controller_Request_RequestHttp as Request;
use Enlight_Controller_Response_ResponseHttp as Response;
use Enlight_View_Default as View;
use Shopware\Components\DependencyInjection\Container;

class Arguments
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var Request|null
     */
    private $request;

    /**
     * @var Response|null
     */
    private $response;

    /**
     * @var View
     */
    private $view;

    /**
     * @var array<string, mixed>
     */
    private $containerServices = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $key
     * @param object $service
     *
     * @return void
     */
    public function addContainerService($key, $service)
    {
        $this->containerServices[$key] = $service;
    }

    /**
     * @return array<string, object>
     */
    public function getContainerServices()
    {
        return $this->containerServices;
    }

    /**
     * @return void
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return Request|null
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return void
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
    }

    /**
     * @return Response|null
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return View|null
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @return void
     */
    public function setView(View $view)
    {
        $this->view = $view;
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }
}
