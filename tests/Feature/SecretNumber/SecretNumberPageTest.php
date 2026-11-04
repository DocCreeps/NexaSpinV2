<?php

use App\Application\History\HistoryStore;
use App\Application\Home\Enums\GameModeType;
use App\Application\SecretNumber\SecretNumberGameStore;
use App\Livewire\SecretNumber\SecretNumberPage;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

/** Le nombre secret n'est jamais côté navigateur : on le relit dans le store serveur. */
function currentSecret($component): int
{
    return app(SecretNumberGameStore::class)->load($component->get('roundId'))->toArray()['secret'];
}

function nextGuessNot(int $secret, int $skip = 0): int
{
    $candidates = array_values(array_filter(range(0, 100), fn (int $n) => $n !== $secret));

    return $candidates[$skip];
}

function startedGame(string $mode = 'solo', array $settings = [])
{
    $component = Livewire::test(SecretNumberPage::class)
        ->set('minNumber', 0)
        ->set('maxNumber', 100)
        ->call('setGameMode', $mode);

    foreach ($settings as $property => $value) {
        $component->set($property, $value);
    }

    return $component->call('startGame');
}

it('starts on the setup screen with the history of the visitor', function () {
    app(HistoryStore::class)->push(GameModeType::SECRET_NUMBER, ['game_mode' => 'solo', 'winner' => 1]);

    Livewire::test(SecretNumberPage::class)
        ->assertSet('gameState', [])
        ->assertSee('Choisissez votre mode')
        ->assertCount('history', 1);
});

it('applies a quick range preset and ignores an unknown one', function () {
    Livewire::test(SecretNumberPage::class)
        ->call('setRangePreset', '0-10000')
        ->assertSet('minNumber', 0)
        ->assertSet('maxNumber', 10000)
        ->call('setRangePreset', 'bidon')
        ->assertSet('maxNumber', 10000);
});

it('starts a solo game without sending the secret to the browser', function () {
    $component = startedGame('solo');
    $secret = currentSecret($component);

    expect($component->get('gameState'))->not->toHaveKey('secret')
        ->and(json_encode($component->get('gameState')))->not->toContain('"secret"');
    expect($secret)->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(100);
});

it('plays a solo game to the end, archives it and frees the server copy', function () {
    $component = startedGame('solo');
    $secret = currentSecret($component);
    $roundId = $component->get('roundId');

    $component->set('guess', (string) nextGuessNot($secret))->call('submitGuess')
        ->assertSet('gameState.phase', 'playing')
        ->set('guess', (string) $secret)->call('submitGuess')
        ->assertSet('gameState.phase', 'finished')
        ->assertSet('gameState.winner', 1)
        ->assertSet('gameState.revealed_number', $secret)
        ->assertCount('history', 1);

    expect(app(SecretNumberGameStore::class)->load($roundId))->toBeNull();
    $entry = app(HistoryStore::class)->all(GameModeType::SECRET_NUMBER)[0];
    expect($entry['secret'])->toBe($secret)->and($entry['winner'])->toBe(1);
});

it('does not archive the same game twice when a finished game receives another guess', function () {
    $component = startedGame('solo');
    $secret = currentSecret($component);

    $component->set('guess', (string) $secret)->call('submitGuess')
        ->set('guess', '5')->call('submitGuess')
        ->assertCount('history', 1)
        ->assertSet('error', null);
});

it('shows an error for an invalid, out-of-range or repeated guess', function () {
    $component = startedGame('solo');
    $secret = currentSecret($component);
    $wrong = nextGuessNot($secret);

    $component->set('guess', 'abc')->call('submitGuess')->assertSet('error', 'Entrez un nombre entier.')
        ->set('guess', '101')->call('submitGuess')->assertSet('error', 'La proposition est hors de la plage autorisée.')
        ->set('guess', (string) $wrong)->call('submitGuess')->assertSet('error', null)
        ->set('guess', (string) $wrong)->call('submitGuess')->assertSet('error', 'Vous avez déjà proposé ce nombre. Choisissez-en un autre.');
});

it('falls back to the default value when a numeric setting is cleared', function () {
    $component = Livewire::test(SecretNumberPage::class)
        ->set('maxNumber', '')
        ->call('startGame');

    expect($component->get('gameState.maximum'))->toBe(1000);

    Livewire::test(SecretNumberPage::class)
        ->call('setGameMode', 'duel')
        ->set('playerCount', '')
        ->call('startGame')
        ->assertSet('gameState.player_count', 2);
});

it('refuses an invalid configuration', function () {
    Livewire::test(SecretNumberPage::class)
        ->set('minNumber', 50)->set('maxNumber', 10)
        ->call('startGame')
        ->assertSet('gameState', [])
        ->assertNotSet('error', null);

    Livewire::test(SecretNumberPage::class)
        ->call('setGameMode', 'duel')->set('playerCount', 11)
        ->call('startGame')
        ->assertSet('error', 'Le nombre de joueurs doit être compris entre 2 et 10.');
});

it('normalizes the player names when a duel starts', function () {
    $component = startedGame('duel', ['playerCount' => 3, 'playerNames' => [1 => '  Ada ', 2 => '', 3 => 'Linus']]);

    $component->assertSet('gameState.player_names', [1 => 'Ada', 2 => 'Joueur 2', 3 => 'Linus'])
        ->assertSet('playerNames', [1 => 'Ada', 2 => 'Joueur 2', 3 => 'Linus']);
});

