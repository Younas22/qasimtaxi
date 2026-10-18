@props(['name', 'value' => ''])

<div x-data="richTextEditor({{ \Illuminate\Support\Js::from($value ?? '') }})" class="overflow-hidden rounded-lg border border-luxury-border bg-luxury-charcoal">
    <div class="flex flex-wrap items-center gap-1 border-b border-luxury-border bg-luxury-graphite px-2 py-1.5">
        <button type="button" @mousedown.prevent="exec('bold')" class="rounded px-2 py-1 text-xs font-bold text-luxury-muted hover:bg-luxury-slate hover:text-luxury-white">B</button>
        <button type="button" @mousedown.prevent="exec('italic')" class="rounded px-2 py-1 text-xs italic text-luxury-muted hover:bg-luxury-slate hover:text-luxury-white">I</button>
        <button type="button" @mousedown.prevent="exec('underline')" class="rounded px-2 py-1 text-xs underline text-luxury-muted hover:bg-luxury-slate hover:text-luxury-white">U</button>
        <span class="mx-1 h-4 w-px bg-luxury-border"></span>
        <button type="button" @mousedown.prevent="exec('formatBlock', 'h2')" class="rounded px-2 py-1 text-xs font-semibold text-luxury-muted hover:bg-luxury-slate hover:text-luxury-white">H2</button>
        <button type="button" @mousedown.prevent="exec('formatBlock', 'h3')" class="rounded px-2 py-1 text-xs font-semibold text-luxury-muted hover:bg-luxury-slate hover:text-luxury-white">H3</button>
        <button type="button" @mousedown.prevent="exec('formatBlock', 'p')" class="rounded px-2 py-1 text-xs text-luxury-muted hover:bg-luxury-slate hover:text-luxury-white">P</button>
        <span class="mx-1 h-4 w-px bg-luxury-border"></span>
        <button type="button" @mousedown.prevent="exec('insertUnorderedList')" class="rounded px-2 py-1 text-xs text-luxury-muted hover:bg-luxury-slate hover:text-luxury-white">&bull; {{ __('List') }}</button>
        <button type="button" @mousedown.prevent="exec('insertOrderedList')" class="rounded px-2 py-1 text-xs text-luxury-muted hover:bg-luxury-slate hover:text-luxury-white">1. {{ __('List') }}</button>
        <span class="mx-1 h-4 w-px bg-luxury-border"></span>
        <button type="button" @mousedown.prevent="addLink()" class="rounded px-2 py-1 text-xs text-luxury-muted hover:bg-luxury-slate hover:text-luxury-white">{{ __('Link') }}</button>
        <button type="button" @mousedown.prevent="triggerImagePick()" :disabled="uploading" class="rounded px-2 py-1 text-xs text-luxury-muted hover:bg-luxury-slate hover:text-luxury-white disabled:opacity-50">
            <span x-show="!uploading">{{ __('Image') }}</span>
            <span x-show="uploading" x-cloak>{{ __('Uploading…') }}</span>
        </button>
        <button type="button" @mousedown.prevent="exec('removeFormat')" class="rounded px-2 py-1 text-xs text-luxury-muted hover:bg-luxury-slate hover:text-luxury-white">{{ __('Clear') }}</button>
        <input type="file" x-ref="imageInput" accept="image/*" class="hidden" @change="uploadImage($event)">
    </div>

    <div x-ref="editor" contenteditable="true" @input="sync()" @paste="handlePaste($event)" x-init="$refs.editor.innerHTML = html"
        class="richtext-content h-[500px] resize-y overflow-y-auto px-4 py-3 text-sm text-luxury-white focus:outline-none"></div>

    <input type="hidden" name="{{ $name }}" x-model="html">
</div>

