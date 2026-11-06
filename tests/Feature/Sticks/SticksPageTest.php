<?php

use App\Application\History\HistoryStore;
use App\Application\Home\Enums\GameModeType;
use App\Livewire\Sticks\SticksPage;
use Livewire\Livewire;

it('starts a 21-stick game against the AI, with the human to move', function () {
    Livewire::test(SticksPage::class)
        ->assertSet('stickCount', 21)
        ->assertSet('opponentType', 'ai')
        ->assertSet('aiStarts', false)
        ->assertSet('started', false)
        ->assertSet('moves', []);
});

it('lets the stick count be chosen between 16 and 50, and clamps anything else', function () {
    Livewire::test(SticksPage::class)
        ->call('setStickCount', 30)
        ->assertSet('stickCount', 30)
        ->call('setStickCount', 5)
        ->assertSet('stickCount', 16)
        ->call('setStickCount', 99)
        ->assertSet('stickCount', 50);
});

it('shows the board only once the game has been started', function () {
    Livewire::test(SticksPage::class)
        ->assertSee('LANCER LA PARTIE')
        ->assertDontSee('Abandonner')
        ->call('startGame')
        ->assertSet('started', true)
        ->assertDontSee('LANCER LA PARTIE')
        ->assertSee('Abandonner');
});

it('does not accept moves before the game is started', function () {
    Livewire::test(SticksPage::class)
        ->call('take', [0])
        ->call('takeFirst', 2)
        ->assertSet('moves', []);
});

it('waits for the delayed AI turn instead of answering instantly', function () {
    $component = Livewire::test(SticksPage::class)
        ->call('setDifficulty', 'hard')
        ->call('startGame')
        ->call('take', [0]);

    expect($component->get('moves'))->toHaveCount(1)
        ->and($component->instance()->aiPending())->toBeTrue();

    $component->assertSee('réfléchit');

    // Le front déclenche le coup de l'IA après le délai.
    $component->call('playAiTurn');

    expect($component->get('moves'))->toHaveCount(2)
        ->and($component->instance()->aiPending())->toBeFalse()
        ->and($component->instance()->game->currentPlayer())->toBe(1)
        ->and($component->instance()->game->remaining())->toBe(17);

    // Hors tour de l'IA, l'appel est sans effet.
    $component->call('playAiTurn')->assertSet('moves', $component->get('moves'));
    expect($component->get('moves'))->toHaveCount(2);
});

it('reports how many sticks the last player removed', function () {
    $component = Livewire::test(SticksPage::class)
        ->call('startGame')
        ->call('take', [0, 1]);

    expect($component->instance()->lastMove())->toBe(['player' => 1, 'count' => 2]);

    $component->call('playAiTurn');

    $last = $component->instance()->lastMove();

    expect($last['player'])->toBe(2)
        ->and($last['count'])->toBe(count($component->get('moves')[1]));

    $component->assertSee('IA a retiré');
});

it('removes the first remaining sticks with the quick buttons', function () {
    $component = Livewire::test(SticksPage::class)
        ->call('setOpponentType', 'local')
        ->call('startGame')
        ->call('takeFirst', 3)
        ->assertSet('moves', [[0, 1, 2]])
        ->call('takeFirst', 2)
        ->assertSet('moves', [[0, 1, 2], [3, 4]]);

    expect($component->instance()->game->remaining())->toBe(16);
});

it('rejects a quick removal outside 1 to 3 sticks', function () {
    $component = Livewire::test(SticksPage::class)
        ->call('setOpponentType', 'local')
        ->call('startGame')
        ->call('takeFirst', 4);

    expect($component->get('error'))->not->toBeNull()
        ->and($component->get('moves'))->toBe([]);
});

it('rejects an invalid selection without changing the board', function (array $indices) {
    $component = Livewire::test(SticksPage::class)->call('startGame')->call('take', $indices);

    expect($component->get('error'))->not->toBeNull()
        ->and($component->get('moves'))->toBe([]);
})->with([
    'none' => [[]],
    'four' => [[0, 1, 2, 3]],
    'unknown' => [[99]],
    'duplicate' => [[4, 4]],
]);

