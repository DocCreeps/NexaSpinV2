<?php

namespace App\Application\Draw\Support;

/**
 * Générateur géométrique SVG et calculateur de rotation pour roue de la fortune.
 */
class WheelSegmentBuilder
{
    private const RADIUS = 150;
    private const CENTER = 150; // Pour viewBox 300x300
    private const DEFAULT_SPINS = 6;

    /**
     * Génère les données SVG (paths, couleurs, positions de texte) de chaque segment.
     *
     * @param  array<int, string>  $names
     * @param  array<string, string>|null  $colors Mappage optionnel des couleurs
     * @param  array<int, int>|null  $weights Poids optionnels par index
     * @return array<int, array{name: string, color: string, path: ?string, fullCircle: bool, labelTransform: string}>
     */
    public static function build(array $names, ?array $colors = null, ?array $weights = null): array
    {
        $total = count($names);

        if ($total === 0) {
            return [];
        }

        $bounds = self::segmentBounds($total, $weights);

        return collect($names)
            ->values()
            ->map(function (string $name, int $index) use ($bounds, $total, $colors): array {
                [$startAngle, $endAngle] = $bounds[$index];
                $midAngle = $startAngle + (($endAngle - $startAngle) / 2);

                return [
                    'name' => $name,
                    'color' => $colors[$name] ?? self::colorFor($index, $total),
                    'fullCircle' => $total === 1, // Repli sur balise <circle> si participant unique
                    'path' => $total === 1 ? null : self::arcPath($startAngle, $endAngle),
                    'labelTransform' => self::labelTransform($midAngle),
                ];
            })
            ->all();
    }

    /**
     * Génère une palette HSL statique indexée par nom.
     *
     * @param  array<int, string>  $names
     * @return array<string, string>
     */
    public static function assignColors(array $names): array
    {
        $total = count($names);

        return collect($names)
            ->values()
            ->mapWithKeys(fn(string $name, int $index) => [$name => self::colorFor($index, $total)])
            ->all();
    }

    /**
     * Calcule la rotation CSS finale (degrés) pour stopper la cible à 12h.
     *
     * @param  array<int, int>|null  $weights
     */
    public static function rotationFor(
        int $targetIndex,
        int $total,
        int $spins = self::DEFAULT_SPINS,
        ?array $weights = null
    ): float {
        $bounds = self::segmentBounds($total, $weights);
        [$startAngle, $endAngle] = $bounds[$targetIndex] ?? [0.0, 360 / max($total, 1)];
        $targetAngle = $startAngle + (($endAngle - $startAngle) / 2);

        return ($spins * 360) + (360 - $targetAngle);
    }

    /**
     * Calcule la rotation cumulée minimale sans effet de retour en arrière.
     *
     * @param  array<int, int>|null  $weights
     * @return array{delta: int, newRotation: int}
     */
    public static function cumulativeRotationFor(
        int $targetIndex,
        int $total,
        int $currentRotation,
        int $minSpins = self::DEFAULT_SPINS,
        ?array $weights = null
    ): array {
        $bounds = self::segmentBounds($total, $weights);
        [$startAngle, $endAngle] = $bounds[$targetIndex] ?? [0.0, 360 / max($total, 1)];
        $midAngle = $startAngle + (($endAngle - $startAngle) / 2);

        $targetAngle = fmod(360 - $midAngle, 360);
        if ($targetAngle < 0) {
            $targetAngle += 360;
        }

        $minRotation = $currentRotation + ($minSpins * 360);
        $current = fmod($minRotation, 360);

        $newRotation = $targetAngle >= $current
            ? $minRotation + ($targetAngle - $current)
            : $minRotation + (360 - $current) + $targetAngle;

        return [
            'delta' => (int) round($newRotation - $currentRotation),
            'newRotation' => (int) round($newRotation),
        ];
    }

    /**
     * Calcule les angles [startAngle, endAngle] de chaque part selon leur poids.
     *
     * @param  array<int, int>|null  $weights
     * @return array<int, array{0: float, 1: float}>
     */
    private static function segmentBounds(int $total, ?array $weights): array
    {
        if ($total <= 0) {
            return [];
        }

        $normalizedWeights = self::normalizeWeights($total, $weights);
        $totalWeight = array_sum($normalizedWeights);

        $bounds = [];
        $cursor = 0.0;

        for ($index = 0; $index < $total; $index++) {
            $sliceAngle = ($normalizedWeights[$index] / $totalWeight) * 360;
            $bounds[$index] = [$cursor, $cursor + $sliceAngle];
            $cursor += $sliceAngle;
        }

        return $bounds;
    }

    /**
     * Normalise le tableau de poids (remplace les poids invalides ou <= 0 par 1).
     *
     * @param  array<int, int>|null  $weights
     * @return array<int, int>
     */
    private static function normalizeWeights(int $total, ?array $weights): array
    {
        if ($weights === null || $weights === [] || array_sum($weights) <= 0) {
            return array_fill(0, $total, 1);
        }

        $normalized = [];

        for ($index = 0; $index < $total; $index++) {
            $weight = $weights[$index] ?? 1;
            $normalized[$index] = $weight > 0 ? $weight : 1;
        }

        return $normalized;
    }

    /**
     * Génère une couleur HSL équilibrée sur le cercle chromatique.
     */
    private static function colorFor(int $index, int $total): string
    {
        $hue = (int) round((360 / max($total, 1)) * $index);

        return "hsl({$hue}, 70%, 55%)";
    }

    /**
     * Génère le path SVG d'un arc de cercle.
     */
    private static function arcPath(float $startAngle, float $endAngle): string
    {
        $center = self::CENTER;
        $radius = self::RADIUS;

        $start = self::polarToCartesian($center, $center, $radius, $endAngle);
        $end = self::polarToCartesian($center, $center, $radius, $startAngle);
        $largeArcFlag = ($endAngle - $startAngle) > 180 ? 1 : 0;

        return "M {$center},{$center} L {$start['x']},{$start['y']} A {$radius},{$radius} 0 {$largeArcFlag},0 {$end['x']},{$end['y']} Z";
    }

    /**
     * Calcule la transformation CSS (position et rotation) du texte du segment.
     */
    private static function labelTransform(float $midAngle): string
    {
        $point = self::polarToCartesian(self::CENTER, self::CENTER, self::RADIUS * 0.65, $midAngle);
        $rotation = $midAngle <= 180 ? $midAngle - 90 : $midAngle + 90; // Inverse le texte sur le demi-cercle gauche pour la lisibilité

        return "translate({$point['x']}, {$point['y']}) rotate({$rotation})";
    }

    /**
     * Convertit des coordonnées polaires en cartésiennes (-90° pour caler 0° à 12h).
     *
     * @return array{x: float, y: float}
     */
    private static function polarToCartesian(float $cx, float $cy, float $r, float $angleDeg): array
    {
        $angleRad = deg2rad($angleDeg - 90);

        return [
            'x' => round($cx + $r * cos($angleRad), 4),
            'y' => round($cy + $r * sin($angleRad), 4),
        ];
    }
}
