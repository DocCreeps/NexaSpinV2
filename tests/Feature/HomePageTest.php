<?php

test('the home page loads the random draw component', function () {
    $response = $this->get('/');

    $response->assertStatus(200)
        ->assertSeeLivewire(\App\Livewire\Draw\RandomDraw::class);
});
