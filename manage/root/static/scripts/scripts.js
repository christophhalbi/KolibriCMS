var base_uri;

document.addEventListener('DOMContentLoaded', function () {
    base_uri = document.getElementById('uri_base');

    if (document.getElementById('tree')) {
        var param = document.location.href.split('?id=');
        if (param.length == 2) {
            var element = document.querySelector('*[data-id="' + param[1] + '"]');
            if (element) {
                while (element) {
                    if (element.parentNode.tagName == 'LABEL') {
                        element.querySelectorParent('li').querySelector('input').checked = true;
                    }
                    element = document.querySelector('*[data-id="' + element.getAttribute('data-parent_id') + '"]');
                }
            }
        }
    }

    if (document.getElementById('texteditor_tinymce')) {
        init_tinymce();
    }

    if (document.getElementById('texteditor_codemirror')) {
        init_codemirror();
    }

    var delete_links = document.querySelectorAll('.delete');
    for (var i = 0; i < delete_links.length; i++) {
        delete_links[i].onclick = function() {
            return confirm('Objekt wirklich entfernen?');
        }
    }

    init_image_preview(document);
    init_dynamic_sorting();

}, false);

function init_codemirror() {
    var editor = CodeMirror.fromTextArea(document.getElementById("texteditor_codemirror"), {
        mode: document.getElementById("mode").value,
        lineNumbers: true,
        lineWrapping: true,
        onCursorActivity: function() {
            editor.setLineClass(h1Line, null, null);
            h1Line = editor.setLineClass(editor.getCursor().line, null, "activeline");
        }
    });
    var h1Line = editor.setLineClass(0, "activeline");
}

function init_tinymce() {
    tinyMCE.init({
        mode                              : 'exact',
        elements                          : 'texteditor_tinymce',
        theme                             : 'advanced',
        plugins                           : 'link_image_browser,safari,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template',
        language                          : 'de',
        convert_urls                      : false,
        theme_advanced_toolbar_location   : 'top',
        theme_advanced_toolbar_align      : 'left',
        theme_advanced_statusbar_location : 'bottom',
        theme_advanced_resizing           : false,
        theme_advanced_buttons1           : 'bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,removeformat,|,fontsizeselect,forecolor,backcolor',
        theme_advanced_buttons2           : 'cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime',
        theme_advanced_buttons3           : 'tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen',
        content_css                       : document.getElementById('uri_static_cms') + '/css/style_ext.css',
        external_image_list               : get_static_images(),
        external_image_list_url           : document.getElementById('uri_static') + '/scripts/set_static_images.js',
        height                            : 600,
        width                             : 700,
        extended_valid_elements           : "iframe[class|src|width|height|name|align|scrolling]"
    });
}

function init_image_preview(requestor) {
    var preview_links = requestor.querySelectorAll('.preview');
    for (var i = 0; i < preview_links.length; i++) {
        preview_links[i].onmouseover = function() {
            var object_id = this.getAttribute('data-object_id');
            if (object_id) {
                var popup_container = document.createElement('div');
                popup_container.className = 'popup';
                popup_container.id        = 'popup_' + object_id;
                var preview = document.createElement('img');
                preview.src = this.href;
                popup_container.appendChild(preview);
                var pos = get_pos(this);
                popup_container.style.top  = pos.top  + 'px';
                popup_container.style.left = pos.left + 50 + 'px';
                document.body.appendChild(popup_container);
            }
        }
        preview_links[i].onmouseout = function() {
            var object_id = this.getAttribute('data-object_id');
            if (object_id) {
                if (document.getElementById('popup_' + object_id))
                    document.body.removeChild(document.getElementById('popup_' + object_id));
            }
        }
    }
}

function init_dynamic_sorting() {
    var sorters = document.querySelectorAll('.sorter');
    for (var i = 0; i < sorters.length; i++) {
        sorters[i].onclick = function() {
            var dir = this.getAttribute('data-dir');
            var row = this.querySelectorParent('.sortable');
            var row_parent = row.parentNode;

            if (dir == "up") {
                var prev_sibling = row.previousSibling;
                if (prev_sibling) {
                    row_parent.removeChild(row);
                    row_parent.insertBefore(row, prev_sibling);
                }
            }
            else {
                var next_sibling = row.nextSibling;
                if (next_sibling) {
                    row_parent.removeChild(row);
                    row_parent.insertBefore(row, next_sibling.nextSibling);
                }
            }

            var params = '&objects=';
            [].forEach.call(row_parent.querySelectorAll('.sortable'), function(row) {
                params += row.getAttribute('data-object_id') + ';';
            });

            ajax_request(base_uri + '/manage/content/index.php', '&manage_sort=1' + params, function(response) { }, on_error);

            return false;
        }
    }
}