it('crowns the player who found the number over one who ran out of attempts', function () {
    $component = startedGame('duel', ['maxAttempts' => 2]);
    $secret = currentSecret($component);

    $component->set('guess', (string) nextGuessNot($secret, 0))->call('submitGuess')
        ->set('guess', (string) $secret)->call('submitGuess')
        ->assertSet('gameState.phase', 'handoff')
        ->call('continueAfterHandoff')
        ->set('guess', (string) nextGuessNot($secret, 0))->call('submitGuess')
        ->set('guess', (string) nextGuessNot($secret, 1))->call('submitGuess')
        ->assertSet('gameState.phase', 'finished')
        ->assertSet('gameState.winner', 1);
});

it('plays a versus game in two legs', function () {
    $component = startedGame('versus');

    $component->assertSet('gameState.phase', 'choose_secret')
        ->set('secretInput', '40')->call('submitSecret')
        ->assertSet('gameState.phase', 'handoff')
        ->assertSet('secretInput', '')
        ->call('continueAfterHandoff')
        ->set('guess', '40')->call('submitGuess')
        ->assertSet('gameState.phase', 'handoff')
        ->call('continueAfterHandoff')
        ->assertSet('gameState.phase', 'choose_secret')
        ->set('secretInput', '60')->call('submitSecret')
        ->call('continueAfterHandoff')
        ->set('guess', '1')->call('submitGuess')
        ->set('guess', '60')->call('submitGuess')
        ->assertSet('gameState.phase', 'finished')
        ->assertSet('gameState.winner', 2)
        ->assertCount('history', 1);
});

it('refuses a versus secret that is invalid and keeps the choose phase', function () {
    startedGame('versus')
        ->set('secretInput', '500')->call('submitSecret')
        ->assertSet('gameState.phase', 'choose_secret')
        ->assertSet('error', 'Le nombre choisi est hors de la plage autorisée.')
        ->set('secretInput', 'x')->call('submitSecret')
        ->assertSet('error', 'Entrez un nombre entier.');
});

it('ignores actions that do not match the current phase', function () {
    $component = Livewire::test(SecretNumberPage::class)
        ->set('guess', '5')->call('submitGuess')
        ->call('continueAfterHandoff')
        ->set('secretInput', '5')->call('submitSecret')
        ->assertSet('gameState', [])
        ->assertSet('error', null);

    startedGame('solo')->set('secretInput', '5')->call('submitSecret')->assertSet('gameState.phase', 'playing');
});

it('tells the player when the server copy of the game has expired', function () {
    $component = startedGame('solo');
    app(SecretNumberGameStore::class)->clear($component->get('roundId'));

    $component->set('guess', '5')->call('submitGuess')
        ->assertSet('error', 'La partie a expiré. Lancez une nouvelle partie.');
});

it('treats a game saved in an unreadable format as expired', function () {
    $component = startedGame('solo');
    $roundId = $component->get('roundId');
    $key = sprintf('secret-number:game:%s:%s', session()->getId(), $roundId);
    cache()->put($key, ['mode' => 'solo', 'phase' => 'playing'], 60);

    $component->set('guess', '5')->call('submitGuess')
        ->assertSet('error', 'La partie a expiré. Lancez une nouvelle partie.');

    expect(cache()->has($key))->toBeFalse();
});

it('does not leave the previous round behind when a new game starts', function () {
    $component = startedGame('solo');
    $firstRound = $component->get('roundId');
    $component->set('guess', (string) currentSecret($component))->call('submitGuess');

    $component->call('startGame');

    expect($component->get('roundId'))->not->toBe($firstRound)
        ->and(app(SecretNumberGameStore::class)->load($firstRound))->toBeNull();
});

it('cannot change settings or restart a running game through the settings actions', function () {
    $component = startedGame('solo');

    $component->call('setGameMode', 'duel')->call('setRangePreset', '0-100')->call('startGame')
        ->assertSet('gameMode', 'solo')
        ->assertSet('gameState.phase', 'playing');
});

it('returns to the mode selection and drops the running game', function () {
    $component = startedGame('solo');
    $roundId = $component->get('roundId');

    $component->call('returnToModeSelection')->assertSet('gameState', []);

    expect(app(SecretNumberGameStore::class)->load($roundId))->toBeNull();
});

it('clears the history', function () {
    $component = startedGame('solo');
    $component->set('guess', (string) currentSecret($component))->call('submitGuess')->assertCount('history', 1);

    $component->call('clearHistory')->assertSet('history', []);
});

it('locks the state that must not be forged by the browser', function (string $property, mixed $value) {
    Livewire::test(SecretNumberPage::class)->set($property, $value);
})->with([
    ['gameState', ['phase' => 'finished']],
    ['roundId', 'forged'],
    ['history', []],
])->throws(CannotUpdateLockedPropertyException::class);

it('renders every phase without errors', function () {
    startedGame('solo')->assertSee('DEVINER');
    startedGame('duel')->assertSee('Classement')->assertSee('DEVINER');
    startedGame('versus')->assertSee('choisissez le nombre secret');
});
