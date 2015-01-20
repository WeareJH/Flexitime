<?php

namespace JhFlexiTimeTest\Controller;

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
 * Class BookingControllerTest
 * @package JhFlexiTimeTest\Controller
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class BookingControllerTest extends AbstractHttpControllerTestCase
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

        $this->controller = new BookingController(
            $this->getBookingService(),
            $this->getTimeCalculatorService(),
            $this->getForm(),
            $this->userSettingsRepository
        );

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

    public function configureMockBookingService($method, array $params, $return)
    {
        $expects = $this->bookingService->expects($this->once())
            ->method($method)
            ->will($this->returnValue($return));

        call_user_func_array([$expects, "with"], $params);
    }

    public function configureMockTimeCalculatorService($method, array $params, $return)
    {
        $expects = $this->timeCalculatorService->expects($this->once())
            ->method($method)
            ->will($this->returnValue($return));

        call_user_func_array([$expects, "with"], $params);
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

    public function getForm()
    {
        return $this->getMock('Zend\Form\FormInterface');
    }

    public function getMockBooking()
    {
        $booking = new Booking();

        return $booking
            ->setDate(new DateTime("25 March 2014"))
            ->setUser($this->user)
            ->setNotes("ALL THE TIME");
    }

    public function testGetListCanBeAccessed()
    {
        $booking    = $this->getMockBooking();
        $date       = new DateTime("25 March 2014");
        $startDate  = new DateTime("21 April 2014");


        $this->controller->setDate($date);
        $this->configureMockBookingService('getUserBookingsForMonth', [$this->user, $date], [$booking]);
        $this->configureMockBookingService('getPagination', [$date], []);
        $this->configureMockTimeCalculatorService('getTotals', [$this->user, $startDate, $date], ['some-total', 20]);

        $this->routeMatch->setParam('action', 'list');

        $userSettings = new UserSettings;
        $userSettings->setFlexStartDate($startDate);

        $this->userSettingsRepository
            ->expects($this->once())
            ->method('findOneByUser')
            ->with($this->user)
            ->will($this->returnValue($userSettings));

        $result   = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
        $this->assertTrue(isset($result->bookings));
        $this->assertTrue(isset($result->pagination));
        $this->assertTrue(isset($result->date));

        $expectedTime = [
            'records' => [$booking],
            'totals'  => ['some-total', 20],
            'user'    => $this->user,
        ];
        $this->assertEquals($expectedTime, $result->getVariable('bookings'));
        $this->assertEquals([], $result->getVariable('pagination'));
        $this->assertSame($date, $result->getVariable('date'));
    }
}