function get_static_images() {
    var static_images = new Array();
    var image_list = document.getElementById('static_images');
    if (image_list) {
        var images = image_list.querySelectorAll('li');
        for (var i = 0; i < images.length; i++) {
            var image = images[i].querySelector('a');
            static_images.push(new Array(image.innerHTML, image.href));
        }
    }
    return static_images;
}

var windows = new Object;

function open_object_browser(requested_by, only_pages, destination_id, format) {
    var browser = document.createElement('div');
    browser.className = 'object_browser';
    var closer = document.createElement('span');
    closer.innerHTML = 'x';
    closer.className = 'close';
    closer.onclick = function() {
        document.body.removeChild(browser);
    }
    browser.appendChild(closer);

    var additional_param = (only_pages) ? '&only_pages=1' : '';

    ajax_request(
        base_uri + '/manage/content/index.php', '&manage_tree=1&parent=1' + additional_param, function (response) {
        var tree = response.getElementsByTagName('body')[0].getElementsByTagName('ul')[0];
        if (tree) {
            browser.appendChild(tree);
            init_browser_elements(browser, only_pages, destination_id, format);
        }
    }, on_error);

    if (requested_by) {
        var pos = get_pos(requested_by);
        browser.style.top  = pos.top  + 'px';
        browser.style.left = pos.left + 50 + 'px';
    }

    document.body.appendChild(browser);

    return browser;
}

function init_browser_elements(requestor, only_pages, destination_id, format) {
    var elements = requestor.querySelectorAll('.node');
    for (var i = 0; i < elements.length; i++) {
        if (elements[i].querySelector('.object_expander')) {
            elements[i].querySelector('.object_expander').onclick = function() {
                if (this.parentNode.querySelector('ul')) {
                    this.parentNode.querySelector('ul').classList.toggle('hide');
                }
                else {
                    load_browser_elements(this, only_pages, destination_id, format);
                }
            }
        }
        var objects = elements[i].querySelectorAll('.object');
        for (var j = 0; j < objects.length; j++) {
            objects[j].onclick = function() {
                if (format == null) {
                    CreateLink(this, this.href);
                }
                else {
                    var object_id   = this.getAttribute('data-object_id');
                    var value       = null;
                    var destination = (destination_id)
                        ? document.getElementById(destination_id)
                        : document.getElementById('object_browser_destination');

                    if (destination) {
                        var my_format = format;
                        if (this.classList.contains('direct')) {
                            my_format = my_format.replace(/%dollar/g, '&');
                        }
                        destination.value = my_format.replace(/%dollar/g, '$').replace(/%id/g, object_id);
                    }
                }
                return false;
            }
        }
    }
}

function load_browser_elements(element, only_pages, destination_id, format) {
    var object_id = element.getAttribute('data-object_id');

    var additional_param = (only_pages) ? '&only_pages=1' : '';

    var sub_tree = element.parentNode.getElementsByTagName('ul')[0];
    if (sub_tree) {
        element.parentNode.removeChild(sub_tree);
    }

    ajax_request(base_uri + '/manage/content/index.php', '&manage_tree=1&parent=' + object_id + additional_param, function (response) {
        var tree = response.getElementsByTagName('body')[0].getElementsByTagName('ul')[0];
        if (tree) {
            element.parentNode.appendChild(tree);
            init_browser_elements(element.parentNode, only_pages, destination_id, format);
        }
    }, on_error);
}

function ajax_request(url, params, oncomplete, onerror) {
    var req = new XMLHttpRequest;
    req.onreadystatechange = function () {
        if (!req)
            return;
        if (req.readyState == 4)
            if (req.status == 200) {
                if (!req.responseXML)
                    alert('Fehler in AJAX Rueckgabe: ' + req.responseText);

                return oncomplete(req.responseXML);
            }
            else {
                alert('Fehler: ' + req.status + ' ' + req.statusText + '\n' + req.responseText);
                if (onerror)
                    onerror();
            }
    };
    req.open('POST', url, true);
    req.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    req.send('ajax=1' + params);

    return req;
}

Element.prototype.matchesSelector = function(selector) {
    var element = this;
    var parent = element.parentNode ? element.parentNode : document;
    var matching_children = parent.querySelectorAll(selector);

    for (var i = 0; i < matching_children.length; i++) {
        if (matching_children[i] === element)
            return element;
    }

    return null;
};

Element.prototype.querySelectorParent = function(selector) {
    var parent = this.parentNode;
    if (!(parent instanceof Element))
        return null;
    return parent.matchesSelector(selector) ? parent : parent.querySelectorParent(selector);
};

function get_pos(element) {
    var pos = new Object();
    var top  = 0;
    var left = 0;

    while (element) {
        top  += element.offsetTop;
        left += element.offsetLeft;
        element = element.offsetParent;
    }
    pos.left = left;
    pos.top  = top;
    return pos;
}

function on_error() {
    alert('Fehler');
}