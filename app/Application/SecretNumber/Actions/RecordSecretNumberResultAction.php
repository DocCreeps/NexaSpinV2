<?php

namespace App\Application\SecretNumber\Actions;

use App\Application\History\HistoryStore;
use App\Application\Home\Enums\GameModeType;
use App\Domain\SecretNumber\Entities\SecretNumberGame;
use App\Domain\SecretNumber\Enums\SecretNumberPhase;

/** Archive dans l'historique le récapitulatif d'une partie terminée. */
final class RecordSecretNumberResultAction
{
    /** Évite qu'une partie sans limite d'essais fasse gonfler l'historique. */
    public const MAX_GUESSES_PER_ENTRY = 50;

    public function __construct(private readonly HistoryStore $historyStore) {}

    public function execute(SecretNumberGame $game): void
    {
        if ($game->phase() !== SecretNumberPhase::FINISHED) {
            return;
        }

        $state = $game->publicState();

        $this->historyStore->push(GameModeType::SECRET_NUMBER, [
            'game_mode' => $state['mode'],
            'minimum' => $state['minimum'],
            'maximum' => $state['maximum'],
            'player_count' => $state['player_count'],
            'player_names' => $state['player_names'],
            'winner' => $state['winner'],
            'attempts' => $state['attempts'],
            'secret' => $state['revealed_number'],
            'secret_setter' => $state['secret_setter'],
            'guesses' => array_map($this->limit(...), $state['guesses'] ?? []),
            'versus_results' => array_map(function (array $result): array {
                $result['guesses'] = $this->limit($result['guesses']);

                return $result;
            }, $state['versus_results'] ?? []),
            'rankings' => $state['rankings'] ?? [],
            'any_player_found' => $state['any_player_found'] ?? false,
        ]);
    }

    /**
     * @param  array<int, array{number: int, hint: string}>  $guesses
     * @return array<int, array{number: int, hint: string}>
     */
    private function limit(array $guesses): array
    {
        return array_slice($guesses, -self::MAX_GUESSES_PER_ENTRY);
    }
}
