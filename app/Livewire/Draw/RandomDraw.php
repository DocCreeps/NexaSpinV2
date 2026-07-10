<?php

namespace App\Livewire\Draw;

use Livewire\Attributes\Computed;
use Livewire\Component;
use App\Domain\Draw\Enums\DrawType;
use App\Domain\Draw\ValueObjects\Participant;
use App\Application\Draw\DTOs\DrawData;
use App\Application\Draw\Actions\RunDrawAction;

class RandomDraw extends Component
{
    private const MAX_PARTICIPANTS = 100;

    private const MAX_NAME_LENGTH = 50;

    /**
     * Au-delà de ce nombre de participants, les noms ne sont plus affichés
     * directement sur la roue (parts trop fines) : on bascule sur une
     * légende à côté.
     */
    private const MAX_LABELS_ON_WHEEL = 10;

    private const WHEEL_RADIUS = 150;

    private const WHEEL_CENTER = 150;

    private const SPIN_TURNS = 6;

    /** @var array<int, string> */
    public array $participants = [];

    public string $participant = '';

    public ?string $result = null;

    public ?string $error = null;

    public function addParticipant(): void
    {
        $this->error = null;

        $name = trim($this->participant);

        if ($name === '') {
            return;
        }

        if (mb_strlen($name) > self::MAX_NAME_LENGTH) {
            $this->error = 'Le nom d\'un participant ne peut pas dépasser '.self::MAX_NAME_LENGTH.' caractères.';

            return;
        }

        if (count($this->participants) >= self::MAX_PARTICIPANTS) {
            $this->error = 'Vous ne pouvez pas ajouter plus de '.self::MAX_PARTICIPANTS.' participants.';

            return;
        }

        $alreadyExists = collect($this->participants)
            ->contains(fn (string $existing) => mb_strtolower($existing) === mb_strtolower($name));

        if ($alreadyExists) {
            $this->error = 'Ce participant a déjà été ajouté.';

            return;
        }

        $this->participants[] = $name;

        $this->participant = '';

        unset($this->segments);
    }

    public function removeParticipant(int $index): void
    {
        unset($this->participants[$index]);

        $this->participants = array_values($this->participants);

        $this->result = null;

        unset($this->segments);
    }

    public function draw(RunDrawAction $action): void
    {
        $this->error = null;
        $this->result = null;

        if (count($this->participants) < 2) {
            $this->error = 'Ajoutez au moins deux participants avant de lancer le tirage.';

            return;
        }

        $winner = $action->execute(
            new DrawData(
                participants: array_map(
                    fn (string $name) => new Participant($name),
                    $this->participants,
                ),
                type: DrawType::WHEEL,
            )
        );

        if ($winner === null) {
            return;
        }

        $winnerIndex = array_search($winner->name, $this->participants, true);

        $this->result = $winner->name;

        $this->dispatch(
            'wheel-spin',
            rotation: $this->rotationFor((int) $winnerIndex, count($this->participants)),
        );
    }

    /**
     * Angle final de rotation (en degrés) pour que le pointeur, fixé en
     * haut de la roue, s'arrête pile sur la part du gagnant, après
     * plusieurs tours complets pour l'effet visuel.
     */
    private function rotationFor(int $winnerIndex, int $total): float
    {
        $sliceAngle = 360 / $total;
        $targetAngle = ($winnerIndex * $sliceAngle) + ($sliceAngle / 2);

        return (self::SPIN_TURNS * 360) + (360 - $targetAngle);
    }

    /**
     * Segments de la roue prêts à être injectés dans le SVG : chemin,
     * couleur, et position du texte le cas échéant.
     *
     * @return array<int, array{name: string, color: string, path: string, labelTransform: string}>
     */
    #[Computed]
    public function segments(): array
    {
        $total = count($this->participants);

        if ($total === 0) {
            return [];
        }

        $sliceAngle = 360 / $total;

        return collect($this->participants)
            ->values()
            ->map(function (string $name, int $index) use ($sliceAngle, $total) {
                $startAngle = $index * $sliceAngle;
                $endAngle = $startAngle + $sliceAngle;
                $midAngle = $startAngle + ($sliceAngle / 2);

                return [
                    'name' => $name,
                    'color' => $this->colorFor($index, $total),
                    'path' => $this->arcPath($startAngle, $endAngle),
                    'labelTransform' => $this->labelTransform($midAngle),
                ];
            })
            ->all();
    }

    public function showLabelsOnWheel(): bool
    {
        return count($this->participants) <= self::MAX_LABELS_ON_WHEEL;
    }

    private function colorFor(int $index, int $total): string
    {
        $hue = (int) round((360 / max($total, 1)) * $index);

        return "hsl({$hue}, 70%, 55%)";
    }

    private function arcPath(float $startAngle, float $endAngle): string
    {
        $center = self::WHEEL_CENTER;
        $radius = self::WHEEL_RADIUS;

        $start = $this->polarToCartesian($center, $center, $radius, $endAngle);
        $end = $this->polarToCartesian($center, $center, $radius, $startAngle);

        $largeArcFlag = ($endAngle - $startAngle) > 180 ? 1 : 0;

        return "M {$center},{$center} L {$start['x']},{$start['y']} A {$radius},{$radius} 0 {$largeArcFlag},0 {$end['x']},{$end['y']} Z";
    }

    private function labelTransform(float $midAngle): string
    {
        $labelRadius = self::WHEEL_RADIUS * 0.65;
        $center = self::WHEEL_CENTER;

        $point = $this->polarToCartesian($center, $center, $labelRadius, $midAngle);

        // Le texte est orienté radialement ; on le retourne de 180° sur la
        // moitié gauche pour qu'il reste lisible plutôt que tête en bas.
        $rotation = $midAngle <= 180 ? $midAngle - 90 : $midAngle + 90;

        return "translate({$point['x']}, {$point['y']}) rotate({$rotation})";
    }

    /**
     * @return array{x: float, y: float}
     */
    private function polarToCartesian(float $cx, float $cy, float $r, float $angleDeg): array
    {
        $angleRad = deg2rad($angleDeg - 90);

        return [
            'x' => round($cx + $r * cos($angleRad), 2),
            'y' => round($cy + $r * sin($angleRad), 2),
        ];
    }

    public function render()
    {
        return view('livewire.draw.random-draw')
            ->layout('layouts.app');
    }
}
