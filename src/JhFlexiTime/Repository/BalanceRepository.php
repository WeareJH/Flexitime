<?php
 
namespace JhFlexiTime\Repository;
 
use Doctrine\Common\Persistence\ObjectRepository;
use ZfcUser\Entity\UserInterface;

/**
 * Class BalanceRepository
 * @package JhFlexiTime\Repository
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class BalanceRepository implements BalanceRepositoryInterface
{
 
    /**
     * @var \Doctrine\Common\Persistence\ObjectRepository
     */
    protected $balanceRepository;

    /**
     * @param ObjectRepository $balanceRepository
     */
    public function __construct(ObjectRepository $balanceRepository)
    {
        $this->balanceRepository = $balanceRepository;
    }

    /**
     * @param UserInterface $user
     * @return \JhFlexiTime\Entity\RunningBalance
     */
    public function findByUser(UserInterface $user)
    {
        return $this->balanceRepository->findOneBy(array('user' => $user));
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
}
