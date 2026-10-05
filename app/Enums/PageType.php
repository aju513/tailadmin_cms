<?php

namespace App\Enums;

enum PageType: string
{
    case Article = 'article';
    case News = 'news';
    case Notices = 'notices';
    case Resource = 'resource';
    case Team = 'team';
    case ContactUs = 'contact_us';
    case Grievance = 'grievance';
    case Sitemap = 'sitemap';
    case Hall = 'hall';
    case Faqs = 'faqs';
    case Videos = 'videos';
    case Gallery = 'gallery';

    public function label(): string
    {
        return match ($this) {
            self::Article => 'Article',
            self::News => 'News',
            self::Notices => 'Notices',
            self::Resource => 'Resource',
            self::Team => 'Team',
            self::ContactUs => 'Contact Us',
            self::Grievance => 'Grievance Form',
            self::Sitemap => 'Sitemap',
            self::Hall => 'Hall',
            self::Faqs => 'FAQs',
            self::Videos => 'Videos',
            self::Gallery => 'Gallery',
        };
    }
}
