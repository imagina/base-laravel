<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void {
        Schema::create('iblog__post_translations', function (Blueprint $table) {
            $table->id();
            $table->text('title');
            $table->string('slug', 191);
            $table->text('description');
            $table->text('summary');
            $table->text('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->text('translatable_options')->nullable();
            $table->unsignedBigInteger('post_id');
            $table->string('locale', 191);
            $table->unsignedInteger('status')->default(0);

            $table->unique(['post_id', 'locale']);
            $table->unique(['slug', 'locale']);
            $table->index('slug');
            $table->index('locale');
            $table->index('status');

            $table->fullText(['title', 'description', 'summary']);
            $table->fullText(['title']);
            $table->fullText(['title', 'description']);
            $table->fullText(['title', 'summary']);
            $table->fullText(['description']);
            $table->fullText(['description', 'summary']);
            $table->fullText(['summary']);

            $table->foreign('post_id')->references('id')->on('iblog__posts')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('iblog__post_translations');
    }
};