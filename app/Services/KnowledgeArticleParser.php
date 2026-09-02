<?php

namespace App\Services;

class KnowledgeArticleParser
{
    public const SCHEMA_VERSION = 1;

    /**
     * Parse any input (Canonical JSON/Array, Legacy HTML, or Legacy Plain Text)
     * into a canonical article content structure with Single Source of Truth (segments).
     *
     * @param string|array|null $content
     * @return array{schema_version: int, blocks: array<int, array>}
     */
    public static function parse(string|array|null $content): array
    {
        if (empty($content)) {
            return [
                'schema_version' => self::SCHEMA_VERSION,
                'blocks' => []
            ];
        }

        // 1. If already an array, check if it's canonical
        if (is_array($content)) {
            if (self::isCanonical($content)) {
                return self::normalizeCanonical($content);
            }
            if (isset($content['blocks']) && is_array($content['blocks'])) {
                return [
                    'schema_version' => self::SCHEMA_VERSION,
                    'blocks' => array_map([self::class, 'normalizeBlock'], $content['blocks'])
                ];
            }
        }

        // 2. If it's a string, check if it's JSON
        if (is_string($content)) {
            $trimmed = trim($content);
            if (str_starts_with($trimmed, '{') && str_ends_with($trimmed, '}')) {
                $decoded = json_decode($trimmed, true);
                if (is_array($decoded) && self::isCanonical($decoded)) {
                    return self::normalizeCanonical($decoded);
                }
            }

            // 3. Detect if string contains HTML tags
            if (self::containsHtml($trimmed)) {
                return self::parseHtml($trimmed);
            }

            // 4. Fallback to Plain Text parser
            return self::parsePlainText($trimmed);
        }

        return [
            'schema_version' => self::SCHEMA_VERSION,
            'blocks' => []
        ];
    }

    /**
     * Check if the array represents a canonical schema v1.
     */
    public static function isCanonical(array $data): bool
    {
        return isset($data['schema_version'], $data['blocks']) && is_array($data['blocks']);
    }

