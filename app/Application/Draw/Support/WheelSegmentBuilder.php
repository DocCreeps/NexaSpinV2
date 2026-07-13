<?php

namespace App\Application\Draw\Support;

class WheelSegmentBuilder
{
    private const RADIUS = 150;

    private const CENTER = 150;

    private const DEFAULT_SPINS = 6;

    /**
     * Construit les segments SVG (chemin, couleur, position du texte) pour
     * une roue affichant les noms donnés, dans l'ordre donné.
     *
     * @param  array<int, string>  $names
     * @param  array<string, string>|null  $colors  Couleurs figées par nom (ex : roue par élimination, où la
     *                                               couleur d'un participant ne doit pas changer quand la liste rétrécit).
     *                                               Si absent, les couleurs sont réparties uniformément sur le cercle chromatique.
     * @return array<int, array{name: string, color: string, path: ?string, fullCircle: bool, labelTransform: string}>
     */
    public static function build(array $names, ?array $colors = null): array
    {
        $total = count($names);

        if ($total === 0) {
            return [];
        }

        $sliceAngle = 360 / $total;

        return collect($names)
            ->values()
            ->map(function (string $name, int $index) use ($sliceAngle, $total, $colors) {
                $startAngle = $index * $sliceAngle;
                $endAngle = $startAngle + $sliceAngle;
                $midAngle = $startAngle + ($sliceAngle / 2);

                return [
                    'name' => $name,
                    'color' => $colors[$name] ?? self::colorFor($index, $total),
                    // Un unique participant occupe 360° : un arc SVG ne peut
                    // pas tracer un cercle complet (départ = arrivée = arc
                    // nul), donc on le signale pour dessiner un <circle> à
                    // la place d'un <path> dans ce cas précis.
                    'fullCircle' => $total === 1,
                    'path' => $total === 1 ? null : self::arcPath($startAngle, $endAngle),
                    'labelTransform' => self::labelTransform($midAngle),
                ];
            })
            ->all();
    }

    /**
     * Fige une couleur par nom, à appeler une seule fois au démarrage d'une
     * roue dont la liste de participants va rétrécir (élimination), pour
     * que chaque participant garde toujours la même couleur.
     *
     * @param  array<int, string>  $names
     * @return array<string, string>
     */
    public static function assignColors(array $names): array
    {
        $total = count($names);

        return collect($names)
            ->values()
            ->mapWithKeys(fn (string $name, int $index) => [$name => self::colorFor($index, $total)])
            ->all();
    }

    /**
     * Angle final de rotation (en degrés) pour que le pointeur, fixé en
     * haut de la roue, s'arrête pile sur la part à l'index donné, après
     * plusieurs tours complets pour l'effet visuel.
     */
    public static function rotationFor(int $targetIndex, int $total, int $spins = self::DEFAULT_SPINS): float
    {
        $sliceAngle = 360 / $total;
        $targetAngle = ($targetIndex * $sliceAngle) + ($sliceAngle / 2);

        return ($spins * 360) + (360 - $targetAngle);
    }

    private static function colorFor(int $index, int $total): string
    {
        $hue = (int) round((360 / max($total, 1)) * $index);

        return "hsl({$hue}, 70%, 55%)";
    }

    private static function arcPath(float $startAngle, float $endAngle): string
    {
        $center = self::CENTER;
        $radius = self::RADIUS;

        $start = self::polarToCartesian($center, $center, $radius, $endAngle);
        $end = self::polarToCartesian($center, $center, $radius, $startAngle);

        $largeArcFlag = ($endAngle - $startAngle) > 180 ? 1 : 0;

        return "M {$center},{$center} L {$start['x']},{$start['y']} A {$radius},{$radius} 0 {$largeArcFlag},0 {$end['x']},{$end['y']} Z";
    }

    private static function labelTransform(float $midAngle): string
    {
        $labelRadius = self::RADIUS * 0.65;
        $center = self::CENTER;

        $point = self::polarToCartesian($center, $center, $labelRadius, $midAngle);

        // Le texte est orienté radialement ; on le retourne de 180° sur la
        // moitié gauche pour qu'il reste lisible plutôt que tête en bas.
        $rotation = $midAngle <= 180 ? $midAngle - 90 : $midAngle + 90;

        return "translate({$point['x']}, {$point['y']}) rotate({$rotation})";
    }

    /**
     * @return array{x: float, y: float}
     */
    private static function polarToCartesian(float $cx, float $cy, float $r, float $angleDeg): array
    {
        $angleRad = deg2rad($angleDeg - 90);

        return [
            'x' => round($cx + $r * cos($angleRad), 2),
            'y' => round($cy + $r * sin($angleRad), 2),
        ];
    }
}
