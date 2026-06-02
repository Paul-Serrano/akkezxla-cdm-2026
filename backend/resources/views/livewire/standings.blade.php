<div>
    <x-header title="Group Stage" subtitle="FIFA World Cup 2026" separator />

    <div class="alert alert-info mb-4">
        <x-icon name="o-cursor-arrow-rays" class="w-5 h-5" />
        <span class="text-sm">Click any team to see the players selected for the World Cup.</span>
    </div>

    {{-- MOBILE: stacked groups --}}
    <div class="block md:hidden space-y-4">
        @foreach ($standings as $standing)
            <livewire:group :standing="$standing" :key="'mob-'.$standing->id" />
        @endforeach
    </div>

    {{-- DESKTOP: 2-column grid --}}
    <div class="hidden md:grid md:grid-cols-2 xl:grid-cols-2 gap-6">
        @foreach ($standings as $standing)
            <livewire:group :standing="$standing" :key="'desk-'.$standing->id" />
        @endforeach
    </div>
</div>
