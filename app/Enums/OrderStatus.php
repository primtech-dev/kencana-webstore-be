<?php

namespace App\Enums;

/**
 * Sumber tunggal status order + aturan transisinya.
 */
enum OrderStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Complete = 'complete';
    case Cancelled = 'cancelled';
    case Return = 'return';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Paid => 'Dibayar',
            self::Processing => 'Diproses',
            self::Shipped => 'Dikirim',
            self::Complete => 'Selesai',
            self::Cancelled => 'Dibatalkan',
            self::Return => 'Retur',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Paid => 'info',
            self::Processing => 'primary',
            self::Shipped => 'secondary',
            self::Complete => 'success',
            self::Cancelled => 'danger',
            self::Return => 'dark',
        };
    }

    /** @return list<self> */
    public function allowedNext(): array
    {
        return match ($this) {
            self::Pending => [self::Paid, self::Processing, self::Cancelled],
            self::Paid => [self::Processing, self::Cancelled],
            self::Processing => [self::Shipped, self::Cancelled],
            self::Shipped => [self::Complete, self::Return],
            self::Complete => [self::Return],
            self::Cancelled, self::Return => [],
        };
    }

    public function canTransitionTo(self $to): bool
    {
        return in_array($to, $this->allowedNext(), true);
    }

    /** @return array<string,string> value => label */
    public static function labels(): array
    {
        $out = [];
        foreach (self::cases() as $case) {
            $out[$case->value] = $case->label();
        }

        return $out;
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }

    public static function labelFor(?string $value): string
    {
        return self::tryFrom((string) $value)?->label() ?? ucfirst((string) $value);
    }

    public static function badgeFor(?string $value): string
    {
        return self::tryFrom((string) $value)?->badge() ?? 'light';
    }
}
