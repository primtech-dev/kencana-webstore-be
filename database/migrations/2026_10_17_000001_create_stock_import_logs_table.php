<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_import_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('batch_id')->index()->comment('sama dengan stock_movements.reference_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('source', 20)->comment('odoo | template');
            $table->string('file_name')->nullable();
            $table->unsignedInteger('rows_total')->default(0)->comment('baris data pada file');
            $table->unsignedInteger('rows_changed')->default(0);
            $table->unsignedInteger('rows_zeroed')->default(0);
            $table->unsignedInteger('rows_skipped')->default(0);
            $table->json('branches')->nullable()->comment('kode cabang yang disinkronkan');
            $table->timestampTz('created_at')->useCurrent();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_import_logs');
    }
};
