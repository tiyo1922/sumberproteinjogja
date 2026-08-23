/**
 * KnowledgeArticleParser - JavaScript Module for Canonical Content Schema v1
 * Single Source of Truth (segments) for all rich inline content.
 */

export const SCHEMA_VERSION = 1;

/**
 * Generate unique stable block ID.
 * @returns {string}
 */
export function generateBlockId() {
    return 'block-' + Math.random().toString(36).substring(2, 9);
}

/**
 * Parse any input (Canonical JSON/Object, Legacy HTML, or Legacy Plain Text) into Canonical Blocks.
 * @param {string|object|null} content
 * @returns {{ schema_version: number, blocks: Array<object> }}
 */
export function parseArticleContent(content) {
    if (!content) {
        return { schema_version: SCHEMA_VERSION, blocks: [] };
    }

    // 1. If already an object/array
    if (typeof content === 'object') {
        if (content.schema_version && Array.isArray(content.blocks)) {
            return {
                schema_version: content.schema_version,
                blocks: content.blocks.map(normalizeBlock)
            };
        }
        if (Array.isArray(content.blocks)) {
            return {
                schema_version: SCHEMA_VERSION,
                blocks: content.blocks.map(normalizeBlock)
            };
        }
    }

    if (typeof content === 'string') {
        const trimmed = content.trim();

        // 2. Check if JSON string
        if (trimmed.startsWith('{') && trimmed.endsWith('}')) {
            try {
                const parsed = JSON.parse(trimmed);
                if (parsed.schema_version && Array.isArray(parsed.blocks)) {
                    return {
                        schema_version: parsed.schema_version,
                        blocks: parsed.blocks.map(normalizeBlock)
                    };
                }
            } catch (e) {
                // Continue to HTML/Text check
            }
        }

        // 3. Check if HTML
        if (containsHtml(trimmed)) {
            return parseHtmlContent(trimmed);
        }

        // 4. Fallback to Plain Text
        return parsePlainTextContent(trimmed);
    }

    return { schema_version: SCHEMA_VERSION, blocks: [] };
}

/**
 * Parse Legacy HTML into Canonical Schema Blocks with segments.
 * @param {string} html
 * @returns {{ schema_version: number, blocks: Array<object> }}
 */
