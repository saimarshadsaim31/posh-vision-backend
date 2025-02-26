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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('product_id')->index();
            $table->foreign('product_id', 'fk_product_variant_product_id')
            ->references('id')
            ->on('products')
            ->onDelete('cascade');
            $table->decimal('price',19, 2)->default(0);
            $table->text('shopify_variant_id')->nullable();
            $table->text('shopify_inventory_item_id')->nullable();
            $table->text('shopify_location_id')->nullable();
            $table->bigInteger('inventory_available')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
