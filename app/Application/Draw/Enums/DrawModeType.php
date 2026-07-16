<?php

namespace App\Application\Draw\Enums;

use App\Application\Draw\DTOs\DrawMode;

enum DrawModeType: string
{
    case CLASSIC = 'classic';
    case ELIMINATION = 'elimination';
    case WEIGHTED = 'weighted';
    case COIN_FLIP = 'coin_flip';
    case TEAMS = 'teams';

    /**
     * Transforme l'enum en objet de présentation (DTO).
     */
    public function toDto(): DrawMode
    {
        return match ($this) {
            self::CLASSIC => new DrawMode(
                icon: '🎡',
                title: 'Roue classique',
                description: 'Lancez la roue et désignez instantanément un gagnant parmi tous les participants.',
                route: route('draw.wheel'),
                available: true,
                color: 'from-indigo-500 to-violet-600',
                shadow: 'shadow-indigo-500/10 hover:shadow-indigo-500/20',
                minParticipants: 2,
            ),
            self::ELIMINATION => new DrawMode(
                icon: '⚔️',
                title: 'Roue par élimination',
                description: 'Les participants s’affrontent tour après tour jusqu’au dernier survivant.',
                route: route('draw.wheel-elimination'),
                available: true,
                color: 'from-rose-500 to-red-600',
                shadow: 'shadow-rose-500/10 hover:shadow-rose-500/20',
                minParticipants: 5,
            ),
            self::WEIGHTED => new DrawMode(
                icon: '🎯',
                title: 'Tirage pondéré',
                description: 'Attribuez plus ou moins de chances à chaque participant grâce à un système de poids personnalisé.',
                route: route('draw.wheel-weighted'),
                available: true,
                color: 'from-emerald-500 to-teal-600',
                shadow: 'shadow-emerald-500/10 hover:shadow-emerald-500/20',
                minParticipants: 3,
            ),
            self::COIN_FLIP => new DrawMode(
                icon: '🪙',
                title: 'Pile ou face',
                description: 'Le grand classique : un tirage rapide à deux issues, sans liste de participants à gérer.',
                route: null,
                available: false,
                color: 'from-slate-400 to-slate-500',
                shadow: 'shadow-slate-500/5',
                minParticipants: null,
            ),
            self::TEAMS => new DrawMode(
                icon: '👥',
                title: 'Tirage par équipes',
                description: 'Créez instantanément des équipes aléatoires et de tailles égales, en un seul clic.',
                route: null,
                available: false,
                color: 'from-zinc-400 to-zinc-500',
                shadow: 'shadow-zinc-500/5',
                minParticipants: 4,
            ),
        };
    }

    /**
     * Récupère la liste complète des DTOs pour la vue.
     *
     * @return array<DrawMode>
     */
    public static function all(): array
    {
        return array_map(
            fn(self $mode) => $mode->toDto(),
            self::cases()
        );
    }
}
