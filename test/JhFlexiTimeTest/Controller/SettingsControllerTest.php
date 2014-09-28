<?php

namespace JhFlexiTimeTest\Controller;

use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use JhFlexiTimeTest\Util\ServiceManagerFactory;
use JhFlexiTime\Controller\SettingsController;
use JhFlexiTime\Options\BookingOptions;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Class SettingsControllerTest
 * @package JhFlexiTimeTest\Controller
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class SettingsControllerTest extends AbstractHttpControllerTestCase
{
    protected $controller;
    protected $routeMatch;
    protected $event;
    protected $request;
    protected $response;
    protected $user;

    public function setUp()
    {
        $options = new BookingOptions([
            'min_start_time'    => '07:00',
            'max_start_time'    => '10:00',
            'min_end_time'      => '16:00',
            'max_end_time'      => '17:00',
        ]);
        $this->controller = new SettingsController($options);

        $this->request      = new Request();
        $this->routeMatch   = new RouteMatch(['controller' => 'settings']);
        $this->event        = new MvcEvent();

        $serviceManager     = ServiceManagerFactory::getServiceManager();
        $config             = $serviceManager->get('Config');
        $routerConfig       = isset($config['router']) ? $config['router'] : [];
        $router             = HttpRouter::factory($routerConfig);
        $this->event->setRouter($router);
        $this->event->setRouteMatch($this->routeMatch);
        $this->controller->setEvent($this->event);

        $this->mockAuth();
    }

    public function mockAuth()
    {
        $ZfcUserMock = $this->getMock('ZfcUser\Entity\UserInterface');

        $ZfcUserMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue('1'));

        $authMock = $this->getMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

        $authMock->expects($this->any())
            ->method('hasIdentity')
            -> will($this->returnValue(true));

        $authMock->expects($this->any())
            ->method('getIdentity')
            ->will($this->returnValue($ZfcUserMock));

        $this->controller->getPluginManager()
            ->setService('zfcUserAuthentication', $authMock);

        $this->user = $ZfcUserMock;
    }

    public function testGetActionCanBeAccessed()
    {
        $this->routeMatch->setParam('action', 'get');
        $result   = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $result);
        $this->assertTrue(isset($result->success));
        $this->assertTrue(isset($result->settings));

        $expectedSettings = [
            'min_start_time'    => '07:00',
            'max_start_time'    => '10:00',
            'min_end_time'      => '16:00',
            'max_end_time'      => '17:00',
        ];
        $this->assertEquals($result->settings->toArray(), $expectedSettings);
    }
}
