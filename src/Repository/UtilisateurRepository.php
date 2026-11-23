<?php

namespace App\Repository;

use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Utilisateur>
 */
class UtilisateurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Utilisateur::class);
    }

    /**
     * Tous les utilisateurs (triés par pseudo) avec leur collection et les jeux associés,
     * chargés en une seule requête (évite une requête par utilisateur dans la liste).
     *
     * @return Utilisateur[]
     */
    public function findAllWithCollection(): array
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.collects', 'c')->addSelect('c')
            ->leftJoin('c.jeuVideo', 'j')->addSelect('j')
            ->orderBy('u.pseudonyme', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
