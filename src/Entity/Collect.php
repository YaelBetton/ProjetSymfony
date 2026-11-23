<?php

namespace App\Entity;

use App\Enum\StatutJeuEnum;
use App\Repository\CollectRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Table de liaison Utilisateur ↔ JeuVideo : un jeu de la collection d'un utilisateur,
 * avec son statut (POSSEDE, EN_COURS, TERMINE...), son prix et sa date d'achat.
 */
#[ORM\Entity(repositoryClass: CollectRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_collect_utilisateur_jeu', columns: ['utilisateur_id', 'jeu_video_id'])]
#[UniqueEntity(
    fields: ['utilisateur', 'jeuVideo'],
    message: 'Ce jeu est déjà dans la collection de cet utilisateur.',
    errorPath: 'jeuVideo',
)]
class Collect
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'collects')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne(inversedBy: 'collects')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?JeuVideo $jeuVideo = null;

    /** Valeur de l'énumération StatutJeuEnum (par défaut : POSSEDE) */
    #[ORM\Column(length: 30, enumType: StatutJeuEnum::class)]
    private StatutJeuEnum $statut = StatutJeuEnum::POSSEDE;

    /** Date du dernier changement de statut (mise à jour automatiquement par setStatut()) */
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateModifStatut = null;

    /** Prix payé par l'utilisateur pour le jeu */
    #[ORM\Column(type: Types::DECIMAL, precision: 6, scale: 2, nullable: true)]
    private ?string $prixAchat = null;

    /** Date d'achat : ne peut pas être antérieure à la date de sortie du jeu (cf. validateDateAchat) */
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $dateAchat = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $commentaire = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->statut = StatutJeuEnum::POSSEDE;
        $this->createdAt = new \DateTimeImmutable();
        $this->dateModifStatut = new \DateTimeImmutable();
    }

    /**
     * La date d'achat ne peut pas être antérieure à la date de sortie du jeu.
     */
    #[Assert\Callback]
    public function validateDateAchat(ExecutionContextInterface $context): void
    {
        $dateSortie = $this->jeuVideo?->getDateSortie();
        if (null === $this->dateAchat || null === $dateSortie) {
            return;
        }

        if ($this->dateAchat->format('Y-m-d') < $dateSortie->format('Y-m-d')) {
            $context->buildViolation('La date d\'achat ne peut pas être antérieure à la date de sortie du jeu ({{ date }}).')
                ->setParameter('{{ date }}', $dateSortie->format('d/m/Y'))
                ->atPath('dateAchat')
                ->addViolation();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    public function getJeuVideo(): ?JeuVideo
    {
        return $this->jeuVideo;
    }

    public function setJeuVideo(?JeuVideo $jeuVideo): static
    {
        $this->jeuVideo = $jeuVideo;

        return $this;
    }

    public function getStatut(): StatutJeuEnum
    {
        return $this->statut;
    }

    /**
     * Change le statut ; la date de modification du statut n'est mise à jour
     * que si le statut change réellement.
     */
    public function setStatut(StatutJeuEnum $statut): static
    {
        if ($statut !== $this->statut) {
            $this->dateModifStatut = new \DateTimeImmutable();
        }
        $this->statut = $statut;

        return $this;
    }

    public function getDateModifStatut(): ?\DateTimeImmutable
    {
        return $this->dateModifStatut;
    }

    public function setDateModifStatut(?\DateTimeImmutable $dateModifStatut): static
    {
        $this->dateModifStatut = $dateModifStatut;

        return $this;
    }

    public function getPrixAchat(): ?string
    {
        return $this->prixAchat;
    }

    public function setPrixAchat(?string $prixAchat): static
    {
        $this->prixAchat = $prixAchat;

        return $this;
    }

    public function getDateAchat(): ?\DateTime
    {
        return $this->dateAchat;
    }

    public function setDateAchat(?\DateTime $dateAchat): static
    {
        $this->dateAchat = $dateAchat;

        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): static
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
