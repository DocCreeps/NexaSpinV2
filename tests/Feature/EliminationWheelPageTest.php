<?php

use App\Livewire\Draw\EliminationWheelPage;
use Livewire\Livewire;

// Note : EliminationWheelPage::MIN_PARTICIPANTS = 5 (contrairement à
// WheelPage/WeightedWheelPage). Avec moins de 5 participants, start()
// échoue silencieusement (error positionné, colors jamais initialisé),
// donc started()/participantsAreLocked() restent false et toute la suite
// du scénario testé (élimination, verrouillage...) ne se produit jamais.
// Tous les tests ci-dessous utilisent donc au moins 5 participants.

it('eliminates participants one by one until a single winner remains', function () {
    $names = ['John', 'Jane', 'Bob', 'Alice', 'Eve'];

    // Important : start() initialise l'état (couleurs, reset) mais NE lance
    // PAS de tirage — il remet même explicitement pendingElimination à null.
    // C'est handleAction() (la méthode réellement liée au bouton dans la
    // vue via wire:click="handleAction") qui enchaîne start() + eliminateNext()
    // au premier appel, puis directement eliminateNext() aux appels suivants.
    $component = Livewire::test(EliminationWheelPage::class)
        ->set('participants', $names)
        ->call('handleAction');

    expect($component->get('pendingElimination'))->not->toBeNull();

    // Un tour = confirmer l'élimination en attente, puis relancer le tour
    // suivant (handleAction, pas eliminateNext seul) tant qu'il n'y a pas
    // de vainqueur.
    for ($i = 0; $i < count($names) - 1; $i++) {
        $component->call('confirmElimination');

        if ($component->get('winner') === null) {
            $component->call('handleAction');
        }
    }

    expect($component->get('winner'))->not->toBeNull()
        ->and($names)->toContain($component->get('winner'))
        ->and($component->get('eliminated'))->toHaveCount(4)
        ->and(array_unique($component->get('eliminated')))->toHaveCount(4)
        ->and($component->get('eliminated'))->not->toContain($component->get('winner'));
});

it('requires at least two participants to start', function () {
    $component = Livewire::test(EliminationWheelPage::class)
        ->set('participants', ['Solo'])
        ->call('start');

    expect($component->get('winner'))->toBeNull()
        ->and($component->get('error'))->not->toBeNull()
        ->and($component->get('pendingElimination'))->toBeNull();
});

it('does not let a second round start while one is already pending', function () {
    $component = Livewire::test(EliminationWheelPage::class)
        ->set('participants', ['John', 'Jane', 'Bob', 'Alice', 'Eve'])
        ->call('handleAction');

    $pending = $component->get('pendingElimination');

    expect($pending)->not->toBeNull();

    // processing=true tant que confirmElimination() n'a pas été appelé :
    // un second eliminateNext() pendant ce temps doit être un no-op.
    $component->call('eliminateNext');

    expect($component->get('pendingElimination'))->toBe($pending);
});

it('restarts the game back to its initial participant list', function () {
    $names = ['John', 'Jane', 'Bob', 'Alice', 'Eve'];

    $component = Livewire::test(EliminationWheelPage::class)
        ->set('participants', $names)
        ->call('handleAction')
        ->call('confirmElimination');

    expect($component->get('eliminated'))->toHaveCount(1);

    $component->call('restart');

    expect($component->get('participants'))->toBe($names)
        ->and($component->get('eliminated'))->toBe([])
        ->and($component->get('winner'))->toBeNull()
        ->and($component->get('pendingElimination'))->toBeNull()
        ->and($component->instance()->started())->toBeFalse();
});

it('locks participant editing once the elimination wheel has started', function () {
    $names = ['John', 'Jane', 'Bob', 'Alice', 'Eve'];

    $component = Livewire::test(EliminationWheelPage::class)
        ->set('participants', $names)
        ->call('start');

    // Précondition : si start() a échoué silencieusement (ex: participants
    // < MIN_PARTICIPANTS), started() est false et ce test ne vérifierait
    // plus rien du tout — on s'assure donc explicitement d'être verrouillé
    // avant de tester le verrouillage lui-même.
    expect($component->instance()->started())->toBeTrue();

    $component
        ->set('participant', 'Zoe')
        ->call('addParticipant')
        ->call('removeParticipant', 0);

    // Comparaison de contenu (pas seulement de taille) : un ajout suivi
    // d'une suppression peut accidentellement retomber sur le même nombre
    // d'éléments tout en ayant changé le contenu réel de la liste.
    expect($component->get('participants'))->toBe($names);
});
