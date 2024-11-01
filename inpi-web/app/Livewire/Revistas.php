<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;

class Revistas extends Component
{
    public $revistas;

    public function boot() {
        $this->revistas = DB::connection('mysql_revistas')->table('revistas')->get();
    }

    public function render()
    {

        return view('livewire.revistas', [
            'revistas' => $this->revistas,
        ])->layout('components.layouts.app');
    }
}
