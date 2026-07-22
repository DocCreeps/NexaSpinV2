<div align="center">
<h1>🎲 NexaSpin </h1>


**Un projet d'apprentissage : refaire un tirage au sort en s'imposant une architecture propre**

[![PHP](https://img.shields.io/badge/PHP-8.4-777bb4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![Livewire](https://img.shields.io/badge/Livewire-4-fb70a9?style=for-the-badge&logo=livewire&logoColor=white)](https://livewire.laravel.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind%20CSS-4-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![Pest](https://img.shields.io/badge/Pest-4-019733?style=for-the-badge&logo=pest&logoColor=white)](https://pestphp.com)
</div>


## 📖 Sommaire
- [📌 Statut du projet](#-statut-du-projet)
- [🧱 Stack technique](#-stack-technique)
- [🏗️ Architecture](#️-architecture)
  - [Découpage par domaine métier](#découpage-par-domaine-métier)
  - [Principe directeur](#principe-directeur)
  - [Patterns clés](#patterns-clés)
- [🎯 Pourquoi cette architecture ?](#-pourquoi-cette-architecture-pour-un-tirage-au-sort-)
- [🔍 Exemple concret : un tirage pondéré](#-ce-qui-se-passe-réellement-quand-on-lance-un-tirage-pondéré)
- [⚠️ Dette technique et limites connues](#-dette-technique-et-limites-connues)
  - [Fonctionnel mais incomplet](#-fonctionnel-mais-incomplet)
  - [Corrigé récemment](#-corrigé-récemment)
- [📚 Ce que ce projet m’a servi à travailler](#-ce-que-ce-projet-ma-servi-à-travailler)
- [🚀 Installation](#-installation)
- [🧪 Tests et qualité de code](#-tests-et-qualité-de-code)
- [🗺️ Pistes pour la suite](#️-pistes-pour-la-suite)
- [💡 Pourquoi documenter les limites ?](#-pourquoi-ce-readme-documente-t-il-les-limites-)

---

## 📌 Statut du projet
*Terrain d’expérimentation : une fonctionnalité simple (tirage au sort) pour se concentrer sur l’architecture.*

| Mode | Description | État | Détails |
| --- | --- | --- | --- |
| 🎡 Roue classique | Tirage aléatoire simple et rapide pour désigner un seul gagnant. | ✅ Fonctionnel |  |
| ⚔️ Roue par élimination | Élimination progressive des participants jusqu'à ce qu'il n'en reste qu'un. | ✅ Fonctionnel |  |
| 🎯 Roue pondérée | Tirage aléatoire où chaque participant a un poids personnalisé pour influencer les résultats. | ✅ Fonctionnel | |
| 🪙 Pile ou face | Simule un lancer de pièce équitable entre deux options. | ⚠️ Fonctionnel mais non testé | Carte active sur l’accueil, route et composant Livewire opérationnels. Aucun test écrit (voir [Fonctionnel mais incomplet](#-fonctionnel-mais-incomplet)). |
| 👥 Tirage par équipes | Permet de former des équipes de manière aléatoire (non encore développé). | 🔒 Non implémenté | Carte visible mais grisée sur l’accueil. |



> **Aucune persistance en base de données** : les participants et résultats vivent dans l’état des composants Livewire (session uniquement).
> **Déploiement continu** : synchronisation automatique de `master` vers un VPS via GitHub Actions (sans porte de qualité, voir [Dette technique](#-dette-technique-et-limites-connues)).

---

## 🧱 Stack technique

| Catégorie | Technologies |
|----------|--------------|
| **Backend** | PHP 8.4 · Laravel 13 |
| **Interactivité** | Livewire 4 (sans JS dédié) |
| **Frontend** | Tailwind CSS 4 (via `@tailwindcss/vite`) |
| **Tests** | Pest 4 (avec plugin Laravel) |
| **Analyse statique** | Larastan / PHPStan (niveau 5) |
| **Style de code** | Laravel Pint |
| **CI/CD** | GitHub Actions → déploiement `rsync` sur VPS |

---

## 🏗️ Architecture
*Séparation Domain / Application / UI, inspirée de la Clean Architecture et du DDD léger.*

### Découpage par domaine métier (`Draw`)
```
app/
├── Domain/Draw/               # Règles métier pures (0 dépendance à Laravel)
│   ├── Entities/              # Draw (garantit l'invariant "≥ 2 participants")
│   ├── ValueObjects/          # Participant (nom + poids), DrawResult (immuables)
│   ├── Collections/           # Participants (typée, itérable)
│   ├── Enums/                 # DrawType (Random, Weighted), DrawDisplay
│   ├── Strategies/            # RandomDrawStrategy, WeightedDrawStrategy
│   ├── Contracts/             # DrawStrategy (interface)
│   └── Exceptions/
│
├── Application/Draw/         # Orchestration (pont Domain ↔ UI)
│   ├── Actions/               # RunDrawAction (construit `Draw`, délègue à la stratégie)
│   ├── DTOs/                  # DrawData, DrawMode
│   ├── Resolvers/             # DrawStrategyResolver (point d'entrée unique)
│   └── Support/               # WheelSegmentBuilder (calcul des segments SVG)
│
└── Livewire/Draw/             # Composants UI (état + délégation)
    ├── WheelPage.php
    ├── EliminationWheelPage.php
    ├── WeightedWheelPage.php
    └── Concerns/              # ManagesParticipants, HandlesDraw (traits partagés)
```

### Principe directeur
- **Domain** : Ignore Laravel/Livewire. Testable en PHP pur.
- **Application** : Orchestre les cas d’usage (ex. : `RunDrawAction` construit `Draw` puis appelle `$strategy->draw()`).
- **Livewire** : Gère uniquement l’état UI et délègue la logique métier.

### Patterns clés
- **Strategy** : `RandomDrawStrategy` et `WeightedDrawStrategy` implémentent `DrawStrategy`. Le branchement se fait en un seul point (`DrawStrategyResolver::resolve()`).
- **Double Dispatch** : `Draw::execute($strategy)` délègue à la stratégie sans connaître son implémentation.
- **Always-Valid Entity** : `Draw` valide ses invariants (ex. : ≥ 2 participants) dans son constructeur.
- **DTO** : `DrawData` transmet les données de l’UI au Domain sans couplage.

---

## 🎯 Pourquoi cette architecture pour un tirage au sort ?
*Un tirage au sort tient en 30 lignes avec `array_rand()`. Découper le code en couches ajoute de la complexité... mais c’est voulus.*

### Avantages
- **Testabilité** : Le Domain se teste sans base de données, sans HTTP, sans Livewire.
- **Portabilité** : `Draw`, `Participant`, `RandomDrawStrategy` pourraient être copiés dans un projet PHP sans Laravel.
- **Évolutivité** : Ajouter un nouveau type de tirage (ex. : équipes) ne nécessite que :
  1. Une nouvelle stratégie (`TeamDrawStrategy`).
  2. Une entrée dans `DrawType`.
  3. Une route et un composant Livewire.

### Coûts assumés
- **Plus de fichiers** : 1 fonctionnalité simple = plus de couches, plus d’indirection.
- **Courbe d’apprentissage** : Un nouveau contributeur doit comprendre la structure avant de modifier le code.
- **Risque de bugs** : Plus de couches = plus d’endroits où une intention peut se perdre (ex. : le bug du tirage pondéré, voir ci-dessous).

> **Le compromis** : Ce projet n’est pas optimisé pour la livraison rapide, mais pour **comprendre où cette architecture aide et où elle coûte**.

---

## 🔍 Ce qui se passe réellement quand on lance un tirage pondéré
*Exemple concret de trajet à travers les couches :*

1. **`WeightedWheelPage`** (Livewire) : Surcharge `drawType()` pour retourner `DrawType::WEIGHTED`.
2. **`HandlesDraw::executeDraw()`** : Construit un `DrawData` (DTO) avec les participants, leurs poids, et appelle `$this->drawType()` pour transmettre le bon type.
3. **`RunDrawAction::execute()`** :
   - Demande à `DrawStrategyResolver` la stratégie pour `DrawType::WEIGHTED`, qui résout bien `WeightedDrawStrategy`.
   - Construit l’entité `Draw` à partir de `$data->participantsCollection()`. Le constructeur valide qu’il y a ≥ 2 participants.
4. **`Draw::execute($strategy)`** : Délègue à `$strategy->draw($this->participants)` (Double Dispatch).
5. **`WeightedDrawStrategy::draw()`** :
   - Calcule la somme des poids.
   - Tire un `random_int(1, $totalWeight)`. 
   - Parcourt les participants en cumulant leurs poids jusqu’à dépasser le nombre tiré (algorithme *Roulette Wheel Selection*).
6. **Résultat** : Le `DrawResult` (Value Object immuable) remonte jusqu’à Livewire pour mise à jour de l’affichage.
   - **En parallèle** : `WheelSegmentBuilder` calcule la taille des segments SVG **proportionnellement aux poids** (les parts inégales sont désormais visibles à l’écran).

---

## ⚠️ Dette technique et limites connues

### 🟡 Fonctionnel mais incomplet
- **Aucun test sur Pile ou face** : `FlipCoinAction`, `RandomCoinFlipStrategy`, et `CoinFlipPage` n’ont **aucune couverture de test**, malgré une fonctionnalité déjà active en production.
- **Déploiement sans porte de qualité** : Le workflow GitHub Actions déploie directement sur `master` sans exécuter `composer test` ou `composer run analyse`.
- **Pas de persistance** : Les tirages ne sont pas sauvegardés en base de données (choix assumé pour l’instant).
- **Tirage par équipes non implémenté** : Carte visible mais désactivée (`available: false`) sur l’accueil.

### ✅ Corrigé récemment
- **Bug du tirage pondéré résolu** : `HandlesDraw::executeDraw()` appelle désormais `$this->drawType()` au lieu d’un `DrawType::RANDOM` codé en dur ; `WeightedDrawStrategy` est bien invoquée.
- **Segments SVG proportionnels aux poids** : `WheelSegmentBuilder` calcule désormais la taille des parts selon `Participant::$weight`.
- **Implémentation du Pile ou face** : `FlipCoinAction`, `RandomCoinFlipStrategy`, `CoinFlipPage` et la route `/pile-ou-face` sont en place et actifs sur l’accueil.
- Fusion de `DrawFactory` et `DrawStrategyResolver` en un seul point de résolution.
- Alignement des namespaces et dossiers.
- Réparation de la suite de tests (`phpunit.xml`, script `composer test`).
- Implémentation de `WeightedDrawStrategy` (algorithme *Roulette Wheel Selection*).
- Passage de `RunDrawAction` par l’entité `Draw` pour appliquer ses invariants.
- Correction de `composer run setup` pour un clone frais.

---

## 📚 Ce que ce projet m’a servi à travailler
*Une liste non exhaustive des concepts et bonnes pratiques appliqués.*

| Concept | Implémentation dans NexaSpin |
|---------|-------------------------------|
| **Séparation des couches** | Domain (règles métier) / Application (orchestration) / UI (Livewire). |
| **Entity vs Value Object** | `Draw` (Entity avec identité et invariants) vs `Participant`/`DrawResult` (Value Objects immuables). |
| **Pattern Strategy** | `RandomDrawStrategy` et `WeightedDrawStrategy` derrière l’interface `DrawStrategy`. |
| **Double Dispatch** | `Draw::execute($strategy)` délègue à la stratégie sans connaître son type. |
| **DTO** | `DrawData` pour transmettre des données de l’UI au Domain sans couplage. |
| **Traits PHP** | `ManagesParticipants`, `HandlesDraw` pour éviter la duplication entre composants Livewire. |
| **Génération SVG dynamique** | `WheelSegmentBuilder` calcule les coordonnées polaires → cartésiennes pour la roue. |
| **Algorithme Roulette Wheel** | Tirage pondéré via cumul des poids et `random_int`. |
| **Tests Livewire** | Couverture des composants (ajout/suppression/édition de participants, tirages). |
| **Analyse statique** | Larastan/PHPStan niveau 5 + documentation des `ignoreErrors`. |
| **CI/CD** | Déploiement automatique via GitHub Actions (à améliorer avec des portes de qualité). |

<details>
<summary><strong>📜 Historique des commits clés</strong></summary>
<br>

| Commit | Apport |
|--------|--------|
| `init roulette v2` | Reprise d’une v1 plus simple. |
| `add livewire` | Introduction de Livewire pour l’interactivité. |
| `Domain` puis `Application` | Extraction des règles métier hors des composants Livewire. |
| `Début UI` + `debug + ajout roue visuel` | Construction de la roue en SVG généré côté serveur. |
| `elimination` | Mode multi-étapes avec confirmation d’animation. |
| `refonte` + `rename drawfactory` | Retour sur des noms et une structure plus cohérents. |
| `merge factory/resolver` | Fusion des mécanismes concurrents de résolution de stratégie. |
| `fix setup script + tests` | Réparation de `composer run setup`, `phpunit.xml`, et tests Livewire. |
| `WeightedDrawStrategy + WeightedWheelPage` | Implémentation du tirage pondéré et de son écran dédié. |
| `wire Draw entity into RunDrawAction` | `RunDrawAction` passe par l’entité `Draw` (Double Dispatch). |
| `test updateParticipant + Larastan + deploy.yml` | Couverture de l’édition inline, analyse statique, et pipeline de déploiement. |
| `tirage ponderer segment = poids` | Correction du bug : les segments SVG suivent enfin les poids réels. |
| `implémentation Coin Flip` | Ajout du mode Pile ou face (`FlipCoinAction`, `RandomCoinFlipStrategy`, `CoinFlipPage`), désormais actif sur l’accueil. |

</details>

---

## 🚀 Installation

### Prérequis
- PHP 8.4
- Composer
- Node.js (pour Tailwind CSS)

### Étapes
```bash
# Cloner le dépôt
git clone https://github.com/DocCreeps/NexaSpinV2.git
cd NexaSpinV2

# Installer les dépendances PHP et générer la clé d'application
composer install
cp .env.example .env
php artisan key:generate

# Installer et builder les assets front
npm install
npm run build
```

**Alternative** : Un script `composer run setup` regroupe toutes ces étapes (sauf la configuration de base de données, non nécessaire).

### Lancement en local
```bash
composer run dev
```
L’application sera accessible sur **[http://localhost:8000](http://localhost:8000)**.

---

## 🧪 Tests et qualité de code

### Exécuter les tests
```bash
composer test
```
- **85+ déclarations de test** (Pest) couvrant :
  - Gestion des participants (ajout/suppression/édition, y compris pondération).
  - Résolution de stratégie et exécution des tirages.
  - Tirage pondéré (résultat **et** segments SVG proportionnels).
  - Déroulé complet d’une élimination.
  - Chargement des routes.
  - Génération des segments SVG.

> ⚠️ **Pile ou face n’a aucun test** malgré une implémentation active (`FlipCoinAction`, `RandomCoinFlipStrategy`, `CoinFlipPage`) — à corriger en priorité.

### Analyse statique
```bash
composer run analyse
```
- Larastan / PHPStan niveau 5.
- Les exceptions volontaires sont documentées dans `ignoreErrors`.

---

## 🗺️ Pistes pour la suite
*Par ordre de priorité.*


- [ ] **Design** :
  - Responsive, accessibilité. 
- [ ] **Ajouter des tests pour Pile ou face** :
  - `FlipCoinAction`, `RandomCoinFlipStrategy`, `CoinFlipPage`.
- [ ] **Améliorer le pipeline de déploiement** :
  - Ajouter `composer test` et `composer run analyse` comme portes de qualité dans `deploy.yml`.
- [ ] **Ajout des paris sur le résultats pile ou face** :
  - Si un tirage a la fois score de paris sous le resulatta du nombre de pile/face.
  - Ne pas mettre de paris pour tirage auto (inutile).
- [ ] **Implémenter le mode manquant** :
  - Tirage par équipes (`DrawModeType::TEAMS`).
- [ ] **Peaufiner Pile ou face** (voir `TODO`) :
  - Texte du bouton dynamique selon le mode (simple/auto).
  - Affichage du gagnant après un tirage automatique selon le max de faces obtenues.
  - Amélioration du design.
- [ ] **Persistance optionnelle** :
  - Sauvegarder l’historique des tirages en base de données (si un besoin réel émerge).

---

## 💡 Pourquoi ce README documente-t-il les limites ?
*Parce que repérer et assumer les trous fait partie de l’exercice autant que d’écrire le code.*

> *"Un README qui ne mentionne que ce qui marche est un mensonge par omission. Celui-ci liste aussi ce qui est cassé, incomplet, ou à améliorer — pour que le prochain qui lit ce code (moi y compris) sache exactement où en est le projet."*

---

<div align="center">

Fait avec 🎲 par [DocCreeps](https://github.com/DocCreeps)

</div>
