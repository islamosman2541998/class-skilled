// Rich text editor (TipTap) as an Alpine component bound with x-modelable:
// <div x-data="richEditor({ dir: 'rtl', placeholder: '…' })" x-modelable="value" wire:model="form.body.ar">
// Output HTML is purified on the server.
import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Link from '@tiptap/extension-link';
import Placeholder from '@tiptap/extension-placeholder';

export function registerEditor(Alpine) {
    Alpine.data('richEditor', ({ dir = 'rtl', placeholder = '' } = {}) => {
        // The editor instance must stay outside Alpine's reactive proxy.
        let editor = null;

        return {
            value: '',
            updatedAt: 0,
            active: {},
            init() {
                editor = new Editor({
                    element: this.$refs.editor,
                    extensions: [
                        StarterKit.configure({ heading: { levels: [2, 3] }, code: false, codeBlock: false, horizontalRule: false, link: false }),
                        Link.configure({ openOnClick: false, autolink: true, HTMLAttributes: { rel: 'noopener', target: null } }),
                        Placeholder.configure({ placeholder }),
                    ],
                    content: this.value || '',
                    editorProps: { attributes: { dir, class: 'prose-admin' } },
                    onUpdate: ({ editor: e }) => {
                        const html = e.isEmpty ? '' : e.getHTML();
                        this.value = html;
                        this.refresh();
                    },
                    onSelectionUpdate: () => this.refresh(),
                    onTransaction: () => this.refresh(),
                });

                this.$watch('value', (html) => {
                    if (!editor) return;
                    const current = editor.isEmpty ? '' : editor.getHTML();
                    if ((html || '') !== current) editor.commands.setContent(html || '', { emitUpdate: false });
                });
            },
            refresh() {
                if (!editor) return;
                this.active = {
                    h2: editor.isActive('heading', { level: 2 }),
                    h3: editor.isActive('heading', { level: 3 }),
                    bold: editor.isActive('bold'),
                    italic: editor.isActive('italic'),
                    bullet: editor.isActive('bulletList'),
                    ordered: editor.isActive('orderedList'),
                    quote: editor.isActive('blockquote'),
                    link: editor.isActive('link'),
                };
            },
            run(command) {
                const chain = editor.chain().focus();
                switch (command) {
                    case 'h2': chain.toggleHeading({ level: 2 }).run(); break;
                    case 'h3': chain.toggleHeading({ level: 3 }).run(); break;
                    case 'bold': chain.toggleBold().run(); break;
                    case 'italic': chain.toggleItalic().run(); break;
                    case 'bullet': chain.toggleBulletList().run(); break;
                    case 'ordered': chain.toggleOrderedList().run(); break;
                    case 'quote': chain.toggleBlockquote().run(); break;
                    case 'undo': chain.undo().run(); break;
                    case 'redo': chain.redo().run(); break;
                    default: break;
                }
            },
            link(promptText) {
                const previous = editor.getAttributes('link').href || 'https://';
                const url = window.prompt(promptText, previous);
                if (url === null) return;
                if (url === '' || url === 'https://') {
                    editor.chain().focus().extendMarkRange('link').unsetLink().run();
                    return;
                }
                editor.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
            },
            destroy() {
                editor?.destroy();
                editor = null;
            },
        };
    });
}
