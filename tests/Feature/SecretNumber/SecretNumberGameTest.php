<?php

use App\Domain\SecretNumber\Entities\SecretNumberGame;
use App\Domain\SecretNumber\Enums\SecretNumberMode;
use App\Domain\SecretNumber\Enums\SecretNumberPhase;
use App\Domain\SecretNumber\Exceptions\InvalidSecretNumberGameException;
use App\Domain\SecretNumber\ValueObjects\SecretNumberPlayers;
use App\Domain\SecretNumber\ValueObjects\SecretNumberRange;

function secretNumberPlayers(int $count = 2, array $names = []): SecretNumberPlayers
{
    return SecretNumberPlayers::fromInput($count, $names);
}

/** Propose $count nombres différents du secret. */
function playWrongGuesses(SecretNumberGame $game, int $secret, int $count): void
{
    $played = 0;
    for ($candidate = 0; $played < $count; $candidate++) {
        if ($candidate !== $secret) {
            $game->guess($candidate);
            $played++;
        }
    }
}

it('keeps the architecture layers apart', function () {
    expect('App\Domain\SecretNumber')->not->toUse(['Illuminate', 'Livewire']);
    expect('App\Application\SecretNumber')->not->toUse('Livewire');
});

describe('range and players value objects', function () {
    it('rejects an invalid range', function (int $minimum, int $maximum) {
        new SecretNumberRange($minimum, $maximum);
    })->with([[-1, 10], [10, 10], [20, 10], [0, 1_000_001]])->throws(InvalidSecretNumberGameException::class);

    it('exposes the quick presets as ranges', function () {
        $range = SecretNumberRange::fromPreset('0-1000');

        expect($range->minimum)->toBe(0)
            ->and($range->maximum)->toBe(1000)
            ->and(SecretNumberRange::fromPreset('nope'))->toBeNull();
    });

    it('normalizes player names and falls back to a default name', function () {
        $players = SecretNumberPlayers::fromInput(3, [1 => '  Ada  ', 2 => '', 3 => str_repeat('x', 40), 4 => 'ignoré']);

        expect($players->names)->toBe([1 => 'Ada', 2 => 'Joueur 2', 3 => str_repeat('x', 24)])
            ->and($players->name(9))->toBe('Joueur 9');
    });

    it('rejects a player count out of bounds', function (int $count) {
        SecretNumberPlayers::fromInput($count);
    })->with([1, 11])->throws(InvalidSecretNumberGameException::class);
});

describe('solo', function () {
    it('gives plus / moins hints and finishes when the number is found', function () {
        $game = SecretNumberGame::forAi(SecretNumberMode::SOLO, new SecretNumberRange(0, 100), 0, 42, secretNumberPlayers());

        $game->guess(10);
        expect($game->publicState()['guesses'][1][0]['hint'])->toBe('plus');
        $game->guess(90);
        expect($game->publicState()['guesses'][1][1]['hint'])->toBe('moins');
        $game->guess(42);

        $state = $game->publicState();
        expect($game->phase())->toBe(SecretNumberPhase::FINISHED)
            ->and($state['winner'])->toBe(1)
            ->and($state['revealed_number'])->toBe(42)
            ->and($state['attempts'][1])->toBe(3);
    });

    it('ends without a winner once the attempts are exhausted', function () {
        $game = SecretNumberGame::forAi(SecretNumberMode::SOLO, new SecretNumberRange(0, 100), 2, 42, secretNumberPlayers());

        playWrongGuesses($game, 42, 2);

        expect($game->phase())->toBe(SecretNumberPhase::FINISHED)
            ->and($game->publicState()['winner'])->toBeNull();
    });

    it('refuses out-of-range and repeated guesses without consuming an attempt', function () {
        $game = SecretNumberGame::forAi(SecretNumberMode::SOLO, new SecretNumberRange(0, 100), 0, 42, secretNumberPlayers());
        $game->guess(10);

        expect(fn () => $game->guess(101))->toThrow(InvalidSecretNumberGameException::class)
            ->and(fn () => $game->guess(10))->toThrow(InvalidSecretNumberGameException::class)
            ->and($game->publicState()['attempts'][1])->toBe(1);
    });

    it('refuses any guess once finished', function () {
        $game = SecretNumberGame::forAi(SecretNumberMode::SOLO, new SecretNumberRange(0, 100), 0, 42, secretNumberPlayers());
        $game->guess(42);

        $game->guess(1);
    })->throws(InvalidSecretNumberGameException::class);
});