    /**
     * Parse Legacy HTML content directly into Canonical Schema Blocks with segments.
     */
    public static function parseHtml(string $html): array
    {
        $blocks = [];
        $cleanHtml = trim($html);

        if (empty($cleanHtml)) {
            return ['schema_version' => self::SCHEMA_VERSION, 'blocks' => []];
        }

        // Use DOMDocument with UTF-8 encoding
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument('1.0', 'UTF-8');
        
        $wrapped = '<!DOCTYPE html><html><head><meta charset="utf-8"></head><body><div id="__article_root">' . $cleanHtml . '</div></body></html>';
        $dom->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $root = $dom->getElementById('__article_root');
        if (!$root) {
            return self::parsePlainText(strip_tags($cleanHtml));
        }

        foreach ($root->childNodes as $node) {
            if ($node->nodeType !== XML_ELEMENT_NODE) {
                $text = trim($node->textContent);
                if (!empty($text)) {
                    $blocks[] = [
                        'id' => self::generateId(),
                        'type' => 'paragraph',
                        'segments' => [['text' => $text]]
                    ];
                }
                continue;
            }

            /** @var \DOMElement $node */
            $tagName = strtolower($node->tagName);
            $class = $node->getAttribute('class');
            $style = $node->getAttribute('style');
            $align = null;
            $lineHeight = null;
            $paddingLeft = null;
            if (!empty($style)) {
                if (preg_match('/text-align:\s*([a-z]+)/i', $style, $m)) {
                    $align = strtolower($m[1]);
                }
                if (preg_match('/line-height:\s*([\d\.]+)/i', $style, $m)) {
                    $lineHeight = $m[1];
                }
                if (preg_match('/padding-left:\s*([\d\.]+(?:rem|px|em))/i', $style, $m)) {
                    $paddingLeft = $m[1];
                }
            }

            // 1. Callout Boxes (Soft Green Tips or Cream Info)
            $isCallout = $tagName === 'div' && (
                $node->getAttribute('data-block') === 'callout' ||
                str_contains($class, 'callout-box') ||
                str_contains($class, 'bg-brand-soft-green') ||
                str_contains($class, 'bg-brand-cream') ||
                str_contains($class, 'rounded-modern')
            );
            if ($isCallout) {
                $dataVar = $node->getAttribute('data-variant');
                $isTips = ($dataVar === 'tips') || str_contains($class, 'bg-brand-soft-green') || (!str_contains($class, 'bg-brand-cream') && !str_contains($class, 'amber'));
                if ($dataVar === 'info' || str_contains($class, 'bg-brand-cream') || str_contains($class, 'amber')) {
                    $isTips = false;
                }
                $variant = $isTips ? 'tips' : 'info';

                $h4 = $node->getElementsByTagName('h4')->item(0);
                $rawTitle = $h4 ? trim($h4->textContent) : '';
                
                $icon = $isTips ? '💡' : '📋';
                $titleText = $rawTitle;

                if (!empty($rawTitle)) {
                    if (preg_match('/^(\X\s*)(.*)$/u', $rawTitle, $matches)) {
                        $potentialIcon = trim($matches[1]);
                        if (mb_strlen($potentialIcon) <= 4 && !ctype_alnum($potentialIcon)) {
                            $icon = $potentialIcon;
                            $titleText = trim($matches[2]);
                        }
                    }
                }

                // Check for inner list in callout
                $ul = $node->getElementsByTagName('ul')->item(0);
                $items = [];
                if ($ul) {
                    foreach ($ul->getElementsByTagName('li') as $li) {
                        $items[] = [
                            'segments' => self::domNodeToSegments($li)
                        ];
                    }
                }

                // Check for inner callout-body or paragraph in callout
                $segments = [];
                $xpath = new \DOMXPath($node->ownerDocument);
                $bodyDiv = $xpath->query('.//div[contains(@class, "callout-body")]', $node)->item(0);
                if ($bodyDiv) {
                    $segments = self::domNodeToSegments($bodyDiv);
                } else {
                    $p = $node->getElementsByTagName('p')->item(0);
                    if ($p) {
                        $segments = self::domNodeToSegments($p);
                    } elseif (empty($items)) {
                        $plain = trim($node->textContent);
                        if (!empty($plain) && !empty($rawTitle) && str_starts_with($plain, $rawTitle)) {
                            $plain = trim(substr($plain, strlen($rawTitle)));
                        }
                        if (!empty($plain)) {
                            $segments = [['text' => $plain]];
                        }
                    }
                }

                $calloutBlock = [
                    'id' => self::generateId(),
                    'type' => 'callout',
                    'variant' => $variant,
                    'icon' => $icon,
                    'title' => $titleText,
                    'segments' => $segments,
                    'items' => $items
                ];
                if ($align) $calloutBlock['align'] = $align;

                $blocks[] = $calloutBlock;
                continue;
            }

            // 2. Headings (h2, h3, h4)
            if (in_array($tagName, ['h2', 'h3', 'h4'])) {
                $level = $tagName === 'h2' ? 2 : 3;
                $headingText = trim($node->textContent);
                if (!empty($headingText)) {
                    $block = [
                        'id' => self::generateId(),
                        'type' => 'heading',
                        'level' => $level,
                        'text' => $headingText
                    ];
                    if ($align) $block['align'] = $align;
                    $blocks[] = $block;
                }
                continue;
            }

            // 3. Blockquotes
            if ($tagName === 'blockquote') {
                $segments = self::domNodeToSegments($node);
                if (!empty($segments)) {
                    $block = [
                        'id' => self::generateId(),
                        'type' => 'quote',
                        'segments' => $segments
                    ];
                    if ($align) $block['align'] = $align;
                    $blocks[] = $block;
                }
                continue;
            }

            // 4. Dividers (hr)
            if ($tagName === 'hr') {
                $blocks[] = [
                    'id' => self::generateId(),
                    'type' => 'divider'
                ];
                continue;
            }

            // 5. Unordered Lists (ul)
            if ($tagName === 'ul') {
                $items = self::parseListElement($node, false);
                if (!empty($items)) {
                    $block = [
                        'id' => self::generateId(),
                        'type' => 'list',
                        'style' => 'unordered',
                        'items' => $items
                    ];
                    if ($align) $block['align'] = $align;
                    $blocks[] = $block;
                }
                continue;
            }

            // 6. Ordered Lists (ol)
            if ($tagName === 'ol') {
                $items = self::parseListElement($node, true);
                if (!empty($items)) {
                    $block = [
                        'id' => self::generateId(),
                        'type' => 'list',
                        'style' => 'ordered',
                        'items' => $items
                    ];
                    if ($align) $block['align'] = $align;
                    $blocks[] = $block;
                }
                continue;
            }

            // 7. Paragraphs (p)
            if ($tagName === 'p') {
                $isLead = (bool) preg_match('/\blead\b/', $class);
                $segments = self::domNodeToSegments($node);
                if (!empty($segments)) {
                    $block = [
                        'id' => self::generateId(),
                        'type' => $isLead ? 'lead' : 'paragraph',
                        'segments' => $segments
                    ];
                    if ($align) $block['align'] = $align;
                    if ($lineHeight) $block['lineHeight'] = $lineHeight;
                    if ($paddingLeft) $block['paddingLeft'] = $paddingLeft;
                    $blocks[] = $block;
                }
                continue;
            }

            // 8. Generic block fallback
            $segments = self::domNodeToSegments($node);
            if (!empty($segments)) {
                $block = [
                    'id' => self::generateId(),
                    'type' => 'paragraph',
                    'segments' => $segments
                ];
                if ($align) $block['align'] = $align;
                if ($lineHeight) $block['lineHeight'] = $lineHeight;
                if ($paddingLeft) $block['paddingLeft'] = $paddingLeft;
                $blocks[] = $block;
            }
        }

        return [
            'schema_version' => self::SCHEMA_VERSION,
            'blocks' => $blocks
        ];
    }

