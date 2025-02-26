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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('collection_id')->index();
            $table->foreign('collection_id', 'fk_product_collection_id')
            ->references('id')
            ->on('collections')
            ->onDelete('cascade');
            $table->text('shopify_product_id')->nullable();
            $table->text('shopify_product_link')->nullable();
            $table->text('title')->nullable();
            $table->text('description')->nullable();
            $table->text('tags')->nullable();
            $table->string('shopify_publication_status')->default('draft');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