export function parseHtmlContent(html) {
    const blocks = [];
    const cleanHtml = (html || '').trim();

    if (!cleanHtml) {
        return { schema_version: SCHEMA_VERSION, blocks: [] };
    }

    const parser = new DOMParser();
    const doc = parser.parseFromString('<div>' + cleanHtml + '</div>', 'text/html');
    const container = doc.body.firstElementChild || doc.body;

    Array.from(container.childNodes).forEach(node => {
        if (node.nodeType === Node.TEXT_NODE) {
            const text = (node.textContent || '').trim();
            if (text) {
                blocks.push({
                    id: generateBlockId(),
                    type: 'paragraph',
                    segments: [{ text: text }]
                });
            }
            return;
        }

        if (node.nodeType !== Node.ELEMENT_NODE) return;

        const el = /** @type {HTMLElement} */ (node);
        const tag = el.tagName.toLowerCase();
        const className = el.className || '';

        // 1. Callouts (Soft Green Tips or Cream Info)
        const isCallout = tag === 'div' && (
            el.getAttribute('data-block') === 'callout' ||
            className.includes('callout-box') ||
            className.includes('bg-brand-soft-green') ||
            className.includes('bg-brand-cream') ||
            className.includes('rounded-modern')
        );
        if (isCallout) {
            const dataVar = el.getAttribute('data-variant');
            const isTips = dataVar === 'tips' || className.includes('bg-brand-soft-green') || (!className.includes('bg-brand-cream') && !className.includes('amber'));
            const variant = (dataVar === 'info' || className.includes('bg-brand-cream') || className.includes('amber')) ? 'info' : (isTips ? 'tips' : 'info');

            const h4 = el.querySelector('h4');
            let rawTitle = h4 ? (h4.textContent || '').trim() : '';
            let icon = variant === 'tips' ? '💡' : '📋';
            let title = rawTitle;

            const emojiMatch = rawTitle.match(/^(\p{Emoji_Presentation}|\p{Extended_Pictographic}|\S)\s*(.*)$/u);
            if (emojiMatch) {
                icon = emojiMatch[1].trim();
                title = emojiMatch[2].trim();
            }

            const ul = el.querySelector('ul');
            const items = [];
            if (ul) {
                Array.from(ul.querySelectorAll('li')).forEach(li => {
                    items.push({
                        segments: domNodeToSegments(li)
                    });
                });
            }

            let segments = [];
            const bodyDiv = el.querySelector('.callout-body');
            if (bodyDiv) {
                segments = domNodeToSegments(bodyDiv);
            } else {
                const p = el.querySelector('p');
                if (p) {
                    segments = domNodeToSegments(p);
                } else if (items.length === 0) {
                    let plain = (el.textContent || '').trim();
                    if (plain && rawTitle && plain.startsWith(rawTitle)) {
                        plain = plain.substring(rawTitle.length).trim();
                    }
                    if (plain) {
                        segments = [{ text: plain }];
                    }
                }
            }

            const calloutBlock = {
                id: generateBlockId(),
                type: 'callout',
                variant: variant,
                icon: icon,
                title: title,
                segments: segments,
                items: items
            };

            blocks.push(calloutBlock);
            return;
        }

        // 2. Headings (H2, H3, H4)
        if (['h2', 'h3', 'h4'].includes(tag)) {
            const level = tag === 'h2' ? 2 : 3;
            const text = (el.textContent || '').trim();
            if (text) {
                const block = {
                    id: generateBlockId(),
                    type: 'heading',
                    level: level,
                    text: text
                };
                if (el.style.textAlign) block.align = el.style.textAlign;
                blocks.push(block);
            }
            return;
        }

        // 3. Blockquotes
        if (tag === 'blockquote') {
            const segments = domNodeToSegments(el);
            if (segments.length > 0) {
                blocks.push({
                    id: generateBlockId(),
                    type: 'quote',
                    segments: segments
                });
            }
            return;
        }

        // 4. Dividers (HR)
        if (tag === 'hr') {
            blocks.push({
                id: generateBlockId(),
                type: 'divider'
            });
            return;
        }

        // 5. Unordered Lists (UL)
        if (tag === 'ul') {
            const items = parseListElement(el, false);
            if (items.length > 0) {
                blocks.push({
                    id: generateBlockId(),
                    type: 'list',
                    style: 'unordered',
                    items: items
                });
            }
            return;
        }

        // 6. Ordered Lists (OL)
        if (tag === 'ol') {
            const items = parseListElement(el, true);
            if (items.length > 0) {
                blocks.push({
                    id: generateBlockId(),
                    type: 'list',
                    style: 'ordered',
                    items: items
                });
            }
            return;
        }

        // 7. Paragraphs (P)
        if (tag === 'p') {
            const isLead = className.includes('lead');
            const segments = domNodeToSegments(el);
            if (segments.length > 0) {
                const block = {
                    id: generateBlockId(),
                    type: isLead ? 'lead' : 'paragraph',
                    segments: segments
                };
                if (el.style.textAlign) block.align = el.style.textAlign;
                if (el.style.lineHeight) block.lineHeight = el.style.lineHeight;
                if (el.style.paddingLeft) block.paddingLeft = el.style.paddingLeft;
                blocks.push(block);
            }
            return;
        }

        // 8. Generic block fallback
        const segments = domNodeToSegments(el);
        if (segments.length > 0) {
            const block = {
                id: generateBlockId(),
                type: 'paragraph',
                segments: segments
            };
            if (el.style && el.style.textAlign) block.align = el.style.textAlign;
            if (el.style && el.style.lineHeight) block.lineHeight = el.style.lineHeight;
            if (el.style && el.style.paddingLeft) block.paddingLeft = el.style.paddingLeft;
            blocks.push(block);
        }
    });

    return {
        schema_version: SCHEMA_VERSION,
        blocks: blocks
    };
}

/**
 * Parse Legacy Plain Text into Canonical Schema Blocks with segments.
 * @param {string} text
 * @returns {{ schema_version: number, blocks: Array<object> }}
 */
