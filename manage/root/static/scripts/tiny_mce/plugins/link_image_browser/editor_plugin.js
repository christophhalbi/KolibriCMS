/**
 * editor_plugin_src.js
 *
 * Copyright 2009, Moxiecode Systems AB
 * Released under LGPL License.
 *
 * License: http://tinymce.moxiecode.com/license
 * Contributing: http://tinymce.moxiecode.com/contributing
 */

(function() {
    tinymce.create('tinymce.plugins.Link_Image_Browser', {
        init : function(ed, url) {
            this.editor = ed;

            if(!this.browser) {
                var browser = open_object_browser(null, false, null, null);
                if (browser) {
                    this.browser = browser;
                    this.browser.editor = ed;
                    document.getElementById('texteditor_tinymce_functions').appendChild(browser);
                }
            }
        },

        getInfo : function() {
            return {
                longname  : 'Link/image browser',
                author    : 'cha',
                authorurl : 'http://www.multitraining.at',
                version   : tinymce.majorVersion + "." + tinymce.minorVersion
            };
        }
    });

    function init_browser() {
    }

    // Register plugin
    tinymce.PluginManager.add('link_image_browser', tinymce.plugins.Link_Image_Browser);
})();

function get_editor_from_link(link) {
    while (! link.editor)
        link = link.parentNode;
    return link.editor;
}

function CreateLink(link, URL) {
    get_editor_from_link(link).execCommand('CreateLink', false, URL);
}

function CreateImage(image, URL) {
    get_editor_from_link(image).execCommand('mceInsertContent', false, '<img src="' + URL + '" alt=""/>');
}