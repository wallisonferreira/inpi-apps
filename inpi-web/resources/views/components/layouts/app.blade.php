<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'INPI Dashboard' }}</title>
    @vite('resources/css/app.css')
    @livewireStyles
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal h-screen overflow-hidden">
    <div class="flex h-full">
        <!-- Sidebar -->
        <div class="flex flex-col w-64 bg-blue-800 text-white shadow-lg h-full">
            <div class="p-4 text-center text-lg font-bold bg-blue-900">
                <a href="{{ route('dashboard') }}">Dashboard PPPI</a>
            </div>
            <nav class="flex flex-col mt-4 space-y-2">
                <a href="{{ route('marcas') }}" class="py-2 px-4 flex items-center hover:bg-blue-700 transition duration-200">
                    <span class="text-lg">📑</span>
                    <span class="ml-2">Marcas</span>
                </a>
                <a href="{{ route('revistas') }}" class="py-2 px-4 flex items-center hover:bg-blue-700 transition duration-200">
                    <span class="text-lg">📚</span>
                    <span class="ml-2">Revistas</span>
                </a>
                <a href="{{ route('processos') }}" class="py-2 px-4 flex items-center hover:bg-blue-700 transition duration-200">
                    <span class="text-lg">🗂️</span>
                    <span class="ml-2">Processos</span>
                </a>
            </nav>
        </div>

        <!-- Main Content with Scroll -->
        <div class="flex-1 p-6 overflow-y-auto">
            <div class="bg-white p-4 rounded-lg shadow-lg">
                <h1 class="text-2xl font-semibold mb-4">{{ $title ?? 'Dashboard' }}</h1>
                {{ $slot }}
            </div>
        </div>
    </div>

    @livewireScripts
</body>
</html>