export function parsePlainTextContent(text) {
    const blocks = [];
    const normalized = (text || '').replace(/\r\n|\r/g, '\n').trim();

    if (!normalized) {
        return { schema_version: SCHEMA_VERSION, blocks: [] };
    }

    const chunks = normalized.split(/\n{2,}/);
    let isFirst = true;

    chunks.forEach(chunk => {
        const trimmedChunk = chunk.trim();
        if (!trimmedChunk) return;

        const lines = trimmedChunk.split('\n').map(l => l.trim()).filter(Boolean);

        // Check if chunk is an ordered list (1. 2. 3.)
        const isOrderedList = lines.length > 1 && lines.every(l => /^\d+[\.\)]\s+/.test(l));
        const isUnorderedList = lines.length > 1 && lines.every(l => /^[-*•]\s+/.test(l));

        if (isOrderedList) {
            const items = lines.map(l => ({
                segments: [{ text: l.replace(/^\d+[\.\)]\s+/, '') }]
            }));
            blocks.push({
                id: generateBlockId(),
                type: 'list',
                style: 'ordered',
                items: items
            });
            isFirst = false;
            return;
        }

        if (isUnorderedList) {
            const items = lines.map(l => ({
                segments: [{ text: l.replace(/^[-*•]\s+/, '') }]
            }));
            blocks.push({
                id: generateBlockId(),
                type: 'list',
                style: 'unordered',
                items: items
            });
            isFirst = false;
            return;
        }

        // Check if chunk has a numbered heading followed by explanation text: "1. Heading Title\nExplanation..."
        const headingWithBodyMatch = trimmedChunk.match(/^(\d+[\.\)]\s+[^\n]+)\n+([\s\S]+)$/);
        if (headingWithBodyMatch) {
            const headingText = headingWithBodyMatch[1].trim();
            const bodyText = headingWithBodyMatch[2].trim();

            blocks.push({
                id: generateBlockId(),
                type: 'heading',
                level: 3,
                text: headingText
            });

            blocks.push({
                id: generateBlockId(),
                type: 'paragraph',
                segments: [{ text: bodyText }]
            });
            isFirst = false;
            return;
        }

        // Check for single line numbered heading
        if (lines.length === 1 && /^\d+[\.\)]\s+[A-Z0-9]/.test(trimmedChunk) && trimmedChunk.length < 100) {
            blocks.push({
                id: generateBlockId(),
                type: 'heading',
                level: 3,
                text: trimmedChunk
            });
            isFirst = false;
            return;
        }

        // Standard Paragraph or Lead
        const type = (isFirst && chunks.length > 1 && trimmedChunk.length > 60) ? 'lead' : 'paragraph';
        blocks.push({
            id: generateBlockId(),
            type: type,
            segments: [{ text: trimmedChunk }]
        });
        isFirst = false;
    });

    return {
        schema_version: SCHEMA_VERSION,
        blocks: blocks
    };
}

/**
 * Render Canonical Blocks into clean, styled Landing Page HTML.
 * @param {object|string} canonical
 * @returns {string}
 */
