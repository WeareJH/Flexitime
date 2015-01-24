<?php

namespace JhFlexiTimeTest\Controller;

use JhFlexiTime\Controller\BookingRestController;

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
 * Class BookingRestControllerTest
 * @package JhFlexiTimeTest\Controller
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class BookingRestControllerTest extends AbstractHttpControllerTestCase
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

        $userRepository
            = $this->getMock('JhUser\Repository\UserRepositoryInterface');
        $this->userSettingsRepository
            = $this->getMock('JhFlexiTime\Repository\UserSettingsRepositoryInterface');

        $this->controller = new BookingRestController(
            $this->getBookingService(),
            $this->getTimeCalculatorService(),
            $userRepository,
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
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $result);
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

    public function testGetCanBeAccessed()
    {
        $id         = "123456789-5";
        $booking    = $this->getMockBooking();
        $this->bookingService->expects($this->once())
            ->method('getBookingByUserAndDate')
            ->with('5', $this->isInstanceOf('JhFlexiTime\DateTime\DateTime'))
            ->will($this->returnValue($booking));

        $this->routeMatch->setParam('id', $id);

        $result   = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $result);
        $this->assertSame($booking, $result->getVariable('booking'));
    }

    public function testCreateCanBeAccessed()
    {
        $startDate  = new DateTime("21 April 2014");
        $booking    = $this->getMockBooking();
        $this->configureMockBookingService('create', [], $booking);
        $this->configureMockTimeCalculatorService(
            'getTotals',
            [$this->user, $startDate, $booking->getDate()],
            ['some-total', 20]
        );

        $this->configureMockTimeCalculatorService(
            'getWeekTotals',
            [$this->user, $booking->getDate()],
            ['some-total', 20]
        );

        $userSettings = new UserSettings;
        $userSettings->setFlexStartDate($startDate);

        $this->userSettingsRepository
            ->expects($this->once())
            ->method('findOneByUser')
            ->with($this->user)
            ->will($this->returnValue($userSettings));

        $this->request->setMethod('POST');
        $this->request->getPost()->set('date', '25-03-2014');
        $this->request->getPost()->set('startTime', '09:00');

        $result   = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $result);
        $this->assertTrue(isset($result->success));
        $this->assertTrue(isset($result->booking));
        $this->assertTrue(isset($result->monthTotals));

        $this->assertSame($booking, $result->booking);
        $this->assertTrue($result->success);
        $this->assertEquals($result->monthTotals, ['some-total', 20]);
    }

    public function testCreateCanBeAccessedButFail()
    {
        $return = ['messages' => ['data' => 'INVALID YO']];
        $data = ['date' => '25-03-2014', 'startTime' => '09:00'];
        $this->configureMockBookingService('create', [$data], $return);

        $this->request->setMethod('POST');
        $this->request->getPost()->set('date', '25-03-2014');
        $this->request->getPost()->set('startTime', '09:00');

        $result   = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $result);
        $this->assertTrue(isset($result->messages));
        $this->assertTrue(isset($result->success));

        $this->assertSame($result->messages, ['data' => 'INVALID YO']);
        $this->assertFalse($result->success);
    }

    public function testUpdateCanBeAccessed()
    {
        $id         = "123456789-5";
        $booking    = $this->getMockBooking();
        $data       = ['date' => '25-03-2014', 'startTime' => '09:00'];
        $startDate  = new DateTime("21 April 2014");

        $this->bookingService->expects($this->once())
            ->method('update')
            ->with('5', $this->isInstanceOf('JhFlexiTime\DateTime\DateTime'), $data)
            ->will($this->returnValue($booking));

        $this->configureMockTimeCalculatorService(
            'getTotals',
            [$this->user, $startDate, $booking->getDate()],
            ['some-total', 20]
        );

        $this->configureMockTimeCalculatorService(
            'getWeekTotals',
            [$this->user, $booking->getDate()],
            ['some-total', 20]
        );

        $userSettings = new UserSettings;
        $userSettings->setFlexStartDate($startDate);

        $this->userSettingsRepository
            ->expects($this->once())
            ->method('findOneByUser')
            ->with($this->user)
            ->will($this->returnValue($userSettings));

        $this->routeMatch->setParam('id', $id);
        $this->request->setMethod('PUT');
        $this->request->setContent(http_build_query($data));

        $result   = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $result);
        $this->assertTrue(isset($result->success));
        $this->assertTrue(isset($result->booking));
        $this->assertTrue(isset($result->monthTotals));
        $this->assertTrue(isset($result->weekTotals));

        $this->assertSame($booking, $result->booking);
        $this->assertTrue($result->success);
        $this->assertEquals($result->monthTotals, ['some-total', 20]);
    }

    public function testUpdateCanBeAccessedButFail()
    {
        $id     = "123456789-5";
        $return = ['messages' => ['data' => 'INVALID YO']];
        $data   = ['date' => '25-03-2014', 'startTime' => '09:00'];

        $this->bookingService->expects($this->once())
            ->method('update')
            ->with('5', $this->isInstanceOf('JhFlexiTime\DateTime\DateTime'), $data)
            ->will($this->returnValue($return));

        $this->routeMatch->setParam('id', $id);
        $this->request->setMethod('PUT');
        $this->request->setContent(http_build_query($data));

        $result   = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $result);
        $this->assertTrue(isset($result->messages));
        $this->assertTrue(isset($result->success));

        $this->assertSame($result->messages, ['data' => 'INVALID YO']);
        $this->assertFalse($result->success);
    }

    public function testDeleteCanBeAccessed()
    {
        $id         = "123456789-5";
        $booking    = $this->getMockBooking();
        $startDate  = new DateTime("21 April 2014");

        $this->bookingService->expects($this->once())
            ->method('delete')
            ->with('5', $this->isInstanceOf('JhFlexiTime\DateTime\DateTime'))
            ->will($this->returnValue($booking));

        $this->configureMockTimeCalculatorService(
            'getTotals',
            [$this->user, $startDate, $booking->getDate()],
            ['some-total', 20]
        );

        $this->configureMockTimeCalculatorService(
            'getWeekTotals',
            [$this->user, $booking->getDate()],
            ['some-total', 20]
        );

        $userSettings = new UserSettings;
        $userSettings->setFlexStartDate($startDate);

        $this->userSettingsRepository
            ->expects($this->once())
            ->method('findOneByUser')
            ->with($this->user)
            ->will($this->returnValue($userSettings));

        $this->routeMatch->setParam('id', $id);
        $this->request->setMethod('DELETE');

        $result   = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $result);
        $this->assertTrue(isset($result->success));
        $this->assertTrue(isset($result->monthTotals));
        $this->assertTrue(isset($result->weekTotals));

        $this->assertTrue($result->success);
        $this->assertEquals($result->monthTotals, ['some-total', 20]);
    }

    public function testDeleteCanBeAccessedButFail()
    {
        $id     = "123456789-5";
        $return = ['messages' => ['data' => 'Invalid ID']];
        $this->bookingService->expects($this->once())
            ->method('delete')
            ->with('5', $this->isInstanceOf('JhFlexiTime\DateTime\DateTime'))
            ->will($this->returnValue($return));


        $this->routeMatch->setParam('id', $id);
        $this->request->setMethod('DELETE');

        $result   = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $result);
        $this->assertTrue(isset($result->success));
        $this->assertTrue(isset($result->messages));

        $this->assertFalse($result->success);
        $this->assertEquals($result->messages, ['data' => 'Invalid ID']);
    }
}
