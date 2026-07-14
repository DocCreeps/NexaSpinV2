<?php

use App\Livewire\Draw\WheelPage;
use Livewire\Livewire;

it('requires at least two participants to draw', function () {
    Livewire::test(WheelPage::class)
        ->set('participants', ['Solo'])
        ->call('draw')
        ->assertSet('result', null)
        ->assertSet('error', fn (?string $error) => $error !== null);
});

it('draws a winner among the participants and dispatches a wheel-spin event', function () {
    $names = ['John', 'Jane', 'Bob'];

    Livewire::test(WheelPage::class)
        ->set('participants', $names)
        ->call('draw')
        ->assertSet('result', fn (?string $result) => in_array($result, $names, true))
        ->assertSet('error', null)
        ->assertDispatched('wheel-spin');
});

it('resets the result whenever a participant is added or removed', function () {
    $component = Livewire::test(WheelPage::class)
        ->set('participants', ['John', 'Jane'])
        ->call('draw');

    expect($component->get('result'))->not->toBeNull();

    $component->set('participant', 'Bob')->call('addParticipant');

    expect($component->get('result'))->toBeNull();
});

it('exposes one wheel segment per participant', function () {
    $component = Livewire::test(WheelPage::class)
        ->set('participants', ['John', 'Jane', 'Bob']);

    expect($component->get('segments'))->toHaveCount(3);
});

it('shows labels on the wheel up to 10 participants', function () {
    $component = Livewire::test(WheelPage::class)
        ->set('participants', array_map(fn (int $i) => "P{$i}", range(1, 10)));

    expect($component->instance()->showLabelsOnWheel())->toBeTrue();
});

it('hides labels on the wheel beyond 10 participants', function () {
    $component = Livewire::test(WheelPage::class)
        ->set('participants', array_map(fn (int $i) => "P{$i}", range(1, 11)));

    expect($component->instance()->showLabelsOnWheel())->toBeFalse();
});
