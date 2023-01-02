$ = jQuery;
if (!Omeka) {
    var Omeka = {};
}

/**
 * Enable the WYSIWYG editor for "html-editor" fields on the form, and allow
 * checkboxes to create editors for more fields.
 *
 * @param {Element} element The element to search at and below.
 */
$(document).ready(function() {

    /**
     * Add the TinyMCE WYSIWYG editor to a page.
     * Default is to add to all textareas.
     *
     * @param {Object} [params] Parameters to pass to TinyMCE, these override the
     * defaults.
     */
    Omeka.wysiwyg = function (params) {
        // Default parameters
        initParams = {
            convert_urls: false,
            selector: "textarea",
            menubar: true,
            statusbar: false,
            toolbar_items_size: "small",
            toolbar: "bold italic underline | alignleft aligncenter alignright | bullist numlist | link formatselect code",
            plugins: "lists,link,code,paste,media,autoresize",
            autoresize_max_height: 500,
            entities: "160,nbsp,173,shy,8194,ensp,8195,emsp,8201,thinsp,8204,zwnj,8205,zwj,8206,lrm,8207,rlm",
            verify_html: false,
            add_unload_trigger: false,
        };

        tinymce.init($.extend(initParams, params));
    };

    Omeka.Elements.enableWysiwyg = function (element) {
        $(element).find('div.inputs .use-html-checkbox').each(function () {
            var textarea = $(this).parents('.input-block').find('textarea');
            if (textarea.length) {
                var enableIfChecked = function () {
                    checkBox = this;
                    $(textarea).each(function(i, ta) {
                      var textareaId = $(ta).attr('id');
                      if (checkBox.checked) {
                          tinyMCE.EditorManager.execCommand("mceAddEditor", false, textareaId);
                      } else {
                          tinyMCE.EditorManager.execCommand("mceRemoveEditor", false, textareaId);
                      }
                    });
                };

                enableIfChecked.call(this);

                // Whenever the checkbox is toggled, toggle the WYSIWYG editor.
                $(this).click(enableIfChecked);
            }
        });
    };
  Omeka.Elements.enableWysiwyg('#item-form');
})
