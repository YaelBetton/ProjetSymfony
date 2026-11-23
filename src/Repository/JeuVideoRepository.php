<?php

namespace App\Repository;

use App\Entity\JeuVideo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<JeuVideo>
 */
class JeuVideoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JeuVideo::class);
    }

    /**
     * Tous les jeux (triés par titre) avec leurs genre, éditeur, développeur et les utilisateurs
     * qui les ont dans leur collection, chargés en une seule requête (liste des jeux, TP6).
     *
     * @return JeuVideo[]
     */
    public function findAllWithCollections(): array
    {
        return $this->createQueryBuilder('j')
            ->leftJoin('j.genre', 'g')->addSelect('g')
            ->leftJoin('j.editeur', 'e')->addSelect('e')
            ->leftJoin('j.developpeur', 'd')->addSelect('d')
            ->leftJoin('j.collects', 'c')->addSelect('c')
            ->leftJoin('c.utilisateur', 'u')->addSelect('u')
            ->orderBy('j.titre', 'ASC')
            ->addOrderBy('u.pseudonyme', 'ASC')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return JeuVideo[] Returns an array of JeuVideo objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('j')
    //            ->andWhere('j.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('j.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?JeuVideo
    //    {
    //        return $this->createQueryBuilder('j')
    //            ->andWhere('j.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
