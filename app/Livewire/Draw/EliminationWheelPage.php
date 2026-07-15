<?php

namespace App\Livewire\Draw;

use App\Application\Draw\Actions\RunDrawAction;
use App\Application\Draw\Support\WheelSegmentBuilder;
use App\Livewire\Draw\Concerns\HandlesDraw;
use App\Livewire\Draw\Concerns\ManagesParticipants;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Page de tirage de la Roue d'Élimination (Battle Royale).
 * Gère l'état d'un jeu où les participants sont éliminés un par un jusqu'au vainqueur final.
 */
class EliminationWheelPage extends Component
{
    use HandlesDraw;
    use ManagesParticipants;

    /**
     * Nombre maximal de segments pour lesquels on affiche encore le texte sur la roue.
     * Au-delà, l'affichage devient illisible.
     */
    private const MAX_LABELS_ON_WHEEL = 10;

    /**
     * Stocke les erreurs de validation ou de logique à afficher à l'utilisateur.
     */
    public ?string $error = null;

    /**
     * Copie de sauvegarde de la liste de départ pour pouvoir relancer une partie.
     *
     * @var array<int, string>
     */
    public array $initialParticipants = [];

    /**
     * Liste ordonnée des participants éliminés.
     *
     * @var array<int, string>
     */
    public array $eliminated = [];

    /**
     * Couleurs HSL figées par nom de participant au lancement du jeu.
     *
     * @var array<string, string>
     */
    public array $colors = [];

    /**
     * Participant désigné par le tirage en cours d'élimination (pendant que la roue tourne).
     */
    public ?string $pendingElimination = null;

    /**
     * Dernier participant à avoir été éliminé.
     */
    public ?string $lastEliminated = null;

    /**
     * Le vainqueur final de la roue (le dernier survivant).
     */
    public ?string $winner = null;

    /**
     * Indique si un tirage et son animation de rotation sont en cours.
     */
    public bool $processing = false;

    /**
     * Angle de rotation cumulé de la roue (évite les retours en arrière lors des lancers successifs).
     */
    public int $wheelRotation = 0;

    /**
     * Mode automatique : enchaîne les éliminations sans intervention de l'utilisateur.
     */
    public bool $autoMode = false;

    /**
     * Verrouille la modification de la liste des participants dès que le jeu commence.
     */
    protected function participantsAreLocked(): bool
    {
        return $this->started();
    }

    /**
     * Déclenche automatiquement l'action suivante lorsque le mode auto est activé.
     */
    public function updatedAutoMode(bool $value): void
    {
        if ($value && ! $this->processing && ! $this->winner) {
            $this->handleAction();
        }
    }

    /**
     * Point d'entrée unique de l'action utilisateur (Bouton principal).
     * Lance le jeu s'il n'est pas commencé, ou procède à l'élimination suivante.
     */
    public function handleAction(?RunDrawAction $action = null): void
    {
        $action = $action ?? app(RunDrawAction::class);

        if (! $this->started()) {
            $this->start();

            if ($this->error) {
                return;
            }
        }

        $this->eliminateNext($action);
    }

    /**
     * Initialise la partie : fige les participants, génère la palette de couleurs stables
     * et réinitialise les états de progression.
     */
    public function start(): void
    {
        $this->error = null;

        if (count($this->participants) < 2) {
            $this->error = "Ajoutez au moins deux participants avant de lancer l'élimination.";

            return;
        }

        $this->initialParticipants = $this->participants;
        $this->colors = WheelSegmentBuilder::assignColors($this->participants);

        $this->eliminated = [];
        $this->winner = null;
        $this->pendingElimination = null;
        $this->lastEliminated = null;
        $this->processing = false;
        $this->wheelRotation = 0;

        // Force Livewire à recalculer la propriété computed lors du prochain cycle de rendu
        unset($this->segments);

        $this->dispatch('start-ready');
    }

