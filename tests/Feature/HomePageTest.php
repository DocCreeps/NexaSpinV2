<?php

test('the home page lists the available draw modes', function () {
    $response = $this->get('/');

    $response->assertStatus(200)
        ->assertSee('Roue classique')
        ->assertSee('Roue par élimination');
});

test('the home page links to the wheel and elimination wheel pages', function () {
    $response = $this->get('/');

    $response->assertStatus(200)
        ->assertSee(route('draw.wheel'), false)
        ->assertSee(route('draw.wheel-elimination'), false);
});
