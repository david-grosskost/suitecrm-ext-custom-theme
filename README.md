# Thème vertical (`custom-theme`)

Le menu horizontal de SuiteCRM devient un **rail vertical d'icônes** à gauche, et toute
l'interface prend **les couleurs du client** : l'en-tête, le menu, les écrans de SuiteCRM 8
(listes, fiches, administration) comme ses écrans historiques (tableau de bord, Studio).
Les couleurs se choisissent dans l'administration.

**Les écrans de SuiteCRM ne sont pas modifiés** : listes, fiches, Studio et administration
gardent leurs comportements et leurs raccourcis. L'extension ne change que le cadre et les
couleurs.

| Fiche | |
|---|---|
| Version de SuiteCRM | 8.10 (`>=8.10 <9`) |
| Types | `ui` |
| Dépend de | — |
| Installée d'office | non |

## Installer

Sur une instance, par **Administration → Chargeur de module** : l'extension y figure dans
la liste des paquets disponibles, cliquer sur **Installer**, puis recharger l'interface
(Ctrl+F5). Depuis le terminal, dans suitecrm-core :

```bash
make extensions@install SLUGS="custom-theme"
```

La désinstallation se fait au même endroit.

## Documentation

| Page | Contenu |
|---|---|
| [Utilisation](docs/utilisation.md) | le rail, les icônes, choisir les couleurs |
| [Fonctionnement](docs/fonctionnement.md) | module Angular, recoloration dans le navigateur, points d'entrée, sécurité |
| [Développement](docs/developpement.md) | modifier le module, vérifier, tester en local, montées de version de SuiteCRM |
| [Dépannage](docs/depannage.md) | couleurs qui ne changent pas, menu vide, rail cassé après une mise à jour |

Les commandes, la CI et le format des extensions sont décrits dans la documentation de
[suitecrm-devkit](https://github.com/david-grosskost/suitecrm-devkit#documentation).
