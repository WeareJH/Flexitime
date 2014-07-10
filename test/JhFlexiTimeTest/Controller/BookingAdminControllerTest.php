<?php

namespace JhFlexiTimeTest\Controller;

use JhFlexiTime\Controller\BookingAdminController;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use JhFlexiTimeTest\Util\ServiceManagerFactory;
use JhFlexiTime\Entity\Booking;
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

    public function setUp()
    {

        $this->controller = new BookingAdminController(
            $this->getBookingService(),
            $this->getTimeCalculatorService(),
            $this->getUserRepositoryMock()
        );

        $this->request      = new Request();
        $this->routeMatch   = new RouteMatch(['controller' => 'booking-admin-controller']);
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

        $this->routeMatch->setParam('action', 'users');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $result);
        $this->assertTrue(isset($result->users));
        $this->assertTrue(isset($result->images));

        $expectedImages = [];
        foreach ($users as $user) {
            $expectedImages[$user->getEmail()] = 'http://www.gravatar.com/avatar/' . md5($user->getEmail()) . '/?s=40&d=mm&r=r';
        }

        $this->assertEquals($expectedImages, $result->images);
        $this->assertEquals($users, $result->users);
    }

    public function testViewActionReturnsEmptyResponseWhenNotXmlHttp()
    {
        $this->routeMatch->setParam('action', 'view');
        $result   = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
        $this->assertEmpty($result->getVariables()->getArrayCopy());
    }

    public function testViewActionReturnsJsonModelWhenAcceptIsJsonAndReturnsErrorIfUserIdNotPresent()
    {
        $headers = $this->request->getHeaders();
        $headers->addHeaderLine('Accept', 'application/json');
        $this->routeMatch->setParam('action', 'view');

        $result   = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $result);
        $this->assertSame(
            ['success' => false, 'message' => 'User does not exist'],
            $result->getVariables()->getArrayCopy()
        );
    }

    public function testViewActionReturnsUsersRecordsWhenAcceptIsJson()
    {
        $headers = $this->request->getHeaders();
        $headers->addHeaderLine('Accept', 'application/json');
        $this->routeMatch->setParam('action', 'view');
        $this->routeMatch->setParam('id', 2);
        $date = new \DateTime("25 March 2014");
        $booking = $this->getMockBooking();
        $user = new User;
        $user->setEmail('jack.bauer@ctu.com');
        $this->controller->setDate($date);

        $this->userRepository
             ->expects($this->once())
             ->method('find')
             ->with(2)
             ->will($this->returnValue($user));

        $this->bookingService
             ->expects($this->once())
             ->method('getUserBookingsForMonth')
             ->with($user, $date)
             ->will($this->returnValue([$booking]));

        $this->bookingService
             ->expects($this->once())
             ->method('getPagination')
             ->with($date)
             ->will($this->returnValue([]));

        $this->timeCalculatorService
             ->expects($this->once())
             ->method('getTotals')
             ->with($user, $date)
             ->will($this->returnValue(['some-total' => 20]));

        $result   = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $result);

        $expectedTime = [
            'records' => [$booking],
            'totals'  => ['some-total' => 20],
            'user'    => $user,
        ];
        $this->assertEquals($expectedTime, $result->getVariable('bookings'));
        $this->assertEquals([], $result->getVariable('pagination'));
        $this->assertSame($date, $result->getVariable('date'));
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

    public function getMockBooking()
    {
        $booking = new Booking();

        return $booking
            ->setDate(new \DateTime("25 March 2014"))
            ->setUser($this->user)
            ->setNotes("ALL THE TIME");
    }
}