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
                metaTitle: 'Tirage au Sort Pondéré — Chances Personnalisées | NexaSpin',
                metaDescription: 'Donnez plus ou moins de chances à chaque participant grâce à un système de poids personnalisable. Le tirage au sort équitable et flexible, gratuit en ligne.',
            ),
            self::COIN_FLIP => new GameMode(
                icon: '🪙',
                title: 'Pile ou face',
                description: 'Le grand classique : un tirage rapide à deux issues, sans liste de participants à gérer.',
                route: route('draw.coinflip'),
                available: true,
                color: 'from-amber-400 to-yellow-500',
                shadow: 'shadow-amber-500/10 hover:shadow-amber-500/20',
                category: GameModeCategory::OTHER,
                minParticipants: null,
                metaTitle: 'Pile ou Face — Tirage Aléatoire Rapide | NexaSpin',
                metaDescription: 'Lancez une pièce virtuelle et enchaînez les tirages "Pile ou Face" en un clic. Historique des résultats inclus, 100% gratuit et sans inscription.',
            ),
            self::TEAMS => new GameMode(
                icon: '👥',
                title: 'Tirage par équipes',
                description: 'Créez instantanément des équipes aléatoires et de tailles égales, en un seul clic.',
                route: null,
                available: false,
                color: 'from-zinc-400 to-zinc-500',
                shadow: 'shadow-zinc-500/5',
                category: GameModeCategory::DEV,
                minParticipants: 4,
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
     * Regroupe les DTOs par catégorie, dans l'ordre de déclaration de GameModeCategory
     * (et non l'ordre d'apparition dans GameModeType), pour un affichage stable et
     * indépendant de l'ordre des cases ci-dessus. Une catégorie sans mode n'est pas
     * retournée : ajouter une case à GameModeCategory ne crée une section sur la home
     * que le jour où un GameModeType lui est effectivement rattaché.
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
