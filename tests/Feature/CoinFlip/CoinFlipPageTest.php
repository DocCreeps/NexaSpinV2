<?php

use App\Application\History\HistoryStore;
use App\Application\Home\Enums\GameModeType;
use App\Livewire\CoinFlip\CoinFlipPage;
use Livewire\Livewire;

it('flips to one of the two sides', function () {
    $component = Livewire::test(CoinFlipPage::class)->call('flip');

    expect($component->get('result'))->toBeIn(['pile', 'face']);
});

it('keeps the result consistent with whether the chosen bet actually won', function () {
    $component = Livewire::test(CoinFlipPage::class)
        ->call('selectBet', 'pile')
        ->call('flip');

    expect($component->get('lastBetWon'))->toBe($component->get('bet') === $component->get('result'));
});

it('toggles a bet off when the same side is selected twice', function () {
    Livewire::test(CoinFlipPage::class)
        ->call('selectBet', 'pile')
        ->assertSet('bet', 'pile')
        ->call('selectBet', 'pile')
        ->assertSet('bet', null);
});

it('ignores an invalid bet side', function () {
    Livewire::test(CoinFlipPage::class)
        ->call('selectBet', 'not-a-side')
        ->assertSet('bet', null);
});

it('does not commit anything to history before confirmFlip is called', function () {
    $component = Livewire::test(CoinFlipPage::class)->call('flip');

    expect($component->get('history'))->toBe([])
        ->and(app(HistoryStore::class)->all(GameModeType::COIN_FLIP))->toBe([]);
});

it('commits the flip to history and to the shared store once confirmed', function () {
    $component = Livewire::test(CoinFlipPage::class)
        ->call('flip')
        ->call('confirmFlip');

    expect($component->get('history'))->toHaveCount(1)
        ->and(app(HistoryStore::class)->all(GameModeType::COIN_FLIP))->toHaveCount(1);
});

it('runs the requested number of automatic flips and tallies both sides', function () {
    $component = Livewire::test(CoinFlipPage::class)
        ->set('autoFlipCount', 20)
        ->call('launch');

    $entries = $component->get('pendingHistoryEntries');

    expect($entries)->toHaveCount(1)
        ->and($entries[0]['type'])->toBe('multiple')
        ->and($entries[0]['pile_count'] + $entries[0]['face_count'])->toBe(20);
});

it('declares the side with the most flips as the winner of a multiple flip', function () {
    $component = Livewire::test(CoinFlipPage::class)
        ->set('autoFlipCount', 20)
        ->call('launch');

    $entry = $component->get('pendingHistoryEntries')[0];

    if ($entry['pile_count'] === $entry['face_count']) {
        expect($entry['winner'])->toBeNull();
    } else {
        $expectedWinner = $entry['pile_count'] > $entry['face_count'] ? 'pile' : 'face';
        expect($entry['winner'])->toBe($expectedWinner);
    }
});

it('refuses an automatic flip count outside the allowed range', function () {
    $component = Livewire::test(CoinFlipPage::class)
        ->set('autoFlipCount', 1000)
        ->call('flipMultiple');

    expect($component->get('error'))->not->toBeNull()
        ->and($component->get('pendingHistoryEntries'))->toBe([]);
});

it('routes a single flip through launch() when autoFlipCount is 1', function () {
    $component = Livewire::test(CoinFlipPage::class)
        ->set('autoFlipCount', 1)
        ->call('launch');

    expect($component->get('pendingHistoryEntries')[0]['type'])->toBe('single');
});

it('resets an empty pile label back to the default', function () {
    Livewire::test(CoinFlipPage::class)
        ->set('pileLabel', '   ')
        ->assertSet('pileLabel', 'Pile');
});

it('truncates an overly long custom label', function () {
    Livewire::test(CoinFlipPage::class)
        ->set('faceLabel', 'This label is definitely too long')
        ->assertSet('faceLabel', mb_substr('This label is definitely too long', 0, 16));
});

it('restores both default labels on demand', function () {
    Livewire::test(CoinFlipPage::class)
        ->set('pileLabel', 'Heads')
        ->set('faceLabel', 'Tails')
        ->call('resetLabels')
        ->assertSet('pileLabel', 'Pile')
        ->assertSet('faceLabel', 'Face');
});

it('counts bet wins and losses only from confirmed single flips with a bet', function () {
    $component = Livewire::test(CoinFlipPage::class);

    // Manually seed history to test the counting logic itself (the flip outcome is random,
    // but once an entry exists in history its accounting must be exact).
    $component->set('history', [
        ['type' => 'single', 'side' => 'pile', 'side_label' => 'Pile', 'bet' => 'pile', 'bet_label' => 'Pile', 'bet_won' => true],
        ['type' => 'single', 'side' => 'face', 'side_label' => 'Face', 'bet' => 'pile', 'bet_label' => 'Pile', 'bet_won' => false],
        ['type' => 'single', 'side' => 'face', 'side_label' => 'Face', 'bet' => null, 'bet_label' => null, 'bet_won' => null],
        [
            'type' => 'multiple',
            'count' => 10,
            'pile_count' => 6,
            'face_count' => 4,
            'pile_label' => 'Pile',
            'face_label' => 'Face',
            'winner' => 'pile',
            'winner_label' => 'Pile',
        ],
    ]);

    expect($component->instance()->totalFlips())->toBe(13)
        ->and($component->instance()->pileCount())->toBe(1 + 6)
        ->and($component->instance()->faceCount())->toBe(2 + 4);
});

it('clears history, bets and the persisted store on resetHistory', function () {
    $component = Livewire::test(CoinFlipPage::class)
        ->call('selectBet', 'pile')
        ->call('flip')
        ->call('confirmFlip')
        ->call('resetHistory');

    expect($component->get('history'))->toBe([])
        ->and($component->get('bet'))->toBeNull()
        ->and($component->get('betHistory'))->toBe([])
        ->and(app(HistoryStore::class)->all(GameModeType::COIN_FLIP))->toBe([]);
});
