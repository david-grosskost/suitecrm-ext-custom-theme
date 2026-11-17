# Thème vertical

Extension SuiteCRM 8 : le menu horizontal devient un rail vertical d'icônes à gauche, et
l'interface prend les couleurs de l'entreprise.

**Les écrans de SuiteCRM ne sont pas modifiés.** L'extension ne touche qu'au cadre —
l'en-tête, le menu, et le fond sur lequel les écrans s'affichent. Listes, fiches, Studio,
administration : tout reste ce que SuiteCRM propose, avec ses comportements et ses
raccourcis.

## Ce qu'elle fait

**Le rail.** La barre de menu quitte le haut de la page et se fixe contre le bord gauche,
sur 56 px. Chaque module y est une icône ; au survol, le rail s'ouvre à 240 px et les
libellés apparaissent, par-dessus le contenu — rien ne se redispose, donc rien ne saute
sous la souris.

**Les icônes.** Celles de SuiteCRM, déjà livrées avec lui
(`themes/suite8/images/<Module>.svg`), teintées de la couleur du module. Rien n'est
dessiné pour l'occasion : un module ajouté par une autre extension apporte la sienne.
Un module sans icône — typiquement un module créé dans Studio — reçoit une pastille
portant son initiale.

**Les couleurs.** Une par module, pour l'identifier d'un coup d'œil : filet sur le bord de
l'entrée et teinte de l'icône. Elles sont déduites du nom du module, donc stables d'une
instance à l'autre et d'une version à l'autre. Le reste de l'interface suit les couleurs
de l'entreprise, posées en variables CSS — c'est tout ce dont SuiteCRM 8 a besoin pour se
reteinter.

## Ce qu'elle ne fait pas encore

- **La page d'administration** pour choisir les couleurs. Elles sont lues dans la table
  `config` (catégorie `custom_theme`), mais rien ne les y écrit encore : l'extension
  utilise ses valeurs par défaut.
- **Le rail ne s'épingle pas** : il s'ouvre au survol et se referme. Un bouton pour le
  garder ouvert viendra.

## Fonctionnement

- Un **module Angular**, chargé par SuiteCRM 8 au démarrage sans recompiler son front,
  pose les variables CSS, installe la feuille de style du rail, puis décore chaque entrée
  de menu de son icône teintée.
- Un **point d'entrée** de SuiteCRM, `legacy/index.php?entryPoint=customTheme`, sert les
  couleurs. Il est **ouvert sans connexion**, pour que l'écran de connexion soit déjà aux
  bonnes couleurs — il ne sert donc que les couleurs choisies et les accents des modules
  **standard** de SuiteCRM. Rien de cette instance, rien des données : un module maison
  ne doit pas voir son nom exposé avant connexion.
- Le rail est écrit contre les **composants nommés** de SuiteCRM (`scrm-navbar-ui`,
  `scrm-menu-items-list`, `scrm-menu-item`), pas contre des classes générées : c'est ce qui
  lui donne une chance de survivre aux montées de version.
- Le menu est redessiné par Angular à chaque changement, donc la décoration est réappliquée
  par un observateur du DOM plutôt qu'une seule fois au chargement.

## La question de la maintenance

Reteinter est sûr : des variables CSS, c'est un contrat. **Déplacer le menu ne l'est pas** :
on réarrange une mise en page qu'on ne possède pas. À chaque montée de version de SuiteCRM,
il faut vérifier que le rail tient toujours — c'est le prix de ce thème, et il est assumé.

## Pour les développeurs

| Fichier | Rôle |
|---|---|
| `legacy/include/custom-theme/Palette.php` | les couleurs : celles choisies, celles déduites |
| `legacy/include/custom-theme/Theme.php` | ce que l'interface reçoit (variables, accents, rail) |
| `legacy/include/custom-theme/ConfigTable.php`, `Settings.php` | les couleurs en base (table `config`) |
| `legacy/include/custom-theme/ServeTheme.php`, `Reply.php` | le point d'entrée |
| `legacy/Extension/…` | déclaration du point d'entrée |
| `config/extension.php` | déclaration du module Angular à SuiteCRM 8 |
| `app/` → `public/` | le module Angular et sa version construite (`make front@build`) |

```bash
make extension@validate && make quality@lint && make quality@stan && make test@unit && make front@check
```