    /**
     * Parse Legacy Plain Text content directly into Canonical Schema Blocks with segments.
     */
    public static function parsePlainText(string $text): array
    {
        $blocks = [];
        $normalized = str_replace(["\r\n", "\r"], "\n", trim($text));

        if (empty($normalized)) {
            return ['schema_version' => self::SCHEMA_VERSION, 'blocks' => []];
        }

        // Split by 2 or more newlines
        $chunks = preg_split('/\n{2,}/', $normalized);
        $isFirst = true;

        foreach ($chunks as $chunk) {
            $chunk = trim($chunk);
            if (empty($chunk)) {
                continue;
            }

            $lines = array_map('trim', explode("\n", $chunk));

            // Check if chunk is a pure ordered list (e.g. "1. Item 1\n2. Item 2")
            $isOrderedList = count($lines) > 1 && self::isAllNumberedLines($lines);
            $isUnorderedList = count($lines) > 1 && self::isAllBulletLines($lines);

            if ($isOrderedList) {
                $items = array_map(function ($l) {
                    $cleaned = preg_replace('/^\d+[\.\)]\s+/', '', $l);
                    return ['segments' => [['text' => $cleaned]]];
                }, $lines);

                $blocks[] = [
                    'id' => self::generateId(),
                    'type' => 'list',
                    'style' => 'ordered',
                    'items' => $items
                ];
                $isFirst = false;
                continue;
            }

            if ($isUnorderedList) {
                $items = array_map(function ($l) {
                    $cleaned = preg_replace('/^[-*•]\s+/', '', $l);
                    return ['segments' => [['text' => $cleaned]]];
                }, $lines);

                $blocks[] = [
                    'id' => self::generateId(),
                    'type' => 'list',
                    'style' => 'unordered',
                    'items' => $items
                ];
                $isFirst = false;
                continue;
            }

            // Check if chunk has a numbered heading with attached body: "1. Gunakan Kemasan Kedap Udara\nUdara adalah musuh..."
            if (preg_match('/^(\d+[\.\)]\s+[^\n]+)\n+(.+)$/s', $chunk, $matches)) {
                $headingTitle = trim($matches[1]);
                $bodyText = trim($matches[2]);

                $blocks[] = [
                    'id' => self::generateId(),
                    'type' => 'heading',
                    'level' => 3,
                    'text' => $headingTitle
                ];

                $blocks[] = [
                    'id' => self::generateId(),
                    'type' => 'paragraph',
                    'segments' => [['text' => $bodyText]]
                ];
                $isFirst = false;
                continue;
            }

            // Single line numbered heading without trailing text: "1. Gunakan Kemasan Kedap Udara"
            if (count($lines) === 1 && preg_match('/^\d+[\.\)]\s+[A-Z0-9]/', $chunk) && mb_strlen($chunk) < 100) {
                $blocks[] = [
                    'id' => self::generateId(),
                    'type' => 'heading',
                    'level' => 3,
                    'text' => $chunk
                ];
                $isFirst = false;
                continue;
            }

            // Standard Paragraph or Lead
            $type = ($isFirst && count($chunks) > 1 && mb_strlen($chunk) > 60) ? 'lead' : 'paragraph';
            $blocks[] = [
                'id' => self::generateId(),
                'type' => $type,
                'segments' => [['text' => $chunk]]
            ];
            $isFirst = false;
        }

        return [
            'schema_version' => self::SCHEMA_VERSION,
            'blocks' => $blocks
        ];
    }

