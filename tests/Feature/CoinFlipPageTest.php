<?php

use App\Domain\CoinFlip\Contracts\CoinFlipStrategy;
use App\Domain\CoinFlip\Enums\CoinSide;
use App\Domain\CoinFlip\ValueObjects\CoinFlipResult;
use App\Livewire\CoinFlip\CoinFlipPage;
use Livewire\Livewire;

/**
 * Force le résultat du prochain tirage en rebindant CoinFlipStrategy dans le
 * conteneur : FlipCoinAction (résolue par Livewire via l'injection de méthode)
 * reçoit alors ce double fixe plutôt que RandomCoinFlipStrategy. Nécessaire
 * pour tester déterministement la logique de pari (gagné/perdu).
 */
function fixCoinFlipResult(CoinSide $side): void
{
    app()->bind(CoinFlipStrategy::class, fn () => new class($side) implements CoinFlipStrategy
    {
        public function __construct(private readonly CoinSide $side) {}

        public function flip(): CoinFlipResult
        {
            return new CoinFlipResult($this->side);
        }
    });
}

// --- Tirage simple et multiple -------------------------------------------------

it('flips once and dispatches a coin-flip event', function () {
    fixCoinFlipResult(CoinSide::PILE);

    Livewire::test(CoinFlipPage::class)
        ->call('flip')
        ->assertSet('result', 'pile')
        ->assertSet('history', ['pile'])
        ->assertDispatched('coin-flip', face: 'pile');
});

it('launch() flips once when autoFlipCount is 1', function () {
    Livewire::test(CoinFlipPage::class)
        ->set('autoFlipCount', 1)
        ->call('launch')
        ->assertSet('history', fn (array $history) => count($history) === 1);
});

it('launch() runs multiple flips when autoFlipCount is greater than 1', function () {
    Livewire::test(CoinFlipPage::class)
        ->set('autoFlipCount', 5)
        ->call('launch')
        ->assertSet('history', fn (array $history) => count($history) === 5);
});

it('rejects an automatic flip count below the allowed minimum', function () {
    Livewire::test(CoinFlipPage::class)
        ->set('autoFlipCount', 1)
        ->call('flipMultiple')
        ->assertSet('history', [])
        ->assertSet('error', fn (?string $error) => $error !== null);
});

it('rejects an automatic flip count above the allowed maximum', function () {
    Livewire::test(CoinFlipPage::class)
        ->set('autoFlipCount', 501)
        ->call('flipMultiple')
        ->assertSet('history', [])
        ->assertSet('error', fn (?string $error) => $error !== null);
});

it('clears any pending bet when running multiple automatic flips', function () {
    Livewire::test(CoinFlipPage::class)
        ->call('selectBet', 'pile')
        ->set('autoFlipCount', 10)
        ->call('flipMultiple')
        ->assertSet('bet', null)
        ->assertSet('lastBetWon', null);
});

it('caps the flip history to the last 5000 entries', function () {
    Livewire::test(CoinFlipPage::class)
        ->set('history', array_fill(0, 5000, 'pile'))
        ->call('flip')
        ->assertSet('history', fn (array $history) => count($history) === 5000);
});

// --- Paris ----------------------------------------------------------------------

it('selects a bet', function () {
    Livewire::test(CoinFlipPage::class)
        ->call('selectBet', 'pile')
        ->assertSet('bet', 'pile');
});

it('unselects a bet by selecting the same side again', function () {
    Livewire::test(CoinFlipPage::class)
        ->call('selectBet', 'pile')
        ->call('selectBet', 'pile')
        ->assertSet('bet', null);
});

it('switches the bet when selecting the other side', function () {
    Livewire::test(CoinFlipPage::class)
        ->call('selectBet', 'pile')
        ->call('selectBet', 'face')
        ->assertSet('bet', 'face');
});

it('ignores an invalid bet side', function () {
    Livewire::test(CoinFlipPage::class)
        ->call('selectBet', 'not-a-side')
        ->assertSet('bet', null);
});

