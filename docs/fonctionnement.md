# Fonctionnement

## Fichiers

| Fichier | Rôle |
|---|---|
| `app/` → `public/` | le module Angular et sa version compilée (`make front@build`) |
| `config/extension.php` | déclare le module à SuiteCRM 8 |
| `config/services/navigation-tab-limits.yaml` | lève le plafond de modules affichés, côté SuiteCRM 8 |
| `app/src/dark.ts` | le mode sombre côté navigateur : la règle des gris, le choix de l'utilisateur, le bouton |
| `legacy/include/custom-theme/Palette.php` | les couleurs : celles choisies, leurs valeurs par défaut, l'accent de chaque module |
| `legacy/include/custom-theme/DarkPalette.php`, `Colour.php` | les couleurs du mode sombre : celles choisies, ou calculées depuis le mode clair ; luminance et contraste |
| `legacy/include/custom-theme/Scheme.php`, `ServeScheme.php` | le choix clair, sombre ou « comme l'ordinateur » de l'utilisateur, dans ses préférences |
| `legacy/include/custom-theme/Theme.php` | ce que l'interface reçoit : variables CSS, accents, recolorations |
| `legacy/include/custom-theme/AngularTheme.php` | les couleurs des écrans de SuiteCRM 8 et ce qui les remplace |
| `legacy/include/custom-theme/LegacyTheme.php` | les couleurs des écrans historiques et ce qui les remplace |
| `legacy/include/custom-theme/Overlay.php` | la forme commune de ces deux recolorations |
| `legacy/include/custom-theme/ConfigTable.php`, `Settings.php` | les couleurs en base (table `config`) |
| `legacy/include/custom-theme/ServeTheme.php`, `Reply.php` | le point d'entrée qui sert les couleurs |
| `legacy/include/custom-theme/Navigation.php` | le plafond de modules affichés, côté historique |
| `legacy/include/custom-theme/AdminForm.php`, `AdminPage.php`, `Page.php`, `PageView.php`, `page.tpl` | la page « Couleurs du thème » |
| `legacy/Extension/…` | le point d'entrée, la section d'administration et son action, les libellés |
| `database/install.php` | lève le plafond de modules à l'installation |
| `tests/` | tests des couleurs, des recolorations, de la navigation et du formulaire |

## Le rail

Le module Angular, chargé par SuiteCRM 8 au démarrage sans recompiler son front, installe la
feuille de style du rail et décore chaque entrée de menu de son icône teintée. Le menu étant
redessiné par Angular à chaque changement, la décoration est réappliquée par un observateur
du DOM, pas une seule fois au chargement.

Le rail est écrit contre les **composants nommés** de SuiteCRM (`scrm-navbar-ui`,
`scrm-menu-items-list`, `scrm-menu-item`), pas contre des classes générées : c'est ce qui lui
donne une chance de survivre aux montées de version.

**Tous les modules** : SuiteCRM plafonne le menu par `default_max_tabs` (côté historique) et
`themes.navigation_tab_limits` (côté SuiteCRM 8). L'extension lève le premier à
l'installation (`database/install.php`) et le second par `config/services/`, que SuiteCRM
importe de lui-même ; la copie repliée dans « Options », devenue un doublon, est masquée.

## Les couleurs

Le point d'entrée `legacy/index.php?entryPoint=customTheme` sert les couleurs à l'interface.
Il est **ouvert sans connexion**, pour que l'écran de connexion soit déjà aux bonnes
couleurs : il ne sert donc que les couleurs choisies et les accents des modules **standard**
de SuiteCRM, rien de l'instance ni de ses données (un module maison ne doit pas voir son nom
exposé avant connexion).

Le module Angular pose ensuite les variables CSS, puis recolore deux familles d'écrans :

- **les écrans de SuiteCRM 8** (listes, fiches, administration) : il lit la feuille de style
  que SuiteCRM a chargée, garde les règles qui portent une de ses couleurs, et les réinjecte
  avec des variables (`AngularTheme`) ;
- **les écrans historiques** (tableau de bord, Studio), servis dans une iframe : même
  principe, dans l'iframe, avec la feuille du thème historique (`LegacyTheme`).

Rien n'est écrit sur disque, et rien ne pourrait l'être : le scanner du Chargeur de module
refuse toute fonction de fichier. La feuille lue étant celle que l'instance sert, les couleurs
suivent les montées de version de SuiteCRM. Chaque variable garde la couleur d'origine en
secours (`var(--nom, #origine)`) : une variable qui n'arrive pas laisse l'écran tel que
SuiteCRM l'a dessiné.

## Le mode sombre

**Les couleurs de l'administration** : le point d'entrée sert aussi leurs valeurs sombres
(`dark` : les mêmes variables, valeurs de `DarkPalette`). Le module les pose sur la racine
de la page quand l'affichage est sombre, à la place des claires.

