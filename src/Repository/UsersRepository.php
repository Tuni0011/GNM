<?php

namespace App\Repository;

use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<Users>
* @implements PasswordUpgraderInterface<Users>
 *
 * @method Users|null find($id, $lockMode = null, $lockVersion = null)
 * @method Users|null findOneBy(array $criteria, array $orderBy = null)
 * @method Users[]    findAll()
 * @method Users[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsersRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Users::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Users) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }
     /**
     * Get all generations of users for a given user.
     *
     * @param Users $user
     *
     * @return array
     */
    public function getAllGenerations(Users $user): array
    {
        $generations = [];

        $this->populateGenerations($user, $generations);

        return $generations;
    }

    /**
     * Recursive function to populate all generations.
     *
     * @param Users $user
     * @param array $generations
     * @param int $currentGeneration
     */
    private function populateGenerations(Users $user, array &$generations, $currentGeneration = 0): void
    {
        // Add the user to the current generation
        $generations[$currentGeneration][] = $user;

        // Get the next generation
        $children = $this->createQueryBuilder('u')
            ->where('u.parent = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        // Recursively process the next generation
        foreach ($children as $child) {
            $this->populateGenerations($child, $generations, $currentGeneration + 1);
        }
    }
      /**
     * Get all uplines of a user, including the user itself.
     *
     * @param Users $user
     *
     * @return array
     */
    public function getAllUplines(Users $user): array
    {
        $uplines = [];

        $this->populateUplines($user, $uplines);

        return $uplines;
    }

    /**
     * Recursive function to populate all uplines.
     *
     * @param Users $user
     * @param array $uplines
     */
    private function populateUplines(Users $user, array &$uplines): void
    {
        // Add the user to uplines
        $uplines[] = $user;

        // If the user has a parent, continue populating uplines
        if ($parent = $user->getParent()) {
            $this->populateUplines($parent, $uplines);
        }
    }

//    /**
//     * @return Users[] Returns an array of Users objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Users
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
