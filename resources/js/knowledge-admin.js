/**
 * Knowledge & Tips Admin Manager (Alpine.js Component)
 * Document Editor UX (B1-R) + Knowledge Category Manager
 */

export function createKnowledgeManager(config = {}) {
    return {
        csrfToken: config.csrfToken || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        articles: config.articles || [],
        categories: config.categories || [],
        mediaLibrary: config.mediaLibrary || [],
        knowledgeSection: config.knowledgeSection || {
            label: 'Edukasi & Inspirasi Dapur',
            title: 'Dapur & Knowledge',
            subtitle: 'Panduan praktis seputar penanganan daging, thawing, penyimpanan frozen food, hingga tips memasak harian keluarga di Yogyakarta.'
        },
        activeMainTab: 'articles', // 'articles' | 'categories'
        editorModalOpen: false,
        categoryModalOpen: false,
        mediaPickerOpen: false,
        previewModalOpen: false,
        deleteModalOpen: false,
        deleteCategoryModalOpen: false,
        unsavedChangesModalOpen: false,
        linkModalOpen: false,
        isEditing: false,
        isEditingCategory: false,
        toastMessage: '',
        toastVisible: false,
        searchQuery: '',
        selectedCategoryFilter: 'all',
        previewDevice: 'desktop', // 'desktop' | 'tablet' | 'mobile'
        mediaTab: 'library', // 'library' | 'upload'
        mediaTarget: 'thumbnail', // 'thumbnail' | 'inline'
        selectedMedia: null,
        uploadedFile: null,
        uploadedPreviewUrl: null,
        previewIsExpanded: false,
        
        // B1-R Document Editor Workspace State
        editorTab: 'content', // 'content' | 'info' | 'preview'
        isFocusMode: false,
        showInsertPanel: true,
        showPreviewPanel: true,
        wordCount: 0,
        charCount: 0,
        canvasHtml: '',
        linkInputUrl: 'https://',
        savedSelectionRange: null,
        undoStack: [],
        redoStack: [],
        isUndoRedoAction: false,
        initialFormJson: '',
        
        colorOptions: [
            { id: 'blue', name: 'Biru (Edukasi)', class: 'bg-blue-100 text-blue-800 border-blue-300' },
            { id: 'green', name: 'Hijau (Tips/Fresh)', class: 'bg-emerald-100 text-emerald-800 border-emerald-300' },
            { id: 'purple', name: 'Ungu (Produk)', class: 'bg-purple-100 text-purple-800 border-purple-300' },
            { id: 'orange', name: 'Oranye (Resep)', class: 'bg-orange-100 text-orange-800 border-orange-300' },
            { id: 'yellow', name: 'Kuning (Belanja)', class: 'bg-yellow-100 text-yellow-800 border-yellow-300' },
            { id: 'red', name: 'Merah (Protein)', class: 'bg-rose-100 text-rose-800 border-rose-300' },
            { id: 'teal', name: 'Teal (Higienis)', class: 'bg-teal-100 text-teal-800 border-teal-300' }
        ],
        
        activeFormats: {
            bold: false,
            italic: false,
            underline: false,
            strikethrough: false,
            fontSize: 16,
            blockStyle: 'p',
            align: 'left',
            lineHeight: '1.75',
            isOrderedList: false,
            isUnorderedList: false,
        },
        
        form: {
            id: null,
            title: '',
            slug: '',
            category: 'Tips Penyimpanan',
            status: 'Published',
            published_at: '17 Agustus 2026',
            image: 'images/know-thawing.jpg',
            excerpt: '',
            content: '',
        },
        
        categoryForm: {
            id: null,
            name: '',
            color: 'blue',
            status: 'Aktif',
            articles_count: 0
        },
        
        selectedArticle: null,
        selectedCategoryItem: null,
        
        init() {
            if (typeof document !== 'undefined') {
                document.addEventListener('selectionchange', () => {
                    if (!this.editorModalOpen) return;
                    const canvas = document.getElementById('documentCanvas');
                    if (!canvas) return;
                    const sel = window.getSelection();
                    if (!sel || sel.rangeCount === 0) return;
                    const anchor = sel.anchorNode;
                    if (anchor && (canvas === anchor || canvas.contains(anchor))) {
                        this.updateActiveFormats();
                    }
                });
            }
        },
        
        showToast(msg) {
            this.toastMessage = msg;
            this.toastVisible = true;
            setTimeout(() => { this.toastVisible = false; }, 3000);
        },
        
        getColorClass(color) {
            const map = {
                orange: 'bg-orange-50 text-orange-800 border-orange-200',
                yellow: 'bg-yellow-50 text-yellow-900 border-yellow-200',
                blue: 'bg-blue-50 text-blue-800 border-blue-200',
                green: 'bg-emerald-50 text-emerald-800 border-emerald-200',
                purple: 'bg-purple-50 text-purple-800 border-purple-200',
                red: 'bg-rose-50 text-rose-800 border-rose-200',
                teal: 'bg-teal-50 text-teal-800 border-teal-200'
            };
            return map[color] || 'bg-gray-50 text-gray-800 border-gray-200';
        },
        
        getCategoryColor(catName) {
            const cat = this.categories.find(c => c.name === catName);
            return cat ? cat.color : 'green';
        },
        
        get activeCategories() {
            return this.categories.filter(c => c.status === 'Aktif');
        },
        
        get filteredArticles() {
            return this.articles.filter(a => {
                const matchCat = this.selectedCategoryFilter === 'all' || a.category === this.selectedCategoryFilter;
                const matchSearch = !this.searchQuery.trim() || 
                    a.title.toLowerCase().includes(this.searchQuery.toLowerCase()) || 
                    a.category.toLowerCase().includes(this.searchQuery.toLowerCase());
                return matchCat && matchSearch;
            });
        },
        
        isDirty() {
            const canvas = document.getElementById('documentCanvas');
            const currentHtml = canvas ? canvas.innerHTML : this.canvasHtml;
            const currentJson = JSON.stringify({ form: this.form, html: currentHtml });
            return currentJson !== this.initialFormJson;
        },
        
        closeEditorModal() {
            if (this.isDirty()) {
                this.unsavedChangesModalOpen = true;
            } else {
                this.editorModalOpen = false;
            }
        },
        
        forceCloseEditorModal() {
            this.unsavedChangesModalOpen = false;
            this.editorModalOpen = false;
        },
        
        openCreateModal() {
            this.isEditing = false;
            this.editorTab = 'content';
            this.isFocusMode = false;
            this.showInsertPanel = true;
            this.showPreviewPanel = true;
            const defaultCat = this.activeCategories[0]?.name || 'Tips Penyimpanan';
            this.form = {
                id: Date.now(),
                title: '',
                slug: '',
                category: defaultCat,
                status: 'Published',
                published_at: '17 Agustus 2026',
                image: 'images/know-thawing.jpg',
                excerpt: '',
                content: '',
            };
            this.canvasHtml = '<p>Daging frozen merupakan pilihan praktis dan higienis bagi keluarga modern. Tuliskan panduan atau tips bermanfaat di sini...</p>';
            this.$nextTick(() => {
                const canvas = document.getElementById('documentCanvas');
                if (canvas) {
                    canvas.innerHTML = this.canvasHtml;
                    this.updateDocStats();
                    this.initHistory();
                }
                const textarea = document.querySelector('textarea[x-model="form.title"]');
                if (textarea) {
                    textarea.style.height = 'auto';
                    textarea.style.height = textarea.scrollHeight + 'px';
                }
                this.initialFormJson = JSON.stringify({ form: this.form, html: this.canvasHtml });
            });
            this.editorModalOpen = true;
        },
        
        openEditModal(a) {
            this.isEditing = true;
            this.editorTab = 'content';
            this.isFocusMode = false;
            this.showInsertPanel = true;
            this.showPreviewPanel = true;
            this.form = JSON.parse(JSON.stringify(a));
            
            // Parse legacy or canonical content to HTML for Document Canvas
            if (window.KnowledgeArticleParser) {
                const parsed = window.KnowledgeArticleParser.parseArticleContent(a.content);
                this.canvasHtml = window.KnowledgeArticleParser.renderBlocksToHtml(parsed);
            } else {
                this.canvasHtml = a.content || '';
            }
            
            if (!this.canvasHtml.trim()) {
                this.canvasHtml = '<p>Tulis isi artikel di sini...</p>';
            }
            
            this.$nextTick(() => {
                const canvas = document.getElementById('documentCanvas');
                if (canvas) {
                    canvas.innerHTML = this.canvasHtml;
                    this.updateDocStats();
                    this.initHistory();
                }
                const textarea = document.querySelector('textarea[x-model="form.title"]');
                if (textarea) {
                    textarea.style.height = 'auto';
                    textarea.style.height = textarea.scrollHeight + 'px';
                }
                this.initialFormJson = JSON.stringify({ form: this.form, html: this.canvasHtml });
            });
            
            this.editorModalOpen = true;
        },
        
        initHistory() {
            const canvas = document.getElementById('documentCanvas');
            const html = canvas ? canvas.innerHTML : this.canvasHtml;
            this.undoStack = [html];
            this.redoStack = [];
        },
        
        pushHistory() {
            if (this.isUndoRedoAction) return;
            const canvas = document.getElementById('documentCanvas');
            if (!canvas) return;
            const current = canvas.innerHTML;
            if (this.undoStack.length === 0 || this.undoStack[this.undoStack.length - 1] !== current) {
                this.undoStack.push(current);
                if (this.undoStack.length > 40) this.undoStack.shift();
                this.redoStack = [];
            }
        },
        
        docUndo() {
            if (this.undoStack.length > 1) {
                const current = this.undoStack.pop();
                this.redoStack.push(current);
                const prev = this.undoStack[this.undoStack.length - 1];
                this.isUndoRedoAction = true;
                const canvas = document.getElementById('documentCanvas');
                if (canvas) {
                    canvas.innerHTML = prev;
                    this.canvasHtml = prev;
                    this.updateDocStats();
                }
                this.isUndoRedoAction = false;
            } else {
                document.execCommand('undo', false, null);
            }
        },
        
        docRedo() {
            if (this.redoStack.length > 0) {
                const next = this.redoStack.pop();
                this.undoStack.push(next);
                this.isUndoRedoAction = true;
                const canvas = document.getElementById('documentCanvas');
                if (canvas) {
                    canvas.innerHTML = next;
                    this.canvasHtml = next;
                    this.updateDocStats();
                }
                this.isUndoRedoAction = false;
            } else {
                document.execCommand('redo', false, null);
            }
        },
        
        formatDoc(cmd, val = null) {
            const canvas = document.getElementById('documentCanvas');
            if (canvas) canvas.focus();
            if (cmd === 'insertOrderedList') {
                this.toggleOrderedList();
                return;
            }
            if (cmd === 'insertUnorderedList') {
                this.toggleUnorderedList();
                return;
            }
            if (cmd === 'indent') {
                this.indentList();
                return;
            }
            if (cmd === 'outdent') {
                this.outdentList();
                return;
            }
            document.execCommand(cmd, false, val);
            this.onCanvasInput();
        },
        
        applyBlockStyle(style) {
            const canvas = document.getElementById('documentCanvas');
            if (!canvas) return;
            canvas.focus();
            if (this.savedSelectionRange) {
                const sel = window.getSelection();
                sel.removeAllRanges();
                sel.addRange(this.savedSelectionRange);
            }
            if (style === 'h2') {
                document.execCommand('formatBlock', false, '<h2>');
            } else if (style === 'h3') {
                document.execCommand('formatBlock', false, '<h3>');
            } else if (style === 'ordered') {
                this.toggleOrderedList();
                return;
            } else if (style === 'unordered') {
                this.toggleUnorderedList();
                return;
            } else {
                document.execCommand('formatBlock', false, '<p>');
            }

            // Clean up stale inline font sizes from the formatted block so heading/paragraph styles take effect cleanly
            const sel = window.getSelection();
            if (sel && sel.anchorNode) {
                let blockNode = sel.anchorNode.nodeType === Node.ELEMENT_NODE ? sel.anchorNode : sel.anchorNode.parentElement;
                while (blockNode && blockNode !== canvas && blockNode !== document.body) {
                    const tag = blockNode.tagName ? blockNode.tagName.toLowerCase() : '';
                    if (['h2', 'h3', 'p'].includes(tag)) {
                        // Remove inline font sizes from spans inside the block
                        const fontSpans = blockNode.querySelectorAll('span[style*="font-size"]');
                        fontSpans.forEach(s => {
                            s.style.fontSize = '';
                            if (!s.getAttribute('style')) {
                                s.removeAttribute('style');
                            }
                        });
                        const fontTags = blockNode.querySelectorAll('font[size]');
                        fontTags.forEach(f => {
                            const parent = f.parentNode;
                            while (f.firstChild) parent.insertBefore(f.firstChild, f);
                            parent.removeChild(f);
                        });
                        break;
                    }
                    blockNode = blockNode.parentElement;
                }
            }

            this.activeFormats.blockStyle = (style === 'h2' || style === 'h3') ? style : 'p';
            this.onCanvasInput();
            this.updateActiveFormats();
        },
        
        setFontSize(size) {
            if (!size || size === 'mixed') return;
            const sz = parseInt(size, 10) || 16;
            const canvas = document.getElementById('documentCanvas');
            if (!canvas) return;
            canvas.focus();
            if (this.savedSelectionRange) {
                const sel = window.getSelection();
                sel.removeAllRanges();
                sel.addRange(this.savedSelectionRange);
            }
            const sel = window.getSelection();
            if (!sel || sel.rangeCount === 0 || sel.isCollapsed) {
                this.activeFormats.fontSize = String(sz);
                return;
            }
            document.execCommand('fontSize', false, '7');
            const fontEls = canvas.querySelectorAll('font[size="7"]');
            const newSpans = [];
            fontEls.forEach(font => {
                const span = document.createElement('span');
                span.style.fontSize = `${sz}px`;
                span.innerHTML = font.innerHTML;
                font.parentNode.replaceChild(span, font);
                newSpans.push(span);

                // If inside a list item LI, set font-size on the LI so marker typography inherits cleanly
                const parentLi = span.closest('li');
                if (parentLi) {
                    parentLi.style.fontSize = `${sz}px`;
                }
            });

            // Re-select formatted spans so selection state is retained immediately
            if (newSpans.length > 0) {
                const newRange = document.createRange();
                newRange.setStartBefore(newSpans[0]);
                newRange.setEndAfter(newSpans[newSpans.length - 1]);
                sel.removeAllRanges();
                sel.addRange(newRange);
                this.savedSelectionRange = newRange.cloneRange();
            }

            this.activeFormats.fontSize = String(sz);
            this.onCanvasInput();
            this.updateActiveFormats();
        },
        
        setAlignment(align) {
            const canvas = document.getElementById('documentCanvas');
            if (canvas) canvas.focus();
            if (this.savedSelectionRange) {
                const sel = window.getSelection();
                sel.removeAllRanges();
                sel.addRange(this.savedSelectionRange);
            }
            if (align === 'center') {
                document.execCommand('justifyCenter', false, null);
            } else if (align === 'right') {
                document.execCommand('justifyRight', false, null);
            } else if (align === 'justify') {
                document.execCommand('justifyFull', false, null);
            } else {
                document.execCommand('justifyLeft', false, null);
            }
            this.activeFormats.align = align;
            this.onCanvasInput();
        },
        
        setLineSpacing(spacing) {
            const canvas = document.getElementById('documentCanvas');
            if (canvas) canvas.focus();
            if (this.savedSelectionRange) {
                const sel = window.getSelection();
                sel.removeAllRanges();
                sel.addRange(this.savedSelectionRange);
            }
            const sel = window.getSelection();
            if (sel && sel.anchorNode) {
                let node = sel.anchorNode.nodeType === Node.ELEMENT_NODE ? sel.anchorNode : sel.anchorNode.parentElement;
                while (node && node !== canvas && node !== document.body) {
                    const tag = node.tagName ? node.tagName.toLowerCase() : '';
                    if (['p', 'h2', 'h3', 'blockquote', 'li', 'div'].includes(tag)) {
                        node.style.lineHeight = String(spacing);
                        break;
                    }
                    node = node.parentElement;
                }
            }
            this.activeFormats.lineHeight = String(spacing);
            this.onCanvasInput();
        },
        
        openLinkModal() {
            const sel = window.getSelection();
            if (sel && sel.rangeCount > 0) {
                this.savedSelectionRange = sel.getRangeAt(0).cloneRange();
            } else {
                this.savedSelectionRange = null;
            }
            this.linkInputUrl = 'https://';
            this.linkModalOpen = true;
        },
        
        applyLink() {
            if (!this.linkInputUrl || this.linkInputUrl.trim() === 'https://') {
                this.linkModalOpen = false;
                return;
            }
            const canvas = document.getElementById('documentCanvas');
            if (canvas) canvas.focus();
            if (this.savedSelectionRange) {
                const sel = window.getSelection();
                sel.removeAllRanges();
                sel.addRange(this.savedSelectionRange);
            }
            document.execCommand('createLink', false, this.linkInputUrl.trim());
            this.linkModalOpen = false;
            this.onCanvasInput();
        },
        
        insertCallout(variant = 'tips') {
            const isTips = variant === 'tips';
            const icon = isTips ? '💡' : '📋';
            const title = isTips ? 'Tips Penting:' : 'Info Penting:';
            const bgClass = isTips ? 'bg-brand-soft-green/60 border-brand-soft-green-border' : 'bg-brand-cream/80 border-gray-200';
            const textClass = isTips ? 'text-brand-primary' : 'text-brand-dark';
            const placeholder = isTips ? 'Tuliskan tips praktis dapur di sini...' : 'Tuliskan catatan informasi penting di sini...';
            
            const calloutHtml = `
                <div data-block="callout" data-variant="${isTips ? 'tips' : 'info'}" class="callout-box my-6 p-4 sm:p-5 rounded-modern ${bgClass} border select-text">
                    <div class="callout-header flex items-center justify-between pb-1.5 select-none" contenteditable="false">
                        <h4 class="font-bold ${textClass} text-xs sm:text-sm flex items-center gap-1.5">
                            <span>${icon}</span>
                            <span>${title}</span>
                        </h4>
                        <button type="button" 
                                onclick="window.deleteCalloutBlock && window.deleteCalloutBlock(this)" 
                                class="callout-delete-btn p-1 rounded text-gray-400 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer" 
                                title="Hapus Block ${isTips ? 'Tips' : 'Info'}">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                    <div class="callout-body text-xs sm:text-sm ${textClass === 'text-brand-primary' ? 'text-brand-dark' : 'text-gray-700'} leading-relaxed focus:outline-hidden min-h-[1.5em]" data-placeholder="${placeholder}">
                        ${placeholder}
                    </div>
                </div>
                <p><br></p>
            `;
            this.insertHtmlAtCursor(calloutHtml);
        },
        
        insertDivider() {
            this.insertHtmlAtCursor('<hr class="my-6 border-t-2 border-gray-200"><p><br></p>');
        },
        
        insertQuote() {
            const quoteHtml = `
                <blockquote class="my-5 pl-4 border-l-4 border-brand-primary italic text-gray-700 bg-brand-soft-green/20 py-2.5 rounded-r text-xs sm:text-sm">
                    "Tuliskan kutipan atau poin penting di sini..."
                </blockquote>
                <p><br></p>
            `;
            this.insertHtmlAtCursor(quoteHtml);
        },
        
        insertHtmlAtCursor(html) {
            const canvas = document.getElementById('documentCanvas');
            if (!canvas) return;
            canvas.focus();
            const sel = window.getSelection();
            if (sel && sel.rangeCount > 0) {
                const range = sel.getRangeAt(0);
                range.deleteContents();
                const tempEl = document.createElement('div');
                tempEl.innerHTML = html;
                const frag = document.createDocumentFragment();
                let lastNode = null;
                while (tempEl.firstChild) {
                    lastNode = tempEl.firstChild;
                    frag.appendChild(lastNode);
                }
                range.insertNode(frag);
                if (lastNode) {
                    range.setStartAfter(lastNode);
                    range.collapse(true);
                    sel.removeAllRanges();
                    sel.addRange(range);
                }
            } else {
                canvas.innerHTML += html;
            }
            this.onCanvasInput();
        },
        
        onCanvasInput() {
            const canvas = document.getElementById('documentCanvas');
            if (!canvas) return;
            this.canvasHtml = canvas.innerHTML;
            this.updateDocStats();
            this.pushHistory();
        },
        
        onCanvasKeydown(e) {
            // Ctrl+S / Cmd+S -> Save
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
                e.preventDefault();
                this.saveArticle();
                return;
            }
            // Ctrl+B -> Bold
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'b') {
                e.preventDefault();
                this.formatDoc('bold');
                return;
            }
            // Ctrl+I -> Italic
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'i') {
                e.preventDefault();
                this.formatDoc('italic');
                return;
            }
            // Ctrl+U -> Underline
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'u') {
                e.preventDefault();
                this.formatDoc('underline');
                return;
            }
            // Ctrl+K -> Link
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                this.openLinkModal();
                return;
            }
            // Ctrl+Z -> Undo
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'z' && !e.shiftKey) {
                e.preventDefault();
                this.docUndo();
                return;
            }
            // Tab / Shift+Tab -> Indent / Outdent List
            if (e.key === 'Tab') {
                e.preventDefault();
                if (e.shiftKey) {
                    this.outdentList();
                } else {
                    this.indentList();
                }
                return;
            }
            // Backspace at list item start
            if (e.key === 'Backspace') {
                if (this.handleListBackspace(e)) {
                    return;
                }
            }
            // Enter key inside list item
            if (e.key === 'Enter' && !e.shiftKey) {
                if (this.handleListEnter(e)) {
                    return;
                }
            }
            // Ctrl+Y or Ctrl+Shift+Z -> Redo
            if ((e.ctrlKey || e.metaKey) && (e.key.toLowerCase() === 'y' || (e.key.toLowerCase() === 'z' && e.shiftKey))) {
                e.preventDefault();
                this.docRedo();
                return;
            }
        },

        getSelectedListItems() {
            const canvas = document.getElementById('documentCanvas');
            if (!canvas) return [];
            const sel = window.getSelection();
            if (!sel || sel.rangeCount === 0) return [];

            const range = sel.getRangeAt(0);
            const lis = Array.from(canvas.querySelectorAll('li'));
            const selected = lis.filter(li => {
                try {
                    if (range.intersectsNode) {
                        return range.intersectsNode(li);
                    }
                    return range.containsNode ? range.containsNode(li, true) : false;
                } catch (e) {
                    return false;
                }
            });

            if (selected.length === 0) {
                const anchor = sel.anchorNode;
                if (anchor) {
                    const el = anchor.nodeType === Node.ELEMENT_NODE ? anchor : anchor.parentElement;
                    const li = el ? el.closest('li') : null;
                    if (li && canvas.contains(li)) {
                        return [li];
                    }
                }
                return [];
            }

            // Keep only the "top-level" selected <li>s: drop any <li> that is
            // itself nested inside another <li> already in the selection, so
            // Indent/Outdent on a multi-item selection doesn't re-move items
            // that are already nested one level deeper (which corrupted the
            // list structure by double-nesting them).
            return selected.filter(li => {
                let ancestor = li.parentElement ? li.parentElement.closest('li') : null;
                while (ancestor) {
                    if (selected.includes(ancestor)) return false;
                    ancestor = ancestor.parentElement ? ancestor.parentElement.closest('li') : null;
                }
                return true;
            });
        },

        getCurrentListItem() {
            const canvas = document.getElementById('documentCanvas');
            if (!canvas) return null;
            const sel = window.getSelection();
            if (!sel || sel.rangeCount === 0) return null;
            const anchor = sel.anchorNode;
            if (!anchor) return null;
            const el = anchor.nodeType === Node.ELEMENT_NODE ? anchor : anchor.parentElement;
            const li = el ? el.closest('li') : null;
            return (li && canvas.contains(li)) ? li : null;
        },

        isCursorAtStartOfListItem(li, sel) {
            if (!sel || !sel.isCollapsed || !li) return false;
            try {
                const range = sel.getRangeAt(0);
                const preRange = document.createRange();
                preRange.setStart(li, 0);
                preRange.setEnd(range.startContainer, range.startOffset);
                const textBefore = preRange.toString();
                return textBefore.length === 0;
            } catch (e) {
                return false;
            }
        },

        getListItemTextOnly(li) {
            if (!li) return '';
            let text = '';
            Array.from(li.childNodes).forEach(child => {
                if (child.nodeType === Node.ELEMENT_NODE && (child.tagName.toLowerCase() === 'ol' || child.tagName.toLowerCase() === 'ul')) {
                    return;
                }
                text += child.textContent || '';
            });
            return text;
        },

        setCaretToStart(el) {
            if (!el) return;
            try {
                const range = document.createRange();
                const sel = window.getSelection();
                const walker = document.createTreeWalker(el, NodeFilter.SHOW_TEXT);
                const firstText = walker.nextNode();
                if (firstText) {
                    range.setStart(firstText, 0);
                } else {
                    range.setStart(el, 0);
                }
                range.collapse(true);
                sel.removeAllRanges();
                sel.addRange(range);
                this.savedSelectionRange = range.cloneRange();
            } catch (e) {
                // ignore
            }
        },

        extractListItemToRootParagraph(li) {
            const canvas = document.getElementById('documentCanvas');
            if (!canvas || !li) return null;

            // 1. Capture current item's non-list inline content into a new <p>
            const p = document.createElement('p');
            const childList = li.querySelector(':scope > ol, :scope > ul');
            
            const inlineNodes = [];
            Array.from(li.childNodes).forEach(node => {
                if (node === childList) return;
                inlineNodes.push(node);
            });
            inlineNodes.forEach(node => p.appendChild(node));

            if (!p.innerHTML.trim() || p.innerHTML === '') {
                p.innerHTML = '<br>';
            }

            // 2. Find the top-level list block inside #documentCanvas
            let rootBlock = li;
            while (rootBlock && rootBlock.parentElement !== canvas) {
                rootBlock = rootBlock.parentElement;
            }
            if (!rootBlock) {
                rootBlock = li.closest('ol, ul');
            }

            // 3. Handle splitting & extraction:
            const parentList = li.parentElement;

            if (parentList === rootBlock) {
                // DIRECT LEVEL 1 EXTRACTION:
                const afterLis = [];
                let next = li.nextElementSibling;
                while (next) {
                    afterLis.push(next);
                    next = next.nextElementSibling;
                }

                // Remove li from parentList
                li.remove();

                if (parentList.children.length === 0) {
                    // Case D: Only item in list
                    canvas.insertBefore(p, parentList);
                    parentList.remove();
                } else if (afterLis.length === 0) {
                    // Case A: Last item in list
                    canvas.insertBefore(p, parentList.nextSibling);
                } else {
                    // Case B & C: Middle or First item with following items
                    const afterList = document.createElement(parentList.tagName.toLowerCase());
                    afterLis.forEach(item => afterList.appendChild(item));
                    
                    canvas.insertBefore(p, parentList.nextSibling);
                    canvas.insertBefore(afterList, p.nextSibling);
                    
                    if (parentList.children.length === 0) {
                        parentList.remove();
                    }
                }
            } else {
                // NESTED EXTRACTION (Level 2, Level 3, etc.):
                li.remove();

                if (parentList && parentList.children.length === 0) {
                    parentList.remove();
                }

                if (rootBlock && rootBlock.parentElement === canvas) {
                    canvas.insertBefore(p, rootBlock.nextSibling);
                } else {
                    canvas.appendChild(p);
                }
            }

            // 4. If li had a nested child list, insert it after p
            if (childList && childList.children.length > 0) {
                canvas.insertBefore(childList, p.nextSibling);
            }

            this.setCaretToStart(p);
            return p;
        },

        indentList() {
            const canvas = document.getElementById('documentCanvas');
            if (!canvas) return;
            canvas.focus();
            if (this.savedSelectionRange) {
                const sel = window.getSelection();
                sel.removeAllRanges();
                sel.addRange(this.savedSelectionRange);
            }
            const sel = window.getSelection();
            if (!sel || sel.rangeCount === 0) return;

            const selectedLis = this.getSelectedListItems();
            if (selectedLis.length === 0) return;

            const firstLi = selectedLis[0];
            const prevLi = firstLi.previousElementSibling;
            if (!prevLi || prevLi.tagName.toLowerCase() !== 'li') {
                return; // First item: safe no-op
            }

            const parentList = firstLi.parentElement;
            const listTag = parentList ? parentList.tagName.toLowerCase() : 'ol';
            let targetSubList = prevLi.querySelector(':scope > ol, :scope > ul');
            if (!targetSubList) {
                targetSubList = document.createElement(listTag);
                prevLi.appendChild(targetSubList);
            }

            selectedLis.forEach(li => {
                targetSubList.appendChild(li);
            });

            if (selectedLis.length === 1) {
                this.setCaretToStart(selectedLis[0]);
            }

            this.onCanvasInput();
            this.updateActiveFormats();
        },

        outdentList() {
            const canvas = document.getElementById('documentCanvas');
            if (!canvas) return;
            canvas.focus();
            if (this.savedSelectionRange) {
                const sel = window.getSelection();
                sel.removeAllRanges();
                sel.addRange(this.savedSelectionRange);
            }
            const sel = window.getSelection();
            if (!sel || sel.rangeCount === 0) return;

            const selectedLis = this.getSelectedListItems();
            if (selectedLis.length === 0) return;

            selectedLis.forEach(li => {
                const currentList = li.parentElement;
                if (!currentList) return;

                const parentLi = currentList.closest('li');
                if (parentLi && parentLi.parentElement) {
                    const grandParentList = parentLi.parentElement;
                    const followingSiblings = [];
                    let next = li.nextElementSibling;
                    while (next) {
                        followingSiblings.push(next);
                        next = next.nextElementSibling;
                    }

                    grandParentList.insertBefore(li, parentLi.nextSibling);

                    if (followingSiblings.length > 0) {
                        let subList = li.querySelector(':scope > ol, :scope > ul');
                        if (!subList) {
                            subList = document.createElement(currentList.tagName.toLowerCase());
                            li.appendChild(subList);
                        }
                        followingSiblings.forEach(s => subList.appendChild(s));
                    }

                    if (currentList.children.length === 0) {
                        currentList.remove();
                    }
                } else {
                    this.extractListItemToRootParagraph(li);
                }
            });

            if (selectedLis.length === 1) {
                this.setCaretToStart(selectedLis[0]);
            }

            this.onCanvasInput();
            this.updateActiveFormats();
        },

        toggleOrderedList() {
            const canvas = document.getElementById('documentCanvas');
            if (!canvas) return;
            canvas.focus();
            if (this.savedSelectionRange) {
                const sel = window.getSelection();
                sel.removeAllRanges();
                sel.addRange(this.savedSelectionRange);
            }
            const sel = window.getSelection();
            if (!sel || sel.rangeCount === 0) return;

            const li = this.getCurrentListItem();
            if (li) {
                const parentList = li.parentElement;
                if (parentList && parentList.tagName.toLowerCase() === 'ol') {
                    this.extractListItemToRootParagraph(li);
                    this.onCanvasInput();
                    this.updateActiveFormats();
                    return;
                } else if (parentList && parentList.tagName.toLowerCase() === 'ul') {
                    const newOl = document.createElement('ol');
                    while (parentList.firstChild) {
                        newOl.appendChild(parentList.firstChild);
                    }
                    parentList.parentNode.replaceChild(newOl, parentList);
                    this.onCanvasInput();
                    this.updateActiveFormats();
                    return;
                }
            }

            let block = sel.anchorNode.nodeType === Node.ELEMENT_NODE ? sel.anchorNode : sel.anchorNode.parentElement;
            while (block && block !== canvas && block !== document.body) {
                const tag = block.tagName ? block.tagName.toLowerCase() : '';
                if (['h2', 'h3', 'h4'].includes(tag)) {
                    const ol = document.createElement('ol');
                    const newLi = document.createElement('li');
                    while (block.firstChild) {
                        newLi.appendChild(block.firstChild);
                    }
                    ol.appendChild(newLi);
                    block.parentNode.replaceChild(ol, block);
                    this.setCaretToStart(newLi);
                    this.onCanvasInput();
                    this.updateActiveFormats();
                    return;
                }
                block = block.parentElement;
            }

            document.execCommand('insertOrderedList', false, null);
            this.onCanvasInput();
            this.updateActiveFormats();
        },

        toggleUnorderedList() {
            const canvas = document.getElementById('documentCanvas');
            if (!canvas) return;
            canvas.focus();
            if (this.savedSelectionRange) {
                const sel = window.getSelection();
                sel.removeAllRanges();
                sel.addRange(this.savedSelectionRange);
            }
            const sel = window.getSelection();
            if (!sel || sel.rangeCount === 0) return;

            const li = this.getCurrentListItem();
            if (li) {
                const parentList = li.parentElement;
                if (parentList && parentList.tagName.toLowerCase() === 'ul') {
                    this.extractListItemToRootParagraph(li);
                    this.onCanvasInput();
                    this.updateActiveFormats();
                    return;
                } else if (parentList && parentList.tagName.toLowerCase() === 'ol') {
                    const newUl = document.createElement('ul');
                    while (parentList.firstChild) {
                        newUl.appendChild(parentList.firstChild);
                    }
                    parentList.parentNode.replaceChild(newUl, parentList);
                    this.onCanvasInput();
                    this.updateActiveFormats();
                    return;
                }
            }

            document.execCommand('insertUnorderedList', false, null);
            this.onCanvasInput();
            this.updateActiveFormats();
        },

        handleListBackspace(e) {
            const sel = window.getSelection();
            if (!sel || !sel.isCollapsed) return false;

            const li = this.getCurrentListItem();
            if (!li) return false;

            if (!this.isCursorAtStartOfListItem(li, sel)) {
                return false;
            }

            // Universal rule: remove numbering from current item completely
            e.preventDefault();
            this.extractListItemToRootParagraph(li);
            this.onCanvasInput();
            this.updateActiveFormats();
            return true;
        },

        handleListEnter(e) {
            const sel = window.getSelection();
            if (!sel || !sel.isCollapsed) return false;

            const li = this.getCurrentListItem();
            if (!li) return false;

            const textContent = this.getListItemTextOnly(li);
            const isEmpty = textContent.trim().length === 0;

            if (isEmpty) {
                e.preventDefault();
                this.extractListItemToRootParagraph(li);
                this.onCanvasInput();
                this.updateActiveFormats();
                return true;
            }

            const childList = li.querySelector(':scope > ol, :scope > ul');
            if (childList) {
                const range = sel.getRangeAt(0);
                if (!childList.contains(range.startContainer)) {
                    e.preventDefault();
                    const newLi = document.createElement('li');
                    newLi.innerHTML = '<br>';
                    li.parentElement.insertBefore(newLi, li.nextSibling);
                    this.setCaretToStart(newLi);
                    this.onCanvasInput();
                    this.updateActiveFormats();
                    return true;
                }
            }

            return false;
        },
        
        updateDocStats() {
            const canvas = document.getElementById('documentCanvas');
            const text = canvas ? (canvas.textContent || canvas.innerText || '') : '';
            const words = text.trim().split(/\s+/).filter(w => w.length > 0);
            this.wordCount = words.length;
            this.charCount = text.length;
            this.updateActiveFormats();
        },
        
        getSelectionFontSize() {
            const canvas = document.getElementById('documentCanvas');
            if (!canvas) return { value: '16', state: 'single' };
            const sel = window.getSelection();
            if (!sel || sel.rangeCount === 0) return { value: '16', state: 'single' };

            // Helper to get explicit or computed font size for a node
            const getNodeFontSize = (node) => {
                if (!node) return '16';
                let curr = node.nodeType === Node.ELEMENT_NODE ? node : node.parentElement;
                while (curr && curr !== canvas && curr !== document.body) {
                    if (curr.style && curr.style.fontSize) {
                        const m = curr.style.fontSize.match(/(\d+)/);
                        if (m) return m[1];
                    }
                    if (curr.tagName && curr.tagName.toLowerCase() === 'font' && curr.hasAttribute('size')) {
                        const sizeMap = { '1': '12', '2': '14', '3': '16', '4': '18', '5': '24', '6': '28', '7': '32' };
                        const sz = curr.getAttribute('size');
                        if (sizeMap[sz]) return sizeMap[sz];
                    }
                    curr = curr.parentElement;
                }
                return '16';
            };

            // If selection is collapsed (single caret position)
            if (sel.isCollapsed) {
                const node = sel.anchorNode;
                return { value: getNodeFontSize(node), state: 'single' };
            }

            // Selection is a range: inspect all text nodes within range
            const range = sel.getRangeAt(0);
            const container = range.commonAncestorContainer;
            const rootNode = container.nodeType === Node.TEXT_NODE ? container.parentNode : container;
            
            const walker = document.createTreeWalker(
                rootNode,
                NodeFilter.SHOW_TEXT,
                {
                    acceptNode(textNode) {
                        if (!textNode.textContent || textNode.textContent.trim().length === 0) {
                            return NodeFilter.FILTER_REJECT;
                        }
                        if (range.intersectsNode && !range.intersectsNode(textNode)) {
                            return NodeFilter.FILTER_REJECT;
                        }
                        return NodeFilter.FILTER_ACCEPT;
                    }
                }
            );

            const detectedSizes = new Set();
            let hasNodes = false;
            let currentTextNode = walker.nextNode();
            while (currentTextNode) {
                hasNodes = true;
                const sz = getNodeFontSize(currentTextNode);
                detectedSizes.add(sz);
                currentTextNode = walker.nextNode();
            }

            if (!hasNodes) {
                // Fallback to anchor & focus nodes
                const anchorSz = getNodeFontSize(sel.anchorNode);
                const focusSz = getNodeFontSize(sel.focusNode);
                if (anchorSz === focusSz) {
                    return { value: anchorSz, state: 'single' };
                } else {
                    return { value: null, state: 'mixed' };
                }
            }

            if (detectedSizes.size > 1) {
                return { value: null, state: 'mixed' };
            }

            const singleSize = Array.from(detectedSizes)[0] || '16';
            return { value: singleSize, state: 'single' };
        },
        
        updateActiveFormats() {
            try {
                this.activeFormats.bold = document.queryCommandState('bold');
                this.activeFormats.italic = document.queryCommandState('italic');
                this.activeFormats.underline = document.queryCommandState('underline');
                this.activeFormats.strikethrough = document.queryCommandState('strikeThrough');
                this.activeFormats.isOrderedList = document.queryCommandState('insertOrderedList');
                this.activeFormats.isUnorderedList = document.queryCommandState('insertUnorderedList');

                const sel = window.getSelection();
                if (sel && sel.rangeCount > 0) {
                    this.savedSelectionRange = sel.getRangeAt(0).cloneRange();
                    
                    // 1. Resolve Font Size via robust algorithm
                    const fontRes = this.getSelectionFontSize();
                    if (fontRes.state === 'mixed') {
                        this.activeFormats.fontSize = 'mixed';
                    } else {
                        this.activeFormats.fontSize = fontRes.value || '16';
                    }

                    // 2. Inspect block style, alignment, line-height
                    let blockNode = sel.anchorNode.nodeType === Node.ELEMENT_NODE ? sel.anchorNode : sel.anchorNode.parentElement;
                    const canvas = document.getElementById('documentCanvas');
                    let foundBlock = false;
                    while (blockNode && blockNode !== canvas && blockNode !== document.body) {
                        const tag = blockNode.tagName ? blockNode.tagName.toLowerCase() : '';
                        if (['h2', 'h3', 'p', 'blockquote', 'li'].includes(tag)) {
                            this.activeFormats.blockStyle = (tag === 'h2' || tag === 'h3') ? tag : 'p';
                            this.activeFormats.align = blockNode.style.textAlign || 'left';
                            this.activeFormats.lineHeight = blockNode.style.lineHeight || '1.75';
                            foundBlock = true;
                            break;
                        }
                        blockNode = blockNode.parentElement;
                    }
                    if (!foundBlock) {
                        this.activeFormats.blockStyle = 'p';
                        this.activeFormats.align = 'left';
                        this.activeFormats.lineHeight = '1.75';
                    }
                } else {
                    this.activeFormats.fontSize = '16';
                }
            } catch (e) {
                // ignore
            }
        },
        
        async saveArticle() {
            if (!this.form.title.trim()) {
                alert('Judul artikel wajib diisi.');
                this.editorTab = 'info';
                return;
            }
            const canvas = document.getElementById('documentCanvas');
            const html = canvas ? canvas.innerHTML : this.canvasHtml;
            
            // Parse Canvas HTML to Canonical Schema for backend and reader
            let rawContent = html;
            if (window.KnowledgeArticleParser) {
                const canonical = window.KnowledgeArticleParser.parseHtmlContent(html);
                this.form.content = JSON.stringify(canonical);
                rawContent = canonical;
            } else {
                this.form.content = html;
            }

            // Resolve category_id
            let catId = this.form.category_id;
            if (!catId) {
                const foundCat = this.categories.find(c => c.name === this.form.category || c.id === this.form.category);
                catId = foundCat ? foundCat.id : (this.categories[0]?.id || 1);
            }

            const payload = {
                category_id: catId,
                title: this.form.title,
                slug: this.form.slug,
                excerpt: this.form.excerpt || '',
                content: rawContent,
                image: this.form.image || 'images/know-thawing.jpg',
                status: (this.form.status || 'draft').toLowerCase(),
                sort_order: this.form.sort_order || 1,
            };

            try {
                const url = this.isEditing ? `/admin/knowledge-articles/${this.form.id}` : '/admin/knowledge-articles';
                const method = this.isEditing ? 'PUT' : 'POST';

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify(payload),
                });

                const result = await response.json();
                if (!response.ok || !result.success) {
                    alert(result.message || 'Gagal menyimpan artikel.');
                    return;
                }

                const saved = result.article || this.form;
                const formattedArticle = {
                    id: saved.id,
                    title: saved.title,
                    slug: saved.slug,
                    category_id: saved.category_id,
                    category: saved.category ? saved.category.name : (this.categories.find(c => c.id === saved.category_id)?.name || 'Edukasi Dapur'),
                    status: (saved.status || 'draft').charAt(0).toUpperCase() + (saved.status || 'draft').slice(1),
                    published_at: saved.created_at ? new Date(saved.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : 'Baru saja',
                    image: saved.image || 'images/know-thawing.jpg',
                    excerpt: saved.excerpt || '',
                    content: html,
                    sort_order: saved.sort_order || 1,
                };

                if (this.isEditing) {
                    const idx = this.articles.findIndex(a => a.id === this.form.id);
                    if (idx !== -1) {
                        this.articles[idx] = formattedArticle;
                    }
                    this.showToast('Artikel berhasil diperbarui!');
                } else {
                    this.articles.unshift(formattedArticle);
                    this.showToast('Artikel baru berhasil ditambahkan!');
                }

                this.initialFormJson = JSON.stringify({ form: this.form, html: html });
                this.editorModalOpen = false;
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan jaringan saat menyimpan artikel.');
            }
        },
        
        async togglePublish(a) {
            try {
                const response = await fetch(`/admin/knowledge-articles/${a.id}/toggle`, {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                });
                const result = await response.json();
                if (response.ok && result.success) {
                    a.status = result.status === 'published' ? 'Published' : 'Draft';
                    this.showToast('Status artikel diubah menjadi ' + a.status);
                } else {
                    alert(result.message || 'Gagal mengubah status artikel.');
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan jaringan saat mengubah status.');
            }
        },
        
        openDelete(a) {
            this.selectedArticle = a;
            this.deleteModalOpen = true;
        },
        
        async confirmDelete() {
            if (!this.selectedArticle) return;
            try {
                const response = await fetch(`/admin/knowledge-articles/${this.selectedArticle.id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                });
                const result = await response.json();
                if (response.ok && result.success) {
                    this.articles = this.articles.filter(a => a.id !== this.selectedArticle.id);
                    this.deleteModalOpen = false;
                    this.showToast('Artikel telah dihapus.');
                    this.selectedArticle = null;
                } else {
                    alert(result.message || 'Gagal menghapus artikel.');
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan jaringan saat menghapus artikel.');
            }
        },
        
        openMediaPicker(target = 'thumbnail') {
            this.mediaTarget = target;
            this.mediaTab = 'library';
            this.selectedMedia = this.mediaLibrary.find(m => m.path === this.form.image) || this.mediaLibrary[0] || null;
            this.uploadedFile = null;
            this.uploadedPreviewUrl = null;
            this.mediaPickerOpen = true;
        },
        
        selectMedia(media) {
            this.selectedMedia = media;
        },
        
        confirmMediaSelection() {
            let chosenUrl = null;
            if (this.mediaTab === 'library' && this.selectedMedia) {
                chosenUrl = this.selectedMedia.path;
            } else if (this.mediaTab === 'upload' && this.uploadedPreviewUrl) {
                chosenUrl = this.uploadedPreviewUrl;
            }
            
            if (!chosenUrl) return;
            
            if (this.mediaTarget === 'inline') {
                const imgFullUrl = this.getImageUrl(chosenUrl);
                const figureHtml = `
                    <figure class="my-5 text-center">
                        <img src="${imgFullUrl}" alt="${this.form.title || 'Gambar Artikel'}" class="rounded-modern max-h-96 mx-auto object-cover shadow-sm border border-gray-200">
                        <figcaption class="text-xs text-gray-500 mt-1.5 italic font-medium">Keterangan gambar artikel</figcaption>
                    </figure>
                    <p><br></p>
                `;
                this.insertHtmlAtCursor(figureHtml);
                this.mediaPickerOpen = false;
                this.showToast('Gambar berhasil disisipkan ke dalam dokumen!');
            } else {
                this.form.image = chosenUrl;
                this.mediaPickerOpen = false;
                this.showToast('Gambar thumbnail artikel dipilih!');
            }
        },
        
        handleFileUpload(e) {
            const file = e.target.files ? e.target.files[0] : (e.dataTransfer ? e.dataTransfer.files[0] : null);
            if (!file) return;
            if (!['image/jpeg', 'image/png', 'image/webp', 'image/jpg'].includes(file.type)) {
                alert('Format file tidak didukung. Gunakan JPG, PNG, atau WebP.');
                return;
            }
            this.uploadedFile = {
                name: file.name,
                size: (file.size / 1024).toFixed(0) + ' KB',
                type: file.type,
            };
            this.uploadedPreviewUrl = URL.createObjectURL(file);
        },
        
        openCreateCategoryModal() {
            this.isEditingCategory = false;
            this.categoryForm = {
                id: null,
                name: '',
                slug: '',
                color: 'blue',
                status: 'Aktif',
                is_active: true,
                articles_count: 0
            };
            this.categoryModalOpen = true;
        },
        
        openEditCategoryModal(cat) {
            this.isEditingCategory = true;
            this.categoryForm = JSON.parse(JSON.stringify(cat));
            this.categoryModalOpen = true;
        },
        
        async saveCategory() {
            if (!this.categoryForm.name.trim()) {
                alert('Nama kategori artikel wajib diisi.');
                return;
            }

            const payload = {
                name: this.categoryForm.name,
                slug: this.categoryForm.slug || '',
                is_active: this.categoryForm.is_active !== false && this.categoryForm.status !== 'Nonaktif',
            };

            try {
                const url = this.isEditingCategory ? `/admin/knowledge-categories/${this.categoryForm.id}` : '/admin/knowledge-categories';
                const method = this.isEditingCategory ? 'PUT' : 'POST';

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify(payload),
                });

                const result = await response.json();
                if (!response.ok || !result.success) {
                    alert(result.message || 'Gagal menyimpan kategori.');
                    return;
                }

                const savedCat = result.category;
                const formattedCat = {
                    id: savedCat.id,
                    name: savedCat.name,
                    slug: savedCat.slug,
                    sort_order: savedCat.sort_order || (this.categories.length + 1),
                    is_active: savedCat.is_active,
                    status: savedCat.is_active ? 'Aktif' : 'Nonaktif',
                    articles_count: savedCat.articles_count || 0,
                    color: this.categoryForm.color || 'blue',
                };

                if (this.isEditingCategory) {
                    const idx = this.categories.findIndex(c => c.id === this.categoryForm.id);
                    if (idx !== -1) {
                        const oldName = this.categories[idx].name;
                        this.categories[idx] = formattedCat;
                        this.articles.forEach(a => {
                            if (a.category === oldName) a.category = formattedCat.name;
                        });
                    }
                    this.showToast(`Kategori ${formattedCat.name} berhasil diperbarui!`);
                } else {
                    this.categories.push(formattedCat);
                    this.showToast(`Kategori baru ${formattedCat.name} berhasil ditambahkan!`);
                }

                this.categoryModalOpen = false;
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan jaringan saat menyimpan kategori.');
            }
        },
        
        async toggleCategoryStatus(cat) {
            try {
                const response = await fetch(`/admin/knowledge-categories/${cat.id}/toggle`, {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                });
                const result = await response.json();
                if (response.ok && result.success) {
                    cat.is_active = result.is_active;
                    cat.status = result.is_active ? 'Aktif' : 'Nonaktif';
                    this.showToast(`Status kategori ${cat.name} diubah menjadi ${cat.status}`);
                } else {
                    alert(result.message || 'Gagal mengubah status kategori.');
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan jaringan saat mengubah status kategori.');
            }
        },
        
        openDeleteCategory(cat) {
            this.selectedCategoryItem = cat;
            this.deleteCategoryModalOpen = true;
        },
        
        async confirmDeleteCategory() {
            if (!this.selectedCategoryItem) return;
            try {
                const response = await fetch(`/admin/knowledge-categories/${this.selectedCategoryItem.id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                });
                const result = await response.json();
                if (response.status === 422 && result.blocked) {
                    alert(result.message);
                    this.deleteCategoryModalOpen = false;
                    return;
                }
                if (response.ok && result.success) {
                    this.categories = this.categories.filter(c => c.id !== this.selectedCategoryItem.id);
                    this.deleteCategoryModalOpen = false;
                    this.showToast(`Kategori ${this.selectedCategoryItem.name} telah dihapus.`);
                    this.selectedCategoryItem = null;
                } else {
                    alert(result.message || 'Gagal menghapus kategori.');
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan jaringan saat menghapus kategori.');
            }
        },
        
        openPreview(a) {
            this.selectedArticle = a;
            this.previewModalOpen = true;
        },
        
        autoSlug() {
            this.form.slug = this.form.title.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .trim()
                .replace(/\s+/g, '-');
        },

        getImageUrl(path) {
            if (!path) return '/images/know-thawing.jpg';
            if (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('blob:')) {
                return path;
            }
            return '/' + path.replace(/^\//, '');
        }
    };
}

// Global helper for callout deletion inside contenteditable canvas
if (typeof window !== 'undefined') {
    window.deleteCalloutBlock = function(btn) {
        if (!btn) return;
        const box = btn.closest('.callout-box') || btn.closest('[data-block="callout"]') || btn.closest('.rounded-modern');
        if (box) {
            const next = box.nextElementSibling;
            box.remove();
            if (next && next.focus) next.focus();
            const canvas = document.getElementById('documentCanvas');
            if (canvas) {
                canvas.dispatchEvent(new Event('input', { bubbles: true }));
            }
        }
    };
}