    /**
     * Render Canonical Schema Blocks into clean, styled Landing Page HTML.
     */
    public static function renderToHtml(array|string $canonical): string
    {
        $data = is_string($canonical) ? self::parse($canonical) : self::normalizeCanonical($canonical);
        $blocks = $data['blocks'] ?? [];

        if (empty($blocks)) {
            return '';
        }

        $htmlParts = [];

        foreach ($blocks as $block) {
            $type = $block['type'] ?? 'paragraph';
            $segments = $block['segments'] ?? [];

            $alignStyle = !empty($block['align']) ? 'text-align: ' . $block['align'] . ';' : '';
            $lineStyle = !empty($block['lineHeight']) ? 'line-height: ' . $block['lineHeight'] . ';' : '';
            $padStyle = !empty($block['paddingLeft']) ? 'padding-left: ' . $block['paddingLeft'] . ';' : '';
            $combinedStyle = trim($alignStyle . ' ' . $lineStyle . ' ' . $padStyle);
            $styleAttr = !empty($combinedStyle) ? ' style="' . htmlspecialchars($combinedStyle, ENT_QUOTES, 'UTF-8') . '"' : '';

            switch ($type) {
                case 'lead':
                    $renderedText = self::renderSegmentsToHtml($segments);
                    $htmlParts[] = '<p class="lead text-base text-gray-700 font-medium mb-4 leading-relaxed"' . $styleAttr . '>' . $renderedText . '</p>';
                    break;

                case 'heading':
                    $level = $block['level'] ?? 3;
                    $headingText = $block['text'] ?? '';
                    $headingClass = $level === 2
                        ? 'text-xl sm:text-2xl font-bold text-brand-dark mt-8 mb-4'
                        : 'text-lg sm:text-xl font-bold text-brand-dark mt-6 mb-3';
                    $tag = 'h' . $level;
                    $htmlParts[] = '<' . $tag . ' class="' . $headingClass . '"' . $styleAttr . '>' . htmlspecialchars($headingText, ENT_QUOTES, 'UTF-8') . '</' . $tag . '>';
                    break;

                case 'quote':
                    $renderedText = self::renderSegmentsToHtml($segments);
                    $htmlParts[] = '<blockquote class="my-5 pl-4 border-l-4 border-brand-primary italic text-gray-700 bg-brand-soft-green/20 py-2.5 rounded-r text-sm"' . $styleAttr . '>' . $renderedText . '</blockquote>';
                    break;

                case 'divider':
                    $htmlParts[] = '<hr class="my-6 border-t-2 border-gray-200">';
                    break;

                case 'list':
                    $style = $block['style'] ?? 'unordered';
                    $isOrdered = $style === 'ordered';
                    $tag = $isOrdered ? 'ol' : 'ul';
                    $listClass = $isOrdered
                        ? 'list-decimal list-inside space-y-2 text-sm sm:text-base text-gray-600 mb-4 pl-2'
                        : 'list-disc list-inside space-y-2 text-sm sm:text-base text-gray-600 mb-4 pl-2';

                    $items = $block['items'] ?? [];
                    $renderedListItems = self::renderListItems($items, $isOrdered, 0);
                    $htmlParts[] = '<' . $tag . ' class="' . $listClass . '"' . $styleAttr . '>' . $renderedListItems . '</' . $tag . '>';
                    break;

                case 'callout':
                    $variant = $block['variant'] ?? 'tips';
                    $isTips = $variant === 'tips';
                    $icon = $block['icon'] ?? ($isTips ? '💡' : '📋');
                    $title = $block['title'] ?? ($isTips ? 'Tips Penting:' : 'Catatan Informasi:');
                    $items = $block['items'] ?? [];

                    $wrapperClass = $isTips
                        ? 'my-6 p-4 sm:p-5 rounded-modern bg-brand-soft-green/60 border border-brand-soft-green-border'
                        : 'my-6 p-4 sm:p-5 rounded-modern bg-brand-cream/80 border border-gray-200';

                    $titleClass = $isTips
                        ? 'font-bold text-brand-primary text-sm sm:text-base mb-2'
                        : 'font-bold text-brand-dark text-sm sm:text-base mb-2';

                    $bodyClass = $isTips
                        ? 'text-xs sm:text-sm text-brand-dark leading-relaxed'
                        : 'text-xs sm:text-sm text-gray-600 leading-relaxed';

                    $calloutHtml = '<div class="' . $wrapperClass . '">';
                    $titleDisplay = trim($icon . ' ' . $title);
                    $calloutHtml .= '<h4 class="' . $titleClass . '">' . htmlspecialchars($titleDisplay, ENT_QUOTES, 'UTF-8') . '</h4>';

                    if (!empty($segments)) {
                        $calloutHtml .= '<p class="' . $bodyClass . '">' . self::renderSegmentsToHtml($segments) . '</p>';
                    }

                    if (!empty($items)) {
                        $calloutHtml .= '<ul class="list-disc list-inside space-y-1.5 text-xs sm:text-sm text-gray-600 mt-2">';
                        foreach ($items as $item) {
                            $itemSegments = isset($item['segments']) ? $item['segments'] : [['text' => is_string($item) ? $item : ($item['text'] ?? '')]];
                            $calloutHtml .= '<li>' . self::renderSegmentsToHtml($itemSegments) . '</li>';
                        }
                        $calloutHtml .= '</ul>';
                    }

                    $calloutHtml .= '</div>';
                    $htmlParts[] = $calloutHtml;
                    break;

                case 'paragraph':
                default:
                    $renderedText = self::renderSegmentsToHtml($segments);
                    $htmlParts[] = '<p class="text-base text-gray-600 mb-4 leading-relaxed"' . $styleAttr . '>' . $renderedText . '</p>';
                    break;
            }
        }

        return implode("\n", $htmlParts);
    }

