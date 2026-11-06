<?php

namespace App\Application\Home\Enums;

use App\Application\Home\DTOs\GameMode;

enum GameModeType: string
{
    case CLASSIC = 'classic';
    case ELIMINATION = 'elimination';
    case WEIGHTED = 'weighted';
    case COIN_FLIP = 'coin_flip';
    case TEAMS = 'teams';
    case TOMBOLA = 'tombola';
    case BRACKETJV = 'bracketjv';
    case POOL = 'pool';
    case NUMBER_ROULETTE = 'number_roulette';
    case DICE_421 = 'dice_421';
    case ROCK_PAPER_SCISSORS = 'rock_paper_scissors';
    case TIC_TAC_TOE = 'tic_tac_toe';
    case DICE_ROLLER = 'dice_roller';
    case SECRET_NUMBER = 'secret_number';
    case STICKS = 'sticks';
    case CURSED_BOX = 'cursed_box';

    /**
     * Transforme l'enum en objet de présentation (DTO).
     */
    public function toDto(): GameMode
    {
        return match ($this) {
            self::CLASSIC => new GameMode(
                icon: '🎡',
                title: 'Roue classique',
                description: 'Lancez la roue et désignez instantanément un gagnant parmi tous les participants.',
                route: route('draw.wheel'),
                available: true,
                color: 'from-indigo-500 to-violet-600',
                shadow: 'shadow-indigo-500/10 hover:shadow-indigo-500/20',
                category: GameModeCategory::WHEEL,
                minParticipants: 2,
                needsPlus: true,
                isSolo: false,
                metaTitle: 'Roue Classique — Tirage au Sort Instantané | NexaSpin',
                metaDescription: 'Créez votre roue de la fortune en ligne : ajoutez vos participants, lancez le tirage et désignez un gagnant en un clic. 100% gratuit, sans inscription.',
            ),
            self::ELIMINATION => new GameMode(
                icon: '⚔️',
                title: 'Roue par élimination',
                description: 'Les participants s’affrontent tour après tour jusqu’au dernier survivant.',
                route: route('draw.wheel-elimination'),
                available: true,
                color: 'from-rose-500 to-red-600',
                shadow: 'shadow-rose-500/10 hover:shadow-rose-500/20',
                category: GameModeCategory::WHEEL,
                minParticipants: 5,
                needsPlus: true,
                isSolo: false,
                metaTitle: 'Roue par Élimination — Tirage au Sort Progressif | NexaSpin',
                metaDescription: 'Éliminez vos participants un par un jusqu’au dernier survivant. Idéal pour vos concours, jeux entre amis ou sélections progressives, gratuit et sans inscription.',
            ),
            self::WEIGHTED => new GameMode(
                icon: '🎯',
                title: 'Tirage pondéré',
                description: 'Attribuez plus ou moins de chances à chaque participant grâce à un système de poids personnalisé.',
                route: route('draw.wheel-weighted'),
                available: true,
                color: 'from-emerald-500 to-teal-600',
                shadow: 'shadow-emerald-500/10 hover:shadow-emerald-500/20',
                category: GameModeCategory::WHEEL,
                minParticipants: 3,
                needsPlus: true,
                isSolo: false,
                metaTitle: 'Tirage au Sort Pondéré — Chances Personnalisées | NexaSpin',
                metaDescription: 'Donnez plus ou moins de chances à chaque participant grâce à un système de poids personnalisable. Le tirage au sort équitable et flexible, gratuit en ligne.',
            ),
            self::COIN_FLIP => new GameMode(
                icon: '🪙',
                title: 'Pile ou face',
                description: 'Le grand classique : un tirage rapide à deux issues, sans liste de participants à gérer.',
                route: route('coinflip'),
                available: true,
                color: 'from-amber-400 to-yellow-500',
                shadow: 'shadow-amber-500/10 hover:shadow-amber-500/20',
                category: GameModeCategory::OTHER,
                minParticipants: null,
                needsPlus: false,
                isSolo: false,
                metaTitle: 'Pile ou Face — Tirage Aléatoire Rapide | NexaSpin',
                metaDescription: 'Lancez une pièce virtuelle et enchaînez les tirages "Pile ou Face" en un clic. Historique des résultats inclus, 100% gratuit et sans inscription.',
            ),
            self::DICE_421 => new GameMode(
                icon: '🎲',
                title: 'Jeu du 421',
                description: 'Le grand classique du jeu de dés : lancez les 3 dés et tentez d’obtenir la combinaison 4-2-1.',
                route: route('dice.dice-421'),
                available: true,
                color: 'from-blue-500 to-cyan-600',
                shadow: 'shadow-blue-500/10 hover:shadow-blue-500/20',
                category: GameModeCategory::GAME,
                minParticipants: null,
                needsPlus: false,
                isSolo: true,
                metaTitle: 'Jeu du 421 en Ligne — Lancez les Dés | NexaSpin',
                metaDescription: 'Jouez au mythique jeu de dés du 421 gratuitement en ligne. Lancez les dés, tentez de faire 4-2-1 et suivez vos meilleurs tirages sans inscription.',
            ),
            self::ROCK_PAPER_SCISSORS => new GameMode(
                icon: '✂️',
                title: 'Pierre-feuille-ciseaux',
                description: 'Affrontez un adversaire aléatoire, coup après coup, dans le classique jeu de mains.',
                route: route('rps'),
                available: true,
                color: 'from-orange-500 to-red-500',
                shadow: 'shadow-orange-500/10 hover:shadow-orange-500/20',
                category: GameModeCategory::GAME,
                minParticipants: 'solo ou 2',
                needsPlus: false,
                isSolo: true,
                metaTitle: 'Pierre-Feuille-Ciseaux en Ligne | NexaSpin',
                metaDescription: 'Jouez à pierre-feuille-ciseaux gratuitement contre un adversaire aléatoire. Historique des manches inclus, sans inscription.',
            ),
            self::TIC_TAC_TOE => new GameMode(
                icon: '⭕',
                title: 'Morpion',
                description: 'Un plateau, deux joueurs, le premier à aligner trois symboles gagne.',
                route: route('tictactoe'),
                available: true,
                color: 'from-sky-500 to-indigo-500',
                shadow: 'shadow-sky-500/10 hover:shadow-sky-500/20',
                category: GameModeCategory::GAME,
                minParticipants: 2,
                needsPlus: false,
                isSolo: true,
                metaTitle: 'Morpion en Ligne à Deux Joueurs | NexaSpin',
                metaDescription: 'Jouez au morpion à deux sur le même écran. Gratuit, sans inscription, avec historique des parties.',
            ),

            self::SECRET_NUMBER => new GameMode(
                icon: '🔐',
                title: 'Nombre secret',
                description: 'Devinez un nombre choisi par l’IA, affrontez-vous en duel ou défiez-vous à deux en versus.',
                route: route('secret-number'),
                available: true,
                color: 'from-violet-500 to-indigo-600',
                shadow: 'shadow-violet-500/10 hover:shadow-violet-500/20',
                category: GameModeCategory::GAME,
                minParticipants: 2,
                needsPlus: true,
                isSolo: true,
                metaTitle: 'Nombre Secret — Jeu de Devinette | NexaSpin',
                metaDescription: 'Jouez au nombre secret : contre l’IA, en duel jusqu’à 10 joueurs ou en versus à deux. Les indices « plus » et « moins » vous guident.',
            ),
            self::STICKS => new GameMode(
                icon: '🪵',
                title: 'Jeu des bâtonnets',
                description: 'Retirez 1, 2 ou 3 bâtonnets à tour de rôle : le dernier bâtonnet fait perdre… ou gagner, au choix. Contre l’IA ou en duel.',
                route: route('sticks'),
                available: true,
                color: 'from-amber-600 to-orange-700',
                shadow: 'shadow-amber-500/10 hover:shadow-amber-500/20',
                category: GameModeCategory::GAME,
                minParticipants: 'Solo ou 2',
                needsPlus: false,
                isSolo: true,
                metaTitle: 'Jeu des Bâtonnets en Ligne — Ne Prenez pas le Dernier | NexaSpin',
                metaDescription: 'Jouez au jeu des bâtonnets : retirez 1 à 3 bâtonnets à tour de rôle, le dernier perd (ou gagne, en variante). Contre l’IA ou en duel, gratuit et sans inscription.',
            ),
            self::CURSED_BOX => new GameMode(
                icon: '📦',
                title: 'Boîte maudite',
                description: 'Choisissez parmi plusieurs boîtes mystères : certaines cachent un trésor, d’autres la malédiction !',
                route: '',
                available: false,
                color: 'from-purple-900 to-slate-900',
                shadow: 'shadow-purple-500/10 hover:shadow-purple-500/20',
                category: GameModeCategory::GAME,
                minParticipants: null,
                needsPlus: false,
                isSolo: true,
                metaTitle: 'Boîte Maudite — Jeu de Hasard et Mystère | NexaSpin',
                metaDescription: 'Tentez votre chance avec la boîte maudite ! Choisissez une boîte mystère, évitez le piège et repartez avec le magot dans ce jeu de suspense.',
            ),
            self::TEAMS => new GameMode(
                icon: '👥',
                title: 'Tirage par équipes',
                description: 'Créez instantanément des équipes aléatoires et de tailles égales, en un seul clic.',
                route: route('teams'),
                available: true,
                color: 'from-cyan-500 to-sky-600',
                shadow: 'shadow-cyan-500/10 hover:shadow-cyan-500/20',
                category: GameModeCategory::OTHER,
                minParticipants: 4,
                needsPlus: true,
                isSolo: false,
                metaTitle: 'Tirage au Sort — Répartition Aléatoire | NexaSpin',
                metaDescription: 'Répartissez vos participants en équipes aléatoires et équilibrées en un clic. Choisissez le nombre d’équipes, gratuit et sans inscription.',
            ),
            self::TOMBOLA => new GameMode(
                icon: '🎟️',
                title: 'Tombola',
                description: 'Tirez plusieurs gagnants d’un coup (1er, 2e, 3e lot...) parmi une liste de participants pondérés.',
                route: route('tombola'),
                available: true,
                color: 'from-fuchsia-500 to-purple-600',
                shadow: 'shadow-fuchsia-500/10 hover:shadow-fuchsia-500/20',
                category: GameModeCategory::OTHER,
                minParticipants: 3,
                needsPlus: true,
                isSolo: false,
                metaTitle: 'Tombola en Ligne — Tirage de Plusieurs Gagnants | NexaSpin',
                metaDescription: 'Tirez plusieurs gagnants d’un coup parmi une liste de participants pondérés : 1er lot, 2e lot, 3e lot... Gratuit et sans inscription.',
            ),
            self::BRACKETJV => new GameMode(
                icon: '🛡️',
                title: 'Tournoi à double élimination (Upper / Lower)',
                description: 'Donnez deux chances à chaque joueur avec un tableau principal et un tableau de repêchage.',
                route: route('draw.bracket'),
                available: true,
                color: 'from-fuchsia-500 to-pink-600',
                shadow: 'shadow-fuchsia-500/10 hover:shadow-fuchsia-500/20',
                category: GameModeCategory::TOOLS,
                minParticipants: 4,
                needsPlus: true,
                isSolo: false,
                metaTitle: 'Tournoi Double Élimination — Upper & Lower Bracket | NexaSpin',
                metaDescription: 'Générez un tournoi avec tableau principal et repêchage (Upper et Lower Bracket). Deux chances pour chaque joueur d’atteindre la finale.',
            ),
            self::DICE_ROLLER => new GameMode(
                icon: '🎲',
                title: 'Lanceur de dés',
                description: 'Choisissez un type de dé façon JDR (d4 à d100) et le nombre à lancer d’un coup.',
                route: route('dice.roller'),
                available: true,
                color: 'from-teal-500 to-emerald-600',
                shadow: 'shadow-teal-500/10 hover:shadow-teal-500/20',
                category: GameModeCategory::TOOLS,
                minParticipants: null,
                needsPlus: false,
                isSolo: true,
                metaTitle: 'Lanceur de Dés en Ligne (d4 à d100) | NexaSpin',
                metaDescription: 'Lancez des dés de jeu de rôle (d4, d6, d8, d10, d12, d20, d100) en choisissant le nombre de dés. Gratuit et sans inscription.',
            ),
            self::POOL => new GameMode(
                icon: '🔄',
                title: 'Phase de poules',
                description: 'Répartissez vos participants en poules équilibrées : round-robin complet, aucun match vide.',
                route: route('draw.pools'),
                available: true,
                color: 'from-cyan-500 to-blue-600',
                shadow: 'shadow-cyan-500/10 hover:shadow-cyan-500/20',
                category: GameModeCategory::TOOLS,
                minParticipants: 4,
                needsPlus: true,
                isSolo: false,
                metaTitle: 'Phase de Poules — Round-Robin Équilibré | NexaSpin',
                metaDescription: 'Générez une phase de poules équilibrée à partir de vos participants : chacun affronte tous les membres de sa poule, sans aucun match vide.',
            ),
            self::NUMBER_ROULETTE => new GameMode(
                icon: '🔢',
                title: 'Roulette numérique',
                description: 'Une roulette américaine avec cagnotte : misez sur un numéro, une couleur, une douzaine...',
                route: route('roulette.number'),
                available: true,
                color: 'from-red-600 to-rose-700',
                shadow: 'shadow-red-500/10 hover:shadow-red-500/20',
                category: GameModeCategory::GAME,
                minParticipants: null,
                needsPlus: false,
                isSolo: true,
                metaTitle: 'Roulette Numérique Américaine — Jouez avec une Cagnotte | NexaSpin',
                metaDescription: 'Jouez à la roulette américaine en ligne : misez sur un numéro, une couleur, une douzaine ou une colonne et suivez votre cagnotte. Gratuit et sans inscription.',
            ),

        };
    }

    /**
     * Récupère la liste complète des DTOs pour la vue.
     *
     * @return array<GameMode>
     */
    public static function all(): array
    {
        return array_map(
            fn(self $mode) => $mode->toDto(),
            self::cases()
        );
    }

    /**
     * Regroupe les DTOs par catégorie, dans l'ordre de déclaration de GameModeCategory.
     *
     * @return array<int, array{category: GameModeCategory, modes: array<GameMode>}>
     */
    public static function grouped(): array
    {
        $modesByCategory = collect(self::all())->groupBy(
            fn(GameMode $mode) => $mode->category->value
        );

        return collect(GameModeCategory::cases())
            ->map(fn(GameModeCategory $category) => [
                'category' => $category,
                'modes' => $modesByCategory->get($category->value, collect())->all(),
            ])
            ->filter(fn(array $group) => $group['modes'] !== [])
            ->values()
            ->all();
    }
}
