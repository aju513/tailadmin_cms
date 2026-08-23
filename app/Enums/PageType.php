<?php

namespace App\Enums;

enum PageType: string
{
    case Standard = 'standard';
    case Article = 'article';
    case Contact = 'contact';
    case Sitemap = 'sitemap';
    case Team = 'team';
    case Photo = 'photo';
    case Video = 'video';
    case Faq = 'faq';
    case Legal = 'legal';

    public function label(): string
    {
        return match ($this) {
            self::Standard => 'Standard Page',
            self::Article => 'Article',
            self::Contact => 'Contact',
            self::Sitemap => 'Sitemap',
            self::Team => 'Team',
            self::Photo => 'Photo Gallery',
            self::Video => 'Video',
            self::Faq => 'FAQ',
            self::Legal => 'Legal Document',
        };
    }
}
