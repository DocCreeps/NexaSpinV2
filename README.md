<div align="center">

# 🎲 NexaSpin

**Application Laravel de tirage au sort — terrain d'expérimentation architecture**

[![PHP](https://img.shields.io/badge/PHP-8.3-777bb4?style=for-the-badge&logo=php&logoColor=white)](https://php.net) [![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com) [![Livewire](https://img.shields.io/badge/Livewire-4-fb70a9?style=for-the-badge&logo=livewire&logoColor=white)](https://livewire.laravel.com) [![Tailwind CSS](https://img.shields.io/badge/Tailwind%20CSS-4-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com) [![Pest](https://img.shields.io/badge/Pest-4-019733?style=for-the-badge&logo=pest&logoColor=white)](https://pestphp.com)

</div>

<br>

> Séparation Domain / Application / UI, pattern Strategy, Value Objects, et Livewire pour l'interactivité.
> Ce README documente l'état **réel** du projet, y compris ses limites actuelles. Ce n'est pas une application "finie" mais un projet d'apprentissage en cours d'itération.

<br>

## 📑 Sommaire

- [📌 Statut du projet](#-statut-du-projet)
- [🧱 Stack technique](#-stack-technique)
- [🏗️ Architecture](#️-architecture)
- [🎓 Ce que ce projet m'a servi à travailler](#-ce-que-ce-projet-ma-servi-à-travailler)
- [⚠️ Limites connues / dette technique](#️-limites-connues--dette-technique-actuelle)
- [🚀 Installation](#-installation)
- [🧪 Tests](#-tests)
- [🗺️ Pistes pour la suite](#️-pistes-pour-la-suite)

<br>

## 📌 Statut du projet

| Mode de tirage | Statut |
|---|:---:|
| 🎡 Roue classique (1 tirage direct) | ✅ Fonctionnel |
| ⚔️ Roue par élimination (tours successifs) | ✅ Fonctionnel |
| 🎯 Tirage pondéré (probabilités par poids) | 🔒 Non implémenté — verrouillé dans l'UI |

> **Pas de persistance en base de données** pour les tirages : les participants et les résultats vivent uniquement dans l'état du composant Livewire, le temps de la session. Les seules migrations présentes sont celles par défaut de Laravel (`users`, `cache`, `jobs`).

<br>

## 🧱 Stack technique

| | |
|---|---|
| **Langage / Framework** | PHP 8.3 · Laravel 13 |
| **Interactivité** | Livewire 4 — sans écrire de JS dédié |
| **Style** | Tailwind CSS 4 (via `@tailwindcss/vite`) |
| **Tests** | Pest 4 (avec plugin Laravel) |

<br>

## 🏗️ Architecture

Séparation inspirée de la Clean Architecture / DDD léger, découpée **par domaine métier** (`Draw`) plutôt que par type technique :

```
app/
├── Domain/Draw/              # Règles métier pures, sans dépendance à Laravel
│   ├── Entities/              # Draw : garantit qu'un tirage est valide
│   ├── ValueObjects/          # Participant, DrawResult (immuables)
│   ├── Collections/           # Participants (typée, itérable)
│   ├── Enums/                 # DrawType, DrawDisplay
│   ├── Strategies/            # RandomDrawStrategy, WheelDrawStrategy
│   ├── Contracts/             # DrawStrategy (interface)
│   └── Exceptions/
│
├── Application/Draw/         # Orchestration, fait le pont Domain ↔ UI
│   ├── Actions/                # RunDrawAction
│   ├── DTOs/                   # DrawData
│   ├── Factories/               # DrawFactory
│   ├── Resolver/                 # DrawStrategyResolver
│   └── Support/                   # WheelSegmentBuilder (calcul des segments SVG)
│
└── Livewire/Draw/             # Composants UI + traits partagés
    ├── WheelPage.php
    ├── EliminationWheelPage.php
    └── Concerns/                # ManagesParticipants, HandlesDraw (dont updateParticipant, édition inline)
```

**Principe directeur :**
- Le **Domain** ne connaît rien de Laravel ni de Livewire
- L'**Application** orchestre les cas d'usage
- Les composants **Livewire** gèrent uniquement l'état UI et délèguent le calcul métier

Le pattern **Strategy** permet en théorie de faire cohabiter plusieurs façons de tirer un gagnant (`RandomDrawStrategy`, `WheelDrawStrategy`, et demain une stratégie pondérée) sans toucher au reste du code.

<br>

## 🎓 Ce que ce projet m'a servi à travailler

<details>
<summary><strong>Voir la progression via l'historique de commits</strong></summary>
<br>

| Commit | Ce qu'il apporte |
|---|---|
| `init roulette v2` | Reprise d'une v1 plus simple |
| `add livewire` | Introduction de Livewire comme couche d'interactivité |
| `Domain` puis `Application` | Extraction des règles métier hors des composants Livewire |
| `Début UI` puis `debug + ajout roue visuel` | Construction de la roue en SVG généré côté serveur (`WheelSegmentBuilder`) |
| `elimination` | Mode de tirage multi-étapes avec confirmation d'animation |
| `refonte` puis `rename drawfactory` | Retour sur des noms et structure jugés bancals après coup |

</details>

**Points travaillés spécifiquement :**

- 🔹 Différencier une **Entity** (`Draw`, avec identité et invariants) d'un **Value Object** immuable (`Participant`, `DrawResult`)
- 🔹 Utiliser des **DTO** (`DrawData`) pour faire transiter des données de l'UI vers le Domain sans lui exposer les objets Livewire
- 🔹 Extraire la logique répétée entre composants Livewire dans des **traits** (`ManagesParticipants`, `HandlesDraw`) plutôt que de dupliquer
- 🔹 Générer une **roue SVG dynamiquement** en PHP (trigonométrie : coordonnées polaires → cartésiennes pour positionner segments et labels)

<br>

## ⚠️ Limites connues / dette technique actuelle

En toute transparence, l'architecture n'est pas encore cohérente de bout en bout.

#### 🔴 Bloquant

- `DrawType` n'a que deux cas (`RANDOM`, `WEIGHTED`) mais `DrawStrategyResolver` fait un `match` sur un cas `DrawType::WHEEL` **qui n'existe pas** dans l'enum — ce resolver n'est donc pas utilisable en l'état (et n'est d'ailleurs appelé nulle part dans le code).
- `DrawFactory::make()` ne gère que `DrawType::RANDOM` dans son `match` (pas de `default`, pas de cas `WEIGHTED`) : l'appeler avec un autre type lève un `UnhandledMatchError`.
- **`composer run setup` casse sur un clone frais** : `.env.example` définit désormais `DB_CONNECTION=null` par défaut, mais le script enchaîne quand même `php artisan migrate --force`, qui échoue avec `Database connection [null] not configured.` Il faut soit repasser `DB_CONNECTION` à `sqlite` avant de lancer `migrate`, soit retirer cette étape du script tant que l'app ne persiste rien.
- `HandlesDraw` force `DrawType::RANDOM` pour tous les composants, y compris la roue — `WheelDrawStrategy` existe mais n'est donc **jamais réellement invoquée** via ce chemin.

#### 🟡 Fonctionnel mais incomplet

- `WheelDrawStrategy` est pour l'instant une copie conforme de `RandomDrawStrategy` (`$participants->random()`) : c'est un **stub** en attente de vraie logique.
- Le **tirage pondéré n'est pas implémenté** : `Participant::$weight` existe et `DrawTypeNotSupportedException` est prête à être levée, mais aucune `WeightedDrawStrategy` n'a encore été écrite.
- L'entité `Draw` (Domain) n'est appelée par **aucun code applicatif actuel** : `RunDrawAction` travaille directement avec `Participants` + une `Strategy`, sans jamais passer par cette entité. Sa règle métier (minimum 2 participants) n'est donc pas appliquée sur le chemin réellement utilisé.
- `ManagesParticipants::updateParticipant()` (édition inline d'un participant) a été ajoutée mais n'est encore documentée nulle part dans ce README ni couverte par un test.

#### ⚪ Dette esthétique

- Namespace/dossier incohérents sur `DrawStrategyResolver` : le namespace déclaré est `App\Application\Draw\Resolvers` (pluriel) alors que le dossier réel est `Resolver` (singulier).

> Ces points sont volontairement listés ici plutôt que masqués : ils font partie du prochain cycle de refactor.

<br>

## 🚀 Installation

```bash
git clone https://github.com/DocCreeps/NexaSpinV2.git
cd NexaSpinV2

composer install
cp .env.example .env
php artisan key:generate

touch database/database.sqlite
php artisan migrate

npm install
npm run build
```

**Alternative :** un script `composer run setup` regroupe ces étapes (`.env`, `key:generate`, `migrate --force`, `npm install --ignore-scripts`, `npm run build`).

> **⚠️ Ce script échoue actuellement tel quel sur un clone frais.** `.env.example` définit `DB_CONNECTION=null` par défaut, mais le script lance quand même `migrate --force`, qui plante avec `Database connection [null] not configured.` En attendant un correctif, force `DB_CONNECTION=sqlite` dans ton `.env` (et `touch database/database.sqlite` si le fichier n'existe pas) avant de lancer `composer run setup` ou `migrate`.

**Lancement en local** (serveur + build front en parallèle) :

```bash
composer run dev
```

L'application est ensuite accessible sur **[http://localhost:8000](http://localhost:8000)**.

<br>

## 🧪 Tests

Le projet utilise **Pest**, avec une couverture qui cible surtout la couche Livewire et Application :

```bash
composer test
```

**Zones couvertes :**
- ✅ Ajout / suppression de participants
- ✅ Exécution d'un tirage simple
- ✅ Déroulé complet d'une élimination (tour par tour jusqu'au dernier survivant)
- ✅ Chargement des routes
- ✅ Génération des segments SVG

> ⚠️ **État réel de la suite (2ᵉ audit) :** les tests obsolètes de l'audit précédent ont été corrigés/remplacés (classes supprimées d'une ancienne architecture, mauvaise méthode testée sur le flux d'élimination, nommage `InvalidDrawException`). Mais un refactor plus récent de `DrawFactory` (passée de statique à méthode d'instance) et `RunDrawAction` (constructeur exigeant désormais `DrawFactory $factory`) a fait régresser deux fichiers : `RunDrawActionTest.php` et `DrawFactoryTest.php` instancient encore ces classes à l'ancienne (`new RunDrawAction()`, `DrawFactory::make()` en statique) et échouent de nouveau. Correctif en attente d'intégration.

<br>

## 🗺️ Pistes pour la suite

- [ ] Ajouter le cas `WHEEL` à l'enum `DrawType` (ou supprimer `DrawStrategyResolver` s'il fait doublon avec `DrawFactory`)
- [ ] Implémenter `WeightedDrawStrategy` (tirage pondéré par `Participant::$weight`)
- [ ] Différencier réellement `WheelDrawStrategy` de `RandomDrawStrategy`
- [ ] Aligner namespace et dossier de `DrawStrategyResolver` (`Resolver/` vs `App\...\Resolvers`)
- [ ] Corriger `composer run setup` pour qu'il fonctionne avec `DB_CONNECTION=null` par défaut (retirer `migrate`, ou forcer `sqlite` avant)
- [ ] Remettre à jour `RunDrawActionTest.php` et `DrawFactoryTest.php` suite au passage de `DrawFactory::make()` en méthode d'instance
- [ ] Documenter et tester `ManagesParticipants::updateParticipant()` (édition inline)
- [ ] Persistance optionnelle de l'historique des tirages

<br>

<div align="center">

Fait avec 🎲 par [DocCreeps](https://github.com/DocCreeps)

</div>
