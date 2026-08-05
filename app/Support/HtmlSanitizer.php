<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;

class HtmlSanitizer
{
    private const ALLOWED_TAGS = ['p', 'br', 'strong', 'em', 'u', 's', 'blockquote', 'ul', 'ol', 'li', 'h2', 'h3', 'h4', 'a', 'img', 'figure', 'figcaption', 'pre', 'code', 'table', 'thead', 'tbody', 'tr', 'th', 'td'];

    private const ALLOWED_ATTRIBUTES = ['href', 'src', 'alt', 'title', 'target', 'rel', 'colspan', 'rowspan'];

    public static function clean(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return $html;
        }
        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8" ?><div id="cms-root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        $xpath = new DOMXPath($document);
        foreach (iterator_to_array($xpath->query('//*[@id="cms-root"]//*') ?: []) as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }
            if (! in_array(strtolower($node->tagName), self::ALLOWED_TAGS, true)) {
                self::unwrap($node);

                continue;
            }
            foreach (iterator_to_array($node->attributes) as $attribute) {
                if (! in_array(strtolower($attribute->name), self::ALLOWED_ATTRIBUTES, true)) {
                    $node->removeAttribute($attribute->name);
                }
            }
            foreach (['href', 'src'] as $attribute) {
                $value = trim($node->getAttribute($attribute));
                if ($value !== '' && preg_match('/^(?:javascript|data):/i', $value)) {
                    $node->removeAttribute($attribute);
                }
            }
            if ($node->tagName === 'a' && $node->getAttribute('target') === '_blank') {
                $node->setAttribute('rel', 'noopener noreferrer');
            }
        }
        $root = $document->getElementById('cms-root');
        if (! $root) {
            return '';
        }
        $clean = '';
        foreach ($root->childNodes as $child) {
            $clean .= $document->saveHTML($child);
        }

        return $clean;
    }

    private static function unwrap(DOMNode $node): void
    {
        $parent = $node->parentNode;
        if (! $parent) {
            return;
        }
        while ($node->firstChild) {
            $parent->insertBefore($node->firstChild, $node);
        }
        $parent->removeChild($node);
    }
}
