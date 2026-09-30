<?php

namespace App\Enums;

enum NoticeType: string
{
    case General = 'general';
    case Tender = 'tender';
    case PressRelease = 'press_release';
    case Application = 'application';

    public function label(): string
    {
        return match ($this) {
            self::General => 'General Notices',
            self::Tender => 'Tender Notices',
            self::PressRelease => 'Press Releases',
            self::Application => 'Application Announcements',
        };
    }

    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $type) {
            $options[$type->value] = $type->label();
        }

        return $options;
    }
}
