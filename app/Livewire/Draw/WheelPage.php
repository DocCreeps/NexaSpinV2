<?php

namespace App\Livewire\Draw;

use Livewire\Attributes\Computed;
use Livewire\Component;
use App\Application\Draw\Actions\RunDrawAction;
use App\Application\Draw\Support\WheelSegmentBuilder;
use App\Livewire\Draw\Concerns\ManagesParticipants;
use App\Livewire\Draw\Concerns\HandlesDraw;

/**
 * Page de tirage classique sous forme de Roue de la Fortune.
 * Contrairement à la roue d'élimination, ce composant effectue un tirage direct et instantané sans exclure de joueurs.
 */
class WheelPage extends Component
{
    use ManagesParticipants;
    use HandlesDraw;

    /**
     * Nombre maximal de segments pour lesquels on affiche encore le texte sur la roue.
     */
    private const MAX_LABELS_ON_WHEEL = 10;

    /**
     * Stocke le nom du vainqueur désigné par le tirage.
     */
    public ?string $result = null;

    /**
     * Hook réactif appelé lorsque la liste des participants est modifiée.
     * Réinitialise le résultat précédent pour masquer l'ancien vainqueur.
     */
    protected function afterParticipantsChanged(): void
    {
        $this->result = null;
    }

    /**
     * Déclenche le tirage au sort, détermine le gagnant via le Domaine,
     * puis délègue le calcul de l'angle de rotation final pour l'animation CSS.
     */
    public function draw(RunDrawAction $action): void
    {
        $this->error = null;

        if (count($this->participants) < 2) {
            $this->error = 'Ajoutez au moins deux participants.';
            return;
        }

        // Exécute le cas d'utilisation de tirage (Application)
        $result = $this->executeDraw($action);
        $winner = $result->winner;

        $index = array_search($winner->name, $this->participants, true);

        if ($index === false) {
            $this->error = 'Gagnant introuvable.';
            return;
        }

        $this->result = $winner->name;

        // Déclenche l'animation de rotation côté frontend (Alpine.js) avec le calcul précis de l'angle d'arrêt
        $this->dispatch(
            'wheel-spin',
            rotation: WheelSegmentBuilder::rotationFor(
                $index,
                count($this->participants)
            )
        );
    }

    /**
     * Propriété calculée (Computed) contenant la configuration géométrique et de rendu SVG des segments.
     *
     * @return array<int, array{name: string, color: string, path: ?string, fullCircle: bool, labelTransform: string}>
     */
    #[Computed]
    public function segments(): array
    {
        return WheelSegmentBuilder::build(
            $this->participants
        );
    }

    /**
     * Détermine s'il convient d'afficher les textes sur les segments de la roue.
     */
    public function showLabelsOnWheel(): bool
    {
        return count($this->participants) <= self::MAX_LABELS_ON_WHEEL;
    }

    /**
     * Rendu du composant Livewire au sein du layout de l'application.
     */
    public function render()
    {
        return view('livewire.draw.wheel-page')
            ->layout('layouts.app');
    }
}
