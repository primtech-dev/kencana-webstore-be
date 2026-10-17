<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kuantitas stok menjadi decimal(14,2) (maks 2 digit di belakang koma).
 *
 * Catatan:
 * - Setiap modifier (default/nullable) harus diulang karena ->change() menimpa definisi kolom.
 * - Data lama bertipe integer otomatis terkonversi (5 -> 5.00).
 * - ALTER TYPE mengambil lock penuh pada tabel; jalankan saat sepi.
 * - down() bersifat LOSSY: nilai pecahan dibulatkan ke integer.
 * - order_items.line_quantity & cart_items.quantity ditulis oleh storefront (di luar repo ini);
 *   penulisan integer tetap valid, namun pembacaan akan berupa string "2.00".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory', function (Blueprint $table) {
            $table->decimal('on_hand', 14, 2)->default(0)->change();
            $table->decimal('available', 14, 2)->default(0)->nullable()->change();
            $table->decimal('reserved', 14, 2)->default(0)->change();
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->decimal('change', 14, 2)->change();
            $table->decimal('resulting_on_hand', 14, 2)->nullable()->change();
        });

        Schema::table('stock_receipt_items', function (Blueprint $table) {
            $table->decimal('quantity_received', 14, 2)->default(0)->change();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('line_quantity', 14, 2)->change();
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->decimal('quantity', 14, 2)->change();
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->integer('quantity')->change();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->integer('line_quantity')->change();
        });

        Schema::table('stock_receipt_items', function (Blueprint $table) {
            $table->integer('quantity_received')->default(0)->change();
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->integer('change')->change();
            $table->integer('resulting_on_hand')->nullable()->change();
        });

        Schema::table('inventory', function (Blueprint $table) {
            $table->integer('on_hand')->default(0)->change();
            $table->integer('available')->default(0)->nullable()->change();
            $table->integer('reserved')->default(0)->change();
        });
    }
};