    /**
     * Sélectionne le prochain participant à éliminer via le Domaine, calcule l'angle
     * de rotation requis et notifie le frontend (Alpine.js) pour lancer l'animation.
     */
    public function eliminateNext(?RunDrawAction $action = null): void
    {
        $action = $action ?? app(RunDrawAction::class);

        if ($this->processing || $this->winner || count($this->participants) <= 1) {
            return;
        }

        $this->processing = true;

        // Exécute le cas d'utilisation (Application) via le trait HandlesDraw
        $result = $this->executeDraw($action);

        if (! $result) {
            $this->processing = false;

            return;
        }

        $this->pendingElimination = $result->winner->name;

        $targetIndex = array_search($this->pendingElimination, $this->participants, true);

        if ($targetIndex === false) {
            $this->processing = false;

            return;
        }

        $totalParticipants = count($this->participants);
        $slice = 360 / $totalParticipants;

        // Calcule l'angle cible pour amener le centre du segment sélectionné
        // directement sous le pointeur situé à 12h (sommet).
        $targetAngle = 360 - (($targetIndex * $slice) + ($slice / 2));
        $targetAngle = fmod($targetAngle, 360);

        if ($targetAngle < 0) {
            $targetAngle += 360;
        }

        // Calcule une rotation minimale d'au moins 5 tours complets (1800°)
        // cumulée à la rotation précédente pour éviter que la roue ne tourne à l'envers.
        $minRotation = $this->wheelRotation + 1800;
        $current = fmod($minRotation, 360);

        $nextRotation = $targetAngle >= $current
            ? $minRotation + ($targetAngle - $current)
            : $minRotation + (360 - $current) + $targetAngle;

        $delta = $nextRotation - $this->wheelRotation;
        $this->wheelRotation = (int) $nextRotation;

        // On dispatch l'événement avec le delta de rotation pour que l'animation CSS s'applique correctement
        $this->dispatch('wheel-spin', rotation: $delta);
    }

    /**
     * Confirme l'élimination du participant désigné.
     * Cette méthode est appelée par le frontend (Alpine.js) une fois l'animation de la roue terminée.
     */
    public function confirmElimination(): void
    {
        if (! $this->processing || ! $this->pendingElimination) {
            $this->processing = false;

            return;
        }

        $player = $this->pendingElimination;

        if (! in_array($player, $this->eliminated, true)) {
            $this->eliminated[] = $player;
            $this->lastEliminated = $player;

            // Retire le joueur de la liste active et réindexe le tableau
            $this->participants = array_values(
                array_diff($this->participants, [$player])
            );

            $this->afterParticipantsChanged();
        }

        $this->pendingElimination = null;

        // S'il ne reste qu'un seul survivant, le jeu est terminé et nous avons notre vainqueur !
        if (count($this->participants) === 1) {
            $this->winner = $this->participants[0];
            $this->autoMode = false;
        }

        $this->processing = false;

        $this->dispatch('elimination-confirmed');
    }

    /**
     * Réinitialise complètement l'état de la page pour rejouer avec la liste initiale.
     */
    public function restart(): void
    {
        $this->participants = $this->initialParticipants;
        $this->initialParticipants = [];
        $this->eliminated = [];
        $this->colors = [];
        $this->pendingElimination = null;
        $this->lastEliminated = null;
        $this->winner = null;
        $this->processing = false;
        $this->error = null;
        $this->wheelRotation = 0;
        $this->autoMode = false;

        unset($this->segments);

        $this->dispatch('game-restarted');
    }

    /**
     * Indique si la partie a démarré (palette de couleurs initialisée).
     */
    public function started(): bool
    {
        return $this->colors !== [];
    }

    /**
     * Propriété calculée (Computed) contenant les tracés et configurations géométriques SVG des parts.
     *
     * @return array<int, array{name: string, color: string, path: ?string, fullCircle: bool, labelTransform: string}>
     */
    #[Computed]
    public function segments(): array
    {
        return WheelSegmentBuilder::build(
            $this->participants,
            $this->colors
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
        return view('livewire.draw.elimination-wheel-page')
            ->layout('layouts.app');
    }
}