it('locks the settings once the game is started', function () {
    $component = Livewire::test(SticksPage::class)->call('startGame');

    $component->call('setStickCount', 40)
        ->call('setOpponentType', 'local')
        ->call('setAiStarts', true)
        ->assertSet('stickCount', 21)
        ->assertSet('opponentType', 'ai')
        ->assertSet('aiStarts', false);
});

it('lets the AI open the game, after the delay, once the game is started', function () {
    $component = Livewire::test(SticksPage::class)
        ->call('setAiStarts', true)
        ->assertSet('moves', []);

    // Avant le lancement, l'humain ne peut pas jouer.
    $component->call('take', [0])->assertSet('moves', []);

    $component->call('startGame')->assertSet('moves', []);

    expect($component->instance()->aiPending())->toBeTrue();

    // Pendant le tour de l'IA, l'humain ne peut pas jouer à sa place.
    $component->call('take', [0])->assertSet('moves', []);

    $component->call('playAiTurn');

    expect($component->get('moves'))->toHaveCount(1)
        ->and($component->instance()->game->currentPlayer())->toBe(1);
});

it('alternates players in a local duel and records the finished game in the session history', function () {
    $component = Livewire::test(SticksPage::class)->call('setOpponentType', 'local')->call('startGame');

    for ($turn = 0; $turn < 60 && ! $component->instance()->game->isOver(); $turn++) {
        $component->call('take', [$component->instance()->game->remainingSticks()[0]]);
    }

    $component->call('revealResult');

    // 20 coups d'un bâtonnet : le joueur 1 se retrouve avec le dernier.
    expect($component->instance()->game->isOver())->toBeTrue()
        ->and($component->get('history'))->toHaveCount(1)
        ->and($component->get('history')[0]['winner'])->toBe(2)
        ->and(app(HistoryStore::class)->all(GameModeType::STICKS))->toHaveCount(1);
});

it('plays a whole game against the AI, then restarts with a clean board but keeps the history', function () {
    $component = Livewire::test(SticksPage::class)->call('startGame');

    for ($turn = 0; $turn < 60 && ! $component->instance()->game->isOver(); $turn++) {
        $component->call('take', [$component->instance()->game->remainingSticks()[0]]);
        $component->call('playAiTurn');
    }

    $component->call('revealResult');

    expect($component->instance()->game->isOver())->toBeTrue()
        ->and($component->get('history'))->toHaveCount(1);

    $component->call('restart');

    expect($component->get('moves'))->toBe([])
        ->and($component->get('started'))->toBeFalse()
        ->and($component->get('history'))->toHaveCount(1);
});

it('clears the history of the session', function () {
    $component = Livewire::test(SticksPage::class)->call('setOpponentType', 'local')->call('startGame');

    for ($turn = 0; $turn < 60 && ! $component->instance()->game->isOver(); $turn++) {
        $component->call('take', [$component->instance()->game->remainingSticks()[0]]);
    }

    $component->call('revealResult');

    $component->call('clearHistory');

    expect($component->get('history'))->toBe([])
        ->and(app(HistoryStore::class)->all(GameModeType::STICKS))->toBe([]);
});

it('announces the result and records the game only after the reveal delay', function () {
    $component = Livewire::test(SticksPage::class)
        ->call('setOpponentType', 'local')
        ->call('startGame');

    while (! $component->instance()->game->isOver()) {
        $component->call('take', [$component->instance()->game->remainingSticks()[0]]);
    }

    // Dernier coup joué : le message de fin et l'historique attendent.
    expect($component->instance()->resultPending())->toBeTrue()
        ->and($component->get('history'))->toBe([])
        ->and($component->get('resultRevealed'))->toBeFalse();

    $component->assertDontSee('GAGNE !');

    $component->call('revealResult');

    expect($component->get('resultRevealed'))->toBeTrue()
        ->and($component->get('history'))->toHaveCount(1);

    $component->assertSee('GAGNE !');

    // Idempotent : pas de doublon dans l'historique.
    $component->call('revealResult');
    expect($component->get('history'))->toHaveCount(1);
});

