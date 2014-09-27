<?php

namespace JhFlexiTime\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\Validator\Date as DateValidator;
use Zend\View\Model\JsonModel;
use JhFlexiTime\Service\TimeCalculatorService;
use JhFlexiTime\Service\BookingService;

/**
 * Class BookingRestController
 * @package JhFlexiTime\Controller
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class BookingRestController extends AbstractRestfulController
{
    use GetSetDateTrait;

    /**
     * @var \JhFlexiTime\Service\BookingService
     */
    protected $bookingService;

    /**
     * @var \JhFlexiTime\Service\TimeCalculatorService
     */
    protected $timeCalculatorService;


    /**
     * @param BookingService $bookingService
     * @param TimeCalculatorService $timeCalculatorService
     */
    public function __construct(
        BookingService $bookingService,
        TimeCalculatorService $timeCalculatorService
    ) {
        $this->bookingService           = $bookingService;
        $this->timeCalculatorService    = $timeCalculatorService;
    }

    /**
     * @return JsonModel
     */
    public function getList()
    {
        $month  = (string) $this->params()->fromQuery('m');
        $year   = (string) $this->params()->fromQuery('y');
        $period = $this->getDate($month, $year);

        $user           = $this->zfcUserAuthentication()->getIdentity();
        $records        = $this->bookingService->getUserBookingsForMonth($user, $period);
        $pagination     = $this->bookingService->getPagination($period);
        $totals         = $this->timeCalculatorService->getTotals($user, $period);

        return new JsonModel(array(
            'bookings' => array(
                'records'   => $records,
                'totals'    => $totals,
                'user'      => $user,
            ),
            'pagination' => $pagination,
            'date'       => $period,
        ));
    }

    /**
     * @param int $id
     * @return JsonModel
     */
    public function get($id)
    {
        $id   = $this->parseIdCriteria($id);
        return new JsonModel(array(
            'booking' => $this->bookingService->getBookingByUserAndDate($id['user'], $id['date']),
        ));
    }

    /**
     * @param array $data
     * @return JsonModel
     */
    public function create($data)
    {
        $return = $this->bookingService->create($data);

        if (is_array($return)) {
            $return['success'] = false;
            return new JsonModel($return);
        }

        return new JsonModel(array(
            'success'       => true,
            'booking'       => $return,
            'monthTotals'   => $this->timeCalculatorService->getTotals($return->getUser(), $return->getDate()),
            'weekTotals'    => $this->timeCalculatorService->getWeekTotals($return->getUser(), $return->getDate())
        ));
    }

    /**
     * @param int $id
     * @param array $data
     * @return JsonModel
     */
    public function update($id, $data)
    {
        $id     = $this->parseIdCriteria($id);
        $return = $this->bookingService->update($id['user'], $id['date'], $data);

        if (is_array($return)) {
            $return['success'] = false;
            return new JsonModel($return);
        }

        return new JsonModel(array(
            'booking'       => $return,
            'success'       => true,
            'monthTotals'   => $this->timeCalculatorService->getTotals($return->getUser(), $return->getDate()),
            'weekTotals'    => $this->timeCalculatorService->getWeekTotals($return->getUser(), $return->getDate())
        ));
    }

    /**
     * @param int $id
     * @return JsonModel
     */
    public function delete($id)
    {
        $id     = $this->parseIdCriteria($id);
        $return = $this->bookingService->delete($id['user'], $id['date']);
        if (is_array($return)) {
            $return['success'] = false;
            return new JsonModel($return);
        }

        return new JsonModel(array(
            'success'       => true,
            'monthTotals'   => $this->timeCalculatorService->getTotals($return->getUser(), $return->getDate()),
            'weekTotals'    => $this->timeCalculatorService->getWeekTotals($return->getUser(), $return->getDate())
        ));
    }

    /**
     * @param $id
     * @return array
     */
    public function parseIdCriteria($id)
    {
        $idParts    = explode("-", $id);
        $date       = new \JhFlexiTime\DateTime\DateTime();
        $date->setTimestamp($idParts[0]);
        return ['date' => $date,'user' => $idParts[1]];
    }
}