describe('duel', function () {
    it('hands over the device and hides previous turns', function () {
        $game = SecretNumberGame::forAi(SecretNumberMode::DUEL, new SecretNumberRange(0, 100), 0, 42, secretNumberPlayers(3));

        playWrongGuesses($game, 42, 2);
        $game->guess(42);

        $state = $game->publicState();
        expect($game->phase())->toBe(SecretNumberPhase::HANDOFF)
            ->and($state['current_player'])->toBe(2)
            ->and($state['guesses'])->toBe([2 => []])
            ->and($state['feedback'])->toBeNull();
    });

    it('ranks by fewest attempts and names the unique winner', function () {
        $game = SecretNumberGame::forAi(SecretNumberMode::DUEL, new SecretNumberRange(0, 100), 0, 42, secretNumberPlayers());
        playWrongGuesses($game, 42, 3);
        $game->guess(42);
        $game->continueAfterHandoff();
        $game->guess(42);

        $state = $game->publicState();
        expect($state['winner'])->toBe(2)
            ->and($state['rankings'][0]['player'])->toBe(2)
            ->and($state['rankings'][0]['rank'])->toBe(1);
    });

    it('declares a tie when two players find the number with the same attempts', function () {
        $game = SecretNumberGame::forAi(SecretNumberMode::DUEL, new SecretNumberRange(0, 100), 0, 42, secretNumberPlayers());
        $game->guess(42);
        $game->continueAfterHandoff();
        $game->guess(42);

        $state = $game->publicState();
        expect($state['winner'])->toBeNull()
            ->and($state['any_player_found'])->toBeTrue()
            ->and(array_column($state['rankings'], 'rank'))->toBe([1, 1]);
    });

    it('lets a player who found the number beat one who ran out of attempts at the same count', function () {
        $game = SecretNumberGame::forAi(SecretNumberMode::DUEL, new SecretNumberRange(0, 100), 3, 42, secretNumberPlayers());
        playWrongGuesses($game, 42, 2);
        $game->guess(42);
        $game->continueAfterHandoff();
        playWrongGuesses($game, 42, 3);

        $state = $game->publicState();
        expect($state['winner'])->toBe(1)
            ->and($state['rankings'][0]['player'])->toBe(1)
            ->and($state['rankings'][1]['found'])->toBeFalse();
    });

    it('also lets the last player win when the first one ran out of attempts', function () {
        $game = SecretNumberGame::forAi(SecretNumberMode::DUEL, new SecretNumberRange(0, 100), 3, 42, secretNumberPlayers());
        playWrongGuesses($game, 42, 3);
        $game->continueAfterHandoff();
        playWrongGuesses($game, 42, 2);
        $game->guess(42);

        expect($game->publicState()['winner'])->toBe(2);
    });

    it('has no winner when nobody finds the number', function () {
        $game = SecretNumberGame::forAi(SecretNumberMode::DUEL, new SecretNumberRange(0, 100), 2, 42, secretNumberPlayers());
        playWrongGuesses($game, 42, 2);
        $game->continueAfterHandoff();
        playWrongGuesses($game, 42, 2);

        $state = $game->publicState();
        expect($state['winner'])->toBeNull()
            ->and($state['any_player_found'])->toBeFalse();
    });
});