it('exposes the cumulated score of the session', function () {
    $component = Livewire::test(SticksPage::class)
        ->call('setOpponentType', 'local')
        ->call('startGame');

    while (! $component->instance()->game->isOver()) {
        $component->call('take', [$component->instance()->game->remainingSticks()[0]]);
    }

    $component->call('revealResult');

    expect($component->instance()->score())->toBe(['p1' => 0, 'p2' => 1]);
});

it('lets the rule be chosen before the game starts and locks it afterwards', function () {
    Livewire::test(SticksPage::class)
        ->assertSet('variant', 'last_loses')
        ->call('setVariant', 'last_wins')
        ->assertSet('variant', 'last_wins')
        ->call('setVariant', 'nonsense')
        ->assertSet('variant', 'last_wins')
        ->call('startGame')
        ->call('setVariant', 'last_loses')
        ->assertSet('variant', 'last_wins');
});

it('plays the last-wins variant to the end and records it in the history', function () {
    $component = Livewire::test(SticksPage::class)
        ->call('setOpponentType', 'local')
        ->call('setVariant', 'last_wins')
        ->call('startGame');

    while (! $component->instance()->game->isOver()) {
        $component->call('take', [$component->instance()->game->remainingSticks()[0]]);
    }

    // Un bâtonnet restant ne termine pas la partie dans cette variante : 21 coups de 1.
    expect($component->instance()->game->remaining())->toBe(0)
        ->and($component->get('moves'))->toHaveCount(21);

    $component->call('revealResult');

    $game = $component->get('history')[0];

    expect($game['winner'])->toBe(1)
        ->and($game['variant'])->toBe('last_wins')
        ->and($game['variant_label'])->toBe('Le dernier gagne');
});

it('lets the AI play the last-wins variant', function () {
    $component = Livewire::test(SticksPage::class)
        ->call('setVariant', 'last_wins')
        ->call('setDifficulty', 'hard')
        ->call('startGame');

    // L'humain retire 2 (il reste 19) : l'IA expert ramène la table à un multiple de 4.
    $component->call('take', [0, 1]);
    $component->call('playAiTurn');

    expect($component->instance()->game->remaining() % 4)->toBe(0);
});

it('uses custom player names in a local duel, trimmed and limited in length', function () {
    $component = Livewire::test(SticksPage::class)
        ->call('setOpponentType', 'local')
        ->call('setPlayerNames', '  Alice  ', str_repeat('B', 40))
        ->call('startGame');

    expect($component->instance()->playerLabel(1))->toBe('Alice')
        ->and($component->instance()->playerLabel(2))->toBe(str_repeat('B', 16));

    $component->assertSee('Alice');
});

it('falls back to default labels when names are empty or when playing the AI', function () {
    $local = Livewire::test(SticksPage::class)
        ->call('setOpponentType', 'local')
        ->call('setPlayerNames', '', '   ');

    expect($local->instance()->playerLabel(1))->toBe('Joueur 1')
        ->and($local->instance()->playerLabel(2))->toBe('Joueur 2');

    $ai = Livewire::test(SticksPage::class)->call('setPlayerNames', 'Alice', 'Bob');

    expect($ai->instance()->playerLabel(1))->toBe('Vous')
        ->and($ai->instance()->playerLabel(2))->toBe('IA');
});

it('stores the player names in the session history', function () {
    $component = Livewire::test(SticksPage::class)
        ->call('setOpponentType', 'local')
        ->call('setPlayerNames', 'Alice', 'Bob')
        ->call('startGame');

    while (! $component->instance()->game->isOver()) {
        $component->call('take', [$component->instance()->game->remainingSticks()[0]]);
    }

    $component->call('revealResult');

    $entry = app(HistoryStore::class)->all(GameModeType::STICKS)[0];

    expect($entry['p1_label'])->toBe('Alice')
        ->and($entry['p2_label'])->toBe('Bob')
        ->and($component->get('history')[0]['winner_label'])->toBe('Bob');
});

it('lets the second player open a local duel', function () {
    $component = Livewire::test(SticksPage::class)
        ->call('setOpponentType', 'local')
        ->call('setLocalStarter', 2)
        ->call('setLocalStarter', 7)
        ->assertSet('localStarter', 2)
        ->call('startGame');

    expect($component->instance()->game->currentPlayer())->toBe(2);

    $component->call('take', [0]);

    expect($component->instance()->game->currentPlayer())->toBe(1);
});

