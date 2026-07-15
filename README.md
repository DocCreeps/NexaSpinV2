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

> **Pas de persistance en base de données** pour les tirages : les participants et les résultats vivent uniquement dans l'état du composant Livewire, le temps de la session. Les seules migrations présentes sont celles par défaut de Laravel (`users`, `cache`, `jobs`), et elles ne sont pas requises pour faire tourner l'application.

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
│   ├── Strategies/            # RandomDrawStrategy
│   ├── Contracts/             # DrawStrategy (interface)
│   └── Exceptions/
│
├── Application/Draw/         # Orchestration, fait le pont Domain ↔ UI
│   ├── Actions/                # RunDrawAction
│   ├── DTOs/                   # DrawData
│   ├── Resolvers/               # DrawStrategyResolver (point d'entrée unique)
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

Le pattern **Strategy** permet en théorie de faire cohabiter plusieurs façons de tirer un gagnant (`RandomDrawStrategy`, et demain une stratégie pondérée) sans toucher au reste du code.

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
| `merge factory/resolver` | Fusion des deux mécanismes concurrents de résolution de stratégie en un seul, suite à audit |
| `fix setup script + tests` | Réparation du script `composer run setup`, de `phpunit.xml` et d'un test Livewire cassé |

</details>

**Points travaillés spécifiquement :**

- 🔹 Différencier une **Entity** (`Draw`, avec identité et invariants) d'un **Value Object** immuable (`Participant`, `DrawResult`)
- 🔹 Utiliser des **DTO** (`DrawData`) pour faire transiter des données de l'UI vers le Domain sans lui exposer les objets Livewire
- 🔹 Extraire la logique répétée entre composants Livewire dans des **traits** (`ManagesParticipants`, `HandlesDraw`) plutôt que de dupliquer
- 🔹 Générer une **roue SVG dynamiquement** en PHP (trigonométrie : coordonnées polaires → cartésiennes pour positionner segments et labels)
- 🔹 Repérer et corriger du **code mort / dupliqué** (deux mécanismes concurrents de résolution de stratégie) plutôt que de les laisser cohabiter indéfiniment
- 🔹 Tester des composants **Livewire** correctement, y compris les pièges de l'API de test (`->instance()` pour appeler une méthode custom du composant, plutôt qu'un appel direct proxyé vers la réponse HTTP)
- 🔹 Comprendre l'impact de **la configuration d'environnement** (`.env.example`, scripts `composer`) sur l'expérience d'un clone frais, pas seulement sur le code applicatif

<br>

## ⚠️ Limites connues / dette technique actuelle

En toute transparence, l'architecture n'est pas encore cohérente de bout en bout. Aucun point bloquant à ce jour — uniquement des zones fonctionnelles mais incomplètes, assumées comme telles.

#### 🟡 Fonctionnel mais incomplet

- Le **tirage pondéré n'est pas implémenté** : `Participant::$weight` existe et `DrawTypeNotSupportedException` est levée proprement par `DrawStrategyResolver`, mais aucune `WeightedDrawStrategy` n'a encore été écrite.
- L'entité `Draw` (Domain) n'est appelée par **aucun code applicatif actuel** : `RunDrawAction` travaille directement avec `Participants` + une `Strategy`, sans jamais passer par cette entité. Sa règle métier (minimum 2 participants) n'est donc pas appliquée sur le chemin réellement utilisé.
- `ManagesParticipants::updateParticipant()` (édition inline d'un participant) est fonctionnelle mais n'est encore documentée nulle part ailleurs dans ce README ni couverte par un test dédié.

#### ✅ Corrigé depuis le dernier audit

- L'ancien duo `DrawFactory` / `DrawStrategyResolver` (deux mécanismes concurrents pour résoudre une stratégie, dont l'un référençait `DrawType::WHEEL`, un cas d'enum inexistant) a été fusionné en un seul `DrawStrategyResolver`, exhaustif sur les cas réels de `DrawType` (`RANDOM`, `WEIGHTED`), testé, sans incohérence namespace/dossier.
- `WheelDrawStrategy` (stub copie conforme de `RandomDrawStrategy`, jamais réellement branchée dans l'application) a été retirée du domaine plutôt que maintenue comme code mort.
- La suite de tests est repassée entièrement au vert (**66 tests**) : testsuite `Unit` fantôme retirée de `phpunit.xml`, script `composer test` réparé, et un test Livewire (`EliminationWheelPageTest`) corrigé pour appeler `->instance()->started()` plutôt que de proxyer par erreur vers la réponse HTTP wrappée par `Testable`.
- Le script `composer test` échouait avec les versions de Composer antérieures à 2.8 à cause d'un marqueur (`@no_additional_args`) non reconnu, passé tel quel comme argument à `artisan config:clear`. Retiré pour rester compatible avec toutes les versions de Composer.
- `composer run setup` échouait sur un clone frais (`.env.example` en `DB_CONNECTION=null` mais le script lançait quand même `migrate --force`). L'étape `migrate` a été retirée du script, cohérent avec le fait que l'application ne persiste rien en base à ce stade.

> Ces points sont volontairement listés ici plutôt que masqués : ils font partie du prochain cycle de refactor.

<br>

## 🚀 Installation

```bash
git clone https://github.com/DocCreeps/NexaSpinV2.git
cd NexaSpinV2

composer install
cp .env.example .env
php artisan key:generate

npm install
npm run build
```

**Alternative :** un script `composer run setup` regroupe ces étapes (`.env`, `key:generate`, `npm install --ignore-scripts`, `npm run build`). Fonctionne tel quel sur un clone frais, sans configuration de base de données nécessaire.

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
- ✅ Ajout / suppression / édition inline de participants
- ✅ Résolution de stratégie et exécution d'un tirage (`DrawStrategyResolver`, `RunDrawAction`)
- ✅ Déroulé complet d'une élimination (tour par tour jusqu'au dernier survivant, restart)
- ✅ Chargement des routes
- ✅ Génération des segments SVG

**État réel de la suite : 66 tests, tous au vert.**

<br>

## 🗺️ Pistes pour la suite

- [x] ~~Fusionner `DrawFactory` / `DrawStrategyResolver` en un point de résolution unique~~
- [x] ~~Aligner namespace et dossier du Resolver~~
- [x] ~~Réparer la suite de tests (`phpunit.xml`, script `composer test`, test Livewire cassé)~~
- [x] ~~Corriger `composer run setup` pour un clone frais~~
- [ ] Implémenter `WeightedDrawStrategy` (tirage pondéré par `Participant::$weight`)
- [ ] Documenter et tester `ManagesParticipants::updateParticipant()` (édition inline)
- [ ] Faire réellement passer les tirages par l'entité `Draw` (Domain) pour que son invariant (min. 2 participants) soit appliqué sur le chemin utilisé
- [ ] Persistance optionnelle de l'historique des tirages

<br>

<div align="center">

Fait avec 🎲 par [DocCreeps](https://github.com/DocCreeps)

</div>
