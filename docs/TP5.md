# TP5 — Design / CSS : choix et vérifications

## 1.1 Design général / template de base

| Exigence | Réalisation |
|---|---|
| Header commun | `templates/base.html.twig` : logo (`public/images/logo.svg` via `asset()`) + nom du site |
| Menu commun, item courant distinct | KnpMenu (voir 1.3) ; item courant sur fond ambre + `aria-current="page"` (ou `"true"` pour la section d'une page de détail / formulaire) |
| Fil d'Ariane | `templates/partials/_breadcrumb.html.twig`, construit à partir du même menu KnpMenu (ex. *Accueil › Jeux vidéo › The Last of Us Part II › Modifier*) |
| Footer commun | `base.html.twig` |
| Peu de duplication | Un seul gabarit (`base.html.twig`) étendu par toutes les pages ; partials réutilisés (`_flash_messages`, `_breadcrumb`, `_form_header`, `_site_web`, `_statut`, `_delete_form`) ; thème de formulaire unique (`templates/form/theme.html.twig`) |
| Site en français | Libellés, boutons, messages flash, messages de validation, pages d'erreur 404/500 (`templates/bundles/TwigBundle/Exception/error.html.twig`), page « Lucky » du TP1 |
| Cohérence du fond | Toutes les actions sont des verbes à l'infinitif : Ajouter, Consulter, Modifier, Supprimer, Enregistrer, Revenir à… |
| Cohérence de la forme | Un seul style de bouton (`.btn` + variantes `--primary`, `--secondary`, `--danger`, `--sm`) ; mêmes en-têtes de page, tableaux et formulaires partout |
| Responsive | Mise en page fluide, menu repliable (bouton « Menu ») sous 768 px, listes affichées en cartes sous 1024 px, formulaires sur 1 ou 2 colonnes, fiches clé/valeur empilées sur téléphone |
| HTML valide W3C | Voir « Vérifications » |
| Accessibilité / inclusivité | Lien d'évitement « Aller au contenu », balises sémantiques (`header`, `nav`, `main`, `footer`, `table` avec `caption` et `scope`), contrastes AA, police 16 px, focus clavier visible, cibles tactiles ≥ 36–44 px, libellés explicites (« Consulter *nom du jeu* » pour les lecteurs d'écran), liens externes signalés « nouvel onglet », statut Actif/Inactif en texte (pas seulement en couleur), respect de `prefers-reduced-motion` |
| Sources organisées | `public/css/` (4 fichiers), `public/js/app.js`, `public/images/`, `templates/partials/`, `templates/form/`, `templates/menu/`, `src/Menu/` |

## 1.2 Style CSS — choix : CSS « traditionnel »

Fichiers chargés avec `asset()` (composant `symfony/asset`) :

- `base.css` : variables (couleurs, espacements, typographie), focus visible, utilitaires (`.visually-hidden`…)
- `layout.css` : conteneur, header, menu, fil d'Ariane, contenu, footer
- `components.css` : boutons, tableaux / cartes, messages flash, badges, accueil, pages d'erreur
- `forms.css` : champs, astérisque des champs obligatoires, aide, erreurs

**Pourquoi pas Bootstrap ?** Le CDN impose une connexion internet (non approprié, comme le souligne le sujet) et l'intégration via Webpack Encore ajoute une chaîne de build non nécessaire pour un site de cette taille. Le CSS écrit à la main est léger, entièrement maîtrisé, et les variables CSS garantissent la cohérence.

Le versionnage des assets (`framework.assets.version` dans `config/packages/framework.yaml`) ajoute `?v=…` aux URL : l'incrémenter après une modification force les navigateurs à recharger le CSS / JS.

La barre de debug Symfony (profiler) n'est pas restylée : aucun sélecteur global ne cible ses éléments.

## 1.3 Menu — choix : KnpMenuBundle

`src/Menu/MenuBuilder.php` (« menu builder as a service », alias `main`), rendu avec `knp_menu_render('main', {depth: 1, …})` et le template `templates/menu/main_menu.html.twig`.

Justification :

- **Dynamique** : les items sont construits à partir des routes ; l'item courant est détecté automatiquement (voter de route KnpMenu), sans condition dans les templates.
- **Un seul arbre pour le menu et le fil d'Ariane** : le builder ajoute, pour la page courante, les sous-items *Ajouter*, *nom de l'élément*, *Modifier* ; le fil d'Ariane est obtenu avec `knp_menu_get_breadcrumbs_array()`. Une seule structure à maintenir.
- **Évolutif** : KnpMenu permet de restreindre des items selon les droits (futur TP sur la sécurité).
- Écarté : *menu HTML statique* (mise à jour manuelle, item courant à gérer à la main) ; *EasyAdmin* (pensé pour un back-office, aurait remplacé les CRUD réalisés au TP4).

## 1.4 Formulaires

- Même style pour tous les champs et libellés (`forms.css`), même bouton « Enregistrer » / « Enregistrer les modifications ».
- **Astérisque rouge** sur les champs obligatoires en CSS : `label.required::after { content: " *" }` (Symfony ajoute la classe `required`), avec une légende « Les champs marqués d'un * sont obligatoires ».
- **Aide** : messages d'aide (prix en €, date, image 5 Mo, nom en majuscules, genre actif) reliés au champ par `aria-describedby`.
- **Erreurs** : messages en français sous chaque champ, champ encadré en rouge + `aria-invalid="true"`, résumé « Le formulaire contient des erreurs » annoncé aux lecteurs d'écran (`role="alert"`), focus placé automatiquement sur le premier champ en erreur (`app.js`).

## Vérifications effectuées

| Test | Outil | Résultat |
|---|---|---|
| HTML (16 pages + 3 formulaires en erreur) | Validateur W3C Nu (validator.w3.org/nu) | 0 erreur, 0 avertissement |
| CSS (4 fichiers) | Validateur CSS W3C (jigsaw.w3.org) | valide, 0 erreur (avertissements restants : variables CSS non vérifiables statiquement, couleur sans fond) |
| Accessibilité (15 pages × 3 largeurs) | axe-core 4.10 (moteur d'axe DevTools), règles WCAG 2.1 A/AA + bonnes pratiques | 0 violation |
| Responsive (15 pages × 320 / 375 / 768 / 1024 / 1440 px) | Mesure du débordement horizontal dans le navigateur | aucun débordement |
| Clavier | Navigation à la touche Tab | 1er Tab = « Aller au contenu », focus visible, Échap referme le menu mobile |

Pour refaire la validation W3C d'une page : afficher le code source (Ctrl+U), le copier dans <https://validator.w3.org/#validate_by_input> (la barre de debug Symfony n'existe pas en production ; elle peut être ignorée).
