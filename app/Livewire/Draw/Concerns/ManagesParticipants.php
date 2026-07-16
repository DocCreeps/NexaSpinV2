<?php

namespace App\Livewire\Draw\Concerns;

/**
 * Trait Livewire gérant l'état et le cycle de vie de la liste des participants.
 * Offre des hooks et des validations d'intégrité pour l'ajout, l'édition et la suppression.
 */
trait ManagesParticipants
{
    /**
     * Limite maximale de participants autorisés pour un tirage.
     */
    protected int $maxParticipants = 100;

    /**
     * Longueur maximale autorisée pour le nom d'un participant.
     */
    protected int $maxParticipantNameLength = 50;

    protected int $minParticipantWeight = 1;

    protected int $maxParticipantWeight = 100;

    /**
     * Liste réactive des participants (indexée numériquement).
     *
     * @var array<int, string>
     */
    public array $participants = [];
    public array $participantWeights = [];
    /**
     * Champ de saisie lié au formulaire d'ajout d'un participant.
     */
    public string $participant = '';

    /**
     * Stocke le message d'erreur de validation temporaire pour l'UI.
     */
    public ?string $error = null;

    /**
     * Valide et ajoute un nouveau participant à la liste.
     */
    public function addParticipant(): void
    {
        if ($this->participantsAreLocked()) {
            return;
        }

        $this->error = null;
        $name = trim($this->participant);

        if ($name === '') {
            return;
        }

        // Validation : Longueur maximale du nom
        if (mb_strlen($name) > $this->maxParticipantNameLength) {
            $this->error = sprintf(
                'Le nom du participant ne peut pas dépasser %d caractères.',
                $this->maxParticipantNameLength
            );

            return;
        }

        // Validation : Capacité maximale atteinte
        if (count($this->participants) >= $this->maxParticipants) {
            $this->error = sprintf(
                'Vous ne pouvez pas ajouter plus de %d participants.',
                $this->maxParticipants
            );

            return;
        }

        // Validation : Unicité du nom
        if ($this->participantExists($name)) {
            $this->error = 'Ce participant existe déjà.';

            return;
        }

        $this->participants[] = $name;
        $this->participantWeights[] = $this->minParticipantWeight;
        $this->participant = '';

        $this->afterParticipantsChanged();
    }

    /**
     * Met à jour un participant existant (Édition Inline).
     */
    public function updateParticipant(int $index, string $newName): void
    {
        if ($this->participantsAreLocked()) {
            return;
        }

        $this->error = null;
        $newName = trim($newName);

        // Validation : Nom vide
        if ($newName === '') {
            $this->error = 'Le nom du participant ne peut pas être vide.';

            return;
        }

        // Validation : Longueur maximale du nom
        if (mb_strlen($newName) > $this->maxParticipantNameLength) {
            $this->error = sprintf(
                'Le nom du participant ne peut pas dépasser %d caractères.',
                $this->maxParticipantNameLength
            );

            return;
        }

        // Validation : Doublon (en ignorant sa propre ancienne valeur)
        $oldName = $this->participants[$index] ?? null;
        if (
            $oldName !== null
            && mb_strtolower($oldName) !== mb_strtolower($newName)
            && $this->participantExists($newName)
        ) {
            $this->error = 'Ce participant existe déjà.';

            return;
        }

        // Sauvegarde si l'index est valide
        if (array_key_exists($index, $this->participants)) {
            $this->participants[$index] = $newName;
            $this->afterParticipantsChanged();
        }
    }
    /**
     * Met à jour le poids d'un participant (utilisé par les modes de tirage pondérés uniquement).
     */
    public function updateParticipantWeight(int $index, int $weight): void
    {
        if ($this->participantsAreLocked()) {
            return;
        }

        if (! array_key_exists($index, $this->participants)) {
            return;
        }

        $this->error = null;

        if ($weight < $this->minParticipantWeight || $weight > $this->maxParticipantWeight) {
            $this->error = sprintf(
                'Le poids doit être compris entre %d et %d.',
                $this->minParticipantWeight,
                $this->maxParticipantWeight
            );

            return;
        }

        $this->participantWeights[$index] = $weight;

        $this->afterParticipantsChanged();
    }
    /**
     * Supprime un participant et réindexe proprement le tableau.
     */
    public function removeParticipant(int $index): void
    {
        if ($this->participantsAreLocked()) {
            return;
        }

        unset($this->participants[$index]);
        unset($this->participantWeights[$index]);

        // Réindexation pour éviter les trous de clés dans le tableau JS/JSON côté frontend
        $this->participants = array_values($this->participants);

        $this->participantWeights = array_values($this->participantWeights);

        $this->afterParticipantsChanged();
    }

    /**
     * Vérifie de manière insensible à la casse si un participant est déjà présent.
     */
    protected function participantExists(string $name): bool
    {
        return collect($this->participants)
            ->contains(
                fn (string $existing) => mb_strtolower($existing) === mb_strtolower($name)
            );
    }

    /**
     * Indique si le nombre minimal de participants requis est atteint.
     */
    protected function hasEnoughParticipants(int $minimum = 2): bool
    {
        return count($this->participants) >= $minimum;
    }

    /**
     * Valide le minimum requis et génère une erreur utilisateur si nécessaire.
     */
    protected function requireParticipants(int $minimum = 2): bool
    {
        if ($this->hasEnoughParticipants($minimum)) {
            return true;
        }

        $this->error = "Ajoutez au moins {$minimum} participants avant de lancer le tirage.";

        return false;
    }

    /**
     * Hook de cycle de vie optionnel pouvant être surchargé par le composant hôte
     * (ex: synchronisation de session, persistance, etc.).
     */
    protected function afterParticipantsChanged(): void
    {
        // À surcharger au besoin
    }

    /**
     * Permet de verrouiller l'état de la liste pendant le déroulement d'une action
     * (ex: pendant que la roue tourne).
     */
    protected function participantsAreLocked(): bool
    {
        return false;
    }
}
