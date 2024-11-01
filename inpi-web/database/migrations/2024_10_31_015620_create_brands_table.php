<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('marca_nome');
            $table->mediumText('NCL')->nullable();
            $table->string('numero_processo')->nullable();
            $table->string('titulares')->nullable();
            $table->string('status')->nullable();
            $table->datetime('last_check')->nullable();
            $table->integer('cases_number')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};
