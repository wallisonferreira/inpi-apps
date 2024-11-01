<div class="p-4">
    <h1 class="text-2xl font-bold mb-4">Marcas Monitoradas</h1>

    @if ($marcas->isEmpty())
        <p class="text-gray-600">Nenhuma marca monitorada encontrada.</p>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($marcas as $marca)
                <div class="flex flex-col bg-white p-3 rounded-md shadow-sm justify-between">
                    <div>
                        <h3 class="text-sm text-gray-600">Marca: <span class="text-md font-semibold">{{ $marca->marca_nome }}</span></h3>
                        <p class="text-sm text-gray-600">NCL: {{ \Illuminate\Support\Str::limit(json_decode($marca->NCL)[0]->especificacao ?? 'N/A', 100) }}</p>
                        <p class="text-sm text-gray-600">Número do Processo: {{ $marca->numero_processo }}</p>
                        <p class="text-sm text-gray-600">Titular: {{ \Illuminate\Support\Str::limit(json_decode($marca->titulares)[0]->nome_razao_social ?? 'N/A', 80) }}</p>
                        <p class="text-sm text-gray-600">Status: {{ json_decode($marca->NCL)[0]->status ?? 'N/A' }}</p>
                        <p class="text-sm text-gray-600">Data de Criação: {{ $marca->created_at->format('d/m/Y') }}</p>
                    </div>
                    <button
                        wire:click="desabilitarMonitoramento('{{ $marca->id }}')"
                        class="mt-2 w-full px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 transition duration-200 text-sm">
                        Desabilitar Monitoramento
                    </button>
                </div>
            @endforeach
        </div>
    @endif
</div>
