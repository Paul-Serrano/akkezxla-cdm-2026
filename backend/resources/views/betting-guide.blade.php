<div class="space-y-8">
        <div class="rounded-3xl border border-base-300 bg-gradient-to-br from-sky-500/10 via-base-100 to-emerald-500/10 shadow-xl overflow-hidden">
            <div class="p-6 sm:p-8 lg:p-10 flex flex-col gap-5">
                <x-header title="How to Bet" subtitle="A quick guide for picking your score predictions" separator class="mb-4" />

                <div class="flex flex-wrap items-center gap-2">
                    <span class="badge badge-primary badge-outline">Before kickoff</span>
                    <span class="badge badge-secondary badge-outline">Edit anytime until lock</span>
                    <span class="badge badge-accent badge-outline">Exact score predictions</span>
                </div>
            </div>
        </div>

        <div class="grid gap-4 xl:grid-cols-[1.35fr_0.85fr]">
            <div class="card bg-gradient-to-br from-sky-500/10 via-base-100 to-emerald-500/10 border border-base-300 shadow-xl">
                <div class="card-body gap-5">
                    <div class="flex items-start justify-between gap-4 flex-wrap">
                        <div class="max-w-2xl">
                            <p class="badge badge-primary badge-outline mb-3">Betting flow</p>
                            <h2 class="card-title text-3xl sm:text-4xl leading-tight">Predict the exact score before kickoff</h2>
                            <p class="text-base-content/70 mt-3 text-base leading-relaxed">
                                Open a match, enter the home and away scores you expect, then save your prediction.
                                You can update it any time while the match is still open.
                            </p>
                        </div>

                        <div class="stats stats-vertical shadow bg-base-100 border border-base-200 min-w-56">
                            <div class="stat py-3 px-4">
                                <div class="stat-title">When</div>
                                <div class="stat-value text-xl">Before kickoff</div>
                            </div>
                            <div class="stat py-3 px-4">
                                <div class="stat-title">Where</div>
                                <div class="stat-value text-xl">Match Day</div>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-3 md:grid-cols-4">
                        <div class="rounded-2xl bg-base-100/90 p-2 ring-1 ring-base-300 shadow-sm">
                            <p class="text-xs uppercase tracking-[0.2em] text-base-content/40">Step 1</p>
                            <p class="font-bold mt-1">Sign in</p>
                            <p class="text-sm text-base-content/65 mt-1">You need an account to place and track bets.</p>
                        </div>
                        <div class="rounded-2xl bg-base-100/90 p-2 ring-1 ring-base-300 shadow-sm">
                            <p class="text-xs uppercase tracking-[0.2em] text-base-content/40">Step 2</p>
                            <p class="font-bold mt-1">Open a match</p>
                            <p class="text-sm text-base-content/65 mt-1">Go to Match Day and choose the game you want to predict.</p>
                        </div>
                        <div class="rounded-2xl bg-base-100/90 p-2 ring-1 ring-base-300 shadow-sm">
                            <p class="text-xs uppercase tracking-[0.2em] text-base-content/40">Step 3</p>
                            <p class="font-bold mt-1">Save your score</p>
                            <p class="text-sm text-base-content/65 mt-1">Enter both scores and press Bet to lock in your prediction.</p>
                        </div>
                        <div class="rounded-2xl bg-base-100/90 p-2 ring-1 ring-base-300 shadow-sm">
                            <p class="text-xs uppercase tracking-[0.2em] text-base-content/40">Step 4</p>
                            <p class="font-bold mt-1">Ranking points</p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <span class="badge badge-warning badge-outline">Exact score: {{ $pointsSuperWin }} {{ $pointsSuperWin === 1 ? 'pt' : 'pts' }}</span>
                                <span class="badge badge-success badge-outline">Good result: {{ $pointsWin }} {{ $pointsWin === 1 ? 'pt' : 'pts' }}</span>
                            </div>
                            <p class="text-sm text-base-content/65 mt-2">Your score affects the ranking as soon as the game ends.</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <a href="/matchday" class="btn btn-primary btn-sm">Go to Match Day</a>
                        <span class="text-sm text-base-content/55">You can revisit and edit a bet until the match is closed.</span>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 border border-base-300 shadow-xl">
                <div class="card-body gap-4">
                    <h3 class="card-title">What happens after you bet?</h3>
                    <p class="text-sm text-base-content/65">Your prediction shows up on the match card and stays editable until kickoff.</p>
                    <ul class="space-y-3 text-sm text-base-content/75">
                        <li class="flex gap-3">
                            <span class="badge badge-success badge-sm mt-0.5">Saved</span>
                            <span>Your prediction is stored and shown on the match card.</span>
                        </li>
                        <li class="flex gap-3">
                            <span class="badge badge-info badge-sm mt-0.5">Editable</span>
                            <span>You can change it again until the match ends.</span>
                        </li>
                        <li class="flex gap-3">
                            <span class="badge badge-error badge-sm mt-0.5">Locked</span>
                            <span>Once the game finishes, betting is closed for that match.</span>
                        </li>
                        <li class="flex gap-3">
                            <span class="badge badge-warning badge-sm mt-0.5">Ranking</span>
                            <span>Your finished bets are scored and added to your total points in ranking.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <div class="card bg-base-100 border border-base-300 shadow-lg">
                <div class="card-body gap-3">
                    <h3 class="card-title">How to place a bet</h3>
                    <div class="space-y-4">
                        <div class="flex gap-3">
                            <div class="w-8 h-8 rounded-full bg-primary text-primary-content flex items-center justify-center font-bold shrink-0">1</div>
                            <div>
                                <p class="font-semibold">Open Match Day</p>
                                <p class="text-sm text-base-content/65">Find the fixture you want to predict.</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <div class="w-8 h-8 rounded-full bg-secondary text-secondary-content flex items-center justify-center font-bold shrink-0">2</div>
                            <div>
                                <p class="font-semibold">Fill in the score fields</p>
                                <p class="text-sm text-base-content/65">Use numbers from 0 to 99 for both teams.</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <div class="w-8 h-8 rounded-full bg-accent text-accent-content flex items-center justify-center font-bold shrink-0">3</div>
                            <div>
                                <p class="font-semibold">Press Bet</p>
                                <p class="text-sm text-base-content/65">Your prediction is saved immediately.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 border border-base-300 shadow-lg">
                <div class="card-body gap-3">
                    <h3 class="card-title">Betting tips</h3>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl bg-base-200/60 p-4">
                            <p class="font-semibold">Check the fixture first</p>
                            <p class="text-sm text-base-content/65 mt-1">Use the match details to confirm the teams and kickoff time.</p>
                        </div>
                        <div class="rounded-2xl bg-base-200/60 p-4">
                            <p class="font-semibold">Watch the deadline</p>
                            <p class="text-sm text-base-content/65 mt-1">Do not wait until after the match starts if you want your bet counted.</p>
                        </div>
                        <div class="rounded-2xl bg-base-200/60 p-4">
                            <p class="font-semibold">You can change your mind</p>
                            <p class="text-sm text-base-content/65 mt-1">Resave a new score any time before the match is closed.</p>
                        </div>
                        <div class="rounded-2xl bg-base-200/60 p-4">
                            <p class="font-semibold">Use the ranking</p>
                            <p class="text-sm text-base-content/65 mt-1">See how your predictions affect your position over time.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 border border-base-300 shadow-lg">
            <div class="card-body gap-3">
                <h3 class="card-title">Follow World Cup teams</h3>
                <p class="text-sm text-base-content/70">
                    You can view the World Cup group ranking on the Standings page.
                </p>
                <p class="text-sm text-base-content/70">
                    Click on any team in a group to see the players selected for the World Cup.
                </p>
                <div class="flex flex-wrap items-center gap-2 mt-1">
                    <a href="/" class="btn btn-outline btn-sm">Open Standings</a>
                    <span class="text-xs text-base-content/55">Tip: use Back to return from team players to the group table.</span>
                </div>
            </div>
        </div>

        @guest
            <div class="alert alert-info shadow-md">
                <x-icon name="o-information-circle" class="w-5 h-5" />
                <div>
                    <h4 class="font-semibold">Need to log in?</h4>
                    <p class="text-sm">If you are not signed in, use the Login link before trying to place a bet.</p>
                </div>
                <a href="{{ route('login') }}" class="btn btn-info btn-sm">Login</a>
            </div>
        @endguest
    </div>