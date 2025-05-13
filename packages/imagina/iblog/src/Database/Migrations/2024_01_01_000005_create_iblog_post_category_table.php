<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void {
        Schema::create('iblog__post__category', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id');
            $table->unsignedBigInteger('category_id');

            $table->index(['post_id', 'category_id']);

            // Define foreign keys optionally
            // $table->foreign('post_id')->references('id')->on('iblog__posts')->onDelete('cascade');
            // $table->foreign('category_id')->references('id')->on('iblog__categories')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('iblog__post__category');
    }
};