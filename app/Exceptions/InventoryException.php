<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Kesalahan domain stok (stok kurang, baris stok tidak ada, dll).
 * Pesannya aman ditampilkan ke pengguna.
 */
class InventoryException extends RuntimeException
{
}