it('marks a bet as won when the chosen side matches the flip result', function () {
    fixCoinFlipResult(CoinSide::PILE);

    Livewire::test(CoinFlipPage::class)
        ->call('selectBet', 'pile')
        ->call('flip')
        ->assertSet('result', 'pile')
        ->assertSet('lastBetWon', true)
        ->assertSet('betHistory', [true]);
});

it('marks a bet as lost when the chosen side does not match the flip result', function () {
    fixCoinFlipResult(CoinSide::FACE);

    Livewire::test(CoinFlipPage::class)
        ->call('selectBet', 'pile')
        ->call('flip')
        ->assertSet('result', 'face')
        ->assertSet('lastBetWon', false)
        ->assertSet('betHistory', [false]);
});

it('does not record a bet outcome when no bet was placed', function () {
    fixCoinFlipResult(CoinSide::PILE);

    Livewire::test(CoinFlipPage::class)
        ->call('flip')
        ->assertSet('lastBetWon', null)
        ->assertSet('betHistory', []);
});

it('caps the bet history to the last 5000 entries', function () {
    fixCoinFlipResult(CoinSide::PILE);

    Livewire::test(CoinFlipPage::class)
        ->set('betHistory', array_fill(0, 5000, true))
        ->call('selectBet', 'pile')
        ->call('flip')
        ->assertSet('betHistory', fn (array $history) => count($history) === 5000);
});

// --- Libellés personnalisés ------------------------------------------------------

it('trims whitespace from custom labels', function () {
    Livewire::test(CoinFlipPage::class)
        ->set('pileLabel', '  Rouge  ')
        ->assertSet('pileLabel', 'Rouge');
});

it('falls back to the default label when set to an empty value', function () {
    Livewire::test(CoinFlipPage::class)
        ->set('pileLabel', '   ')
        ->assertSet('pileLabel', 'Pile');
});

it('truncates a custom label beyond 16 characters', function () {
    Livewire::test(CoinFlipPage::class)
        ->set('faceLabel', str_repeat('a', 30))
        ->assertSet('faceLabel', str_repeat('a', 16));
});

it('resets both labels back to their defaults', function () {
    Livewire::test(CoinFlipPage::class)
        ->set('pileLabel', 'Rouge')
        ->set('faceLabel', 'Bleu')
        ->call('resetLabels')
        ->assertSet('pileLabel', 'Pile')
        ->assertSet('faceLabel', 'Face');
});

it('label() returns the custom label for a given raw side value', function () {
    $component = Livewire::test(CoinFlipPage::class)
        ->set('pileLabel', 'Rouge')
        ->set('faceLabel', 'Bleu');

    expect($component->instance()->label('pile'))->toBe('Rouge')
        ->and($component->instance()->label('face'))->toBe('Bleu');
});

// --- Statistiques -----------------------------------------------------------------

it('computes flip statistics from the history', function () {
    $component = Livewire::test(CoinFlipPage::class)
        ->set('history', ['pile', 'pile', 'face']);

    expect($component->instance()->totalFlips())->toBe(3)
        ->and($component->instance()->pileCount())->toBe(2)
        ->and($component->instance()->faceCount())->toBe(1);
});

it('computes bet statistics from the bet history', function () {
    $component = Livewire::test(CoinFlipPage::class)
        ->set('betHistory', [true, true, false]);

    expect($component->instance()->betWinCount())->toBe(2)
        ->and($component->instance()->betLossCount())->toBe(1)
        ->and($component->instance()->betTotal())->toBe(3);
});

// --- Réinitialisation ---------------------------------------------------------------

it('resets all state and dispatches coin-flip-reset', function () {
    fixCoinFlipResult(CoinSide::PILE);

    Livewire::test(CoinFlipPage::class)
        ->call('selectBet', 'pile')
        ->call('flip')
        ->call('resetHistory')
        ->assertSet('result', null)
        ->assertSet('history', [])
        ->assertSet('error', null)
        ->assertSet('bet', null)
        ->assertSet('lastBetWon', null)
        ->assertSet('betHistory', [])
        ->assertDispatched('coin-flip-reset');
});
