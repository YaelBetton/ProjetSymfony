<?php

namespace App\Repository;

use App\Entity\Collect;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Collect>
 */
class CollectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Collect::class);
    }

    /**
     * Collection d'un utilisateur, triée par titre de jeu, avec les jeux, genres et éditeurs.
     *
     * @return Collect[]
     */
    public function findByUtilisateur(Utilisateur $utilisateur): array
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.jeuVideo', 'j')->addSelect('j')
            ->leftJoin('j.genre', 'g')->addSelect('g')
            ->leftJoin('j.editeur', 'e')->addSelect('e')
            ->andWhere('c.utilisateur = :utilisateur')
            ->setParameter('utilisateur', $utilisateur)
            ->orderBy('j.titre', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
