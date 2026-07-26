<?php

namespace App\Support;

class Currency
{
    /**
     * Common currencies for restaurant pricing (ISO 4217).
     *
     * @return array<string, array{code: string, name: string, symbol: string}>
     */
    public static function options(): array
    {
        return [
            'MAD' => ['code' => 'MAD', 'name' => 'Moroccan Dirham', 'symbol' => 'DH'],
            'USD' => ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$'],
            'EUR' => ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€'],
            'GBP' => ['code' => 'GBP', 'name' => 'British Pound', 'symbol' => '£'],
            'CAD' => ['code' => 'CAD', 'name' => 'Canadian Dollar', 'symbol' => 'CA$'],
            'AUD' => ['code' => 'AUD', 'name' => 'Australian Dollar', 'symbol' => 'A$'],
            'CHF' => ['code' => 'CHF', 'name' => 'Swiss Franc', 'symbol' => 'CHF'],
            'AED' => ['code' => 'AED', 'name' => 'UAE Dirham', 'symbol' => 'AED'],
            'SAR' => ['code' => 'SAR', 'name' => 'Saudi Riyal', 'symbol' => 'SAR'],
            'TND' => ['code' => 'TND', 'name' => 'Tunisian Dinar', 'symbol' => 'DT'],
            'DZD' => ['code' => 'DZD', 'name' => 'Algerian Dinar', 'symbol' => 'DA'],
            'EGP' => ['code' => 'EGP', 'name' => 'Egyptian Pound', 'symbol' => 'E£'],
            'TRY' => ['code' => 'TRY', 'name' => 'Turkish Lira', 'symbol' => '₺'],
            'JPY' => ['code' => 'JPY', 'name' => 'Japanese Yen', 'symbol' => '¥'],
            'CNY' => ['code' => 'CNY', 'name' => 'Chinese Yuan', 'symbol' => '¥'],
            'INR' => ['code' => 'INR', 'name' => 'Indian Rupee', 'symbol' => '₹'],
            'BRL' => ['code' => 'BRL', 'name' => 'Brazilian Real', 'symbol' => 'R$'],
            'MXN' => ['code' => 'MXN', 'name' => 'Mexican Peso', 'symbol' => 'MX$'],
        ];
    }

    /**
     * @return list<string>
     */
    public static function codes(): array
    {
        return array_keys(self::options());
    }

    public static function isValid(string $code): bool
    {
        return isset(self::options()[strtoupper($code)]);
    }

    public static function default(): string
    {
        return 'MAD';
    }

    /**
     * @return list<array{code: string, name: string, symbol: string, label: string}>
     */
    public static function forFrontend(): array
    {
        return collect(self::options())
            ->map(fn (array $currency) => [
                ...$currency,
                'label' => "{$currency['code']} — {$currency['name']} ({$currency['symbol']})",
            ])
            ->values()
            ->all();
    }
}
