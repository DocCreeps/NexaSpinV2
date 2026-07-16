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
- [🧪 Tests et qualité de code](#-tests-et-qualité-de-code)
- [🗺️ Pistes pour la suite](#️-pistes-pour-la-suite)

<br>

## 📌 Statut du projet

| Mode de tirage | Statut |
|---|:---:|
| 🎡 Roue classique (1 tirage direct) | ✅ Fonctionnel |
| ⚔️ Roue par élimination (tours successifs) | ✅ Fonctionnel |
| 🎯 Tirage pondéré (probabilités par poids) | ⚠️ Fonctionnel, mais bug connu (poids ignorés en prod — [détail](#️-limites-connues--dette-technique-actuelle)) |
| 🪙 Pile ou face | 🔒 Non implémenté — carte visible mais grisée sur l'accueil |
| 👥 Tirage par équipes | 🔒 Non implémenté — carte visible mais grisée sur l'accueil |

> Les 5 modes existent déjà comme entrées dans `DrawModeType` (Application) — `COIN_FLIP` et `TEAMS` ont chacun une `icon`, un `title` et une `description` définis pour la page d'accueil, mais `route: null` et `available: false` : la façade UI est prête, la logique Domain/Livewire derrière ne l'est pas encore.

> **Pas de persistance en base de données** pour les tirages : les participants et les résultats vivent uniquement dans l'état du composant Livewire, le temps de la session. Les seules migrations présentes sont celles par défaut de Laravel (`users`, `cache`, `jobs`), et elles ne sont pas requises pour faire tourner l'application.

> **Déploiement continu** : un workflow GitHub Actions synchronise `master` vers un VPS via rsync/SSH à chaque push (voir [Limites connues](#️-limites-connues--dette-technique-actuelle)).

<br>

## 🧱 Stack technique

| | |
|---|---|
| **Langage / Framework** | PHP 8.3 · Laravel 13 |
| **Interactivité** | Livewire 4 — sans écrire de JS dédié |
| **Style** | Tailwind CSS 4 (via `@tailwindcss/vite`) |
| **Tests** | Pest 4 (avec plugin Laravel) |
| **Analyse statique** | Larastan / PHPStan niveau 5 |
| **Style de code** | Laravel Pint |
| **CI/CD** | GitHub Actions → déploiement rsync sur VPS à chaque push sur `master` |

<br>

## 🏗️ Architecture

Séparation inspirée de la Clean Architecture / DDD léger, découpée **par domaine métier** (`Draw`) plutôt que par type technique :

```
app/
├── Domain/Draw/              # Règles métier pures, sans dépendance à Laravel
│   ├── Entities/               # Draw : garantit qu'un tirage est valide (min. 2 participants)
│   ├── ValueObjects/           # Participant (nom + poids), DrawResult (immuables)
│   ├── Collections/            # Participants (typée, itérable)
│   ├── Enums/                  # DrawType (Random, Weighted), DrawDisplay
│   ├── Strategies/             # RandomDrawStrategy, WeightedDrawStrategy
│   ├── Contracts/              # DrawStrategy (interface)
│   └── Exceptions/
│
├── Application/Draw/         # Orchestration, fait le pont Domain ↔ UI
│   ├── Actions/                 # RunDrawAction (construit l'entité Draw, délègue l'exécution)
│   ├── DTOs/                    # DrawData, DrawMode
│   ├── Resolvers/                # DrawStrategyResolver (point d'entrée unique)
│   └── Support/                    # WheelSegmentBuilder (calcul des segments SVG)
│
└── Livewire/Draw/             # Composants UI + traits partagés
    ├── WheelPage.php
    ├── EliminationWheelPage.php
    ├── WeightedWheelPage.php
    └── Concerns/                 # ManagesParticipants, HandlesDraw
```

**Principe directeur :**
- Le **Domain** ne connaît rien de Laravel ni de Livewire
- L'**Application** orchestre les cas d'usage
- Les composants **Livewire** gèrent uniquement l'état UI et délèguent le calcul métier

Le pattern **Strategy** permet de faire cohabiter plusieurs façons de tirer un gagnant (`RandomDrawStrategy`, `WeightedDrawStrategy`) sans toucher au reste du code. `RunDrawAction` construit désormais l'entité `Draw` (Domain) puis lui délègue l'exécution du tirage via **Double Dispatch** — l'invariant "minimum 2 participants" est donc appliqué sur le chemin réellement utilisé par l'application.

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
| `WeightedDrawStrategy + WeightedWheelPage` | Implémentation du tirage pondéré (algorithme "Roulette Wheel Selection") et de son écran dédié |
| `wire Draw entity into RunDrawAction` | `RunDrawAction` passe enfin par l'entité `Draw` (Double Dispatch) au lieu de contourner ses invariants |
| `test updateParticipant + Larastan + deploy.yml` | Couverture de l'édition inline, ajout de l'analyse statique et d'un pipeline de déploiement continu |

</details>

**Points travaillés spécifiquement :**

- 🔹 Différencier une **Entity** (`Draw`, avec identité et invariants) d'un **Value Object** immuable (`Participant`, `DrawResult`)
- 🔹 Utiliser des **DTO** (`DrawData`) pour faire transiter des données de l'UI vers le Domain sans lui exposer les objets Livewire
- 🔹 Extraire la logique répétée entre composants Livewire dans des **traits** (`ManagesParticipants`, `HandlesDraw`) plutôt que de dupliquer
- 🔹 Générer une **roue SVG dynamiquement** en PHP (trigonométrie : coordonnées polaires → cartésiennes pour positionner segments et labels)
- 🔹 Implémenter un **tirage pondéré** via l'algorithme "Roulette Wheel Selection" (tirage d'un nombre dans `[1, somme des poids]`, puis parcours cumulatif des participants)
- 🔹 Appliquer le pattern **Double Dispatch** pour que la construction d'une entité (`Draw`) garantisse ses invariants avant que la stratégie ne s'exécute ("Always-Valid Entity")
- 🔹 Repérer et corriger du **code mort / dupliqué** (deux mécanismes concurrents de résolution de stratégie) plutôt que de les laisser cohabiter indéfiniment
- 🔹 Tester des composants **Livewire** correctement, y compris les pièges de l'API de test (`->instance()` pour appeler une méthode custom du composant, plutôt qu'un appel direct proxyé vers la réponse HTTP)
- 🔹 Mettre en place une **analyse statique** (Larastan/PHPStan niveau 5) et documenter les exceptions volontaires (`ignoreErrors`) plutôt que de baisser le niveau globalement
- 🔹 Comprendre l'impact de **la configuration d'environnement** (`.env.example`, scripts `composer`) sur l'expérience d'un clone frais, pas seulement sur le code applicatif

<br>

## ⚠️ Limites connues / dette technique actuelle

En toute transparence, l'architecture n'est pas encore cohérente de bout en bout. Aucun point bloquant à ce jour — uniquement des zones fonctionnelles mais incomplètes, assumées comme telles.

#### 🔴 Bug identifié

- **Le tirage pondéré ignore silencieusement les poids en production.** `HandlesDraw::executeDraw()` construit le `DrawData` avec `type: DrawType::RANDOM` codé en dur, au lieu d'appeler `$this->drawType()`. Or `WeightedWheelPage::drawType()` (qui retourne `DrawType::WEIGHTED`) n'est référencé nulle part ailleurs dans le code : c'est du code mort. Résultat, `DrawStrategyResolver` résout toujours `RandomDrawStrategy`, qui ignore `Participant::$weight` — le tirage pondéré se comporte donc comme un tirage uniforme classique, alors même que l'UI laisse configurer des poids. Les tests actuels ne le détectent pas car ils vérifient seulement qu'un gagnant fait partie de la liste, jamais que la distribution respecte les poids. Correctif attendu : faire appeler `$this->drawType()` par `executeDraw()` au lieu du littéral `DrawType::RANDOM`.

#### 🟡 Fonctionnel mais incomplet

- Sur la roue pondérée, **les segments visuels restent de taille égale** quel que soit le poids : `WheelSegmentBuilder` divise toujours le cercle en `360 / total`, sans tenir compte de `Participant::$weight`. Seule la probabilité réelle de tirage (côté Domain) reflète le poids — c'est d'ailleurs documenté tel quel dans le docblock de `WeightedWheelPage`. Un participant à poids 10 a autant de chances de gagner qu'annoncé, mais sa part sur la roue n'est pas visuellement plus grande.
- Le pipeline `deploy.yml` **synchronise et déploie directement sur push vers `master`**, sans exécuter `composer test` ni `composer run analyse` au préalable : aucune porte de qualité n'empêche un commit cassé d'atteindre le VPS.
- Aucune persistance en base pour l'historique des tirages (choix assumé pour l'instant, voir [Statut du projet](#-statut-du-projet)) : un redémarrage ou un rafraîchissement de session fait perdre les résultats passés.

#### ✅ Corrigé depuis le dernier audit

- Le **tirage pondéré est implémenté au niveau Domain** : `WeightedDrawStrategy` applique l'algorithme "Roulette Wheel Selection", branchée dans `DrawStrategyResolver` et exposée via l'écran `WeightedWheelPage` (route `/roue-ponderee`), avec sa propre suite de tests unitaires. ⚠️ Reste non câblée de bout en bout — voir le bug ci-dessus.
- `RunDrawAction` **passe désormais par l'entité `Draw`** (Domain) avant de déléguer à la stratégie choisie (Double Dispatch) : son invariant métier (minimum 2 participants) est donc appliqué sur le chemin réellement utilisé, et non plus contourné.
- `ManagesParticipants::updateParticipant()` (édition inline d'un participant) est désormais **couverte par des tests dédiés** (nom vide, doublon insensible à la casse, verrouillage une fois l'élimination démarrée).
- L'ancien duo `DrawFactory` / `DrawStrategyResolver` (deux mécanismes concurrents pour résoudre une stratégie) a été fusionné en un seul `DrawStrategyResolver`, exhaustif sur les cas réels de `DrawType`, testé, sans incohérence namespace/dossier.
- La suite de tests est repassée entièrement au vert : testsuite `Unit` fantôme retirée de `phpunit.xml`, script `composer test` réparé, et un test Livewire (`EliminationWheelPageTest`) corrigé pour appeler `->instance()->started()` plutôt que de proxyer par erreur vers la réponse HTTP wrappée par `Testable`.
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

## 🧪 Tests et qualité de code

Le projet utilise **Pest**, avec une couverture qui cible surtout la couche Livewire, Application et Domain :

```bash
composer test
```

**Zones couvertes :**
- ✅ Ajout / suppression / édition inline de participants (y compris pondération)
- ✅ Résolution de stratégie et exécution d'un tirage (`DrawStrategyResolver`, `RunDrawAction`)
- ✅ Tirage pondéré (`WeightedDrawStrategy`) et son écran Livewire dédié — au niveau unitaire uniquement, pas de test de bout en bout qui vérifierait la distribution réelle (voir le bug ci-dessus)
- ✅ Déroulé complet d'une élimination (tour par tour jusqu'au dernier survivant, restart)
- ✅ Chargement des routes
- ✅ Génération des segments SVG

> Le code compte aujourd'hui un peu plus de **80 déclarations de test** (`it()` / `test()`), contre 66 lors du dernier audit — le chiffre exact d'assertions dépend des jeux de données (`->with()`) exécutés par `composer test`.

**Analyse statique** (Larastan / PHPStan, niveau 5) :

```bash
composer run analyse
```

<br>

## 🗺️ Pistes pour la suite

- [x] ~~Fusionner `DrawFactory` / `DrawStrategyResolver` en un point de résolution unique~~
- [x] ~~Aligner namespace et dossier du Resolver~~
- [x] ~~Réparer la suite de tests (`phpunit.xml`, script `composer test`, test Livewire cassé)~~
- [x] ~~Corriger `composer run setup` pour un clone frais~~
- [x] ~~Implémenter `WeightedDrawStrategy` (tirage pondéré par `Participant::$weight`)~~
- [x] ~~Documenter et tester `ManagesParticipants::updateParticipant()` (édition inline)~~
- [x] ~~Faire réellement passer les tirages par l'entité `Draw` (Domain) pour que son invariant (min. 2 participants) soit appliqué sur le chemin utilisé~~
- [ ] **Corriger `HandlesDraw::executeDraw()`** pour qu'il utilise `$this->drawType()` au lieu de `DrawType::RANDOM` codé en dur (le tirage pondéré n'applique actuellement pas les poids en conditions réelles)
- [ ] Faire varier la taille des segments de la roue pondérée selon `Participant::$weight` (actuellement `WheelSegmentBuilder` divise toujours en parts égales)
- [ ] Implémenter le mode **Pile ou face** (`DrawModeType::COIN_FLIP`, déjà scaffoldé côté UI)
- [ ] Implémenter le mode **Tirage par équipes** (`DrawModeType::TEAMS`, déjà scaffoldé côté UI — répartition en groupes de taille égale)
- [ ] Ajouter une porte de qualité (`composer test` + `composer run analyse`) dans `deploy.yml` avant le déploiement
- [ ] Persistance optionnelle de l'historique des tirages

<br>

<div align="center">

Fait avec 🎲 par [DocCreeps](https://github.com/DocCreeps)

</div>
