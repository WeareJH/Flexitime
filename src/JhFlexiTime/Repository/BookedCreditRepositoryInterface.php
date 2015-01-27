<?php

namespace JhFlexiTime\Repository;

use Doctrine\DBAL\LockMode;
use ZfcUser\Entity\UserInterface;

/**
 * Class BookedCreditRepository
 * @package JhFlexiTime\Repository
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
interface BookedCreditRepositoryInterface
{
    /**
     * @param UserInterface $user
     * @return array
     */
    public function findAllByUser(UserInterface $user, $paginate = false);

    /**
     * Finds an entity by its primary key / identifier.
     *
     * @param mixed $id The identifier.
     * @param int $lockMode The lock mode.
     * @param int|null $lockVersion The lock version.
     *
     * @return object|null The entity instance or NULL if the entity can not be found.
     */
    public function find($id, $lockMode = LockMode::NONE, $lockVersion = null);

    /**
     * Finds all entities in the repository.
     *
     * @return array The entities.
     */
    public function findAll();

    /**
     * Finds entities by a set of criteria.
     *
     * @param array $criteria
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array The objects.
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null);

    /**
     * Finds a single entity by a set of criteria.
     *
     * @param array $criteria
     * @param array|null $orderBy
     *
     * @return object|null The entity instance or NULL if the entity can not be found.
     */
    public function findOneBy(array $criteria, array $orderBy = null);
}
