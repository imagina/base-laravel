<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void {
        Schema::create('iblog__categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('parent_id')->nullable()->index();
            $table->unsignedInteger('lft')->nullable()->index();
            $table->unsignedInteger('rgt')->nullable()->index();
            $table->unsignedInteger('depth')->nullable();
            $table->boolean('internal')->default(false)->index();
            $table->text('options')->nullable();
            $table->string('external_id', 191)->nullable();
            $table->timestamps();
            $table->boolean('show_menu')->default(false);
            $table->boolean('featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->unsignedBigInteger('updated_by')->nullable()->index();
            $table->unsignedBigInteger('deleted_by')->nullable()->index();
            $table->unsignedBigInteger('organization_id')->nullable()->index();

            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
    }

    public function down(): void {
        Schema::dropIfExists('iblog__categories');
    }
};