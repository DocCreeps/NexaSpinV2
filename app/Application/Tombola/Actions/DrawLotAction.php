<?php

namespace App\Application\Tombola\Actions;

use App\Application\Draw\Actions\RunDrawAction;
use App\Application\Draw\DTOs\DrawData;
use App\Application\Tombola\DTOs\LotDrawResult;
use App\Domain\Draw\Enums\DrawDisplay;
use App\Domain\Draw\Enums\DrawType;
use App\Domain\Draw\Support\WeightedRandomPicker;

/**
 * Tire un lot pondéré parmi le pool restant d'une tombola, et retourne le
 * pool/poids mis à jour (avec ou sans remise).
 *
 * Délègue à `RunDrawAction` (donc à `WeightedDrawStrategy`) dès que possible,
 * pour rester cohérent avec les autres tirages pondérés de l'application ;
 * `Draw` imposant un minimum de 2 participants, le cas à un seul candidat
 * restant passe par `WeightedRandomPicker` à la place.
 */
final class DrawLotAction
{
    /**
     * @param  array<int, string>  $remainingPool
     * @param  array<int, int>  $remainingWeights
     */
    public function execute(array $remainingPool, array $remainingWeights, bool $allowDuplicates): LotDrawResult
    {
        if ($remainingPool === []) {
            throw new \InvalidArgumentException('Le pool de tirage est vide.');
        }

        $index = count($remainingPool) < 2
            ? WeightedRandomPicker::pick($remainingWeights)
            : $this->pickViaWeightedDraw($remainingPool, $remainingWeights);

        $winnerName = $remainingPool[$index];

        if ($allowDuplicates) {
            $remainingWeights[$index]--;

            if ($remainingWeights[$index] <= 0) {
                unset($remainingPool[$index], $remainingWeights[$index]);
            }
        } else {
            unset($remainingPool[$index], $remainingWeights[$index]);
        }

        return new LotDrawResult(
            winner: $winnerName,
            remainingPool: array_values($remainingPool),
            remainingWeights: array_values($remainingWeights),
        );
    }

    /**
     * @param  array<int, string>  $remainingPool
     * @param  array<int, int>  $remainingWeights
     */
    private function pickViaWeightedDraw(array $remainingPool, array $remainingWeights): int
    {
        $result = app(RunDrawAction::class)->execute(new DrawData(
            participants: $remainingPool,
            type: DrawType::WEIGHTED,
            display: DrawDisplay::WHEEL,
            weights: $remainingWeights,
        ));

        $index = array_search($result->winner->name, $remainingPool, true);

        return $index === false ? 0 : $index;
    }
}
