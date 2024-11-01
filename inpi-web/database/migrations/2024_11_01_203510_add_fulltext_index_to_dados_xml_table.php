<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_revistas'; // Define a conexão a ser utilizada

    public function up()
    {
        // Use o esquema da conexão específica
        Schema::connection($this->connection)->table('dados_xml', function (Blueprint $table) {
            // Certifique-se de que as colunas existem antes de criar o índice
            if (!Schema::connection($this->connection)->hasColumn('dados_xml', 'marca_nome')) {
                $table->string('marca_nome'); // Adicione a coluna se ela não existir
            }

            if (!Schema::connection($this->connection)->hasColumn('dados_xml', 'lista_classe_nice')) {
                $table->string('lista_classe_nice'); // Adicione a coluna se ela não existir
            }
        });

        // Adiciona o índice FULLTEXT para as colunas marca_nome e lista_classe_nice
        DB::connection($this->connection)->statement('ALTER TABLE dados_xml ADD FULLTEXT INDEX idx_marca_nome(marca_nome)');
        DB::connection($this->connection)->statement('ALTER TABLE dados_xml ADD FULLTEXT INDEX idx_lista_classe_nice(lista_classe_nice)');
    }

    public function down()
    {
        // Remove o índice FULLTEXT ao desfazer o migration
        DB::connection($this->connection)->statement('ALTER TABLE dados_xml DROP INDEX idx_marca_nome');
        DB::connection($this->connection)->statement('ALTER TABLE dados_xml DROP INDEX idx_lista_classe_nice');
    }
};
