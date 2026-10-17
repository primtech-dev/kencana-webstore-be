<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id')->index();
            $table->string('from_status', 32)->nullable();
            $table->string('to_status', 32);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('note')->nullable();
            $table->timestampTz('created_at')->useCurrent();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_histories');
    }
};