<style>
    .richtext-content h2 { font-size: 1.25rem; font-weight: 600; margin: 0.75rem 0 0.5rem; color: inherit; }
    .richtext-content h3 { font-size: 1.1rem; font-weight: 600; margin: 0.75rem 0 0.5rem; color: inherit; }
    .richtext-content p { margin: 0.5rem 0; line-height: 1.6; }
    .richtext-content ul { list-style: disc; padding-inline-start: 1.5rem; margin: 0.5rem 0; }
    .richtext-content ol { list-style: decimal; padding-inline-start: 1.5rem; margin: 0.5rem 0; }
    .richtext-content a { color: #c9a227; text-decoration: underline; }
    .richtext-content img { max-width: 100%; height: auto; border-radius: 0.5rem; margin: 0.5rem 0; }
    .richtext-content table { border-collapse: collapse; width: 100%; margin: 0.75rem 0; font-size: 0.875rem; }
    .richtext-content th, .richtext-content td { border: 1px solid #2e2e33; padding: 0.5rem 0.75rem; text-align: start; }
    .richtext-content th { background: #1c1c1f; font-weight: 600; }
    .richtext-content { scrollbar-width: thin; scrollbar-color: #c9a227 transparent; }
    .richtext-content::-webkit-scrollbar { width: 6px; }
    .richtext-content::-webkit-scrollbar-track { background: transparent; }
    .richtext-content::-webkit-scrollbar-thumb { background-color: #c9a227; border-radius: 9999px; }
    .richtext-content::-webkit-scrollbar-thumb:hover { background-color: #e0b830; }
</style>

<script>
    function richTextEditor(initialHtml) {
        return {
            html: initialHtml,
            uploading: false,
            savedRange: null,
            sync() {
                this.html = this.$refs.editor.innerHTML;
            },
            exec(command, value = null) {
                document.execCommand(command, false, value);
                this.sync();
                this.$refs.editor.focus();
            },
            addLink() {
                const url = window.prompt({{ \Illuminate\Support\Js::from(__('Enter a URL')) }});
                if (url) {
                    this.exec('createLink', url);
                }
            },
            // Pasting from ChatGPT, Word, Google Docs, or a webpage brings
            // along that source's own wrapper divs, classes, and data
            // attributes — sometimes with real content nested *inside* a
            // heading tag (e.g. Word/ChatGPT markup). Stripped down to only
            // the tags this editor's toolbar actually produces, with each
            // heading's own text captured separately from any block content
            // nested inside it (which becomes sibling content instead, so
            // paragraphs never end up trapped inside a heading).
            handlePaste(event) {
                event.preventDefault();

                const html = event.clipboardData.getData('text/html');
                const clean = html
                    ? this.sanitizeHtml(html)
                    : this.escapeHtml(event.clipboardData.getData('text/plain')).replace(/\n/g, '<br>');

                document.execCommand('insertHTML', false, clean);
                this.sync();
            },
            escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            },
            sanitizeHtml(dirtyHtml) {
                const blockTags = new Set(['P', 'UL', 'OL', 'LI', 'TABLE', 'THEAD', 'TBODY', 'TR', 'TD', 'TH', 'HR']);
                const inlineTags = new Set(['A', 'B', 'STRONG', 'I', 'EM', 'U', 'BR']);
                const headingTags = new Set(['H1', 'H2', 'H3', 'H4', 'H5', 'H6']);

                const template = document.createElement('template');
                template.innerHTML = dirtyHtml;

                // A heading's "own text" stops at the first block-level
                // child — anything after that is nested content that got
                // caught inside the tag by mistake, not part of the heading.
                // Returns how many leading child nodes were consumed, so the
                // caller can resume from there instead of re-walking (and
                // duplicating) the text just copied.
                const copyOwnText = (source, target) => {
                    let consumed = 0;
                    for (const child of Array.from(source.childNodes)) {
                        if (child.nodeType === Node.TEXT_NODE) {
                            target.appendChild(document.createTextNode(child.textContent));
                            consumed++;
                        } else if (child.nodeType === Node.ELEMENT_NODE && inlineTags.has(child.tagName)) {
                            const clone = document.createElement(child.tagName.toLowerCase());
                            target.appendChild(clone);
                            copyOwnText(child, clone);
                            consumed++;
                        } else {
                            break;
                        }
                    }
                    return consumed;
                };

                const walk = (source, target) => {
                    for (const child of Array.from(source.childNodes)) {
                        if (child.nodeType === Node.TEXT_NODE) {
                            target.appendChild(document.createTextNode(child.textContent));
                            continue;
                        }
                        if (child.nodeType !== Node.ELEMENT_NODE) continue;

                        const tag = child.tagName;

                        if (headingTags.has(tag)) {
                            // This editor only offers H2/H3 — demote H1 and H4-H6.
                            const level = tag === 'H1' ? 'h2' : (tag === 'H2' || tag === 'H3' ? tag.toLowerCase() : 'h3');
                            const heading = document.createElement(level);
                            const consumed = copyOwnText(child, heading);
                            if (heading.textContent.trim()) target.appendChild(heading);

                            // Only the *remaining* children (after the text
                            // just consumed above) become sibling content —
                            // re-walking from the start would duplicate it.
                            const remaining = document.createElement('div');
                            Array.from(child.childNodes).slice(consumed).forEach((node) => remaining.appendChild(node.cloneNode(true)));
                            walk(remaining, target);
                            continue;
                        }

                        if (tag === 'IMG') {
                            const src = child.getAttribute('src');
                            if (src) {
                                const img = document.createElement('img');
                                img.setAttribute('src', src);
                                target.appendChild(img);
                            }
                            continue;
                        }

                        if (blockTags.has(tag)) {
                            const clone = document.createElement(tag.toLowerCase());
                            target.appendChild(clone);
                            walk(child, clone);
                            continue;
                        }

                        if (inlineTags.has(tag)) {
                            const clone = document.createElement(tag.toLowerCase());
                            if (tag === 'A') {
                                const href = child.getAttribute('href');
                                if (href) clone.setAttribute('href', href);
                            }
                            target.appendChild(clone);
                            walk(child, clone);
                            continue;
                        }

                        // Unknown wrapper (div, span, section, etc.) — unwrap
                        // it and keep processing its children in place.
                        walk(child, target);
                    }
                };

                const output = document.createElement('div');
                walk(template.content, output);

                return output.innerHTML;
            },
            triggerImagePick() {
                // The editor loses text-cursor focus once the native file
                // picker opens, so the insertion point has to be captured
                // now and restored right before inserting the image.
                const selection = window.getSelection();
                this.savedRange = selection.rangeCount ? selection.getRangeAt(0).cloneRange() : null;
                this.$refs.imageInput.click();
            },
            uploadImage(event) {
                const file = event.target.files[0];
                if (!file) return;

                this.uploading = true;

                const formData = new FormData();
                formData.append('image', file);

                axios.post({{ \Illuminate\Support\Js::from(route('admin.rich-text.upload-image')) }}, formData)
                    .then((response) => {
                        const editor = this.$refs.editor;
                        editor.focus();

                        // Fall back to inserting at the end of the content
                        // when there's no valid saved cursor position inside
                        // the editor (e.g. the toolbar button was clicked
                        // without focusing the editor first).
                        let range = this.savedRange;
                        if (!range || !editor.contains(range.commonAncestorContainer)) {
                            range = document.createRange();
                            range.selectNodeContents(editor);
                            range.collapse(false);
                        }

                        const selection = window.getSelection();
                        selection.removeAllRanges();
                        selection.addRange(range);

                        document.execCommand('insertImage', false, response.data.url);
                        this.sync();
                    })
                    .catch(() => {
                        window.dispatchEvent(new CustomEvent('notify', {
                            detail: { type: 'error', message: {{ \Illuminate\Support\Js::from(__('Image upload failed — please try again.')) }} },
                        }));
                    })
                    .finally(() => {
                        this.uploading = false;
                        event.target.value = '';
                    });
            },
        };
    }
</script>
