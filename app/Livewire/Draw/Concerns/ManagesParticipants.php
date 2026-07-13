<?php

namespace App\Livewire\Draw\Concerns;

trait ManagesParticipants
{
    protected int $maxParticipants = 100;

    protected int $maxParticipantNameLength = 50;

    /** @var array<int, string> */
    public array $participants = [];

    public string $participant = '';

    public ?string $error = null;

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

        if (mb_strlen($name) > $this->maxParticipantNameLength) {
            $this->error = 'Le nom d\'un participant ne peut pas dépasser '.$this->maxParticipantNameLength.' caractères.';

            return;
        }

        if (count($this->participants) >= $this->maxParticipants) {
            $this->error = 'Vous ne pouvez pas ajouter plus de '.$this->maxParticipants.' participants.';

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

        $this->afterParticipantsChanged();
    }

    public function removeParticipant(int $index): void
    {
        if ($this->participantsAreLocked()) {
            return;
        }

        unset($this->participants[$index]);

        $this->participants = array_values($this->participants);

        $this->afterParticipantsChanged();
    }

    /**
     * Point d'extension appelé après tout ajout/suppression réussi, pour
     * que les composants concrets puissent réagir (ex : invalider un
     * résultat de tirage devenu obsolète). Ne fait rien par défaut.
     */
    protected function afterParticipantsChanged(): void
    {
        //
    }

    /**
     * Empêche l'ajout/la suppression, typiquement une fois un tirage lancé.
     * Les composants qui n'ont pas cette notion (roue simple, jamais
     * verrouillée) n'ont rien à surcharger.
     */
    protected function participantsAreLocked(): bool
    {
        return false;
    }
}
