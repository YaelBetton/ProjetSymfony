<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Enregistre les images envoyées par les formulaires dans public/<sous-dossier>
 * (nom unique et parlant) et supprime les anciennes.
 * Utilisé pour les images de profil des utilisateurs (TP6).
 */
final class ImageUploader
{
    public function __construct(
        #[Autowire('%kernel.project_dir%/public')]
        private readonly string $publicDir,
        private readonly SluggerInterface $slugger,
    ) {
    }

    /**
     * Déplace le fichier dans public/$subdir sous un nom du type « nom-parlant-64f1c2.png ».
     *
     * @param string $subdir   Sous-dossier de public/ (ex. « uploads/utilisateurs »)
     * @param string $baseName Base du nom de fichier (ex. le pseudo de l'utilisateur)
     *
     * @return string Chemin relatif à public/, à enregistrer en BDD
     *
     * @throws FileException si le fichier n'a pas pu être déplacé
     */
    public function upload(UploadedFile $file, string $subdir, string $baseName): string
    {
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeName = $this->slugger->slug($baseName ?: $originalName)->lower();
        $newFilename = sprintf('%s-%s.%s', $safeName, uniqid(), $file->guessExtension() ?? 'bin');

        $file->move($this->publicDir.'/'.$subdir, $newFilename);

        return $subdir.'/'.$newFilename;
    }

    /**
     * Supprime physiquement une image de public/, uniquement si elle se trouve dans $subdir
     * (on ne supprime jamais un fichier ailleurs dans le projet).
     */
    public function remove(?string $path, string $subdir): void
    {
        if (!$path || !str_starts_with($path, $subdir.'/') || str_contains($path, '..')) {
            return;
        }

        $fullPath = $this->publicDir.'/'.$path;
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }
}