    /**
     * Render structured segments array to safe HTML (Single Source of Truth).
     *
     * @param array<int, array{text: string, bold?: bool, italic?: bool, underline?: bool, strikethrough?: bool, fontSize?: int, link?: string}> $segments
     * @return string
     */
    public static function renderSegmentsToHtml(array $segments): string
    {
        if (empty($segments)) {
            return '';
        }

        $rendered = '';
        foreach ($segments as $seg) {
            $text = htmlspecialchars($seg['text'] ?? '', ENT_QUOTES, 'UTF-8');
            if (empty($text) && $text !== '0') {
                continue;
            }

            if (!empty($seg['bold'])) {
                $text = '<strong>' . $text . '</strong>';
            }
            if (!empty($seg['italic'])) {
                $text = '<em>' . $text . '</em>';
            }
            if (!empty($seg['underline'])) {
                $text = '<u>' . $text . '</u>';
            }
            if (!empty($seg['strikethrough'])) {
                $text = '<s>' . $text . '</s>';
            }
            if (!empty($seg['fontSize']) && (int) $seg['fontSize'] !== 16) {
                $text = '<span style="font-size: ' . (int) $seg['fontSize'] . 'px">' . $text . '</span>';
            }
            if (!empty($seg['link'])) {
                $href = htmlspecialchars($seg['link'], ENT_QUOTES, 'UTF-8');
                $text = '<a href="' . $href . '" target="_blank" rel="noopener noreferrer" class="text-brand-primary underline hover:text-brand-primary-dark">' . $text . '</a>';
            }

            $rendered .= $text;
        }

        return $rendered;
    }

    /**
     * Convert segments to plain text without tags or formatting.
     *
     * @param array<int, array{text: string}> $segments
     * @return string
     */
    public static function segmentsToPlainText(array $segments): string
    {
        return implode('', array_map(fn($s) => $s['text'] ?? '', $segments));
    }

