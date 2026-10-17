<?php

namespace App\Support;

/**
 * Helper kuantitas stok desimal (2 digit). Aritmatika dilakukan dalam bilangan bulat
 * "sen" (x100) agar bebas error floating point; nilai dikembalikan sebagai string "123.45"
 * (sama dengan cara PDO membaca kolom decimal(14,2)).
 */
final class Qty
{
    public const SCALE = 2;

    public static function toCents(string|int|float|null $value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        return (int) round(((float) $value) * 100);
    }

    public static function fromCents(int $cents): string
    {
        $sign = $cents < 0 ? '-' : '';
        $abs = abs($cents);

        return sprintf('%s%d.%02d', $sign, intdiv($abs, 100), $abs % 100);
    }

    public static function norm(string|int|float|null $value): string
    {
        return self::fromCents(self::toCents($value));
    }

    public static function add(string|int|float|null $a, string|int|float|null $b): string
    {
        return self::fromCents(self::toCents($a) + self::toCents($b));
    }

    public static function sub(string|int|float|null $a, string|int|float|null $b): string
    {
        return self::fromCents(self::toCents($a) - self::toCents($b));
    }

    /** -1, 0, 1 */
    public static function cmp(string|int|float|null $a, string|int|float|null $b): int
    {
        return self::toCents($a) <=> self::toCents($b);
    }

    public static function isZero(string|int|float|null $value): bool
    {
        return self::toCents($value) === 0;
    }

    public static function max0(string|int|float|null $value): string
    {
        return self::fromCents(max(0, self::toCents($value)));
    }

    /** Apakah angka memiliki paling banyak 2 digit desimal. */
    public static function hasValidScale(string|int|float $value): bool
    {
        $f = (float) $value;

        return abs($f * 100 - round($f * 100)) < 1e-6;
    }

    /**
     * Tampilan lokal (id-ID): 1.234,5 -> "1.234,50"; bilangan bulat tanpa desimal.
     */
    public static function fmt(string|int|float|null $value): string
    {
        $cents = self::toCents($value);
        $decimals = $cents % 100 === 0 ? 0 : 2;

        return number_format($cents / 100, $decimals, ',', '.');
    }

    /** Untuk value input HTML (titik desimal, tanpa pemisah ribuan). */
    public static function input(string|int|float|null $value): string
    {
        $cents = self::toCents($value);

        return $cents % 100 === 0 ? (string) intdiv($cents, 100) : self::fromCents($cents);
    }

    /**
     * Parse angka dari input pengguna / sel Excel.
     * Menerima "1.25", "1,25", 1.25. Mengembalikan null bila ambigu/bukan angka.
     */
    public static function parse(mixed $raw): ?string
    {
        if ($raw === null || $raw === '') {
            return null;
        }
        if (is_int($raw) || is_float($raw)) {
            return self::hasValidScale($raw) ? self::norm($raw) : null;
        }

        $s = trim((string) $raw);
        if ($s === '') {
            return null;
        }

        // Hanya koma sebagai desimal: "1,25"
        if (preg_match('/^-?\d+,\d{1,2}$/', $s)) {
            $s = str_replace(',', '.', $s);
        }

        if (! preg_match('/^-?\d+(\.\d+)?$/', $s)) {
            return null;
        }

        return self::hasValidScale($s) ? self::norm($s) : null;
    }
}
