# TP6 — Collection de jeux vidéo : choix et vérifications

## 1.1 Entités

| Entité | Champs | Remarques |
|---|---|---|
| `Utilisateur` | prenom, nom, pseudonyme (30 car. max, unique), mail (unique), dateNaissance, imageProfil (facultatif), createdAt, updatedAt | Nom enregistré en majuscules, mail en minuscules. `OneToMany` vers `Collect` (orphanRemoval). |
| `Collect` (table de liaison) | utilisateur, jeuVideo, statut, dateModifStatut, prixAchat, dateAchat, commentaire, createdAt, updatedAt | Un jeu ne peut être qu'une fois dans une collection (contrainte unique + `UniqueEntity`). Clés étrangères `ON DELETE CASCADE` : supprimer un jeu ou un utilisateur supprime les lignes de collection associées. |
| `JeuVideo` (mise à jour) | + `collects` (`OneToMany` vers `Collect`) | Permet d'afficher les possesseurs d'un jeu. |

Règles métier :

- **Date d'achat** : ne peut pas être antérieure à la date de sortie du jeu (`Assert\Callback` dans `Collect`), ni dans le futur.
- **Date de modification du statut** : mise à jour automatiquement par `Collect::setStatut()`, uniquement si le statut change réellement.
- **createdAt / updatedAt** : gérés par les contrôleurs comme pour les autres entités (pas dans les formulaires).

Migration : `migrations/Version20261123180205.php` (tables `utilisateur` et `collect`).

## 1.2 Énumération `StatutJeuEnum`

`src/Enum/StatutJeuEnum.php` : `POSSEDE`, `SOUHAITE`, `EN_COURS`, `TERMINE`, `ABANDONNE`, `PRETE`, `VENDU`, `PLATINE`.

- Colonne `collect.statut` : `#[ORM\Column(length: 30, enumType: StatutJeuEnum::class)]`, valeur par défaut `POSSEDE` — aucune table de statuts en BDD.
- `getLabel()` : libellé lisible (« En cours », « Prêté »…), utilisé dans le formulaire (`EnumType` + `choice_label`) et dans Twig (`collect.statut.label`).
- `getBadgeClass()` : couleur du badge de statut ; le texte reste toujours affiché (l'information ne repose pas que sur la couleur), contrastes AA.
- L'exemple du sujet mélange `POSSEDER` et `POSSEDE` : `POSSEDE` est utilisé partout.

## Données (fixtures)

`src/DataFixtures/CollectionFixtures.php` (groupe `collection`, chargé après `AppFixtures`) : 6 utilisateurs (dont Denis DUPONT et Marcelle DUMONT, exemples du sujet), 4 utilisateurs avec plus de 5 jeux, 1 utilisateur (Hugo) sans aucun jeu.

```bash
# Tout recharger (purge la BDD)
php bin/console doctrine:fixtures:load
# Ajouter seulement les utilisateurs et collections, sans toucher aux jeux existants
php bin/console doctrine:fixtures:load --group=collection --append
```

## 1.3 Écrans des collections

| Écran | Route | Contenu |
|---|---|---|
| Collections des utilisateurs | `/utilisateur` | Pseudo, nom, nombre de jeux, jeux en cours, valeur de la collection |
| Collection d'un utilisateur | `/utilisateur/{id}` | Profil, nombre de jeux par statut, liste des jeux (statut, prix et date d'achat, date de changement de statut) |
| Ajouter un jeu à la collection | `/collection/new/{utilisateur}` | Seuls les jeux absents de la collection sont proposés |
| Détail d'un jeu de la collection | `/collection/{id}` | Liens vers l'utilisateur et le jeu, prix d'achat comparé au prix public, date d'achat comparée à la date de sortie |
| Modifier / retirer un jeu | `/collection/{id}/edit`, `/collection/{id}` (POST) | Le jeu n'est plus modifiable, seulement statut, prix, date, commentaire |
| Ajouter / modifier / supprimer un utilisateur | `/utilisateur/new`, `/utilisateur/{id}/edit`, `/utilisateur/{id}` (POST) | Image de profil envoyée comme les jaquettes (`src/Service/ImageUploader.php`, `public/uploads/utilisateurs`) ; sans image, les initiales sont affichées |

Menu : section « Collections » ajoutée dans `src/Menu/MenuBuilder.php` ; le fil d'Ariane suit (ex. *Accueil › Collections › DenisDu › Elden Ring › Modifier*).

## 1.4 Liste des jeux vidéo

Colonne « Collections » (libellé complet « Dans la collection de ») : pseudos des utilisateurs possédant le jeu, chacun menant à la ligne de collection correspondante. Les jeux, genres, éditeurs, développeurs et possesseurs sont chargés en une seule requête (`JeuVideoRepository::findAllWithCollections()`).

## Harmonisation avec le TP5

Mêmes composants que le reste du site : `.btn`, tableaux / cartes `.table--cards`, fiches `.table--detail`, en-tête de formulaire `_form_header` (légende + résumé des erreurs), thème de formulaire, actions à l'infinitif (« Revenir à la collection », « Retirer de la collection »…), dates au format français, symbole €. Styles propres au TP6 (badges de statut, avatars) dans `public/css/collection.css`, chargé seulement par les pages concernées.

## Vérifications effectuées

| Test | Outil | Résultat |
|---|---|---|
| HTML (9 pages TP6 + liste des jeux + 3 formulaires en erreur) | Validateur W3C Nu (validator.w3.org/nu) | 0 erreur, 0 avertissement |
| Accessibilité (pages TP6 + pages TP4/TP5, 1280 et 375 px) | Script de contrôle dans le navigateur : alternatives des images, libellés des champs, noms des liens / boutons, ordre des titres, identifiants uniques, `aria-describedby`, `scope` et `caption` des tableaux, contrastes des badges (≥ 4,5:1) | aucun problème |
| Responsive (320 / 375 / 768 / 1024 / 1280 px) | Mesure du débordement horizontal | aucun débordement |
| Fonctionnel | Requêtes réelles | validations (date d'achat, prix, doublon, mail / pseudo uniques), date de statut, suppressions en cascade, upload de l'image de profil |
