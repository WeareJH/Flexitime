<?php

namespace JhFlexiTime\Listener;

use JhFlexiTime\DateTime\DateTime;
use JhFlexiTime\Entity\RunningBalance;
use JhFlexiTime\Options\ModuleOptions;
use JhFlexiTime\Service\CappedCreditService;
use JhFlexiTime\Service\RunningBalanceService;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\Event;

/**
 * Class CappedCreditListener
 * @package JhFlexiTime\Listener
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class CappedCreditListener extends AbstractListenerAggregate
{
    /**
     * @var CappedCreditService
     */
    protected $cappedCreditService;

    /**
     * @var ModuleOptions
     */
    protected $options;

    /**
     * @param CappedCreditService $cappedCreditService
     * @param ModuleOptions $options
     */
    public function __construct(CappedCreditService $cappedCreditService, ModuleOptions $options)
    {
        $this->cappedCreditService  = $cappedCreditService;
        $this->options              = $options;
    }

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        if (!$this->options->creditCapEnabled()) {
            return;
        }

        $sharedEvents = $events->getSharedManager();

        $this->listeners[] = $sharedEvents->attach(
            'JhFlexiTime\Service\RunningBalanceService',
            'reIndexUserRunningBalance.pre',
            [$this, 'clearCappedCreditRecords'],
            100
        );

        $this->listeners[] = $sharedEvents->attach(
            'JhFlexiTime\Service\RunningBalanceService',
            'addMonthBalance.post',
            [$this, 'applyCreditCarryLimit'],
            100
        );

    }

    /**
     * @param Event $e
     */
    public function clearCappedCreditRecords(Event $e)
    {
        /** @var RunningBalance $runningBalance */
        $runningBalance = $e->getParam('runningBalance');
        $this->cappedCreditService->clearCappedCreditEntries($runningBalance->getUser());
    }

    /**
     * @param Event $e
     */
    public function applyCreditCarryLimit(Event $e)
    {

        /** @var RunningBalance $runningBalance */
        $runningBalance = $e->getParam('runningBalance');
        $month          = $e->getParam('month');

        /**
         * If running balance is over allowed credit limit, change it to the limit
         * Store the difference so it may be used for other things. Eg trade for overtime etc.
         */
        $creditLimit = $this->options->getCreditCapForDate($month);
        if (null !== $creditLimit && $runningBalance->getBalance() > $creditLimit) {
            $overage        = $runningBalance->getBalance() - $creditLimit;
            $runningBalance->setBalance($creditLimit);

            $this->cappedCreditService->create($runningBalance->getUser(), $overage, $month);
        }
    }
}
