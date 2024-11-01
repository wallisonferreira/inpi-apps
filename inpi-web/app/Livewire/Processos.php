<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Models\Brand; // Importa o modelo Brand
use Illuminate\Database\Eloquent\Builder;

class Processos extends Component
{
    use WithPagination;

    public $searchMarca = '';
    public $searchNCL = '';
    public $monitorados = []; // Array para armazenar IDs das marcas monitoradas

    protected $paginationTheme = 'tailwind';

    public function scopeBuscaFulltext(Builder $query, $termo)
    {
        return $query->selectRaw("*, MATCH(marca_nome) AGAINST(? IN NATURAL LANGUAGE MODE) AS relevance_score", [$termo])
                     ->whereRaw("MATCH(marca_nome) AGAINST(? IN NATURAL LANGUAGE MODE)", [$termo])
                     ->orderByDesc('relevance_score');
    }

    public function mount() {
        $this->monitorados = Brand::pluck('marca_nome')->toArray(); // Preenche a lista de marcas monitoradas
    }

    public function updatedSearchMarca($value)
    {
        $this->searchMarca = $value;
        $this->resetPage();
    }

    public function updatedSearchNCL($value)
    {
        $this->searchNCL = $value;
        $this->resetPage();
    }

    public function monitorarMarca($marcaNome, $titular)
    {

        $query = DB::connection('mysql_revistas')->table('dados_xml')
            ->where('marca_nome', $marcaNome)
            ->where('titulares', 'like', '%'.$titular.'%')
            ->where('numero_processo', '<>', '')
            ->where('lista_classe_nice', '<>', '')
            ->first();

        dd($query);

        if (!$query) {
            session()->flash('status', 'Marca não possui dados o suficiente.');

            $this->redirect('/processos');
        }

        if (!in_array($marcaNome, $this->monitorados)) {
            try {
                // Adiciona a marca ao banco de dados
                Brand::create([
                    'marca_nome' => $marcaNome,
                    'NCL' => $query->lista_classe_nice,
                    'numero_processo' => $query->numero_processo,
                    'titulares' => $query->titulares,
                    'status' => $query->status ? $query->status : "",
                ]);
                $this->monitorados[] = $marcaNome; // Atualiza a lista de monitorados
            } catch (\Throwable $th) {
                session()->flash('status', 'Marca não possui dados o suficiente.');

                $this->redirect('/processos');
            }
        } else {
            // Se a marca já estiver monitorada, remove do banco de dados
            Brand::where('marca_nome', $marcaNome)->delete();
            $this->monitorados = array_diff($this->monitorados, [$marcaNome]); // Atualiza a lista
        }
    }

    public function render()
    {
        $query = DB::connection('mysql_revistas')->table('dados_xml');

        // Aplicando filtros de pesquisa
        if ($this->searchMarca) {

            if ($this->searchMarca == '') {
                $query->where('marca_nome', 'like', $this->searchMarca . '%');
            } else {
                $query->selectRaw("*, MATCH(marca_nome) AGAINST(? IN NATURAL LANGUAGE MODE) AS relevance_score", [$this->searchMarca])
                     ->whereRaw("MATCH(marca_nome) AGAINST(? IN NATURAL LANGUAGE MODE)", [$this->searchMarca])
                     ->orderByDesc('relevance_score');
            }
        }

        if ($this->searchNCL) {
            $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(lista_classe_nice, '$[0].especificacao')) LIKE ?", ["{$this->searchNCL}%"]);
        }

        $processos = $query->paginate(50);

        return view('livewire.processos', [
            'processos' => $processos,
        ])->layout('components.layouts.app');
    }
}
