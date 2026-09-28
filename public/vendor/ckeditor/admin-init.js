(function () {
    function registerCustomBlocks(CKEDITOR) {
        if (CKEDITOR.plugins.registered.customdivs) {
            return;
        }

        CKEDITOR.plugins.add('customdivs', {
            init: function (editor) {
                editor.addCommand('insertCustomBlockQuoteSection', {
                    exec: function (currentEditor) {
                        var selection = currentEditor.getSelection();
                        var selectedText = selection && selection.getSelectedText();
                        var range = selection && selection.getRanges()[0];
                        var block = new CKEDITOR.dom.element('div');

                        block.addClass('custom-block-quote-section');
                        block.setHtml('<p>' + CKEDITOR.tools.htmlEncode(selectedText || 'Your content here...') + '</p>');

                        if (selectedText && range) {
                            range.deleteContents();
                            range.insertNode(block);
                        } else {
                            currentEditor.insertElement(block);
                        }
                    },
                });

                editor.ui.addButton('CustomBlockQuoteSection', {
                    label: 'Insert Custom Block Quote Section',
                    command: 'insertCustomBlockQuoteSection',
                    toolbar: 'insert',
                    icon: window.CKEDITOR_BASEPATH + 'icons/highlight.svg',
                });
            },
        });
    }

    function initializeRichTextEditors() {
        var CKEDITOR = window.CKEDITOR;

        if (!CKEDITOR || !CKEDITOR.replace) {
            console.error('CKEditor did not load; rich-text fields remain unavailable.');
            return;
        }

        registerCustomBlocks(CKEDITOR);

        document.querySelectorAll('textarea.js-rich-text-editor').forEach(function (textarea) {
            if (CKEDITOR.instances[textarea.id]) {
                return;
            }

            var editor = CKEDITOR.replace(textarea.id, {
                extraPlugins: 'embed,embedsemantic,autoembed,image2,colorbutton,colordialog,customdivs',
                toolbar: [
                    { name: 'clipboard', items: ['PasteText', 'PasteFromWord', 'Undo', 'Redo'] },
                    { name: 'styles', items: ['Styles', 'Format'] },
                    { name: 'basicstyles', items: ['Bold', 'Italic', 'Strike', '-', 'RemoveFormat', 'Underline', 'HorizontalRule', 'TextColor', 'BGColor'] },
                    { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote'] },
                    { name: 'links', items: ['Link', 'Unlink'] },
                    { name: 'insert', items: ['Image', 'Embed', 'EmbedSemantic', 'Table', 'CustomBlockQuoteSection'] },
                    { name: 'tools', items: ['Maximize'] },
                    { name: 'contents', items: ['Source'] },
                ],
                removeButtons: '',
                image2_altRequired: true,
                allowedContent: true,
                height: 400,
                format_tags: 'div;p;h1;h2;h3;h4;h5;pre',
                removeDialogTabs: 'image:advanced;link:advanced',
                contentsCss: [window.CKEDITOR_BASEPATH + 'contents.css'],
                readOnly: textarea.disabled,
                title: textarea.getAttribute('aria-label') || textarea.dataset.placeholder || 'Rich text editor',
            });

            function syncPlaceholder() {
                var body = editor.document && editor.document.getBody();

                if (!body) {
                    return;
                }

                body.setAttribute('data-placeholder', textarea.dataset.placeholder || '');

                var empty = body.getText().replace(/\u00a0/g, '').trim() === '';
                body[empty ? 'addClass' : 'removeClass']('cke-placeholder-empty');
            }

            editor.on('instanceReady', function () {
                syncPlaceholder();
                if (editor.container && editor.container.$.offsetParent) {
                    editor.resize('100%', 400);
                }
            });
            editor.on('change', function () {
                editor.updateElement();
                syncPlaceholder();
            });
            textarea.form && textarea.form.addEventListener('submit', function () {
                editor.updateElement();
            });
        });
    }

    var CKEDITOR = window.CKEDITOR;

    if (!CKEDITOR || !CKEDITOR.replace) {
        console.error('CKEditor did not load; rich-text fields remain unavailable.');
    } else if (CKEDITOR.status === 'loaded') {
        initializeRichTextEditors();
    } else {
        CKEDITOR.on('loaded', initializeRichTextEditors);
    }

    window.addEventListener('page-language-changed', function () {
        if (!window.CKEDITOR) return;
        Object.values(window.CKEDITOR.instances).forEach(function (editor) {
            if (editor.status === 'ready' && editor.container && editor.container.$.offsetParent) {
                editor.resize('100%', 400);
            }
        });
    });
}());
