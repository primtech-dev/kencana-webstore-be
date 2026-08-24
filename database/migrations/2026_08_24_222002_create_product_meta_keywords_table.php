<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_meta_keywords', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('product_id')->unsigned();
            $table->bigInteger('meta_keyword_id')->unsigned();
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index('product_id');
            $table->index('meta_keyword_id');

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('meta_keyword_id')->references('id')->on('meta_keywords')->onDelete('cascade');

            $table->unique(['product_id', 'meta_keyword_id'], 'product_meta_keyword_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_meta_keywords');
    }
};
