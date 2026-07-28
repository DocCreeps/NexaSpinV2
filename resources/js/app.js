import diceGame from './dice-game';

document.addEventListener('alpine:init', () => {

    Alpine.data('diceGame', diceGame);
});
