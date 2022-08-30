<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('versements', function (Blueprint $table) {
            $table->id();
            $table->string('libelle', 200);
            $table->float('montant');
            $table->timestamps();
            $table->unsignedBigInteger('id_utilisateur'); // Clé étrangère
            $table->foreign('id_utilisateur')->references('id')->on('utilisateurs')->onDelete('cascade'); // Contrainte créée pour la clé étrangère
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('versements');
    }
};