export function renderBlocksToHtml(canonical) {
    const data = typeof canonical === 'string' ? parseArticleContent(canonical) : (canonical || {});
    const blocks = data.blocks || [];

    if (blocks.length === 0) {
        return '';
    }

    const htmlParts = [];

    blocks.forEach(block => {
        const type = block.type || 'paragraph';
        const segments = block.segments || [];
        const alignStyle = block.align ? `text-align: ${block.align};` : '';
        const lineStyle = block.lineHeight ? `line-height: ${block.lineHeight};` : '';
        const padStyle = block.paddingLeft ? `padding-left: ${block.paddingLeft};` : '';
        const combinedStyle = [alignStyle, lineStyle, padStyle].filter(Boolean).join(' ');
        const styleAttr = combinedStyle ? ` style="${combinedStyle}"` : '';

        switch (type) {
            case 'lead': {
                const rendered = renderSegmentsToHtml(segments);
                htmlParts.push(`<p class="lead text-base text-gray-700 font-medium mb-4 leading-relaxed"${styleAttr}>${rendered}</p>`);
                break;
            }
            case 'heading': {
                const level = block.level === 2 ? 2 : 3;
                const headingClass = level === 2
                    ? 'text-xl sm:text-2xl font-bold text-brand-dark mt-8 mb-4'
                    : 'text-lg sm:text-xl font-bold text-brand-dark mt-6 mb-3';
                const tag = `h${level}`;
                htmlParts.push(`<${tag} class="${headingClass}"${styleAttr}>${escapeHtml(block.text || '')}</${tag}>`);
                break;
            }
            case 'quote': {
                const rendered = renderSegmentsToHtml(segments);
                htmlParts.push(`<blockquote class="my-5 pl-4 border-l-4 border-brand-primary italic text-gray-700 bg-brand-soft-green/20 py-2.5 rounded-r text-sm"${styleAttr}>${rendered}</blockquote>`);
                break;
            }
            case 'divider': {
                htmlParts.push(`<hr class="my-6 border-t-2 border-gray-200">`);
                break;
            }
            case 'list': {
                const isOrdered = block.style === 'ordered';
                const tag = isOrdered ? 'ol' : 'ul';
                const listClass = isOrdered
                    ? 'list-decimal list-inside space-y-2 text-sm sm:text-base text-gray-600 mb-4 pl-2'
                    : 'list-disc list-inside space-y-2 text-sm sm:text-base text-gray-600 mb-4 pl-2';

                const items = block.items || [];
                const renderedList = renderListItems(items, isOrdered);
                htmlParts.push(`<${tag} class="${listClass}"${styleAttr}>${renderedList}</${tag}>`);
                break;
            }
            case 'callout': {
                const isTips = block.variant === 'tips';
                const icon = block.icon || (isTips ? '💡' : '📋');
                const title = block.title || (isTips ? 'Tips Penting:' : 'Catatan Informasi:');
                const items = block.items || [];

                const wrapperClass = isTips
                    ? 'my-6 p-4 sm:p-5 rounded-modern bg-brand-soft-green/60 border border-brand-soft-green-border'
                    : 'my-6 p-4 sm:p-5 rounded-modern bg-brand-cream/80 border border-gray-200';

                const titleClass = isTips
                    ? 'font-bold text-brand-primary text-sm sm:text-base mb-2'
                    : 'font-bold text-brand-dark text-sm sm:text-base mb-2';

                const bodyClass = isTips
                    ? 'text-xs sm:text-sm text-brand-dark leading-relaxed'
                    : 'text-xs sm:text-sm text-gray-600 leading-relaxed';

                let calloutHtml = `<div class="${wrapperClass}">`;
                const titleDisplay = `${icon} ${title}`.trim();
                calloutHtml += `<h4 class="${titleClass}">${escapeHtml(titleDisplay)}</h4>`;

                if (segments.length > 0) {
                    calloutHtml += `<p class="${bodyClass}">${renderSegmentsToHtml(segments)}</p>`;
                }

                if (items.length > 0) {
                    calloutHtml += `<ul class="list-disc list-inside space-y-1.5 text-xs sm:text-sm text-gray-600 mt-2">`;
                    items.forEach(item => {
                        const itemSegments = item && item.segments ? item.segments : [{ text: typeof item === 'string' ? item : (item.text || '') }];
                        calloutHtml += `<li>${renderSegmentsToHtml(itemSegments)}</li>`;
                    });
                    calloutHtml += `</ul>`;
                }

                calloutHtml += `</div>`;
                htmlParts.push(calloutHtml);
                break;
            }
            case 'paragraph':
            default: {
                const rendered = renderSegmentsToHtml(segments);
                htmlParts.push(`<p class="text-base text-gray-600 mb-4 leading-relaxed"${styleAttr}>${rendered}</p>`);
                break;
            }
        }
    });

    return htmlParts.join('\n');
}

/**
 * Render structured segments to safe HTML.
 * @param {Array<{ text: string, bold?: boolean, italic?: boolean, underline?: boolean, strikethrough?: boolean, fontSize?: number, link?: string }>} segments
 * @returns {string}
 */
