<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ config('app.name', 'CDM 2026') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-base-200">

    {{-- Top nav --}}
    <x-nav sticky class="lg:hidden">
        <x-slot:brand>
            <span class="font-black text-lg">CDM 2026</span>
        </x-slot:brand>
        <x-slot:actions>
            @auth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-button label="{{ Auth::user()->name }}" icon="o-arrow-left-on-rectangle" class="btn-ghost btn-sm" type="submit" />
                </form>
            @else
                <x-button label="Login" icon="o-arrow-right-on-rectangle" link="{{ route('login') }}" class="btn-ghost btn-sm" />
            @endauth
            <label for="main-drawer" class="lg:hidden ms-1 cursor-pointer">
                <x-icon name="o-bars-3" class="w-6 h-6" />
            </label>
        </x-slot:actions>
    </x-nav>

    <x-main full-width with-nav>
        {{-- Sidebar (desktop) --}}
        <x-slot:sidebar drawer="main-drawer" collapsible class="bg-base-100 lg:bg-inherit">
            <x-menu activate-by-route>
                <x-menu-item title="Standings" icon="o-table-cells" link="/" />
                <x-menu-item title="Match Day" icon="o-calendar-days" link="/matchday" />
                @auth
                    @if (Auth::user()->isAdmin() || Auth::user()->isWinamax())
                        <x-menu-item title="Ranking" icon="o-trophy" link="{{ route('ranking') }}" />
                    @endif
                @endauth
                <x-menu-separator />
                @auth
                    @if (Auth::user()->isAdmin())
                        <x-menu-sub title="Admin" icon="o-cog-6-tooth">
                            <x-menu-item title="Users" icon="o-users" link="{{ route('admin.users') }}" />
                            <x-menu-item title="Roles" icon="o-shield-check" link="{{ route('admin.roles') }}" />
                            <x-menu-item title="Config" icon="o-adjustments-horizontal" link="{{ route('admin.config') }}" />
                        </x-menu-sub>
                        <x-menu-separator />
                    @endif
                    <x-menu-item title="{{ Auth::user()->alias }}" icon="o-user-circle" link="{{ route('profile') }}" />
                    <div class="flex flex-wrap gap-1 mx-4 mb-1">
                        @foreach (Auth::user()->roles->sortBy('label') as $r)
                            <x-badge :value="$r->label" class="badge-sm
                                {{ $r->name === 'admin' ? 'badge-error' : ($r->name === 'winamax' ? 'badge-warning' : 'badge-ghost') }}" />
                        @endforeach
                    </div>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex gap-2 items-center px-4 py-2 w-full text-sm hover:bg-base-200 rounded-lg">
                                <x-icon name="o-arrow-left-on-rectangle" class="w-5 h-5" />
                                Logout
                            </button>
                        </form>
                    </li>
                @else
                    <x-menu-item title="Login" icon="o-arrow-right-on-rectangle" link="{{ route('login') }}" />
                @endauth
            </x-menu>
        </x-slot:sidebar>

        <x-slot:content>
            {{ $slot }}
        </x-slot:content>
    </x-main>

    @livewireScripts
</body>
</html>
