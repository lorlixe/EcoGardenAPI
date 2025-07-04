<?php

namespace App\Repository;

use App\Entity\Advice;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Advice>
 */
class AdviceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Advice::class);
    }
    public function findByMonth(DateTime $date): array
    {

        $start = (clone $date)->modify('first day of this month')->setTime(0, 0, 0);
        $end = (clone $date)->modify('last day of this month')->setTime(23, 59, 59);
        return $this->createQueryBuilder('a')
            ->where('a.date BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('a.date', 'ASC') // ou DESC si tu veux les plus récents d’abord
            ->getQuery()
            ->getResult();
    }


    //    /**
    //     * @return Advice[] Returns an array of Advice objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Advice
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
