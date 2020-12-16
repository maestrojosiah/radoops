<?php

namespace App\Repository;

use App\Entity\Orda;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Orda|null find($id, $lockMode = null, $lockVersion = null)
 * @method Orda|null findOneBy(array $criteria, array $orderBy = null)
 * @method Orda[]    findAll()
 * @method Orda[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrdaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Orda::class);
    }

    // /**
    //  * @return Orda[] Returns an array of Orda objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Orda
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
