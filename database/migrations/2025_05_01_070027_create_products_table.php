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
            $table->string('offer_id')->unique();
            $table->text('title');
            $table->text('slug')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('price_without_rebate', 10, 2)->nullable();
            $table->decimal('rebate_percentage', 5, 2)->nullable();
            $table->decimal('delivery_cost', 10, 2)->nullable();
            $table->decimal('total_price', 10, 2);
            $table->string('currency', 10);
            $table->string('availability_status')->nullable();
            $table->string('time_to_deliver')->nullable();
            $table->string('ean')->nullable();
            $table->text('image_url')->nullable();
            $table->text('zoom_image_url')->nullable();
            $table->text('offer_url')->nullable();
            $table->text('go_url')->nullable();
            $table->decimal('estimated_cpc', 10, 5)->nullable();
            $table->decimal('estimated_mobile_cpc', 10, 5)->nullable();

            // Foreign keys
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->foreignId('brand_id')->nullable()->constrained('brands')->onDelete('set null');
            $table->foreignId('merchant_id')->nullable()->constrained('merchants')->onDelete('set null');

            $table->timestamps();
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
