<?php

namespace JhFlexiTimeTest\Controller;

use JhFlexiTime\Controller\BookingRestController;

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

    public function setUp()
    {
        $this->controller = new BookingRestController(
            $this->getBookingService(),
            $this->getTimeCalculatorService()
        );

        $this->request      = new Request();
        $this->routeMatch   = new RouteMatch(array());
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

    public function configureMockBookingService($method, array $params, $return)
    {
        $expects = $this->bookingService->expects($this->once())
            ->method($method)
            ->will($this->returnValue($return));

        call_user_func_array(array($expects, "with"), $params);
    }

    public function configureMockTimeCalculatorService($method, array $params, $return)
    {
        $expects = $this->timeCalculatorService->expects($this->once())
            ->method($method)
            ->will($this->returnValue($return));

        call_user_func_array(array($expects, "with"), $params);
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
            ->setDate(new \DateTime("25 March 2014"))
            ->setUser($this->user)
            ->setNotes("ALL THE TIME");
    }

    public function testGetListCanBeAccessed()
    {
        $booking    = $this->getMockBooking();
        $date       = new \DateTime("25 March 2014");

        $this->controller->setDate($date);
        $this->configureMockBookingService('getUserBookingsForMonth', array($this->user, $date), array($booking));
        $this->configureMockBookingService('getPagination', array($date), array());
        $this->configureMockTimeCalculatorService('getTotals', array($this->user, $date), array('some-total', 20));

        $result   = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $result);
        $this->assertTrue(isset($result->bookings));
        $this->assertTrue(isset($result->pagination));
        $this->assertTrue(isset($result->date));

        $expectedTime = array(
            'records' => array($booking),
            'totals'  => array('some-total', 20),
            'user'    => $this->user,
        );
        $this->assertEquals($expectedTime, $result->getVariable('bookings'));
        $this->assertEquals(array(), $result->getVariable('pagination'));
        $this->assertSame($date, $result->getVariable('date'));
    }

    public function testGetCanBeAccessed()
    {
        $id         = 1;
        $booking    = $this->getMockBooking();
        $this->configureMockBookingService('getBookingByUserAndId', array($this->user, $id), $booking);

        $this->routeMatch->setParam('id', $id);

        $result   = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $result);
        $this->assertSame($booking, $result->getVariable('booking'));
    }

    public function testCreateCanBeAccessed()
    {
        $booking = $this->getMockBooking();
        $this->configureMockBookingService('create', array(), $booking);
        $this->configureMockTimeCalculatorService('getTotals', array($this->user, $booking->getDate()), array('some-total', 20));
        $this->configureMockTimeCalculatorService('getWeekTotals', array($this->user, $booking->getDate()), array('some-total', 20));

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
        $this->assertEquals($result->monthTotals, array('some-total', 20));
    }

    public function testCreateCanBeAccessedButFail()
    {
        $return = array('messages' => array('data' => 'INVALID YO'));
        $data = array('date' => '25-03-2014', 'startTime' => '09:00');
        $this->configureMockBookingService('create', array($data, $this->user), $return);

        $this->request->setMethod('POST');
        $this->request->getPost()->set('date', '25-03-2014');
        $this->request->getPost()->set('startTime', '09:00');

        $result   = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $result);
        $this->assertTrue(isset($result->messages));
        $this->assertTrue(isset($result->success));

        $this->assertSame($result->messages, array('data' => 'INVALID YO'));
        $this->assertFalse($result->success);
    }

    public function testUpdateCanBeAccessed()
    {
        $id = 5;
        $booking = $this->getMockBooking();
        $data = array('date' => '25-03-2014', 'startTime' => '09:00');
        $this->configureMockBookingService('update', array($id, $data, $this->user), $booking);
        $this->configureMockTimeCalculatorService('getTotals', array($this->user, $booking->getDate()), array('some-total', 20));
        $this->configureMockTimeCalculatorService('getWeekTotals', array($this->user, $booking->getDate()), array('some-total', 20));


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
        $this->assertEquals($result->monthTotals, array('some-total', 20));
    }

    public function testUpdateCanBeAccessedButFail()
    {
        $id = 5;
        $return = array('messages' => array('data' => 'INVALID YO'));
        $data = array('date' => '25-03-2014', 'startTime' => '09:00');
        $this->configureMockBookingService('update', array($id, $data, $this->user), $return);

        $this->routeMatch->setParam('id', $id);
        $this->request->setMethod('PUT');
        $this->request->setContent(http_build_query($data));

        $result   = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $result);
        $this->assertTrue(isset($result->messages));
        $this->assertTrue(isset($result->success));

        $this->assertSame($result->messages, array('data' => 'INVALID YO'));
        $this->assertFalse($result->success);
    }

    public function testDeleteCanBeAccessed()
    {
        $id = 5;
        $booking = $this->getMockBooking();
        $this->configureMockBookingService('getBookingByUserAndId', array($this->user, $id), $booking);
        $this->configureMockBookingService('delete', array($booking), $booking);
        $this->configureMockTimeCalculatorService('getTotals', array($this->user, $booking->getDate()), array('some-total', 20));
        $this->configureMockTimeCalculatorService('getWeekTotals', array($this->user, $booking->getDate()), array('some-total', 20));

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
        $this->assertEquals($result->monthTotals, array('some-total', 20));
    }

    public function testDeleteCanBeAccessedButFail()
    {
        $id = 5;
        $this->bookingService->expects($this->once())
            ->method('getBookingByUserAndId')
            ->will($this->throwException(new \Exception))
            ->with($this->user, $id);

        $this->routeMatch->setParam('id', $id);
        $this->request->setMethod('DELETE');

        $result   = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $result);
        $this->assertTrue(isset($result->success));
        $this->assertTrue(isset($result->message));

        $this->assertFalse($result->success);
        $this->assertEquals($result->message, 'Invalid ID');
    }
}
