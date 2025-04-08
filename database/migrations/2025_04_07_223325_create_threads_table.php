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
        Schema::create('threads', function (Blueprint $table) {
            $table->id();
            $table->string('thread_id', 255)->unique(); // Campo para el thread_id que será un string único
            $table->string('apikey', 255); // Campo obligatorio para el APIKEY
            $table->string('user_id', 255); // Campo para el user_id
            $table->timestamps();
            $table->softDeletes(); // Campo para el borrado suave
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('threads');
    }
};
