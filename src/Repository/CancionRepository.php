<?php

namespace App\Repository;

use App\Entity\Cancion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cancion>
 *
 * @method Cancion|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cancion|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cancion[]    findAll()
 * @method Cancion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CancionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cancion::class);
    }
    public function findByNombre($nombre)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.titulo LIKE :nombre')
            ->setParameter('nombre', '%' . $nombre . '%')
            ->getQuery()
            ->getResult();
    }
    public function findMostPlayedSongs($limit = 10)
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.numeroReproducciones', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findSongsByGenre()
    {
        return $this->createQueryBuilder('c')
            ->select('c.generoMusical, COUNT(c.id) as count')
            ->groupBy('c.generoMusical')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Cancion[] Returns an array of Cancion objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Cancion
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
