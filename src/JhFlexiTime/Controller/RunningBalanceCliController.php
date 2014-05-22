<?php

namespace JhFlexiTime\Controller;

use JhFlexiTime\Service\RunningBalanceService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Console\Request as ConsoleRequest;
use Doctrine\Common\Persistence\ObjectManager;
use JhUser\Repository\UserRepositoryInterface;
use JhUser\Repository\RoleRepositoryInterface;
use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\ColorInterface;

/**
 * Class RoleController
 * @package JhUser\Controller
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class RunningBalanceCliController extends AbstractActionController
{
    /**
     * @var \JhUser\Repository\UserRepositoryInterface
     */
    protected $userRepository;

    /**
     * @var \JhFlexiTime\Service\RunningBalanceService
     */
    protected $runningBalanceService;

    /**
     * @param UserRepositoryInterface $userRepository
     * @param RunningBalanceService $runningBalanceService
     * @param AdapterInterface $console
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        RunningBalanceService $runningBalanceService,
        AdapterInterface $console
    ) {
        $this->userRepository           = $userRepository;
        $this->runningBalanceService    = $runningBalanceService;
        $this->console                  = $console;
    }

    /**
     * Recaulculate running balance,
     * for either a single user or all users
     *
     * @throws \RuntimeException
     */
    public function reCalcRunningBalanceAction()
    {
        $request    = $this->getRequest();
        $email      = $request->getParam('userEmail');

        if ($email) {
            $user = $this->userRepository->findOneByEmail($email);

            if (!$user) {
                throw new \RuntimeException(sprintf('User with email: "%s" could not be found', $email));
            }

            $this->console->writeLine("Recalculating Running Balance for $email", ColorInterface::GREEN);
            $this->runningBalanceService->recalculateUserRunningBalance($user);
        } else {
            $this->console->writeLine("Recalculating Running Balance for all Users", ColorInterface::GREEN);
            $this->runningBalanceService->recalculateAllUsersRunningBalance();

        }

        $this->console->writeLine("Finished! ", ColorInterface::GREEN);
    }

    /**
     * Calculate the balance for the previous month,
     * for each user, and add it on to their running balance
     */
    public function calcPrevMonthBalanceAction()
    {
        $this->console->writeLine(
            "Calculating Running Balance for all Users for previous month",
            ColorInterface::GREEN
        );
        $this->runningBalanceService->calculatePreviousMonthBalance();
        $this->console->writeLine("Finished! ", ColorInterface::GREEN);
    }

    /**
     * Set a Users Initial Balance
     * + then recalculate their running balance
     *
     * @throws \RuntimeException
     */
    public function setUserStartingBalanceAction()
    {
        $request    = $this->getRequest();
        $balance    = $request->getParam('balance');
        $email      = $request->getParam('userEmail');

        $user = $this->userRepository->findOneByEmail($email);

        if (!$user) {
            throw new \RuntimeException(sprintf('User with email: "%s" could not be found', $email));
        }

        $this->runningBalanceService->setUserStartingBalance($user, $balance);
        //recalculate balance
        $this->runningBalanceService->recalculateUserRunningBalance($user);
        $this->console->writeLine(
            sprintf("Successfully set User '%s' balance to '%s'! ", $email, $balance),
            ColorInterface::GREEN
        );

    }
}
