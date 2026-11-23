<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Utilisateur de l'application, qui possède une collection de jeux vidéo (0 à n objets Collect).
 */
#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
#[UniqueEntity(fields: ['mail'], message: 'Cette adresse mail est déjà utilisée par un autre utilisateur.')]
#[UniqueEntity(fields: ['pseudonyme'], message: 'Ce pseudo est déjà pris.')]
class Utilisateur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $prenom = null;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    #[ORM\Column(length: 30, unique: true)]
    private ?string $pseudonyme = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $mail = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $dateNaissance = null;

    /** Chemin de l'image de profil relatif à public/ (ex. uploads/utilisateurs/xxx.png) */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageProfil = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * Jeux de la collection de l'utilisateur.
     * Supprimer un utilisateur supprime aussi sa collection (orphanRemoval + ON DELETE CASCADE).
     *
     * @var Collection<int, Collect>
     */
    #[ORM\OneToMany(targetEntity: Collect::class, mappedBy: 'utilisateur', orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private Collection $collects;

    public function __construct()
    {
        $this->collects = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = trim($prenom);

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    /**
     * Le nom de famille est enregistré en majuscules (ex. « Dupont » → « DUPONT »).
     */
    public function setNom(string $nom): static
    {
        $this->nom = mb_strtoupper(trim($nom), 'UTF-8');

        return $this;
    }

    /**
     * Prénom + nom (ex. « Denis DUPONT »).
     */
    public function getNomComplet(): string
    {
        return trim($this->prenom.' '.$this->nom);
    }

    public function getPseudonyme(): ?string
    {
        return $this->pseudonyme;
    }

    public function setPseudonyme(string $pseudonyme): static
    {
        $this->pseudonyme = trim($pseudonyme);

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(string $mail): static
    {
        $this->mail = mb_strtolower(trim($mail), 'UTF-8');

        return $this;
    }

    public function getDateNaissance(): ?\DateTime
    {
        return $this->dateNaissance;
    }

    public function setDateNaissance(?\DateTime $dateNaissance): static
    {
        $this->dateNaissance = $dateNaissance;

        return $this;
    }

    public function getImageProfil(): ?string
    {
        return $this->imageProfil;
    }

    public function setImageProfil(?string $imageProfil): static
    {
        $this->imageProfil = $imageProfil;

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

    /**
     * @return Collection<int, Collect>
     */
    public function getCollects(): Collection
    {
        return $this->collects;
    }

    public function addCollect(Collect $collect): static
    {
        if (!$this->collects->contains($collect)) {
            $this->collects->add($collect);
            $collect->setUtilisateur($this);
        }

        return $this;
    }

    public function removeCollect(Collect $collect): static
    {
        $this->collects->removeElement($collect);

        return $this;
    }

    /**
     * Valeur totale payée pour les jeux de la collection (prix d'achat renseignés).
     */
    public function getTotalPrixAchat(): float
    {
        $total = 0.0;
        foreach ($this->collects as $collect) {
            $total += (float) $collect->getPrixAchat();
        }

        return $total;
    }
}
