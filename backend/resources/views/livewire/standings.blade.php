<div>
    <x-header title="Group Stage" subtitle="FIFA World Cup 2026" separator />

    {{-- MOBILE: stacked groups --}}
    <div class="block md:hidden space-y-4">
        @foreach ($standings as $standing)
            <livewire:group :standing="$standing" :key="'mob-'.$standing->id" />
        @endforeach
    </div>

    {{-- DESKTOP: 2-column grid --}}
    <div class="hidden md:grid md:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach ($standings as $standing)
            <livewire:group :standing="$standing" :key="'desk-'.$standing->id" />
        @endforeach
    </div>
</div>
