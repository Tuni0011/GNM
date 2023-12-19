<?php

namespace App\Repository;

use App\Entity\Linker;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Linker>
 *
 * @method Linker|null find($id, $lockMode = null, $lockVersion = null)
 * @method Linker|null findOneBy(array $criteria, array $orderBy = null)
 * @method Linker[]    findAll()
 * @method Linker[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LinkerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Linker::class);
    }

//    /**
//     * @return Linker[] Returns an array of Linker objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('l.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Linker
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
