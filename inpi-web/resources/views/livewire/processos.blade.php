<div class="p-4">
    @php
    $itens = [
        ['codigo' => 'IPAS157', 'descricao' => 'Arquivamento definitivo de pedido de registro por falta de pagamento da concessão', 'cor' => '#FFB2B2'], // Vermelho claro (mais negativo)
        ['codigo' => 'IPAS024', 'descricao' => 'Indeferimento do pedido', 'cor' => '#D57D7D'], // Bordô claro (negativo)
        ['codigo' => 'IPAS161', 'descricao' => 'Extinção de registro pela expiração do prazo de vigência', 'cor' => '#D09A9A'], // Marrom claro (negativo)
        ['codigo' => 'IPAS136', 'descricao' => 'Exigência de mérito', 'cor' => '#D5C639'], // Ocre claro (exigência, neutro)
        ['codigo' => 'IPAS423', 'descricao' => 'Notificação de oposição', 'cor' => '#EBAF6B'], // Laranja claro (neutro, de atenção)
        ['codigo' => 'IPAS009', 'descricao' => 'Publicação de pedido de registro para oposição (exame formal concluído)', 'cor' => '#B5B52F'], // Verde oliva claro (neutro, aguardando)
        ['codigo' => 'IPAS029', 'descricao' => 'Deferimento do pedido', 'cor' => '#A4D79A'], // Verde claro (positivo)
        ['codigo' => 'IPAS158', 'descricao' => 'Concessão de registro', 'cor' => '#76B76D'], // Verde mais claro (mais positivo)
        ['codigo' => 'IPAS142', 'descricao' => 'Sobrestamento do exame de mérito', 'cor' => '#BFAF34'], // Amarelo claro (neutro, atenção)
    ];
    @endphp

    <h1 class="text-2xl font-bold mb-4">Processos</h1>

    <!-- Campos de busca -->
    <div class="flex space-x-4 mb-4">
        <input
            type="text"
            wire:model.live="searchMarca"
            placeholder="Buscar por nome da marca"
            class="p-2 border rounded w-1/2"
            wire:dirty.class="border-yellow-500"
            wire:loading.class="opacity-50"
        />

        <input
            type="text"
            wire:model.live="searchNCL"
            placeholder="Buscar por especificação NCL"
            class="p-2 border rounded w-1/2"
            wire:dirty.class="border-yellow-500"
            wire:loading.class="opacity-50"
        />
    </div>

    @if (session('status'))
        <div class="alert alert-success py-2 px-4 bg-[#FFB2B2]">
            {{ session('status') }}
        </div>
    @endif

    <!-- Indicador de Carregamento Customizado -->
    <div wire:loading class="flex items-center justify-center text-blue-600 font-semibold space-x-2 mt-2">
        <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4h4a8 8 0 01-8-8z"></path>
        </svg>
        <span>Buscando...</span>
    </div>

    <!-- Lista de processos com layout compacto e paginação -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach ($processos as $processo)
            @php
                // Obtém o código do despacho
                $codigoDespacho = json_decode($processo->despachos)[0]->codigo ?? null;

                // Encontra a cor correspondente
                $corEtiqueta = '#FFFFFF'; // Cor padrão (branca) caso não encontre
                foreach ($itens as $item) {
                    if ($item['codigo'] === $codigoDespacho) {
                        $corEtiqueta = $item['cor'];
                        break;
                    }
                }

                // Verifica se a marca e pelo menos um titular estão disponíveis
                $titular = json_decode($processo->titulares)[0]->nome_razao_social ?? '';
                $podeMonitorar = !empty($processo->marca_nome) && !empty($titular);
            @endphp

            <div class="flex flex-col bg-white p-3 rounded-md shadow-sm justify-between" style="border-left: 4px solid {{ $corEtiqueta }};">
                <div>
                    <div class="flex justify-between">
                        <div>
                            <h3 class="text-md font-semibold">Marca: {{ $processo->marca_nome }}</h3>
                        </div>
                        <div>
                            <h3 class="text-md text-gray-600">Revista: {{ $processo->numero_revista }}</h3>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600">Data: {{ $processo->data_revista }}</p>
                    <p class="text-sm text-gray-600">Processo: {{ $processo->numero_processo }}</p>
                    <p class="text-sm text-gray-600">Titular: {{ $titular ?: 'N/A' }}</p>
                    <p class="text-sm text-gray-800">Procurador: {{ $processo->procurador ?: 'N/A' }}</p>

                    <p class="text-sm text-gray-800">
                        Despachos:
                        <span class="block overflow-hidden text-ellipsis max-h-16">
                            {{ \Illuminate\Support\Str::limit(json_decode($processo->despachos)[0]->codigo ?? 'N/A', 100) }}
                        </span>
                    </p>
                    <p class="text-sm text-gray-800">
                        Despachos:
                        <span class="block overflow-hidden text-ellipsis max-h-16">
                            {{ \Illuminate\Support\Str::limit(json_decode($processo->despachos)[0]->nome ?? 'N/A', 100) }}
                        </span>
                    </p>

                    <!-- Classe Nice com truncamento de texto -->
                    <p class="text-sm text-gray-600">
                        Classe Nice:
                        <span class="block overflow-hidden text-ellipsis max-h-16">
                            {{ \Illuminate\Support\Str::limit(json_decode($processo->lista_classe_nice)[0]->especificacao ?? 'N/A', 100) }}
                        </span>
                    </p>
                    <p class="text-sm text-gray-600">Status: {{ json_decode($processo->lista_classe_nice)[0]->status ?? 'N/A' }}</p>
                </div>

                <!-- Botão Monitorar Marca -->
                @if ($podeMonitorar)
                    <button
                        wire:click="monitorarMarca('{{ $processo->marca_nome }}', '{{ $titular }}')"
                        class="mt-2 w-full px-3 py-1 {{ in_array($processo->marca_nome, $monitorados) ? 'bg-red-500' : 'bg-blue-500' }} text-white rounded hover:bg-blue-600 transition duration-200 text-sm">
                        {{ in_array($processo->marca_nome, $monitorados) ? 'Desabilitar monitoramento' : 'Monitorar marca' }}
                    </button>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Paginação -->
    <div class="mt-4">
        {{ $processos->links() }}
    </div>
</div>
