<?php

namespace JhFlexiTimeTest\Controller;

use JhFlexiTime\Controller\BookingAdminController;
use JhFlexiTime\Controller\BookingController;

use JhFlexiTime\DateTime\DateTime;
use JhFlexiTime\Entity\UserSettings;
use JhFlexiTime\Repository\UserSettingsRepositoryInterface;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use JhFlexiTimeTest\Util\ServiceManagerFactory;
use JhFlexiTime\Entity\Booking;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Class BookingAdminControllerTest
 * @package JhFlexiTimeTest\Controller
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class BookingAdminControllerTest extends AbstractHttpControllerTestCase
{

    protected $controller;
    protected $routeMatch;
    protected $event;
    protected $request;
    protected $response;
    protected $user;
    protected $bookingService;
    protected $timeCalculatorService;

    /**
     * @var UserSettingsRepositoryInterface
     */
    protected $userSettingsRepository;

    public function setUp()
    {
        $this->userSettingsRepository
            = $this->getMock('JhFlexiTime\Repository\UserSettingsRepositoryInterface');

        $this->controller = new BookingAdminController();

        $this->request      = new Request();
        $this->routeMatch   = new RouteMatch([]);
        $this->event        = new MvcEvent();

        $serviceManager     = ServiceManagerFactory::getServiceManager();
        $config             = $serviceManager->get('Config');
        $routerConfig       = isset($config['router']) ? $config['router'] : [];
        $router             = HttpRouter::factory($routerConfig);
        $this->event->setRouter($router);
        $this->event->setRouteMatch($this->routeMatch);
        $this->controller->setEvent($this->event);
    }


    public function testGetListCanBeAccessed()
    {
        $this->routeMatch->setParam('action', 'index');

        $result   = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNull($result);
    }
}
