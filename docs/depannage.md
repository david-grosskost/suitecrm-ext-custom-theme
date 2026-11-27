# Dépannage

## Les nouvelles couleurs n'apparaissent pas

Recharger le CRM avec Ctrl+F5 : les couleurs sont lues au chargement de l'interface. Sur une
application installée (extension pwa), la fermer et la rouvrir.

## Le menu des modules est vide dans toute l'interface

Cause déjà rencontrée : un lien de l'administration sans module (voir
[Fonctionnement](fonctionnement.md#points-dattention)). SuiteCRM 8 échoue alors sur toute sa
requête de métadonnées, sans rien écrire dans les logs. Vérifier tout lien ajouté dans
`legacy/Extension/modules/Administration/Ext/Administration/` : il doit porter
`module=…`.

## Le rail est cassé après une mise à jour de SuiteCRM

Le rail s'appuie sur la mise en page de SuiteCRM. Recompiler le module contre la nouvelle
version (`make front@build`), puis reprendre les sélecteurs qui ne correspondent plus (voir
[Développement](developpement.md#nouvelle-version-de-suitecrm)).

## Un écran garde les couleurs de SuiteCRM

La recoloration remplace des couleurs précises de la feuille de style de SuiteCRM. Une couleur
absente des tables `AngularTheme::COLOURS` ou `LegacyTheme` reste telle quelle : l'y ajouter,
avec la variable qui la remplace.

## Un écran ou un élément reste clair en mode sombre

Sa couleur n'est pas dans une feuille de style que le thème lit : soit elle est écrite par
du code directement sur l'élément (comme la bande des en-têtes des tableaux YUI, traitée à
part), soit elle vient d'une image ou d'un graphique dessiné par du code. Repérer l'élément
avec l'inspecteur du navigateur (onglet Styles : une règle `style=""` sur l'élément, ou une
image), puis ajouter une règle sous `:root[data-ct-scheme="dark"]` dans le module Angular.

## Le mode sombre revient en clair après une connexion

Le choix est gardé dans les préférences de l'utilisateur : celui de la session l'emporte sur
celui du navigateur dès que l'utilisateur est connecté. Vérifier le choix enregistré :
`legacy/index.php?entryPoint=customThemeScheme`, connecté, répond `{"scheme":"…"}`.
