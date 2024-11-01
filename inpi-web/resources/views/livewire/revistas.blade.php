<div class="p-4">
    <h1 class="text-2xl font-bold mb-4">Revistas</h1>

    <!-- Lista com altura fixa e rolagem -->
    <div class="w-1/3 justify-center space-y-2 h-100 overflow-y-auto">
        @foreach ($revistas as $revista)
            <div class="bg-white p-2 rounded-md shadow flex justify-between items-center hover:bg-blue-50 hover:shadow-lg transition duration-200">
                <div>
                    <h3 class="text-sm font-semibold">Número: {{ $revista->numero }}</h3>
                    <p class="text-xs text-gray-600">Data: {{ \Carbon\Carbon::parse($revista->data)->format('d/m/Y') }}</p>
                </div>
                <div>
                    <a href="{{ $revista->url_zip }}" target="_blank" class="text-blue-500 text-xs underline">Baixar ZIP</a>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Botão de ação -->
    <button wire:click="decrement" class="mt-4 px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 transition duration-200 text-sm">
        Remover Último
    </button>
</div>
