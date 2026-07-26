<?php

namespace App\Support;

class QrStyle
{
    public const Pulse = 'pulse';

    public const Noir = 'noir';

    public const Emblem = 'emblem';

    /**
     * @return array<string, array{id: string, name: string, tagline: string, subtitle: string, description: string, requires_logo: bool}>
     */
    public static function options(): array
    {
        return [
            self::Pulse => [
                'id' => self::Pulse,
                'name' => 'Pulse',
                'tagline' => 'Discover the menu',
                'subtitle' => '',
                'description' => 'Soft light card',
                'requires_logo' => false,
            ],
            self::Noir => [
                'id' => self::Noir,
                'name' => 'Noir',
                'tagline' => "See what's cooking",
                'subtitle' => '',
                'description' => 'Bold dark card',
                'requires_logo' => false,
            ],
            self::Emblem => [
                'id' => self::Emblem,
                'name' => 'Emblem',
                'tagline' => 'Explore the menu',
                'subtitle' => '',
                'description' => 'Logo woven in',
                'requires_logo' => true,
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function codes(bool $hasLogo = true): array
    {
        return collect(self::options())
            ->filter(fn (array $option) => $hasLogo || ! $option['requires_logo'])
            ->keys()
            ->values()
            ->all();
    }

    public static function isValid(string $style, bool $hasLogo = true): bool
    {
        return in_array(strtolower($style), self::codes($hasLogo), true);
    }

    public static function default(): string
    {
        return self::Pulse;
    }

    public static function requiresLogo(string $style): bool
    {
        $style = strtolower($style);

        return (bool) (self::options()[$style]['requires_logo'] ?? false);
    }

    /**
     * @return list<array{id: string, name: string, tagline: string, subtitle: string, description: string, requires_logo: bool}>
     */
    public static function forFrontend(bool $hasLogo = true): array
    {
        return collect(self::options())
            ->filter(fn (array $option) => $hasLogo || ! $option['requires_logo'])
            ->values()
            ->all();
    }

    public static function normalize(?string $style, bool $hasLogo = true): string
    {
        $style = strtolower((string) $style);

        // Legacy "frame" style maps to pulse.
        if ($style === 'frame') {
            $style = self::default();
        }

        return self::isValid($style, $hasLogo) ? $style : self::default();
    }
}