    /**
     * Convert DOM node directly to structured segments.
     */
    private static function domNodeToSegments(\DOMNode $node, array $currentFormat = []): array
    {
        if ($node->nodeType === XML_TEXT_NODE) {
            $text = $node->textContent;
            if ($text !== '') {
                $seg = ['text' => $text];
                if (!empty($currentFormat['bold'])) $seg['bold'] = true;
                if (!empty($currentFormat['italic'])) $seg['italic'] = true;
                if (!empty($currentFormat['underline'])) $seg['underline'] = true;
                if (!empty($currentFormat['strikethrough'])) $seg['strikethrough'] = true;
                if (!empty($currentFormat['fontSize'])) $seg['fontSize'] = $currentFormat['fontSize'];
                if (!empty($currentFormat['link'])) $seg['link'] = $currentFormat['link'];
                return [$seg];
            }
            return [];
        }

        $segments = [];

        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                $text = $child->textContent;
                if ($text !== '') {
                    $seg = ['text' => $text];
                    if (!empty($currentFormat['bold'])) $seg['bold'] = true;
                    if (!empty($currentFormat['italic'])) $seg['italic'] = true;
                    if (!empty($currentFormat['underline'])) $seg['underline'] = true;
                    if (!empty($currentFormat['strikethrough'])) $seg['strikethrough'] = true;
                    if (!empty($currentFormat['fontSize'])) $seg['fontSize'] = $currentFormat['fontSize'];
                    if (!empty($currentFormat['link'])) $seg['link'] = $currentFormat['link'];
                    $segments[] = $seg;
                }
            } elseif ($child->nodeType === XML_ELEMENT_NODE) {
                /** @var \DOMElement $child */
                $tag = strtolower($child->tagName);
                $format = $currentFormat;

                if (in_array($tag, ['strong', 'b'])) {
                    $format['bold'] = true;
                } elseif (in_array($tag, ['em', 'i'])) {
                    $format['italic'] = true;
                } elseif (in_array($tag, ['u']) || str_contains($child->getAttribute('style'), 'text-decoration: underline')) {
                    $format['underline'] = true;
                } elseif (in_array($tag, ['s', 'strike', 'del']) || str_contains($child->getAttribute('style'), 'text-decoration: line-through')) {
                    $format['strikethrough'] = true;
                } elseif ($tag === 'a') {
                    $format['link'] = $child->getAttribute('href');
                }

                $style = $child->getAttribute('style');
                if (!empty($style) && preg_match('/font-size:\s*(\d+)px/i', $style, $m)) {
                    $format['fontSize'] = (int) $m[1];
                } elseif ($tag === 'font' && $child->hasAttribute('size')) {
                    $sizeMap = ['1' => 12, '2' => 14, '3' => 16, '4' => 18, '5' => 24, '6' => 28, '7' => 32];
                    $sz = $child->getAttribute('size');
                    if (isset($sizeMap[$sz])) $format['fontSize'] = $sizeMap[$sz];
                }

                $childSegments = self::domNodeToSegments($child, $format);
                $segments = array_merge($segments, $childSegments);
            }
        }

        return self::mergeConsecutiveSegments($segments);
    }

    /**
     * Merge consecutive segments with identical formatting.
     */
    public static function mergeConsecutiveSegments(array $segments): array
    {
        if (empty($segments)) {
            return [];
        }

        $merged = [];
        $current = null;

        foreach ($segments as $seg) {
            $text = $seg['text'] ?? '';
            if ($text === '') continue;

            $bold = !empty($seg['bold']);
            $italic = !empty($seg['italic']);
            $underline = !empty($seg['underline']);
            $strikethrough = !empty($seg['strikethrough']);
            $fontSize = isset($seg['fontSize']) ? (int) $seg['fontSize'] : null;
            $link = $seg['link'] ?? null;

            if ($current === null) {
                $current = [
                    'text' => $text,
                    'bold' => $bold,
                    'italic' => $italic,
                    'underline' => $underline,
                    'strikethrough' => $strikethrough,
                    'fontSize' => $fontSize,
                    'link' => $link
                ];
                continue;
            }

            if ($current['bold'] === $bold && 
                $current['italic'] === $italic && 
                $current['underline'] === $underline && 
                $current['strikethrough'] === $strikethrough && 
                $current['fontSize'] === $fontSize && 
                $current['link'] === $link) {
                $current['text'] .= $text;
            } else {
                $cleanSeg = ['text' => $current['text']];
                if ($current['bold']) $cleanSeg['bold'] = true;
                if ($current['italic']) $cleanSeg['italic'] = true;
                if ($current['underline']) $cleanSeg['underline'] = true;
                if ($current['strikethrough']) $cleanSeg['strikethrough'] = true;
                if ($current['fontSize']) $cleanSeg['fontSize'] = $current['fontSize'];
                if ($current['link']) $cleanSeg['link'] = $current['link'];
                $merged[] = $cleanSeg;

                $current = [
                    'text' => $text,
                    'bold' => $bold,
                    'italic' => $italic,
                    'underline' => $underline,
                    'strikethrough' => $strikethrough,
                    'fontSize' => $fontSize,
                    'link' => $link
                ];
            }
        }

        if ($current !== null && $current['text'] !== '') {
            $cleanSeg = ['text' => $current['text']];
            if ($current['bold']) $cleanSeg['bold'] = true;
            if ($current['italic']) $cleanSeg['italic'] = true;
            if ($current['underline']) $cleanSeg['underline'] = true;
            if ($current['strikethrough']) $cleanSeg['strikethrough'] = true;
            if ($current['fontSize']) $cleanSeg['fontSize'] = $current['fontSize'];
            if ($current['link']) $cleanSeg['link'] = $current['link'];
            $merged[] = $cleanSeg;
        }

        return $merged;
    }

    /**
     * Normalize and validate block attributes for single source of truth.
     */
    public static function normalizeBlock(array $block): array
    {
        $type = $block['type'] ?? 'paragraph';
        $id = $block['id'] ?? self::generateId();

        // Ensure segments exist for rich-text blocks
        $segments = $block['segments'] ?? [];
        if (empty($segments) && isset($block['text']) && is_string($block['text'])) {
            $segments = [['text' => $block['text']]];
        }

        $normalized = [
            'id' => $id,
            'type' => $type,
        ];

        if (!empty($block['align'])) $normalized['align'] = $block['align'];
        if (!empty($block['lineHeight'])) $normalized['lineHeight'] = $block['lineHeight'];
        if (!empty($block['paddingLeft'])) $normalized['paddingLeft'] = $block['paddingLeft'];

        if ($type === 'heading') {
            $normalized['level'] = isset($block['level']) ? (int) $block['level'] : 3;
            $normalized['text'] = $block['text'] ?? self::segmentsToPlainText($segments);
            return $normalized;
        }

        if ($type === 'quote') {
            $normalized['segments'] = self::mergeConsecutiveSegments($segments);
            return $normalized;
        }

        if ($type === 'divider') {
            return $normalized;
        }

        if ($type === 'list') {
            $normalized['style'] = $block['style'] ?? 'unordered';
            $normalizeItems = function (array $rawItems) use (&$normalizeItems) {
                $items = [];
                foreach ($rawItems as $item) {
                    $normItem = [];
                    if (is_array($item) && isset($item['segments'])) {
                        $normItem['segments'] = self::mergeConsecutiveSegments($item['segments']);
                    } elseif (is_string($item)) {
                        $normItem['segments'] = [['text' => $item]];
                    } elseif (is_array($item) && isset($item['text'])) {
                        $normItem['segments'] = [['text' => $item['text']]];
                    } else {
                        $normItem['segments'] = [['text' => '']];
                    }
                    if (is_array($item) && !empty($item['fontSize'])) $normItem['fontSize'] = (int) $item['fontSize'];
                    if (is_array($item) && !empty($item['style'])) $normItem['style'] = $item['style'];
                    if (is_array($item) && !empty($item['children'])) {
                        $normItem['children'] = $normalizeItems($item['children']);
                        $normItem['childrenStyle'] = $item['childrenStyle'] ?? 'unordered';
                    }
                    $items[] = $normItem;
                }
                return $items;
            };
            $normalized['items'] = $normalizeItems($block['items'] ?? []);
            return $normalized;
        }

        if ($type === 'callout') {
            $normalized['variant'] = $block['variant'] ?? 'tips';
            $normalized['icon'] = $block['icon'] ?? ($normalized['variant'] === 'tips' ? '💡' : '📋');
            $normalized['title'] = $block['title'] ?? '';
            $normalized['segments'] = self::mergeConsecutiveSegments($segments);
            $items = [];
            foreach ($block['items'] ?? [] as $item) {
                if (is_array($item) && isset($item['segments'])) {
                    $items[] = ['segments' => self::mergeConsecutiveSegments($item['segments'])];
                } elseif (is_string($item)) {
                    $items[] = ['segments' => [['text' => $item]]];
                }
            }
            $normalized['items'] = $items;
            return $normalized;
        }

        // lead or paragraph
        $normalized['segments'] = self::mergeConsecutiveSegments($segments);
        return $normalized;
    }

    /**
     * Normalize canonical array wrapper.
     */
    public static function normalizeCanonical(array $data): array
    {
        return [
            'schema_version' => self::SCHEMA_VERSION,
            'blocks' => array_map([self::class, 'normalizeBlock'], $data['blocks'] ?? [])
        ];
    }

    /**
     * Generate unique stable ID for blocks.
     */
    public static function generateId(): string
    {
        return 'block-' . bin2hex(random_bytes(4));
    }

    /**
     * Detect if text contains HTML tags.
     */
    private static function containsHtml(string $text): bool
    {
        return preg_match('/<\s*[a-z][^>]*>/i', $text) === 1;
    }

    /**
     * Check if all lines are numbered list items (1. 2. 3.).
     */
    private static function isAllNumberedLines(array $lines): bool
    {
        foreach ($lines as $line) {
            if (!empty($line) && !preg_match('/^\d+[\.\)]\s+/', $line)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if all lines are bullet items (- * •).
     */
    private static function isAllBulletLines(array $lines): bool
    {
        foreach ($lines as $line) {
            if (!empty($line) && !preg_match('/^[-*•]\s+/', $line)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Parse list DOM element recursively for nested list support.
     */
    private static function parseListElement(\DOMNode $listNode, bool $isOrdered): array
    {
        $items = [];
        foreach ($listNode->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) continue;
            $childTag = strtolower($child->nodeName);
            if ($childTag === 'li') {
                $itemSegments = [];
                $nestedChildren = [];
                $nestedStyle = $isOrdered ? 'ordered' : 'unordered';

                $liStyleAttr = $child->getAttribute('style');
                $fontSize = null;
                if ($liStyleAttr && preg_match('/font-size:\s*(\d+)px/i', $liStyleAttr, $m)) {
                    $fontSize = (int)$m[1];
                }

                foreach ($child->childNodes as $subChild) {
                    if ($subChild->nodeType === XML_ELEMENT_NODE && in_array(strtolower($subChild->nodeName), ['ol', 'ul'])) {
                        $childIsOrdered = strtolower($subChild->nodeName) === 'ol';
                        $nestedChildren = array_merge($nestedChildren, self::parseListElement($subChild, $childIsOrdered));
                        $nestedStyle = $childIsOrdered ? 'ordered' : 'unordered';
                    } else {
                        $itemSegments = array_merge($itemSegments, self::domNodeToSegments($subChild));
                    }
                }

                $item = [
                    'segments' => $itemSegments
                ];
                if ($fontSize) {
                    $item['fontSize'] = $fontSize;
                }
                if ($liStyleAttr) {
                    $item['style'] = $liStyleAttr;
                }
                if (!empty($nestedChildren)) {
                    $item['children'] = $nestedChildren;
                    $item['childrenStyle'] = $nestedStyle;
                }
                $items[] = $item;
            } elseif (in_array($childTag, ['ol', 'ul'])) {
                $childIsOrdered = $childTag === 'ol';
                $nestedItems = self::parseListElement($child, $childIsOrdered);
                if (!empty($items)) {
                    $lastIdx = count($items) - 1;
                    $items[$lastIdx]['children'] = array_merge($items[$lastIdx]['children'] ?? [], $nestedItems);
                    $items[$lastIdx]['childrenStyle'] = $childIsOrdered ? 'ordered' : 'unordered';
                } else {
                    $items = array_merge($items, $nestedItems);
                }
            }
        }
        return $items;
    }

    /**
     * Ordered-list marker style per nesting depth, matching the Word-style
     * cycle used in #documentCanvas (1, 2, 3 -> a, b, c -> i, ii, iii -> repeat).
     */
    private static function orderedMarkerClass(int $depth): string
    {
        $cycle = ['list-decimal', 'list-style-alpha', 'list-style-roman'];
        return $cycle[$depth % count($cycle)];
    }

    /**
     * Render list items recursively with nested children and item typography styles.
     * Nested sub-lists get their own marker + list-inside classes (not just the
     * top-level list) so they stay visible outside #documentCanvas too (e.g. in
     * a .prose reader view), and their numbering style cycles per depth like Word.
     */
    private static function renderListItems(array $items, bool $isOrdered, int $depth = 0): string
    {
        $liParts = [];
        foreach ($items as $item) {
            $itemSegments = isset($item['segments']) ? $item['segments'] : [['text' => is_string($item) ? $item : ($item['text'] ?? '')]];
            $renderedText = self::renderSegmentsToHtml($itemSegments);
            $itemStyle = !empty($item['style']) ? ' style="' . htmlspecialchars($item['style'], ENT_QUOTES, 'UTF-8') . '"' : (!empty($item['fontSize']) ? ' style="font-size: ' . (int)$item['fontSize'] . 'px;"' : '');

            $nestedHtml = '';
            if (!empty($item['children'])) {
                $childDepth = $depth + 1;
                $childOrdered = isset($item['childrenStyle']) ? ($item['childrenStyle'] === 'ordered') : $isOrdered;
                $childTag = $childOrdered ? 'ol' : 'ul';
                $childClass = $childOrdered
                    ? self::orderedMarkerClass($childDepth) . ' list-inside'
                    : 'list-disc list-inside';
                $nestedHtml = '<' . $childTag . ' class="' . $childClass . '">' . self::renderListItems($item['children'], $childOrdered, $childDepth) . '</' . $childTag . '>';
            }

            $liParts[] = '<li' . $itemStyle . '>' . $renderedText . $nestedHtml . '</li>';
        }
        return implode('', $liParts);
    }

    /**
     * Render Canonical Blocks or JSON into clean, styled Landing Page HTML (alias).
     */
    public static function renderBlocksToHtml(array|string|null $canonical): string
    {
        return self::renderToHtml($canonical ?? []);
    }

    /**
     * Parse input into Canonical Representation (alias matching JS API).
     */
    public static function parseArticleContent(string|array|null $content): array
    {
        return self::parse($content);
    }
}

