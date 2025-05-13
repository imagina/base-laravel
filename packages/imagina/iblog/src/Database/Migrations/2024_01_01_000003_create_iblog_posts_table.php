<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void {
        Schema::create('iblog__posts', function (Blueprint $table) {
            $table->id();
            $table->text('options')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->string('external_id', 191)->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->boolean('featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->date('date_available')->nullable();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->unsignedBigInteger('organization_id')->nullable()->index();

            $table->index('date_available');

            $table->foreign('category_id')->references('id')->on('iblog__categories');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
    }

    public function down(): void {
        Schema::dropIfExists('iblog__posts');
    }
};