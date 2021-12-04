<?php

namespace App\Repository;

use App\Entity\MDWUsers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use DateTime;
use DateInterval;

/**
 * @method MDWUsers|null find($id, $lockMode = null, $lockVersion = null)
 * @method MDWUsers|null findOneBy(array $criteria, array $orderBy = null)
 * @method MDWUsers[]    findAll()
 * @method MDWUsers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MDWUsersRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MDWUsers::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof MDWUsers) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    public function getOldUsers($delai_max) {
        $limite = new DateTime();
        $limite->sub(new DateInterval($delai_max));

        return $this->createQueryBuilder('g')
            ->andWhere('g.date_modification < :delai')
            ->setParameter('delai', $limite)
            ->getQuery()
            ->getResult()
        ;
    }

    // /**
    //  * @return MDWUsers[] Returns an array of MDWUsers objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MDWUsers
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
