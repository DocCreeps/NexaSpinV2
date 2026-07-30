export default function diceGame(
    initialDice,
    initialKept,
    initialThrows,
    initialIsOver,
    initialIsWon,
    initialLabel,
    maxThrows
) {
    return {
        isRolling: false,
        isOver: initialIsOver,
        isWon: initialIsWon,
        combinationLabel: initialLabel,
        throwCount: initialThrows,
        maxThrows: maxThrows,
        displayDice: [...initialDice],
        keptState: [...initialKept],
        popped: initialDice.map(() => false),
        spinning: initialDice.map(() => false),
        pendingServerData: null,
        pendingCount: 0,
        awaitingImmediateResolve: false,
        reducedMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches,

        pipPositions(value) {
            return {
                1: [4],
                2: [0, 8],
                3: [0, 4, 8],
                4: [0, 2, 6, 8],
                5: [0, 2, 4, 6, 8],
                6: [0, 2, 3, 5, 6, 8],
            }[value] || [];
        },

        announcement() {
            if (this.isRolling) {
                return 'Lancement des dés en cours.';
            }
            if (this.isOver) {
                return this.isWon
                    ? `Combinaison gagnante obtenue en ${this.throwCount} lancer${this.throwCount > 1 ? 's' : ''}. Partie gagnée.`
                    : `Partie perdue. Résultat final : ${this.combinationLabel}.`;
            }
            return `Lancer ${this.throwCount} sur ${this.maxThrows}.`;
        },

        toggleKeep(index) {
            if (this.isRolling || this.isOver) return;
            this.$wire.toggleKeep(index);
            this.keptState[index] = !this.keptState[index];
        },

        startSpin() {
            if (this.isRolling || this.isOver) return;
            this.isRolling = true;
            this.pendingServerData = null;
            this.pendingCount = 0;

            this.$wire.roll();

            if (this.reducedMotion) {
                this.awaitingImmediateResolve = true;
                return;
            }

            this.displayDice.forEach((_, i) => {
                if (this.keptState[i]) return;
                this.pendingCount++;
                this.spinning[i] = true;
                this.spinDie(i, performance.now(), 500 + i * 350 + Math.floor(Math.random() * 300));
            });

            if (this.pendingCount === 0) {
                this.awaitingImmediateResolve = true;
            }
        },

        spinDie(index, startTime, duration) {
            if (!this.spinning[index]) return;

            this.displayDice[index] = Math.floor(Math.random() * 6) + 1;

            const elapsed = performance.now() - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const nextDelay = 40 + Math.pow(progress, 3) * 180;

            setTimeout(() => this.spinDie(index, startTime, duration), nextDelay);
        },

        onDiceRolled(detail) {
            this.pendingServerData = detail;
            const finalDice = this.pendingServerData.dice;

            if (this.reducedMotion || this.awaitingImmediateResolve) {
                this.displayDice = [...finalDice];
                this.awaitingImmediateResolve = false;
                this.finalizeRoll();
                return;
            }

            const settle = (i, finalVal) => {
                this.spinning[i] = false;
                this.displayDice[i] = finalVal;
                this.popped[i] = true;
                setTimeout(() => this.popped[i] = false, 180);
                this.pendingCount--;
                if (this.pendingCount <= 0) this.finalizeRoll();
            };

            finalDice.forEach((finalVal, i) => {
                if (this.keptState[i]) {
                    this.displayDice[i] = finalVal;
                    return;
                }
                const randomDelay = Math.floor(Math.random() * 300) + (i * 350) + 400;
                setTimeout(() => settle(i, finalVal), randomDelay);
            });
        },

        finalizeRoll() {
            if (!this.pendingServerData) {
                this.isRolling = false;
                return;
            }

            this.throwCount = this.pendingServerData.throwCount;
            this.isOver = this.pendingServerData.isOver;
            this.isWon = this.pendingServerData.isWon;
            this.combinationLabel = this.pendingServerData.combinationLabel;
            this.isRolling = false;

            // Historique + compteurs Livewire seulement maintenant
            this.$wire.finalizeRoll();
        },

        onDiceReset() {
            this.isRolling = false;
            this.isOver = false;
            this.isWon = false;
            this.combinationLabel = null;
            this.throwCount = 0;
            this.keptState = this.keptState.map(() => false);
            this.displayDice = this.displayDice.map(() => 1);
            this.spinning = this.spinning.map(() => false);
            this.pendingCount = 0;
            this.pendingServerData = null;
            this.awaitingImmediateResolve = false;
        },
    };
}
