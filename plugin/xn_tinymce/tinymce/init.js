const paste_image_upload_handler = (blobInfo, progress) => new Promise((resolve, reject) => {
    xn.upload_file(blobInfo.blob(), xn.url('attach-create'), {
		is_image: 1
	}, function(code, json) {
		if (code == 0) {
			resolve(json.url);
		} else {
		   reject({ message: 'Error: ' + json });
		}
	});
});

tinymce.init({
    selector: 'textarea#message',
    content_css: 'plugin/xn_tinymce/tinymce/style.css',
    language: 'en',
    menubar: true,
    statusbar: true,
    resize: true,
    toolbar_mode: 'floating', //floating / sliding / scrolling / wrap
    toolbar_sticky: true,
    branding: false,
    min_height: 500,
    draggable_modal: true,
    image_uploadtab: false,
    autosave_ask_before_unload: true,
    autosave_interval: '20s',
    autosave_retention: '360m',
    autosave_restore_when_empty: true,
    images_file_types: 'jpeg,jpg,png,gif,bmp,webp',
    plugins: ['advlist', 'anchor', 'autolink','autosave', 'autoresize', 'charmap', 'code', 'codesample', 'xiunoimgup','-directionality', 'fullscreen', 'help', 'image', 'importcss','insertdatetime', 'link', 'lists', 'media', 'nonbreaking','pagebreak', 'preview', 'quickbars', 'save', 'searchreplace', 'table', '-template',  '-visualblocks', '-visualchars', 'wordcount'],
    menu: {
        file: { title: 'File', items: 'restoredraft | preview | export print | deleteallconversations' },
        edit: { title: 'Edit', items: 'undo redo | cut copy paste pastetext | selectall | searchreplace' },
        view: { title: 'View', items: 'code | visualaid visualchars visualblocks | spellchecker | preview fullscreen | showcomments' },
        insert: { title: 'Insert', items: 'xiunoimgup | image link media addcomment pageembed  codesample inserttable | charmap emoticons hr | pagebreak nonbreaking anchor tableofcontents | insertdatetime' },
        format: { title: 'Format', items: 'bold italic underline strikethrough superscript subscript codeformat | styles blocks fontfamily fontsize align lineheight | forecolor backcolor | language | removeformat' },
        tools: { title: 'Tools', items: 'spellchecker spellcheckerlanguage | a11ycheck code wordcount' },
        table: { title: 'Table', items: 'inserttable | cell row column | advtablesort | tableprops deletetable' },
        help: { title: 'Help', items: 'help' }
    },
    toolbar: ['fontfamily code | undo redo  | formatting fontcolor removeformat | alignment blockquote indentation list | imgup link media codesample table | anchor hr toc preview | fullscreen restoredraft | other', t_external_toolbar.join(' ')],
    toolbar_groups: {
        formatting: {
            icon: 'format',
            tooltip: 'Format',
            items: 'formatselect | fontselect | fontsizeselect | bold italic underline strikethrough | superscript subscript'
        },
        alignment: {
            icon: 'align-left',
            tooltip: 'Alignment',
            items: 'alignleft aligncenter alignright alignjustify'
        },
        imgup: {
            icon: 'gallery',
            tooltip: 'Upload image',
            items: 'xiunoimgup | image'
        },
        list: {
            icon: 'unordered-list',
            tooltip: 'List',
            items: 'bullist numlist'
        },
        indentation: {
            icon: 'indent',
            tooltip: 'Indent',
            items: 'indent outdent'
        },
        fontcolor: {
            icon: 'color-levels',
            tooltip: 'Text color',
            items: 'forecolor backcolor'
        },
        other: {
            icon: 'more-drawer',
            tooltip: 'More button',
            items: 'charmap -insertdatetime help'
        }
    },
    fontsize_formats: '12px 14px 16px 18px 24px 36px 48px 56px 72px',
    font_family_formats: 'Arial=arial,helvetica,sans-serif;Arial Black=arial black,avant garde;Book Antiqua=book antiqua,palatino;Comic Sans MS=comic sans ms,sans-serif;Courier New=courier new,courier;Georgia=georgia,palatino;Helvetica=helvetica;Impact=impact,chicago;Symbol=symbol;Tahoma=tahoma,arial,helvetica,sans-serif;Terminal=terminal,monaco;Times New Roman=times new roman,times;Verdana=verdana,geneva;Webdings=webdings;Wingdings=wingdings,zapf dingbats',
    paste_data_images: true,
    indentation: '2em',
    quickbars_selection_toolbar: 'bold italic | link | H1 H2 H3 | blockquote',
    quickbars_insert_toolbar: false,
    media_live_embeds: true,
    contextmenu: false,
    external_plugins: t_external_plugins,
    images_upload_handler: paste_image_upload_handler,
    mobile: {
        toolbar_sticky: true,
        toolbar_mode: 'Wrap',
        toolbar: ['code | undo redo | removeformat | blockquote | xiunoimgup link media codesample table | anchor hr toc preview  | fullscreen restoredraft'],
    },
    convert_fonts_to_spans: false,
    extended_valid_elements: 'span[style|class],b,i',
    paste_remove_styles_if_webkit: false,
    // forced_root_block : '',
    // cache_suffix: '?v=1.0.3',
});

