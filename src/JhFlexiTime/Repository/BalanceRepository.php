<?php
 
namespace JhFlexiTime\Repository;
 
use Doctrine\Common\Persistence\ObjectRepository;
use ZfcUser\Entity\UserInterface;
use JhFlexiTime\Entity\RunningBalance;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class BalanceRepository
 * @package JhFlexiTime\Repository
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class BalanceRepository implements BalanceRepositoryInterface
{
 
    /**
     * @var ObjectRepository
     */
    protected $balanceRepository;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @param ObjectRepository $balanceRepository
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectRepository $balanceRepository, ObjectManager $objectManager)
    {
        $this->balanceRepository    = $balanceRepository;
        $this->objectManager        = $objectManager;
    }

    /**
     * Get a User's running balance,
     * if it does not exist, create it
     *
     * @param UserInterface $user
     * @return \JhFlexiTime\Entity\RunningBalance
     */
    public function findByUser(UserInterface $user)
    {
        $runningBalance = $this->balanceRepository->findOneBy(array('user' => $user));

        if(!$runningBalance) {
            $runningBalance = $this->createRunningBalance($user);
        }

        return $runningBalance;
    }

    /**
     * Proxy to Doctrine Repo
     *
     * @param array $criteria
     * @return \JhFlexiTime\Entity\RunningBalance
     */
    public function findOneBy(array $criteria)
    {
        return $this->balanceRepository->findOneBy($criteria);
    }

    /**
     * @param UserInterface $user
     * @return RunningBalance
     */
    public function createRunningBalance(UserInterface $user)
    {
        $runningBalance = new RunningBalance;
        $runningBalance->setUser($user);
        $this->objectManager->persist($runningBalance);
        $this->objectManager->flush();
        return $runningBalance;
    }
}