export function renderSegmentsToHtml(segments) {
    if (!Array.isArray(segments) || segments.length === 0) return '';

    return segments.map(seg => {
        let text = escapeHtml(seg.text || '');
        if (!text && text !== '0') return '';

        if (seg.bold) text = `<strong>${text}</strong>`;
        if (seg.italic) text = `<em>${text}</em>`;
        if (seg.underline) text = `<u>${text}</u>`;
        if (seg.strikethrough) text = `<s>${text}</s>`;
        if (seg.fontSize && seg.fontSize !== 16) {
            text = `<span style="font-size: ${seg.fontSize}px">${text}</span>`;
        }
        if (seg.link) {
            const href = escapeHtml(seg.link);
            text = `<a href="${href}" target="_blank" rel="noopener noreferrer" class="text-brand-primary underline hover:text-brand-primary-dark">${text}</a>`;
        }
        return text;
    }).join('');
}

/**
 * Convert structured segments to plain text without markup.
 * @param {Array<{ text: string }>} segments
 * @returns {string}
 */
export function segmentsToPlainText(segments) {
    if (!Array.isArray(segments)) return '';
    return segments.map(s => s.text || '').join('');
}

/**
 * Convert DOM node directly to structured segments.
 * @param {Node} node
 * @param {object} [currentFormat]
 * @returns {Array<object>}
 */
function domNodeToSegments(node, currentFormat = {}) {
    if (node.nodeType === 3) { // Node.TEXT_NODE
        const text = node.textContent || '';
        if (text !== '') {
            const seg = { text: text };
            if (currentFormat.bold) seg.bold = true;
            if (currentFormat.italic) seg.italic = true;
            if (currentFormat.underline) seg.underline = true;
            if (currentFormat.strikethrough) seg.strikethrough = true;
            if (currentFormat.fontSize) seg.fontSize = currentFormat.fontSize;
            if (currentFormat.link) seg.link = currentFormat.link;
            return [seg];
        }
        return [];
    }

    const segments = [];

    node.childNodes.forEach(child => {
        if (child.nodeType === Node.TEXT_NODE) {
            const text = child.textContent || '';
            if (text !== '') {
                const seg = { text: text };
                if (currentFormat.bold) seg.bold = true;
                if (currentFormat.italic) seg.italic = true;
                if (currentFormat.underline) seg.underline = true;
                if (currentFormat.strikethrough) seg.strikethrough = true;
                if (currentFormat.fontSize) seg.fontSize = currentFormat.fontSize;
                if (currentFormat.link) seg.link = currentFormat.link;
                segments.push(seg);
            }
        } else if (child.nodeType === Node.ELEMENT_NODE) {
            const el = /** @type {HTMLElement} */ (child);
            const tag = el.tagName.toLowerCase();
            const format = { ...currentFormat };

            if (['strong', 'b'].includes(tag)) format.bold = true;
            else if (['em', 'i'].includes(tag)) format.italic = true;
            else if (['u'].includes(tag) || (el.style && el.style.textDecoration && el.style.textDecoration.includes('underline'))) {
                format.underline = true;
            }
            else if (['s', 'strike', 'del'].includes(tag) || (el.style && el.style.textDecoration && el.style.textDecoration.includes('line-through'))) {
                format.strikethrough = true;
            }
            else if (tag === 'a') format.link = el.getAttribute('href') || '#';

            if (el.style && el.style.fontSize) {
                const match = el.style.fontSize.match(/(\d+)/);
                if (match) format.fontSize = parseInt(match[1], 10);
            } else if (tag === 'font' && el.getAttribute('size')) {
                const sizeMap = { '1': 12, '2': 14, '3': 16, '4': 18, '5': 24, '6': 28, '7': 32 };
                const sz = el.getAttribute('size');
                if (sizeMap[sz]) format.fontSize = sizeMap[sz];
            }

            const childSegments = domNodeToSegments(child, format);
            childSegments.forEach(cs => segments.push(cs));
        }
    });

    return mergeConsecutiveSegments(segments);
}

/**
 * Merge consecutive segments with identical formatting.
 * @param {Array<object>} segments
 * @returns {Array<object>}
 */
