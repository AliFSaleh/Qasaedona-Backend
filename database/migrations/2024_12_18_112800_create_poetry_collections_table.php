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
        Schema::create('poetry_collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poet_id')->constrained('users');
            $table->text('title');
            $table->text('description');
            $table->integer('poems_count')->default(0);
            $table->boolean('status');
            $table->enum('publish_status', ['pending', 'approved', 'rejected']);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('poetry_collections');
    }
};