it('replays straight away on a rematch, with the same settings and swapped starters in a local duel', function () {
    $component = Livewire::test(SticksPage::class)
        ->call('setOpponentType', 'local')
        ->call('setVariant', 'last_wins')
        ->call('setStickCount', 24)
        ->call('startGame');

    // Revanche impossible tant que la partie n'est pas terminée et annoncée.
    $component->call('take', [0])->call('rematch');
    expect($component->get('moves'))->toHaveCount(1);

    while (! $component->instance()->game->isOver()) {
        $component->call('take', [$component->instance()->game->remainingSticks()[0]]);
    }

    $component->call('rematch');
    expect($component->get('started'))->toBeTrue()->and($component->get('moves'))->toHaveCount(24);

    $component->call('revealResult')->call('rematch');

    expect($component->get('started'))->toBeTrue()
        ->and($component->get('moves'))->toBe([])
        ->and($component->get('localStarter'))->toBe(2)
        ->and($component->get('variant'))->toBe('last_wins')
        ->and($component->get('stickCount'))->toBe(24)
        ->and($component->get('resultRevealed'))->toBeFalse()
        ->and($component->get('history'))->toHaveCount(1)
        ->and($component->instance()->game->currentPlayer())->toBe(2);
});

it('keeps the starter on a rematch against the AI, which opens after the delay when asked to', function () {
    $component = Livewire::test(SticksPage::class)
        ->call('setAiStarts', true)
        ->call('startGame');

    while (! $component->instance()->game->isOver()) {
        $component->instance()->aiPending()
            ? $component->call('playAiTurn')
            : $component->call('take', [$component->instance()->game->remainingSticks()[0]]);
    }

    $component->call('revealResult')->call('rematch');

    expect($component->get('started'))->toBeTrue()
        ->and($component->get('aiStarts'))->toBeTrue()
        ->and($component->get('moves'))->toBe([])
        ->and($component->instance()->aiPending())->toBeTrue();
});

it('goes back to the settings, without swapping the starter, with the other-settings button', function () {
    $component = Livewire::test(SticksPage::class)
        ->call('setOpponentType', 'local')
        ->call('startGame');

    while (! $component->instance()->game->isOver()) {
        $component->call('take', [$component->instance()->game->remainingSticks()[0]]);
    }

    $component->call('revealResult')->call('restart');

    expect($component->get('started'))->toBeFalse()
        ->and($component->get('localStarter'))->toBe(1)
        ->and($component->get('moves'))->toBe([]);
});

it('starts a fresh score when switching the opponent type', function () {
    $component = Livewire::test(SticksPage::class)
        ->call('setOpponentType', 'local')
        ->call('startGame');

    while (! $component->instance()->game->isOver()) {
        $component->call('take', [$component->instance()->game->remainingSticks()[0]]);
    }

    $component->call('revealResult');

    expect($component->instance()->score())->toBe(['p1' => 0, 'p2' => 1]);

    $component->call('restart');

    // Même type d'adversaire : le score est conservé.
    $component->call('setOpponentType', 'local');
    expect($component->instance()->score())->toBe(['p1' => 0, 'p2' => 1]);

    // Autre type d'adversaire : le score repart de zéro.
    $component->call('setOpponentType', 'ai');
    expect($component->instance()->score())->toBe(['p1' => 0, 'p2' => 0])
        ->and($component->get('history'))->toBe([]);
});

it('undoes the last move in a local duel only', function () {
    $component = Livewire::test(SticksPage::class)
        ->call('setOpponentType', 'local')
        ->call('startGame')
        ->call('take', [0, 1])
        ->call('take', [5])
        ->call('undo');

    expect($component->get('moves'))->toBe([[0, 1]])
        ->and($component->instance()->game->currentPlayer())->toBe(2)
        ->and($component->instance()->game->remaining())->toBe(19);

    $component->call('undo')->call('undo');

    expect($component->get('moves'))->toBe([]);

    $ai = Livewire::test(SticksPage::class)->call('startGame')->call('take', [0]);
    $ai->call('undo');

    expect($ai->get('moves'))->toHaveCount(1);
});
