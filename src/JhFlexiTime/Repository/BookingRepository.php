<?php
 
namespace JhFlexiTime\Repository;
 
use Doctrine\Common\Persistence\ObjectRepository;
use ZfcUser\Entity\UserInterface;
use JhFlexiTime\Entity\Booking;
use JhFlexiTime\DateTime\DateTime;

/**
 * Booking repository
 * 
 * @author Ben Lill <ben@wearejh.com>
 */
class BookingRepository implements BookingRepositoryInterface, ObjectRepository
{
 
    /**
     * @var \Doctrine\Common\Persistence\ObjectRepository
     */
    protected $bookingRepository;

    /**
     * @param ObjectRepository $bookingRepository
     */
    public function __construct(ObjectRepository $bookingRepository)
    {
        $this->bookingRepository = $bookingRepository;
    }

    /**
     * @param UserInterface $user
     * @return array
     */
    public function findAllByUser(UserInterface $user)
    {
        return $this->bookingRepository->findBy(
            array('user' => $user),
            array('date' => 'ASC'),
            null,
            null
        );
    }
    
    /**
     * Find all bookings for a given user/month
     *
     * @param \ZfcUser\Entity\UserInterface $user
     * @param DateTime $date
     * @return Booking[]
     */
    public function findByUserAndMonth(UserInterface $user, DateTime $date)
    {
        $firstDay = new DateTime(sprintf('first day of %s', $date->format('F Y')));
        $lastDay = new DateTime(sprintf('last day of %s', $date->format('F Y')));
        
        $params = array(
            'user'      => $user,
            'firstDay'  => $firstDay->format('Y-m-d'),
            'lastDay'   => $lastDay->format('Y-m-d')
        );
        
        $qb = $this->bookingRepository->createQueryBuilder('b');
        $qb->select('b')
            ->where('b.user = :user')
            ->andWhere('b.date >= :firstDay')
            ->andWhere('b.date <= :lastDay')
            ->setParameters($params)
            ->orderBy('b.date', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Check if this booking, is the first booking
     * for this user + month
     *
     * @param UserInterface $user
     * @param DateTime $month
     * @return bool
     */
    public function isUsersFirstBookingForMonth(UserInterface $user, DateTime $month)
    {
        $firstDay   = new DateTime(sprintf('first day of %s', $month->format('F Y')));
        $lastDay    = new DateTime(sprintf('last day of %s', $month->format('F Y')));

        $params = array(
            'user'          => $user,
            'firstDay'      => $firstDay->format('Y-m-d'),
            'lastDay'       => $lastDay->format('Y-m-d')
        );

        $qb = $this->bookingRepository->createQueryBuilder('b');
        $qb->select('b')
            ->where('b.user = :user')
            ->andWhere('b.date >= :firstDay')
            ->andWhere('b.date <= :lastDay')
            ->setParameters($params)
            ->setMaxResults(1);

        $result = $qb->getQuery()->getOneOrNullResult();
        //if result not an instance of time then
        //this is the first booking of the month
        return !$result instanceof Booking;
    }

    /**
     * Get the sum of all all hours booked between the first day of the given month,
     * and the current day of the passed in DateTime object
     *
     * @param UserInterface $user
     * @param DateTime $date
     * @return float
     */
    public function getMonthBookedToDateTotalByUser(UserInterface $user, DateTime $date)
    {
        $firstDay = new DateTime(sprintf('first day of %s', $date->format('F')));
        $currentDay = new DateTime($date->format('Y-m-d'));

        $params = array(
            'user'          => $user,
            'firstDay'      => $firstDay->format('Y-m-d'),
            'currentDay'    => $currentDay->format('Y-m-d')
        );

        $qb = $this->bookingRepository->createQueryBuilder('b');
        $qb->select('sum(b.total)')
            ->where('b.user = :user')
            ->andWhere('b.date >= :firstDay')
            ->andWhere('b.date <= :currentDay')
            ->setParameters($params)
            ->orderBy('b.date', 'ASC');

        $totalHoursBookedThisMonth = $qb->getQuery()->getSingleScalarResult();

        if (null === $totalHoursBookedThisMonth) {
            $totalHoursBookedThisMonth = 0;
        }

        return $totalHoursBookedThisMonth;
    }

    /**
     * Get the sum of all all hours booked between the first day of the given month,
     * and the last day of the month
     *
     * @param UserInterface $user
     * @param DateTime $date
     * @return float
     */
    public function getMonthBookedTotalByUser(UserInterface $user, DateTime $date)
    {
        $firstDay   = new DateTime(sprintf('first day of %s', $date->format('F')));
        $lastDay    = new DateTime(sprintf('last day of %s', $date->format('F')));

        $params = array(
            'user'      => $user,
            'firstDay'  => $firstDay->format('Y-m-d'),
            'lastDay'   => $lastDay->format('Y-m-d')
        );

        $qb = $this->bookingRepository->createQueryBuilder('b');
        $qb->select('sum(b.total)')
            ->where('b.user = :user')
            ->andWhere('b.date >= :firstDay')
            ->andWhere('b.date <= :lastDay')
            ->setParameters($params)
            ->orderBy('b.date', 'ASC');

        $totalHoursBookedThisMonth = $qb->getQuery()->getSingleScalarResult();

        if (null === $totalHoursBookedThisMonth) {
            $totalHoursBookedThisMonth = 0;
        }

        return $totalHoursBookedThisMonth;
    }

    /**
     * @param UserInterface $user
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @return float
     */
    public function getTotalBookedBetweenByUser(UserInterface $user, DateTime $startDate, DateTime $endDate)
    {
        $params = array(
            'user'      => $user,
            'firstDay'  => $startDate->format('Y-m-d'),
            'lastDay'   => $endDate->format('Y-m-d')
        );

        $qb = $this->bookingRepository->createQueryBuilder('b');
        $qb->select('sum(b.total)')
            ->where('b.user = :user')
            ->andWhere('b.date >= :firstDay')
            ->andWhere('b.date <= :lastDay')
            ->setParameters($params)
            ->orderBy('b.date', 'ASC');

        $totalHoursBookedThisPeriod = $qb->getQuery()->getSingleScalarResult();

        if (null === $totalHoursBookedThisPeriod) {
            $totalHoursBookedThisPeriod = 0;
        }

        return $totalHoursBookedThisPeriod;
    }

    /**
     * find(): defined by ObjectRepository.
     *
     * @see    ObjectRepository::find()
     * @param  int $id
     * @return Booking|null
     */
    public function find($id)
    {
        return $this->bookingRepository->find($id);
    }

    /**
     * findAll(): defined by ObjectRepository.
     *
     * @see    ObjectRepository::findAll()
     * @return Booking[]
     */
    public function findAll()
    {
        return $this->bookingRepository->findAll();
    }

    /**
     * findBy(): defined by ObjectRepository.
     *
     * @see    ObjectRepository::findBy()
     * @param  array      $criteria
     * @param  array|null $orderBy
     * @param  int|null   $limit
     * @param  int|null   $offset
     * @return Booking[]
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->bookingRepository->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * findOneBy(): defined by ObjectRepository.
     *
     * @see    ObjectRepository::findOneBy()
     * @param  array $criteria
     * @return Booking|null
     */
    public function findOneBy(array $criteria)
    {
        return $this->bookingRepository->findOneBy($criteria);
    }

    /**
     * getClassName(): defined by ObjectRepository.
     *
     * @see    ObjectRepository::getClassName()
     * @return string
     */
    public function getClassName()
    {
        return $this->bookingRepository->getClassName();
    }
}