export function mergeConsecutiveSegments(segments) {
    if (!Array.isArray(segments) || segments.length === 0) return [];

    const merged = [];
    let current = null;

    segments.forEach(seg => {
        const text = seg.text || '';
        if (text === '') return;

        const bold = !!seg.bold;
        const italic = !!seg.italic;
        const underline = !!seg.underline;
        const strikethrough = !!seg.strikethrough;
        const fontSize = seg.fontSize ? parseInt(seg.fontSize, 10) : null;
        const link = seg.link || null;

        if (!current) {
            current = { text, bold, italic, underline, strikethrough, fontSize, link };
            return;
        }

        if (current.bold === bold && 
            current.italic === italic && 
            current.underline === underline && 
            current.strikethrough === strikethrough && 
            current.fontSize === fontSize && 
            current.link === link) {
            current.text += text;
        } else {
            const clean = { text: current.text };
            if (current.bold) clean.bold = true;
            if (current.italic) clean.italic = true;
            if (current.underline) clean.underline = true;
            if (current.strikethrough) clean.strikethrough = true;
            if (current.fontSize) clean.fontSize = current.fontSize;
            if (current.link) clean.link = current.link;
            merged.push(clean);

            current = { text, bold, italic, underline, strikethrough, fontSize, link };
        }
    });

    if (current && current.text !== '') {
        const clean = { text: current.text };
        if (current.bold) clean.bold = true;
        if (current.italic) clean.italic = true;
        if (current.underline) clean.underline = true;
        if (current.strikethrough) clean.strikethrough = true;
        if (current.fontSize) clean.fontSize = current.fontSize;
        if (current.link) clean.link = current.link;
        merged.push(clean);
    }

    return merged;
}

/**
 * Helper to escape HTML characters.
 * @param {string} str
 * @returns {string}
 */
