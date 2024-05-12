<?php

namespace App\Repository;

use App\Entity\Artista;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Artista>
 *
 * @method Artista|null find($id, $lockMode = null, $lockVersion = null)
 * @method Artista|null findOneBy(array $criteria, array $orderBy = null)
 * @method Artista[]    findAll()
 * @method Artista[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArtistaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Artista::class);
    }

    //    /**
    //     * @return Artista[] Returns an array of Artista objects
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

    //    public function findOneBySomeField($value): ?Artista
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
