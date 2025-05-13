<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void {
        Schema::create('iblog__category_translations', function (Blueprint $table) {
            $table->id();
            $table->text('title');
            $table->string('slug', 191);
            $table->text('description');
            $table->text('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->text('translatable_options')->nullable();
            $table->unsignedBigInteger('category_id');
            $table->string('locale', 191);
            $table->unsignedInteger('status')->default(0);

            $table->unique(['category_id', 'locale']);
            $table->unique(['slug', 'locale']);
            $table->index('slug');
            $table->index('locale');
            $table->index('status');

            $table->foreign('category_id')->references('id')->on('iblog__categories')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('iblog__category_translations');
    }
};