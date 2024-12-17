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
        Schema::create('poems', function (Blueprint $table) {
            $table->id();
            $table->enum('created_by', ['admin','user']);
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('title');
            $table->foreignId('poet_id')->constrained('users');
            $table->enum('type', ['madeh','rethaa']);
            $table->foreignId('poem_type_id')->constrained('poem_types');
            $table->foreignId('category_id')->constrained('categories');
            $table->foreignId('language_id')->constrained('languages');
            $table->foreignId('occasion_id')->constrained('occasions');
            $table->text('body');
            $table->integer('audios_count')->default(0);
            $table->enum('publish_status', ['pending', 'approved', 'rejected']);
            $table->boolean('status')->default(true);
            $table->foreignId('reject_reason_id')->nullable()->constrained('reject_reasons');
            $table->string('custom_reject_reason')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('poems');
    }
};
