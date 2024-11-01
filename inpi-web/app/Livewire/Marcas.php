<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Brand;

class Marcas extends Component
{
    public $marcas;

    public function desabilitarMonitoramento($id)
    {
        $marca = Brand::find($id);
        if ($marca) {
            $marca->delete();
            session()->flash('message', 'Monitoramento desabilitado com sucesso!');
        }
    }

    public function boot()
    {
        $this->marcas = Brand::all();
    }

    public function render()
    {
        return view('livewire.marcas', [
            'marcas' => $this->marcas,
        ]);
    }
}
