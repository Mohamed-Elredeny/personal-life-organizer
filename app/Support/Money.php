<?php

namespace App\Support;

class Money
{
    public const CURRENCIES = [
        'SAR' => ['symbol' => 'ر.س',  'name_en' => 'Saudi Riyal',     'name_ar' => 'ريال سعودي',  'symbol_first' => false],
        'EGP' => ['symbol' => 'ج.م',  'name_en' => 'Egyptian Pound',  'name_ar' => 'جنيه مصري',   'symbol_first' => false],
        'USD' => ['symbol' => '$',    'name_en' => 'US Dollar',       'name_ar' => 'دولار أمريكي', 'symbol_first' => true],
    ];

    public static function options(): array
    {
        $locale = app()->getLocale();
        $key = $locale === 'ar' ? 'name_ar' : 'name_en';
        $out = [];
        foreach (self::CURRENCIES as $code => $meta) {
            $out[$code] = "{$code} · {$meta[$key]}";
        }
        return $out;
    }

    public static function shortOptions(): array
    {
        $out = [];
        foreach (self::CURRENCIES as $code => $meta) {
            $out[$code] = "{$meta['symbol']}  {$code}";
        }
        return $out;
    }

    public static function symbol(string $code): string
    {
        return self::CURRENCIES[$code]['symbol'] ?? $code;
    }

    public static function format(float|int|null $amount, string $code = 'SAR', int $decimals = 2): string
    {
        $amount ??= 0;
        $meta = self::CURRENCIES[$code] ?? ['symbol' => $code, 'symbol_first' => false];
        $formatted = number_format((float) $amount, $decimals);
        return $meta['symbol_first']
            ? $meta['symbol'] . ' ' . $formatted
            : $formatted . ' ' . $meta['symbol'];
    }

    public static function formatCompact(float|int|null $amount, string $code = 'SAR'): string
    {
        return self::format($amount, $code, 0);
    }
}
