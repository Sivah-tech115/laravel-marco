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
        Schema::create('query_brand_merchants', function (Blueprint $table) {
            $table->id();
            $table->string('query');
            $table->string('brand_id')->nullable(); // store kelkoo_brand_id
            $table->string('merchant_id')->nullable(); // store kelkoo_merchant_id
            $table->timestamps();

            $table->unique(['query', 'brand_id', 'merchant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('query_brand_merchants');
    }
};
