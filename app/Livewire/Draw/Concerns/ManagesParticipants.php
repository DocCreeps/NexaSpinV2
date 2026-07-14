<?php

namespace App\Livewire\Draw\Concerns;

trait ManagesParticipants
{
    protected int $maxParticipants = 100;

    protected int $maxParticipantNameLength = 50;


    /** @var array<int,string> */
    public array $participants = [];


    public string $participant = '';


    public ?string $error = null;



    /**
     * Ajoute un participant après validation.
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


        if (
            mb_strlen($name)
            > $this->maxParticipantNameLength
        ) {

            $this->error =
                'Le nom du participant ne peut pas dépasser '
                . $this->maxParticipantNameLength
                . ' caractères.';

            return;
        }


        if (
            count($this->participants)
            >= $this->maxParticipants
        ) {

            $this->error =
                'Vous ne pouvez pas ajouter plus de '
                . $this->maxParticipants
                . ' participants.';

            return;
        }



        if ($this->participantExists($name)) {

            $this->error =
                'Ce participant existe déjà.';

            return;
        }



        $this->participants[] = $name;


        $this->participant = '';


        $this->afterParticipantsChanged();
    }





    /**
     * Supprime un participant.
     */
    public function removeParticipant(
        int $index
    ): void {

        if ($this->participantsAreLocked()) {
            return;
        }


        unset($this->participants[$index]);


        $this->participants =
            array_values($this->participants);


        $this->afterParticipantsChanged();
    }





    /**
     * Vérifie si un participant existe déjà.
     */
    protected function participantExists(
        string $name
    ): bool {

        return collect($this->participants)
            ->contains(
                fn(string $existing) =>
                mb_strtolower($existing)
                    === mb_strtolower($name)
            );
    }





    /**
     * Vérifie qu'un tirage peut être lancé.
     */
    protected function hasEnoughParticipants(
        int $minimum = 2
    ): bool {

        return count($this->participants) >= $minimum;
    }





    /**
     * Message commun d'erreur.
     */
    protected function requireParticipants(
        int $minimum = 2
    ): bool {

        if ($this->hasEnoughParticipants($minimum)) {
            return true;
        }


        $this->error =
            "Ajoutez au moins {$minimum} participants avant de lancer le tirage.";


        return false;
    }





    /**
     * Hook exécuté après modification.
     */
    protected function afterParticipantsChanged(): void
    {
        //
    }





    /**
     * Permet de bloquer les modifications
     * pendant un tirage.
     */
    protected function participantsAreLocked(): bool
    {
        return false;
    }
}
