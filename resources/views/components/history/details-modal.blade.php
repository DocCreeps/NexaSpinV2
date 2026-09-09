<div x-show="openEntry" x-cloak class="fixed inset-0 z-50 flex items-end justify-center p-0 sm:items-center sm:p-4">

    {{-- Backdrop --}}
    <div x-show="openEntry" x-on:click="openEntry = null" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="absolute inset-0 bg-ink/60 backdrop-blur-sm"></div>

    {{-- Modal Box --}}
    <div x-show="openEntry" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="card-hard relative max-h-[85vh] w-full max-w-md overflow-y-auto rounded-t-2xl border-2 border-ink bg-panel sm:rounded-2xl">

        <template x-if="openEntry">
            <div>
                {{-- EN-TÊTE --}}
                <div class="sticky top-0 flex items-start justify-between gap-3 rounded-t-2xl border-b-2 border-ink bg-secondary/20 px-5 py-4 sm:px-6">
                    <div class="min-w-0">
                        <p class="font-mono text-[10px] uppercase tracking-widest text-subtle" x-text="openEntry.modeLabel"></p>

                        <template x-if="['classic', 'weighted', 'elimination'].includes(openEntry.mode)">
                            <div class="mt-1 flex items-center gap-2">
                                <span class="text-2xl leading-none">🏆</span>
                                <p class="truncate font-display text-2xl leading-tight text-ink" x-text="openEntry.winner"></p>
                            </div>
                        </template>

                        <template x-if="openEntry.mode === 'tombola'">
                            <div class="mt-1 flex items-center gap-2">
                                <span class="text-2xl leading-none">🎟️</span>
                                <p class="truncate font-display text-2xl leading-tight text-ink" x-text="(openEntry.winners ? openEntry.winners.length : 0) + ' lot' + ((openEntry.winners && openEntry.winners.length > 1) ? 's' : '')"></p>
                            </div>
                        </template>

                        <template x-if="openEntry.mode === 'number_roulette'">
                            <div class="mt-1 flex items-center gap-2">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border-2 border-ink font-mono text-xs font-bold" :class="{ 'bg-emerald-600 text-white': openEntry.color === 'green', 'bg-red-600 text-white': openEntry.color === 'red', 'bg-ink text-white': openEntry.color === 'black' }" x-text="openEntry.result"></span>
                                <p class="truncate font-display text-2xl leading-tight" :class="openEntry.payout > 0 ? 'text-ink' : 'text-danger'" x-text="(openEntry.payout > 0 ? '+' : '') + openEntry.payout"></p>
                            </div>
                        </template>

                        <template x-if="openEntry.mode === 'teams'">
                            <div class="mt-1 flex items-center gap-2">
                                <span class="text-2xl leading-none">👥</span>
                                <p class="truncate font-display text-2xl leading-tight text-ink" x-text="openEntry.teams_count + ' équipes'"></p>
                            </div>
                        </template>

                        <template x-if="openEntry.mode === 'rock_paper_scissors'">
                            <div class="mt-1 flex items-center gap-2">
                                <span class="text-2xl leading-none">✂️</span>
                                <p class="truncate font-display text-2xl leading-tight text-ink" x-text="((openEntry.score?.a ?? openEntry.score?.x ?? 0) + 'V · ' + (openEntry.score?.draw ?? 0) + 'N · ' + (openEntry.score?.b ?? openEntry.score?.o ?? 0) + 'D')"></p>
                            </div>
                        </template>

                        <template x-if="openEntry.mode === 'tic_tac_toe'">
                            <div class="mt-1 flex items-center gap-2">
                                <span class="text-2xl leading-none">⭕</span>
                                <p class="truncate font-display text-2xl leading-tight text-ink" x-text="(openEntry.score ? openEntry.score.x : 0) + ' - ' + (openEntry.score ? openEntry.score.o : 0)"></p>
                            </div>
                        </template>
                    </div>

                    <button type="button" x-on:click="openEntry = null" aria-label="Fermer" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border-2 border-ink bg-panel font-mono text-xs text-subtle transition hover:bg-ink hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2">
                        ✕
                    </button>
                </div>

                {{-- CONTENU DU MODAL --}}
                <div class="space-y-5 px-5 py-5 sm:px-6">

                    {{-- PIERRE-FEUILLE-CISEAUX (PFC / RPS) --}}
                    <template x-if="openEntry.mode === 'rock_paper_scissors'">
                        <div class="space-y-4">
                            {{-- En-tête Global --}}
                            <div class="rounded-xl border-2 p-3.5 shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]" :class="{
            'border-emerald-500 bg-emerald-100/60': String(openEntry.difficulty).toLowerCase().includes('eas') || String(openEntry.difficulty).toLowerCase().includes('fac'),
            'border-amber-500 bg-amber-100/60': String(openEntry.difficulty).toLowerCase().includes('med') || String(openEntry.difficulty).toLowerCase().includes('moy'),
            'border-rose-500 bg-rose-100/60': String(openEntry.difficulty).toLowerCase().includes('hard') || String(openEntry.difficulty).toLowerCase().includes('dif'),
            'border-purple-600 bg-purple-100/60': String(openEntry.difficulty).toLowerCase().includes('imp') || String(openEntry.difficulty).toLowerCase().includes('exp'),
            'border-ink bg-wash': !openEntry.difficulty
        }">

                                <div class="relative flex items-center justify-center min-h-[28px]">
                                    <div class="font-mono text-sm text-ink">
                                        <span class="font-bold" x-text="openEntry.a_label || openEntry.x_label || 'Vous'"></span>
                                        <span class="px-1 text-xs text-subtle">vs</span>
                                        <span class="font-bold" x-text="openEntry.b_label || openEntry.o_label || (openEntry.vs_ai !== false ? 'IA' : 'Joueur 2')"></span>
                                    </div>

                                    <template x-if="openEntry.difficulty || openEntry.difficulty_label">
                                        <span class="absolute right-0 rounded-md border-2 border-ink px-2 py-0.5 font-mono text-[10px] font-bold uppercase tracking-wider text-ink shadow-[1px_1px_0px_0px_rgba(0,0,0,1)]" :class="{
                        'bg-emerald-400': String(openEntry.difficulty).toLowerCase().includes('eas') || String(openEntry.difficulty).toLowerCase().includes('fac'),
                        'bg-amber-400': String(openEntry.difficulty).toLowerCase().includes('med') || String(openEntry.difficulty).toLowerCase().includes('moy'),
                        'bg-rose-400': String(openEntry.difficulty).toLowerCase().includes('hard') || String(openEntry.difficulty).toLowerCase().includes('dif'),
                        'bg-purple-400': String(openEntry.difficulty).toLowerCase().includes('imp') || String(openEntry.difficulty).toLowerCase().includes('exp'),
                        'bg-wash': !openEntry.difficulty
                    }" x-text="openEntry.difficulty_label || openEntry.difficulty">
                                        </span>
                                    </template>
                                </div>

                                <div class="mt-3.5 space-y-2 font-mono text-xs">
                                    <div class="flex items-center justify-between rounded-lg border border-ink/20 bg-black/5 px-2.5 py-1">
                                        <span class="font-medium text-subtle">Parties jouées</span>
                                        <span class="rounded border border-ink bg-panel px-1.5 py-0.5 font-bold text-ink shadow-[1px_1px_0px_0px_rgba(0,0,0,1)]" x-text="(openEntry.rounds || openEntry.games || openEntry.history || []).length || openEntry.games_count || ((openEntry.score?.a || 0) + (openEntry.score?.draw || 0) + (openEntry.score?.b || 0))">
                                        </span>
                                    </div>

                                    <div class="flex items-center justify-between rounded-lg border border-ink/20 bg-black/5 px-2.5 py-1">
                                        <span class="font-medium text-subtle">Score</span>
                                        <div class="flex items-center gap-1.5 font-bold">
                                            <span class="rounded border border-ink bg-emerald-300 px-1.5 py-0.5 text-ink shadow-[1px_1px_0px_0px_rgba(0,0,0,1)]" x-text="(openEntry.score?.a ?? openEntry.score?.x ?? 0) + 'V'">
                                            </span>
                                            <span class="rounded border border-ink bg-amber-200 px-1.5 py-0.5 text-ink shadow-[1px_1px_0px_0px_rgba(0,0,0,1)]" x-text="(openEntry.score?.draw || 0) + 'N'">
                                            </span>
                                            <span class="rounded border border-ink bg-rose-300 px-1.5 py-0.5 text-ink shadow-[1px_1px_0px_0px_rgba(0,0,0,1)]" x-text="(openEntry.score?.b ?? openEntry.score?.o ?? 0) + 'D'">
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- DÉTAIL DES MANCHES RPS --}}
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <p class="font-mono text-[10px] font-bold uppercase tracking-widest text-subtle">Détail des manches</p>
                                    <span class="font-mono text-[10px] font-bold text-subtle" x-text="((openEntry.rounds || openEntry.games || openEntry.history || []).length) + ' manche(s)'"></span>
                                </div>

                                <div class="max-h-56 space-y-2 overflow-y-auto overflow-x-hidden pr-1">
                                    <template x-for="(round, index) in (openEntry.rounds || openEntry.games || openEntry.history || [])" :key="index">
                                        <div x-data="{
                get status() {
                    let w = String(round.winner || round.result || '').toLowerCase().trim();
                    let aLabel = String(openEntry.a_label || openEntry.x_label || 'joueur 1').toLowerCase().trim();
                    let bLabel = String(openEntry.b_label || openEntry.o_label || 'joueur 2').toLowerCase().trim();

                    // 1. Vérification directe des valeurs de gagnant
                    if (['a', 'x', '1', 'win', 'v', 'user', 'player1'].includes(w) || w.includes('joueur 1') || (aLabel && w === aLabel)) return 'a';
                    if (['b', 'o', '2', 'lose', 'd', 'ia', 'ai', 'player2'].includes(w) || w.includes('joueur 2') || (bLabel && w === bLabel)) return 'b';
                    if (['draw', 'n', 'equal', 'tie', 'égalite', 'égalité'].includes(w)) return 'draw';

                    // 2. Fallback : calcul automatique selon les coups joués
                    const parseMove = (m) => {
                        const v = String(m || '').toLowerCase();
                        if (v.includes('rock') || v.includes('pier') || v === 'r') return 'r';
                        if (v.includes('paper') || v.includes('feui') || v === 'p') return 'p';
                        if (v.includes('scis') || v.includes('cise') || v === 's') return 's';
                        return null;
                    };

                    const moveA = parseMove(round.a_choice || round.a_move || round.user_move || round.player_move || round.a || round.choice);
                    const moveB = parseMove(round.b_choice || round.b_move || round.ai_move || round.opponent_move || round.b);

                    if (moveA && moveB) {
                        if (moveA === moveB) return 'draw';
                        if ((moveA === 'r' && moveB === 's') || (moveA === 'p' && moveB === 'r') || (moveA === 's' && moveB === 'p')) return 'a';
                        return 'b';
                    }

                    return 'draw';
                }
            }" class="flex items-center justify-between gap-1.5 rounded-xl border-2 border-ink bg-panel p-2 font-mono text-xs shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">

                                            <span class="shrink-0 rounded bg-black/5 px-1.5 py-0.5 font-bold text-subtle" x-text="'#' + (index + 1)"></span>

                                            {{-- Comparaison par Icônes Seules --}}
                                            <div class="flex items-center justify-center gap-1.5 rounded-lg border border-ink/20 bg-wash px-2 py-1 text-base font-bold leading-none">
                                                <span x-text="(() => {
                        const v = String(round.a_choice || round.a_move || round.user_move || round.player_move || round.a || round.choice || '').toLowerCase();
                        if (v.includes('rock') || v.includes('pier') || v === 'r') return '🪨';
                        if (v.includes('paper') || v.includes('feui') || v === 'p') return '📄';
                        if (v.includes('scis') || v.includes('cise') || v === 's') return '✂️';
                        return v || '❓';
                    })()"></span>

                                                <span class="font-mono text-[10px] font-normal text-subtle">vs</span>

                                                <span x-text="(() => {
                        const v = String(round.b_choice || round.b_move || round.ai_move || round.opponent_move || round.b || '').toLowerCase();
                        if (v.includes('rock') || v.includes('pier') || v === 'r') return '🪨';
                        if (v.includes('paper') || v.includes('feui') || v === 'p') return '📄';
                        if (v.includes('scis') || v.includes('cise') || v === 's') return '✂️';
                        return v || '❓';
                    })()"></span>
                                            </div>

                                            {{-- Badge de Résultat --}}
                                            <span class="shrink-0 rounded-md border border-ink px-2 py-0.5 text-[11px] font-bold shadow-[1px_1px_0px_0px_rgba(0,0,0,1)]" :class="{
                        'bg-emerald-300 text-ink': status === 'a',
                        'bg-rose-300 text-ink': status === 'b',
                        'bg-amber-200 text-ink': status === 'draw'
                    }" x-text="
                        status === 'a' ? (openEntry.vs_ai !== false ? 'Victoire' : (openEntry.a_label || openEntry.x_label || 'Joueur 1')) :
                        (status === 'b' ? (openEntry.vs_ai !== false ? 'Défaite' : (openEntry.b_label || openEntry.o_label || 'Joueur 2')) : 'Égalité')
                    ">
                                            </span>

                                        </div>
                                    </template>

                                    <template x-if="!(openEntry.rounds || openEntry.games || openEntry.history || []).length">
                                        <div class="rounded-xl border-2 border-dashed border-ink/30 bg-wash/50 p-4 text-center font-mono text-xs text-subtle">
                                            Aucune manche détaillée disponible.
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>


                    {{-- TIC TAC TOE (MORPION) --}}
                    <template x-if="openEntry.mode === 'tic_tac_toe'">
                        <div class="space-y-4">
                            {{-- En-tête Global --}}
                            <div class="rounded-xl border-2 p-3.5 shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]" :class="{
                                'border-emerald-500 bg-emerald-100/60': String(openEntry.difficulty).toLowerCase().includes('eas') || String(openEntry.difficulty).toLowerCase().includes('fac'),
                                'border-amber-500 bg-amber-100/60': String(openEntry.difficulty).toLowerCase().includes('med') || String(openEntry.difficulty).toLowerCase().includes('moy'),
                                'border-rose-500 bg-rose-100/60': String(openEntry.difficulty).toLowerCase().includes('hard') || String(openEntry.difficulty).toLowerCase().includes('dif'),
                                'border-purple-600 bg-purple-100/60': String(openEntry.difficulty).toLowerCase().includes('imp') || String(openEntry.difficulty).toLowerCase().includes('exp'),
                                'border-ink bg-wash': !openEntry.difficulty
                            }">

                                <div class="relative flex items-center justify-center min-h-[28px]">
                                    <div class="font-mono text-sm text-ink">
                                        <span class="font-bold" x-text="openEntry.x_label || 'Vous'"></span>
                                        <span class="px-1 text-xs text-subtle">vs</span>
                                        <span class="font-bold" x-text="openEntry.o_label || (openEntry.vs_ai !== false ? 'IA' : 'Joueur 2')"></span>
                                    </div>

                                    <template x-if="openEntry.difficulty || openEntry.difficulty_label">
                                        <span class="absolute right-0 rounded-md border-2 border-ink px-2 py-0.5 font-mono text-[10px] font-bold uppercase tracking-wider text-ink shadow-[1px_1px_0px_0px_rgba(0,0,0,1)]" :class="{
                                            'bg-emerald-400': String(openEntry.difficulty).toLowerCase().includes('eas') || String(openEntry.difficulty).toLowerCase().includes('fac'),
                                            'bg-amber-400': String(openEntry.difficulty).toLowerCase().includes('med') || String(openEntry.difficulty).toLowerCase().includes('moy'),
                                            'bg-rose-400': String(openEntry.difficulty).toLowerCase().includes('hard') || String(openEntry.difficulty).toLowerCase().includes('dif'),
                                            'bg-purple-400': String(openEntry.difficulty).toLowerCase().includes('imp') || String(openEntry.difficulty).toLowerCase().includes('exp'),
                                            'bg-wash': !openEntry.difficulty
                                        }" x-text="openEntry.difficulty_label || openEntry.difficulty">
                                        </span>
                                    </template>
                                </div>

                                <div class="mt-3.5 space-y-2 font-mono text-xs">
                                    <div class="flex items-center justify-between rounded-lg border border-ink/20 bg-black/5 px-2.5 py-1">
                                        <span class="font-medium text-subtle">Parties jouées</span>
                                        <span class="rounded border border-ink bg-panel px-1.5 py-0.5 font-bold text-ink shadow-[1px_1px_0px_0px_rgba(0,0,0,1)]" x-text="(openEntry.rounds || openEntry.games || openEntry.history || []).length || openEntry.games_count || ((openEntry.score?.x || 0) + (openEntry.score?.draw || 0) + (openEntry.score?.o || 0))">
                                        </span>
                                    </div>

                                    <div class="flex items-center justify-between rounded-lg border border-ink/20 bg-black/5 px-2.5 py-1">
                                        <span class="font-medium text-subtle">Score</span>
                                        <div class="flex items-center gap-1.5 font-bold">
                                            <span class="rounded border border-ink bg-emerald-300 px-1.5 py-0.5 text-ink shadow-[1px_1px_0px_0px_rgba(0,0,0,1)]" x-text="(openEntry.score?.x || 0) + 'V'">
                                            </span>
                                            <span class="rounded border border-ink bg-amber-200 px-1.5 py-0.5 text-ink shadow-[1px_1px_0px_0px_rgba(0,0,0,1)]" x-text="(openEntry.score?.draw || 0) + 'N'">
                                            </span>
                                            <span class="rounded border border-ink bg-rose-300 px-1.5 py-0.5 text-ink shadow-[1px_1px_0px_0px_rgba(0,0,0,1)]" x-text="(openEntry.score?.o || 0) + 'D'">
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- DÉTAIL DES MANCHES MORPION --}}
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <p class="font-mono text-[10px] font-bold uppercase tracking-widest text-subtle">Détail des manches</p>
                                    <span class="font-mono text-[10px] font-bold text-subtle" x-text="((openEntry.rounds || openEntry.games || openEntry.history || []).length) + ' manche(s)'"></span>
                                </div>

                                <div class="max-h-56 space-y-2 overflow-y-auto pr-1">
                                    <template x-for="(game, index) in (openEntry.rounds || openEntry.games || openEntry.history || [])" :key="index">
                                        <div class="flex items-center justify-between gap-2 rounded-xl border-2 border-ink bg-panel p-2.5 font-mono text-xs shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">

                                            <div class="flex items-center gap-2">
                                                <span class="shrink-0 rounded bg-black/5 px-1.5 py-0.5 font-bold text-subtle" x-text="'#' + (index + 1)"></span>

                                                <template x-if="game.difficulty || openEntry.difficulty">
                                                    <span class="rounded border border-ink px-1.5 py-0.5 font-mono text-[9px] font-bold uppercase text-ink shadow-[1px_1px_0px_0px_rgba(0,0,0,1)]" :class="{
                                                        'bg-emerald-300': String(game.difficulty || openEntry.difficulty).toLowerCase().includes('eas') || String(game.difficulty || openEntry.difficulty).toLowerCase().includes('fac'),
                                                        'bg-amber-300': String(game.difficulty || openEntry.difficulty).toLowerCase().includes('med') || String(game.difficulty || openEntry.difficulty).toLowerCase().includes('moy'),
                                                        'bg-rose-300': String(game.difficulty || openEntry.difficulty).toLowerCase().includes('hard') || String(game.difficulty || openEntry.difficulty).toLowerCase().includes('dif'),
                                                        'bg-purple-300': String(game.difficulty || openEntry.difficulty).toLowerCase().includes('imp') || String(game.difficulty || openEntry.difficulty).toLowerCase().includes('exp'),
                                                        'bg-wash': !(game.difficulty || openEntry.difficulty)
                                                    }" x-text="game.difficulty || openEntry.difficulty">
                                                    </span>
                                                </template>
                                            </div>

                                            <div class="flex items-center gap-1 font-bold">
                                                <span x-text="
                                                    (!game.winner || game.winner === 'draw' || game.result === 'draw') ? '➖ Égalité' :
                                                    (game.winner === 'X' || game.winner === 'x' || String(game.winner).toLowerCase().includes('vous')) ? '❌ ' + (openEntry.x_label || 'Vous') :
                                                    '⭕ ' + (openEntry.o_label || (openEntry.vs_ai !== false ? 'IA' : 'Joueur 2'))
                                                "></span>
                                            </div>

                                            <span class="shrink-0 rounded-md border border-ink px-2 py-0.5 font-bold shadow-[1px_1px_0px_0px_rgba(0,0,0,1)]" :class="{
                                                'bg-emerald-300 text-ink': game.winner === 'X' || game.winner === 'x' || String(game.winner).toLowerCase().includes('vous') || game.result === 'win' || game.result === 'v',
                                                'bg-rose-300 text-ink': game.winner === 'O' || game.winner === 'o' || String(game.winner).toLowerCase().includes('ia') || game.result === 'lose' || game.result === 'd',
                                                'bg-amber-200 text-ink': !game.winner || game.winner === 'draw' || game.result === 'draw' || game.result === 'n'
                                            }" x-text="
                                                (!game.winner || game.winner === 'draw' || game.result === 'draw' || game.result === 'n') ? 'Égalité' :
                                                ((openEntry.vs_ai !== false) ? (
                                                    (game.winner === 'X' || game.winner === 'x' || String(game.winner).toLowerCase().includes('vous') || game.result === 'win') ? 'Victoire' : 'Défaite'
                                                ) : (
                                                    (game.winner === 'X' || game.winner === 'x') ? (openEntry.x_label || 'Joueur 1') : (openEntry.o_label || 'Joueur 2')
                                                ))
                                            ">
                                            </span>

                                        </div>
                                    </template>

                                    <template x-if="!(openEntry.rounds || openEntry.games || openEntry.history || []).length">
                                        <div class="rounded-xl border-2 border-dashed border-ink/30 bg-wash/50 p-4 text-center font-mono text-xs text-subtle">
                                            Aucune manche détaillée disponible.
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>

                    {{-- ROUES / TOMBOLA / AUTRES --}}
                    <template x-if="['classic', 'weighted', 'elimination'].includes(openEntry.mode)">
                        <div class="space-y-5">
                            <template x-if="!openEntry.weights">
                                <div>
                                    <p class="mb-2 font-mono text-[10px] uppercase tracking-widest text-faint">
                                        Participants (<span x-text="openEntry.participants ? openEntry.participants.length : 0"></span>)
                                    </p>
                                    <div class="flex flex-wrap gap-1.5">
                                        <template x-for="name in (openEntry.participants || [])" :key="name">
                                            <span class="inline-flex items-center gap-1 rounded-md border-2 border-ink px-2 py-1 font-mono text-[11px]" :class="name === openEntry.winner ? 'bg-secondary font-bold text-ink' : 'bg-wash text-ink'">
                                                <span x-show="name === openEntry.winner">🏆</span>
                                                <span x-text="name"></span>
                                            </span>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                </div>
            </div>
        </template>
    </div>
</div>
