<?php

namespace App\Services\Frontend;

use DOMDocument;
use DOMElement;
use DOMNode;
use Illuminate\Support\Str;

class SafeHtml
{
    private const TAGS = ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'li', 'blockquote', 'a', 'table', 'thead', 'tbody', 'tr', 'th', 'td', 'figure', 'figcaption', 'img', 'span', 'div', 'section', 'hr'];

    public function clean(?string $html): string
    {
        if (! $html) {
            return '';
        }
        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML('<?xml encoding="UTF-8"><html><body>'.$html.'</body></html>', LIBXML_NONET);
            $body = $document->getElementsByTagName('body')->item(0);
            if (! $body) {
                return '';
            }
            $this->walk($body);
            $result = '';
            foreach ($body->childNodes as $child) {
                $result .= $document->saveHTML($child);
            }

            return $result;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    private function walk(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if (! $child instanceof DOMElement) {
                if ($child->nodeType !== XML_TEXT_NODE) {
                    $node->removeChild($child);
                }

                continue;
            }
            $tag = strtolower($child->tagName);
            if (! in_array($tag, self::TAGS, true)) {
                $node->removeChild($child);

                continue;
            }
            foreach (iterator_to_array($child->attributes) as $attribute) {
                $allowed = match ($tag) {
                    'a' => ['href', 'title'], 'img' => ['src', 'alt', 'width', 'height'], 'td','th' => ['colspan', 'rowspan'], default => []
                };
                if (! in_array($attribute->name, $allowed, true) || (in_array($attribute->name, ['href', 'src']) && ! $this->safeUrl($attribute->value, $tag === 'a'))) {
                    $child->removeAttribute($attribute->name);
                }
            }
            if ($tag === 'a') {
                $child->setAttribute('rel', 'noopener noreferrer');
            }
            if ($tag === 'img') {
                $child->setAttribute('loading', 'lazy');
                $child->setAttribute('decoding', 'async');
            }
            $this->walk($child);
        }
    }

    public function safeUrl(?string $url, bool $links = false): bool
    {
        if (! $url || preg_match('/[\x00-\x20\\\\]/', $url)) {
            return false;
        }

        return (str_starts_with($url, '/') && ! str_starts_with($url, '//')) || str_starts_with($url, '#') || in_array(strtolower(parse_url($url, PHP_URL_SCHEME) ?: ''), $links ? ['https', 'http', 'mailto', 'tel'] : ['https', 'http'], true);
    }

    public function externalUrl(?string $url): bool
    {
        return $this->safeUrl($url) && Str::isUrl($url, ['http', 'https']);
    }
}
