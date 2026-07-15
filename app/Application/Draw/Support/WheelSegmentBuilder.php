<?php

namespace App\Application\Draw\Support;

/**
 * Générateur géométrique et visuel (SVG) pour afficher une roue de la fortune.
 * Calcule les coordonnées, tracés et rotations des portions de la roue.
 */
class WheelSegmentBuilder
{
    private const RADIUS = 150;       // Rayon de la roue en pixels.

    private const CENTER = 150;       // Coordonnées X/Y du centre (conçu pour une viewBox de 300x300).

    private const DEFAULT_SPINS = 6;  // Nombre de rotations complètes pour simuler l'élan visuel.

    /**
     * Calcule et formate les données SVG (paths, couleurs, positions de texte) de chaque part.
     *
     * @param  array<int, string>  $names
     * @param  array<string, string>|null  $colors  Mappage optionnel pour figer les couleurs lors d'éliminations.
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
                    // Un seul participant = cercle complet (360°). Un tracé SVG standard échoue si départ = arrivée.
                    // On bascule donc l'affichage sur une balise <circle> côté frontend.
                    'fullCircle' => $total === 1,
                    'path' => $total === 1 ? null : self::arcPath($startAngle, $endAngle),
                    'labelTransform' => self::labelTransform($midAngle),
                ];
            })
            ->all();
    }

    /**
     * Génère une palette statique de couleurs HSL basée sur la liste initiale de noms.
     * Permet de conserver les mêmes couleurs par participant même si le nombre de parts diminue.
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
     * Calcule l'angle de rotation CSS final (en degrés) pour que la part ciblée s'arrête
     * précisément sous le curseur de sélection (situé au sommet à 12h / 90° de décalage).
     */
    public static function rotationFor(int $targetIndex, int $total, int $spins = self::DEFAULT_SPINS): float
    {
        $sliceAngle = 360 / $total;
        $targetAngle = ($targetIndex * $sliceAngle) + ($sliceAngle / 2);

        // Somme des tours complets + complément angulaire pour ramener le centre de la part à 12h.
        return ($spins * 360) + (360 - $targetAngle);
    }

    /**
     * Distribue équitablement les couleurs sur le cercle chromatique (de 0° à 360°).
     */
    private static function colorFor(int $index, int $total): string
    {
        $hue = (int) round((360 / max($total, 1)) * $index);

        return "hsl({$hue}, 70%, 55%)";
    }

    /**
     * Calcule la commande d'un chemin (path) SVG représentant une portion (arc de cercle) de la roue.
     */
    private static function arcPath(float $startAngle, float $endAngle): string
    {
        $center = self::CENTER;
        $radius = self::RADIUS;

        $start = self::polarToCartesian($center, $center, $radius, $endAngle);
        $end = self::polarToCartesian($center, $center, $radius, $startAngle);

        // Si l'arc de la part fait plus de 180°, le drapeau SVG Large Arc doit passer à 1.
        $largeArcFlag = ($endAngle - $startAngle) > 180 ? 1 : 0;

        // Commande d'arc SVG : Déplacement au centre -> Ligne vers le point de départ -> Tracé de l'arc -> Retour au centre (Z).
        return "M {$center},{$center} L {$start['x']},{$start['y']} A {$radius},{$radius} 0 {$largeArcFlag},0 {$end['x']},{$end['y']} Z";
    }

    /**
     * Positionne et oriente l'étiquette de texte au sein de sa portion de roue.
     */
    private static function labelTransform(float $midAngle): string
    {
        $labelRadius = self::RADIUS * 0.65; // Place le texte à 65% du rayon (centré visuellement).
        $center = self::CENTER;

        $point = self::polarToCartesian($center, $center, $labelRadius, $midAngle);

        // Retourne le texte de 180° sur la partie gauche du cercle (entre 180° et 360°)
        // pour qu'il soit écrit de gauche à droite et lisible sans pencher la tête.
        $rotation = $midAngle <= 180 ? $midAngle - 90 : $midAngle + 90;

        return "translate({$point['x']}, {$point['y']}) rotate({$rotation})";
    }

    /**
     * Convertit un angle polaire (rayon, angle) en coordonnées cartésiennes (X, Y).
     * Le décalage de -90° réaligne le point d'origine (0°) au sommet du cercle (12h).
     *
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