describe('versus', function () {
    it('needs exactly two players', function () {
        SecretNumberGame::forVersus(new SecretNumberRange(0, 100), 0, secretNumberPlayers(3));
    })->throws(InvalidSecretNumberGameException::class);

    it('plays both legs and rewards the fastest guesser', function () {
        $game = SecretNumberGame::forVersus(new SecretNumberRange(0, 100), 0, secretNumberPlayers());

        $game->chooseSecret(10);
        $game->continueAfterHandoff();
        $game->guess(10);                  // joueur 2 trouve en 1 essai

        expect($game->phase())->toBe(SecretNumberPhase::HANDOFF);
        $game->continueAfterHandoff();
        $game->chooseSecret(20);           // joueur 2 choisit
        $game->continueAfterHandoff();
        $game->guess(0);
        $game->guess(20);                  // joueur 1 trouve en 2 essais

        $state = $game->publicState();
        expect($game->phase())->toBe(SecretNumberPhase::FINISHED)
            ->and($state['winner'])->toBe(2)
            ->and($state['versus_results'][0]['secret'])->toBe(10)
            ->and($state['versus_results'][1]['secret'])->toBe(20);
    });

    it('rejects a secret outside the range or outside the choose phase', function () {
        $game = SecretNumberGame::forVersus(new SecretNumberRange(0, 100), 0, secretNumberPlayers());

        expect(fn () => $game->chooseSecret(500))->toThrow(InvalidSecretNumberGameException::class);

        $game->chooseSecret(5);
        expect(fn () => $game->chooseSecret(6))->toThrow(InvalidSecretNumberGameException::class);
    });
});

describe('secret confidentiality', function () {
    it('never exposes the secret in the public state while the game is running', function () {
        foreach ([SecretNumberMode::SOLO, SecretNumberMode::DUEL] as $mode) {
            $game = SecretNumberGame::forAi($mode, new SecretNumberRange(0, 1_000_000), 0, 987_654, secretNumberPlayers());
            $game->guess(1);

            $state = $game->publicState();
            expect($state)->not->toHaveKey('secret')
                ->and($state['revealed_number'])->toBeNull()
                ->and(json_encode($state))->not->toContain('987654');
        }
    });

    it('hides the versus secret during the hand-off and the guessing leg', function () {
        $game = SecretNumberGame::forVersus(new SecretNumberRange(0, 1_000_000), 0, secretNumberPlayers());
        $game->chooseSecret(987_654);
        expect(json_encode($game->publicState()))->not->toContain('987654');

        $game->continueAfterHandoff();
        $game->guess(1);
        expect(json_encode($game->publicState()))->not->toContain('987654');
    });
});

describe('persistence', function () {
    it('round-trips through toArray / fromArray', function () {
        $game = SecretNumberGame::forAi(SecretNumberMode::DUEL, new SecretNumberRange(0, 100), 5, 42, secretNumberPlayers(3, [1 => 'Ada']));
        $game->guess(1);
        $game->guess(99);

        $restored = SecretNumberGame::fromArray($game->toArray());

        expect($restored->toArray())->toBe($game->toArray());
    });

    it('rejects an unversioned or incomplete state instead of crashing', function () {
        $state = SecretNumberGame::forAi(SecretNumberMode::SOLO, new SecretNumberRange(0, 100), 0, 42, secretNumberPlayers())->toArray();

        $unversioned = $state;
        unset($unversioned['version']);
        $incomplete = $state;
        unset($incomplete['solved']);

        expect(fn () => SecretNumberGame::fromArray($unversioned))->toThrow(InvalidSecretNumberGameException::class)
            ->and(fn () => SecretNumberGame::fromArray($incomplete))->toThrow(InvalidSecretNumberGameException::class);
    });

    it('limits the guesses sent to the browser but keeps all of them server-side', function () {
        $game = SecretNumberGame::forAi(SecretNumberMode::SOLO, new SecretNumberRange(0, 1_000_000), 0, 999_999, secretNumberPlayers());
        for ($guess = 0; $guess < SecretNumberGame::MAX_PUBLIC_GUESSES + 20; $guess++) {
            $game->guess($guess);
        }

        expect($game->publicState()['guesses'][1])->toHaveCount(SecretNumberGame::MAX_PUBLIC_GUESSES)
            ->and($game->toArray()['guesses'][1])->toHaveCount(SecretNumberGame::MAX_PUBLIC_GUESSES + 20);
    });
});
