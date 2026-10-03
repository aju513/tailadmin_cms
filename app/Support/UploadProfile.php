<?php

namespace App\Support;

use Illuminate\Validation\Rule;
use InvalidArgumentException;

class UploadProfile
{
    public static function settings(string $profile): array
    {
        $settings = config('settings.'.$profile);
        if (! is_array($settings)) {
            throw new InvalidArgumentException("Unknown upload profile [{$profile}].");
        }

        return str_starts_with($profile, 'images.')
            ? array_replace(config('settings.uploads.image'), $settings)
            : $settings;
    }

    public static function rules(string $profile, mixed $presence = 'nullable'): array
    {
        $settings = self::settings($profile);
        $rules = [$presence, $settings['type'], 'mimes:'.implode(',', $settings['mimes']), 'max:'.$settings['max_size_kb']];
        if ($settings['type'] === 'image' && ($settings['enforce_dimensions'] ?? false) && isset($settings['width'], $settings['height'])) {
            $rules[] = Rule::dimensions()->width($settings['width'])->height($settings['height']);
        }

        return $rules;
    }

    public static function maxBytes(string $profile): int
    {
        return self::settings($profile)['max_size_kb'] * 1024;
    }

    public static function accept(string $profile): string
    {
        return implode(',', array_map(fn ($extension) => '.'.$extension, self::settings($profile)['mimes']));
    }

    public static function dimensionHint(string $profile): ?string
    {
        $settings = self::settings($profile);
        if (! isset($settings['width'], $settings['height'])) {
            return null;
        }

        $label = ($settings['enforce_dimensions'] ?? false) ? 'Required' : 'Recommended';

        return "{$label} dimensions: {$settings['width']} × {$settings['height']} px.";
    }
}
