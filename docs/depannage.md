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
