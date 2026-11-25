# Développement

Les commandes `make`, la CI et le format des extensions viennent de
[suitecrm-devkit](https://github.com/david-grosskost/suitecrm-devkit#documentation), à la
version de `composer.json`. Cette page ne décrit que ce qui est propre à l'extension.

## Modifier le module Angular

Après toute modification de `app/` : `make front@build`, puis commiter `app/` **et**
`public/` ensemble. La CI recompile le module (`make front@check`) et refuse un `public/`
qui ne correspond pas à `app/`.

## Ajouter une couleur réglable

1. La déclarer dans `Palette::DEFAULTS` (avec sa valeur par défaut) et dans
   `AdminForm::FIELDS` (avec son libellé).
2. La relier à ce qu'elle colore : une variable dans `Theme`, et si besoin les couleurs de
   SuiteCRM qu'elle remplace dans `AngularTheme` ou `LegacyTheme`.
3. Compléter les tests et le tableau des réglages de [Utilisation](utilisation.md).

Choisir des couleurs que le client peut régler sans rendre un écran illisible : vérifier le
contraste du texte blanc posé dessus (4,5 au moins pour un texte ordinaire).

## Vérifier

```bash
make extension@validate && make quality@lint && make quality@stan && make test@unit
make front@check
make package@build && make package@scan
```

## Tester sur une instance locale

1. `make package@build`, puis sur l'instance locale de suitecrm-core, **Administration →
   Chargeur de module** : **Choisir un fichier** (`build/custom-theme-<version>.zip`),
   **Télécharger**, **Installer**, puis Ctrl+F5.
2. Vérifier le rail (ouverture, épingle, sous-menus, y compris le second niveau du sous-menu
   d'Administration), puis changer les couleurs dans **Thème du client** et vérifier une
   liste, une fiche, le tableau de bord et Studio.
3. Dans suitecrm-core : `make test@regression`.

## Nouvelle version de SuiteCRM

1. Recompiler le module contre la nouvelle version (`make front@build`).
2. Vérifier le rail sur tous les écrans : c'est la partie fragile (voir
   [Fonctionnement](fonctionnement.md#points-dattention)).
3. Vérifier que les couleurs remplacées (`AngularTheme::COLOURS`, `LegacyTheme`) sont
   toujours celles de SuiteCRM.

## Publier une version

Pull request relue, puis merge (squash) : release-please ouvre la pull request
« chore(main): release x.y.z ». La merger crée le tag, la release et son paquet. Penser à la
`version` de `config/extension.php`, affichée par SuiteCRM. suitecrm-core la récupère avec
`make composer@update PACKAGES="david-grosskost/suitecrm-ext-custom-theme"`.

Mettre à jour `README.md` et `docs/` dans la même pull request que le changement qu'ils
décrivent.
