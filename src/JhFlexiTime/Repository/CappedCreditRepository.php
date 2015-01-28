<?php
 
namespace JhFlexiTime\Repository;
 
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityRepository;
use JhFlexiTime\Entity\CappedCredit;
use ZfcUser\Entity\UserInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class CappedCreditRepository
 * @package JhFlexiTime\Repository
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CappedCreditRepository extends EntityRepository implements CappedCreditRepositoryInterface
{

    /**
     * @param UserInterface $user
     * @return CappedCredit[]
     */
    public function findAllByUser(UserInterface $user)
    {
        return $this->findBy(['user' => $user], ['date' => 'ASC']);
    }

    /**
     * @param UserInterface $user
     * @return float
     */
    public function getTotalCappedCreditByUser(UserInterface $user)
    {
        $qb = $this->createQueryBuilder('c');
        $qb->select('sum(c.cappedCredit)')
            ->where('c.user = :user')
            ->setParameters(['user' => $user]);

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Delete all records by user
     *
     * @param UserInterface $user
     */
    public function deleteAllByUser(UserInterface $user)
    {
        $qb = $this->createQueryBuilder('c');
        $qb
            ->delete()
            ->where('c.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
    }

    /**
     * Delete all Records in table
     */
    public function deleteAll()
    {
        $qb = $this->createQueryBuilder('c');
        $qb
            ->delete()
            ->getQuery()
            ->execute();
    }
}