function escapeHtml(str) {
    return (str || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

/**
 * Check if string contains HTML tags.
 * @param {string} str
 * @returns {boolean}
 */
function containsHtml(str) {
    return /<\s*[a-z][^>]*>/i.test(str);
}

/**
 * Normalize block properties.
 * @param {object} block
 * @returns {object}
 */
function normalizeBlock(block) {
    const type = block.type || 'paragraph';
    const id = block.id || generateBlockId();

    let segments = Array.isArray(block.segments) ? block.segments : [];
    if (segments.length === 0 && typeof block.text === 'string' && block.text) {
        segments = [{ text: block.text }];
    }

    const normalized = {
        id: id,
        type: type
    };

    if (block.align) normalized.align = block.align;
    if (block.lineHeight) normalized.lineHeight = block.lineHeight;
    if (block.paddingLeft) normalized.paddingLeft = block.paddingLeft;

    if (type === 'heading') {
        normalized.level = block.level ? Number(block.level) : 3;
        normalized.text = block.text || segmentsToPlainText(segments);
        return normalized;
    }

    if (type === 'list') {
        normalized.style = block.style || 'unordered';
        const normalizeListItems = (rawItems) => {
            const items = [];
            (rawItems || []).forEach(item => {
                let normItem = {};
                if (item && Array.isArray(item.segments)) {
                    normItem.segments = mergeConsecutiveSegments(item.segments);
                } else if (typeof item === 'string') {
                    normItem.segments = [{ text: item }];
                } else if (item && typeof item.text === 'string') {
                    normItem.segments = [{ text: item.text }];
                } else {
                    normItem.segments = [{ text: '' }];
                }
                if (item && item.fontSize) normItem.fontSize = item.fontSize;
                if (item && item.style) normItem.style = item.style;
                if (item && item.children && Array.isArray(item.children)) {
                    normItem.children = normalizeListItems(item.children);
                    normItem.childrenStyle = item.childrenStyle || 'unordered';
                }
                items.push(normItem);
            });
            return items;
        };
        normalized.items = normalizeListItems(block.items);
        return normalized;
    }

    if (type === 'callout') {
        normalized.variant = block.variant || 'tips';
        normalized.icon = block.icon || (normalized.variant === 'tips' ? '💡' : '📋');
        normalized.title = block.title || '';
        normalized.segments = mergeConsecutiveSegments(segments);
        const items = [];
        (block.items || []).forEach(item => {
            if (item && Array.isArray(item.segments)) {
                items.push({ segments: mergeConsecutiveSegments(item.segments) });
            } else if (typeof item === 'string') {
                items.push({ segments: [{ text: item }] });
            }
        });
        normalized.items = items;
        return normalized;
    }

    normalized.segments = mergeConsecutiveSegments(segments);
    return normalized;
}

/**
 * Parse list DOM element recursively for nested list support.
 * @param {HTMLElement} listNode
 * @param {boolean} isOrdered
 * @returns {Array<object>}
 */
function parseListElement(listNode, isOrdered) {
    const items = [];
    Array.from(listNode.childNodes).forEach(child => {
        if (child.nodeType !== 1) return; // Node.ELEMENT_NODE
        const childTag = child.tagName.toLowerCase();
        if (childTag === 'li') {
            let itemSegments = [];
            let nestedChildren = [];
            let nestedStyle = isOrdered ? 'ordered' : 'unordered';

            const liStyle = child.getAttribute('style') || '';
            let fontSize = null;
            const m = liStyle.match(/font-size:\s*(\d+)px/i);
            if (m) {
                fontSize = parseInt(m[1], 10);
            }

            Array.from(child.childNodes).forEach(subChild => {
                if (subChild.nodeType === 1 && (subChild.tagName.toLowerCase() === 'ol' || subChild.tagName.toLowerCase() === 'ul')) {
                    const childIsOrdered = subChild.tagName.toLowerCase() === 'ol';
                    nestedChildren = nestedChildren.concat(parseListElement(subChild, childIsOrdered));
                    nestedStyle = childIsOrdered ? 'ordered' : 'unordered';
                } else {
                    itemSegments = itemSegments.concat(domNodeToSegments(subChild));
                }
            });

            const item = {
                segments: itemSegments
            };
            if (fontSize) {
                item.fontSize = fontSize;
            }
            if (liStyle) {
                item.style = liStyle;
            }
            if (nestedChildren.length > 0) {
                item.children = nestedChildren;
                item.childrenStyle = nestedStyle;
            }
            items.push(item);
        } else if (childTag === 'ol' || childTag === 'ul') {
            const childIsOrdered = childTag === 'ol';
            const nestedItems = parseListElement(child, childIsOrdered);
            if (items.length > 0) {
                const last = items[items.length - 1];
                last.children = (last.children || []).concat(nestedItems);
                last.childrenStyle = childIsOrdered ? 'ordered' : 'unordered';
            } else {
                nestedItems.forEach(ni => items.push(ni));
            }
        }
    });
    return items;
}

/**
 * Render list items recursively with nested children and item typography styles.
 * @param {Array<object>} items
 * @param {boolean} isOrdered
 * @returns {string}
 */
function renderListItems(items, isOrdered) {
    return items.map(item => {
        const itemSegments = item && item.segments ? item.segments : [{ text: typeof item === 'string' ? item : (item.text || '') }];
        const renderedText = renderSegmentsToHtml(itemSegments);
        const itemStyle = item.style ? ` style="${escapeHtml(item.style)}"` : (item.fontSize ? ` style="font-size: ${item.fontSize}px;"` : '');

        let nestedHtml = '';
        if (item.children && item.children.length > 0) {
            const childOrdered = item.childrenStyle === 'ordered' || (item.childrenStyle === undefined ? isOrdered : false);
            const childTag = childOrdered ? 'ol' : 'ul';
            nestedHtml = `<${childTag}>${renderListItems(item.children, childOrdered)}</${childTag}>`;
        }

        return `<li${itemStyle}>${renderedText}${nestedHtml}</li>`;
    }).join('');
}

// Attach to window for Alpine.js browser access if in browser environment
if (typeof window !== 'undefined') {
    window.KnowledgeArticleParser = {
        SCHEMA_VERSION,
        generateBlockId,
        parseArticleContent,
        parseHtmlContent,
        parsePlainTextContent,
        renderBlocksToHtml,
        renderSegmentsToHtml,
        segmentsToPlainText,
        mergeConsecutiveSegments
    };
}
