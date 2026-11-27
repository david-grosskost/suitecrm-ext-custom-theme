# Utilisation

## Ce qui change

**Le rail.** La barre de menu quitte le haut de la page et se fixe contre le bord gauche,
sur 56 px. Chaque module y est une icône. Trois lignes en tête du rail l'ouvrent à 240 px,
avec les libellés, par-dessus le contenu ; elles deviennent une croix pour le refermer. Le
rail s'ouvre au clic, jamais au survol : un menu qui se déplie tout seul gêne l'écran
derrière lui.

**L'épingle.** Un bouton en tête du rail le garde ouvert : le contenu se décale alors au
lieu d'être recouvert. Ce choix, comme l'ouverture du rail, est gardé par navigateur : c'est
une habitude de la personne, pas un réglage de l'instance.

**Les modules.** Tous les modules sont dans le rail, chacun avec son sous-menu, qui s'ouvre
sur ses trois points (cliquer le module mène au module). SuiteCRM n'en affiche
normalement que huit et replie le reste dans « Options ».

**Les icônes.** Celles de SuiteCRM, teintées de la couleur du module. Un module sans icône,
typiquement créé dans Studio, reçoit une pastille portant son initiale.

**Les couleurs.** Chaque module a sa couleur (filet sur le bord de l'entrée et teinte de
l'icône), déduite de son nom : la même d'une instance et d'une version à l'autre. Le reste
de l'interface suit les couleurs du client, y compris l'écran de connexion.

**Le mode sombre.** Un bouton du bandeau du haut fait le tour de trois choix : **clair**
(soleil), **sombre** (lune) et **comme l'ordinateur** (demi-cercle, le choix par défaut, qui
suit le réglage clair ou sombre du système). Le choix est gardé dans les préférences de
l'utilisateur : il le retrouve sur un autre ordinateur, à la connexion suivante. Tout passe
au sombre : le cadre, les écrans de SuiteCRM 8, les écrans historiques (tableau de bord,
Studio, Chargeur de module), et les pages d'administration des autres extensions. Les
couleurs qui ont un sens (rouge d'une erreur, vert d'un succès, couleurs du client) gardent
leur valeur ; le texte blanc des boutons reste blanc.

## Réglages

**Administration → Thème du client → Couleurs du thème**, réservé aux administrateurs :

| Réglage | Par défaut | Ce qu'il colore |
|---|---|---|
| Couleur principale | `#0284c5` | le texte, les icônes et les barres que SuiteCRM met en avant |
| Bandeau : début du dégradé | `#0369a0` | l'en-tête, et les barres des écrans |
| Bandeau : fin du dégradé | `#3abff8` | l'en-tête |
| Fond des écrans | `#f1f5f9` | le fond sur lequel les écrans s'affichent |
| Fond du menu | `#ffffff` | le rail |
| Couleur des boutons, liens et champs actifs | `#007bff` | le bleu de SuiteCRM (boutons, liens, champs actifs) |

**Mode sombre** : les mêmes six couleurs, pour le mode sombre. Tant qu'elles ne sont pas
changées, elles sont **calculées à partir des couleurs ci-dessus** : fond et menu très sombres
dans la teinte du fond clair, bandeau assombri pour que le texte blanc y reste lisible,
couleur principale et bleu dans la teinte du client, éclaircis seulement s'ils ne se
détachent pas assez du fond. Une couleur changée est gardée telle quelle ; **Recalculer le mode
sombre** oublie ces choix et revient au calcul.

**Rétablir les couleurs d'origine** remet toutes ces valeurs. Après un enregistrement, recharger le
CRM pour voir les nouvelles couleurs.

La couleur des boutons, liens et champs actifs garde par défaut le bleu de SuiteCRM : elle
porte aussi le contour des champs actifs, et une couleur trop claire peut rendre un écran
difficile à lire.

## Données de démo

Aucune : les couleurs par défaut s'appliquent tant qu'aucune n'a été choisie.

## Limites

- L'extension n'apparaît pas dans **Administration → Paramétrages des thèmes** : n'y figurent
  que les thèmes historiques de SuiteCRM, et en être un ferait perdre l'habillage des fiches
  de SuiteCRM 8. Elle se règle dans **Thème du client**.
- Les couleurs des modules ne se choisissent pas une par une : elles sont déduites de leur
  nom.
- Mode sombre : une image (logo, photo) pensée pour un fond blanc reste telle quelle ; les
  graphiques dessinés par du code (tableaux de bord) gardent leurs couleurs. Au chargement
  d'un écran historique, il peut s'afficher un instant en clair avant de passer au sombre.
