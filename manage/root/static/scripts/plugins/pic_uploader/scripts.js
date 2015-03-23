var input;
var ul;
var item;
var scale_options;

document.addEventListener('DOMContentLoaded', function () {

    input         = document.getElementById("files");
    ul            = document.getElementById("file_list");
    item          = document.getElementById("file_item");
    scale_options = document.getElementById("scale_options");

}, false);

function make_file_list() {
    // clear
    while (ul.hasChildNodes()) {
        ul.removeChild(ul.firstChild);
    }
    
    for (var i = 0; i < input.files.length; i++) {
        var li = item.cloneNode(true);
        li.getElementsByTagName('span')[0].innerHTML = input.files[i].name;
        
        var name_input = li.getElementsByTagName('input')[0];
        name_input.name  = "names[]";
        name_input.value = input.files[i].name;
        
        li.removeAttribute('class');
        li.removeAttribute('id');
        ul.appendChild(li);
    }

    if(!ul.hasChildNodes()) {
        var li = document.createElement("li");
        li.innerHTML = 'No Files Selected';
        ul.appendChild(li);
    }
}

function toggle_scale_options() {
    scale_options.classList.toggle('hide');
}