**Les couleurs de SuiteCRM** : quelque quatre cents gris, écrits en dur dans ses feuilles de
style. Le même passage qui pose les couleurs du client remet chaque gris à une variable
nommée d'après son **rôle** et sa couleur (`--ct-dk-bg-ffffff`, `--ct-dk-text-212529`,
`--ct-dk-border-dee2e6`), avec la couleur d'origine en secours. Ces variables ne sont définies
que sous `:root[data-ct-scheme="dark"]` : en clair, rien ne change. Le rôle compte, car une
même couleur en joue plusieurs : le blanc est le fond des pages et le texte des boutons.

| Rôle | En sombre |
|---|---|
| fond clair | sombre, le plus clair devenant le plus sombre (le blanc devient la page) |
| bordure claire | un cran au-dessus des fonds, pour rester visible ; noire translucide → blanche translucide |
| texte foncé | clair, le plus foncé devenant le plus clair ; un texte clair est posé sur une couleur et reste |
| texte aux couleurs du client | la même couleur, éclaircie jusqu'à un contraste de 4,5 sur la page |
| couleur vive | inchangée, sauf un texte très foncé (éclairci) ou un fond très pâle (assombri) |

Les gris prennent la teinte du fond sombre du client. Dans les écrans historiques, la même
règle s'applique aux feuilles annexes (tableaux YUI, arborescences) et aux blocs de style
écrits dans la page, comme ceux des pages d'administration des extensions ; une règle à part
couvre ce que YUI écrit directement sur ses tableaux.

**Le choix de l'utilisateur** : `legacy/index.php?entryPoint=customThemeScheme`, réservé aux
utilisateurs connectés, le lit (GET) ou le change (POST, champ `scheme`) dans ses préférences
(catégorie `custom_theme`). Le navigateur le garde aussi, pour l'afficher dès le premier
instant et sur l'écran de connexion, où personne n'est encore connu.

## La page d'administration

**Administration → Thème du client → Couleurs du thème** est une action du module
Administration (`index.php?module=Administration&action=CustomTheme`), derrière la
connexion, qui renvoie un 403 à qui n'est pas administrateur. Elle n'écrit que des codes
couleur, dans la table `config`, catégorie de l'extension : les six du mode clair, et ceux du
mode sombre que l'administrateur a changés (préfixe `dark_`). Une couleur sombre laissée telle
qu'elle a été calculée n'est pas enregistrée : elle continue de suivre le mode clair.

## Données en base

- Les couleurs choisies : table `config`, catégorie de l'extension.
- Le choix clair ou sombre de chaque utilisateur : ses préférences (`user_preferences`,
  catégorie `custom_theme`).
- Le plafond de modules du menu historique (`default_max_tabs`), relevé à l'installation.

## Installation et désinstallation

- **Installation** (`database/install.php`, une seule fois) : relève le plafond de modules
  du menu historique.
- **Désinstallation** : pas de script. Les couleurs choisies restent en base ; réinstallée,
  l'extension les retrouve.

## Points d'attention

**Sécurité de la page d'administration.** Elle suit le standard de SuiteCRM : pas de jeton
de formulaire, parce que son socle historique n'en fournit pas et que ses propres écrans
d'administration n'en ont pas. Le choix est assumé : la page ne manipule que des codes
couleur, et le pire scénario d'un formulaire forgé est de faire changer les couleurs du CRM
à un administrateur à son insu. Aucune donnée, aucun droit. **Si l'extension devait un jour
écrire autre chose que des couleurs, c'est la première chose à revoir.**

Une valeur qui n'est pas un code couleur à six chiffres est **écartée, pas corrigée** : le
réglage retombe sur la couleur d'origine, toujours lisible. Tout ce qui ressort dans la page
est échappé, y compris une valeur hostile arrivée en base par un autre chemin.

**L'entrée d'administration** (`legacy/Extension/modules/Administration/Ext/Administration/`)
doit respecter deux règles, apprises à la dure :

- son lien doit porter un module : SuiteCRM 8 convertit chaque lien de l'administration et
  lève « No module defined » sur un lien sans module ; rien ne l'attrape, et **le menu des
  modules de toute l'interface revient vide**, sans trace dans aucun log. D'où une action du
  module Administration plutôt qu'un point d'entrée ;
- le fichier ne contient que des tableaux : SuiteCRM le charge deux fois dans la même requête
  (`require`, pas `require_once`), une fonction ou une classe y provoquerait une erreur fatale.

**La maintenance.** Recolorer est sûr : des variables CSS, c'est un contrat. Déplacer le menu
ne l'est pas : on réarrange une mise en page qu'on ne possède pas. À chaque montée de version
de SuiteCRM, il faut vérifier que le rail tient toujours. C'est le prix de ce thème, et il est
assumé.
