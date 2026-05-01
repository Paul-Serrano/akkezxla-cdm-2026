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
        </x-slot:actions>
    </x-nav>

    <x-main full-width>
        {{-- Sidebar (desktop) --}}
        <x-slot:sidebar drawer="main-drawer" collapsible class="bg-base-100 lg:bg-inherit">
            <x-menu activate-by-route>
                <x-menu-item title="Standings" icon="o-table-cells" link="/" />
                <x-menu-item title="Match Day" icon="o-calendar-days" link="/matchday" />
                <x-menu-separator />
                @auth
                    @if (Auth::user()->isAdmin())
                        <x-menu-item title="Users" icon="o-users" link="{{ route('admin.users') }}" />
                        <x-menu-separator />
                    @endif
                    <x-menu-item title="{{ Auth::user()->alias }}" icon="o-user-circle" />
                    <x-badge :value="ucfirst(Auth::user()->role)" class="mx-4 mb-1 badge-sm
                        {{ Auth::user()->role === 'admin' ? 'badge-error' : (Auth::user()->role === 'winamax' ? 'badge-warning' : 'badge-ghost') }}" />
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
