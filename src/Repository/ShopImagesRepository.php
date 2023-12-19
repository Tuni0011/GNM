<?php

namespace App\Repository;

use App\Entity\ShopImages;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ShopImages>
 *
 * @method ShopImages|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShopImages|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShopImages[]    findAll()
 * @method ShopImages[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShopImagesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShopImages::class);
    }

//    /**
//     * @return ShopImages[] Returns an array of ShopImages objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ShopImages
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
