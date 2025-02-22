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
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('user_id')->index();
            $table->foreign('user_id', 'fk_collection_user_id')
            ->references('id')
            ->on('users')
            ->onDelete('cascade');
            $table->text('title');
            $table->text('shopify_collection_id')->nullable();
            $table->text('shopify_collection_link')->nullable();
            $table->longText('image')->nullable();
            $table->longText('description')->nullable();
            $table->enum('status', ['approved', 'pending', 'rejected'])->default('pending');
            $table->string('shopify_publication_status')->default('draft');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collections');
    }
};
