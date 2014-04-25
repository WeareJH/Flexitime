<?php

namespace JhFlexiTimeTest\Controller;

use JhFlexiTime\Controller\BookingAdminController;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use JhFlexiTimeTest\Util\ServiceManagerFactory;
use JhFlexiTime\Controller\SettingsController;
use JhFlexiTime\Options\BookingOptions;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use ZfcUser\Entity\User;

/**
 * Class BookingAdminControllerTest
 * @package JhFlexiTimeTest\Controller
 * @author Aydin Hassan <aydin@wearejh.com>
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
    protected $userRepository;
    protected $gravatarHelper;

    public function setUp()
    {

        $this->controller = new BookingAdminController(
            $this->getBookingService(),
            $this->getTimeCalculatorService(),
            $this->getUserRepositoryMock(),
            $this->getGravatarMock()
        );

        $this->request      = new Request();
        $this->routeMatch   = new RouteMatch(array('controller' => 'booking-admin-controller'));
        $this->event        = new MvcEvent();

        $serviceManager     = ServiceManagerFactory::getServiceManager();
        $config             = $serviceManager->get('Config');
        $routerConfig       = isset($config['router']) ? $config['router'] : array();
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

    public function testUsersActionReturnsUsersAndUserImages()
    {
        $user1 = new User;
        $user1->setEmail('jack.bauer@ctu.com');
        $user2 = new User;
        $user2->setEmail('chloe.obrian@ctu.com');

        $users = [$user1, $user2];

        $this->userRepository
            ->expects($this->once())
            ->method('findAll')
            ->with(false)
            ->will($this->returnValue($users));


        foreach($users as $key => $user) {

            $index = $key * 2;
            $this->gravatarHelper
                ->expects($this->at($index))
                ->method('__invoke')
                ->with($user->getEmail(), ['img_size' => '40'])
                ->will($this->returnSelf());

            $this->gravatarHelper
                ->expects($this->at($index + 1))
                ->method('__toString')
                ->will($this->returnValue('str'));
        }




        $this->routeMatch->setParam('action', 'users');
        $result   = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

    }

    public function getBookingService()
    {
        $mock = $this->getMockBuilder('JhFlexiTime\Service\BookingService')
            ->disableOriginalConstructor()
            ->getMock();

        $this->bookingService = $mock;

        return $mock;
    }

    public function getTimeCalculatorService()
    {
        $mock = $this->getMockBuilder('JhFlexiTime\Service\TimeCalculatorService')
            ->disableOriginalConstructor()
            ->getMock();

        $this->timeCalculatorService = $mock;

        return $mock;
    }

    public function getUserRepositoryMock()
    {
        $this->userRepository = $this->getMock('JhUser\Repository\UserRepositoryInterface');
        return $this->userRepository;
    }

    public function getGravatarMock()
    {
        $this->gravatarHelper = $this->getMock('Zend\View\Helper\Gravatar');
        var_dump($this->gravatarHelper);
        return $this->gravatarHelper;
    }
}