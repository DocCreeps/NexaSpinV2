<?php

use App\Livewire\Draw\EliminationWheelPage;
use Livewire\Livewire;

it('eliminates participants one by one until a single winner remains', function () {
    $names = ['John', 'Jane', 'Bob', 'Alice'];

    $component = Livewire::test(EliminationWheelPage::class)
        ->set('participants', $names)
        ->call('start');

    expect($component->get('pendingElimination'))->not->toBeNull();

    // Un tour = confirmer l'élimination en attente, puis relancer le tour
    // suivant tant qu'il n'y a pas de vainqueur.
    for ($i = 0; $i < count($names) - 1; $i++) {
        $component->call('confirmElimination');

        if ($component->get('winner') === null) {
            $component->call('eliminateNext');
        }
    }

    expect($component->get('winner'))->not->toBeNull()
        ->and($names)->toContain($component->get('winner'))
        ->and($component->get('eliminated'))->toHaveCount(3)
        ->and(array_unique($component->get('eliminated')))->toHaveCount(3)
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
        ->set('participants', ['John', 'Jane', 'Bob'])
        ->call('start');

    $pending = $component->get('pendingElimination');

    $component->call('eliminateNext');

    expect($component->get('pendingElimination'))->toBe($pending);
});
