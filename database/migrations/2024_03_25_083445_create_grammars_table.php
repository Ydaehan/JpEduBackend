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
        Schema::create('grammars', function (Blueprint $table) {
            $table->id();
            $table->text("grammar")->unique();
            $table->text("explain");
            $table->jsonb("example");
            $table->text("mean");
            $table->text("conjunction");
            $table->enum("tier", ["N1", "N2", "N3", "N4", "N5"]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grammars');
    }
};
