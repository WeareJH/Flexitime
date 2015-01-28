<?php
 
namespace JhFlexiTime\Repository;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityRepository;
use ZfcUser\Entity\UserInterface;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;

/**
 * Class BookedCreditRepository
 * @package JhFlexiTime\Repository
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class BookedCreditRepository extends EntityRepository implements BookedCreditRepositoryInterface
{

    /**
     * @param UserInterface $user
     * @param bool          $paginate
     *
     * @return array
     */
    public function findAllByUser(UserInterface $user, $paginate = false)
    {
        $qb = $this->createQueryBuilder('b');
        $qb->where('b.user = :user')
            ->setParameter('user', $user);

        if ($paginate) {
            return $this->paginate($qb);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Return a paginator object using the query builder object
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @return \Zend\Paginator\Paginator
     */
    public function paginate(QueryBuilder $queryBuilder)
    {
        return new Paginator(
            new DoctrinePaginator(new ORMPaginator($queryBuilder))
        );
    }
}
