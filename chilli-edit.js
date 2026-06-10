
/* Part of the Chilli kit — https://chillikit.com | Free to use, modify, and share. Please keep this note. */

// Chilli Editor
function chilliEdit(textBoxclass){
    var textBox = document.querySelector(textBoxclass);
    textBox.style.display = "none";
// add  a outer frame that contains textarea, tools and editor
    var chilliFrame = document.createElement('div');
    textBox.parentElement.insertBefore(chilliFrame, textBox);
    chilliFrame.appendChild(textBox);
    chilliFrame.classList.add('chilli-frame');
    chilliFrame.style.overflow = 'hidden';
    
    var chilliTools = document.createElement('div');
    chilliFrame.appendChild(chilliTools);
    chilliTools.classList.add('chilli-tools');
    

    var chilliEditor = document.createElement('div');
    chilliFrame.appendChild(chilliEditor);
    chilliEditor.classList.add('chilli-editor');
    chilliEditor.contentEditable = true;
    chilliEditor.innerHTML = textBox.value;
    document.execCommand('styleWithCSS', false, true);
    document.execCommand('defaultParagraphSeparator', false, 'p');
    if(chilliEditor.childNodes.length == 0) chilliEditor.innerHTML = '<p>&nbsp;</p>';
    chilliEditor.addEventListener('blur', function(){
        textBox.value = chilliEditor.innerHTML;
    });

    function addButton(title, caption, action){
        var btn = document.createElement('button');
        btn.setAttribute('type', 'button');
        btn.classList.add('chilli-button');
        btn.title = title;
        btn.innerHTML = caption;
        btn.addEventListener('click', action);
        chilliTools.appendChild(btn);
    }
    
    function addColorbutton(title, cssColor, action){
        var btn = document.createElement('button');
        btn.setAttribute('type', 'button');
        btn.classList.add('chilli-button');
        btn.classList.add(cssColor);
        btn.title = title;
        btn.innerHTML = '<b>T</b>';
        btn.addEventListener('click', action);
        chilliTools.appendChild(btn);
    }

    addButton('Bold', '<b>B</b>', function(event){
        document.execCommand('bold');
    });

    addButton('Italic', '<i>I</i>', function(event){
        document.execCommand('italic');
    });
    
    addButton('Underline', '<u>U</u>', function(event){
        document.execCommand('underline'); 
    });
    
    addButton('Header 1', 'H1', function(event){
        document.execCommand('formatBlock', false, '<h1>');
    });

    addButton('Header 2', 'H2', function(){
        document.execCommand('formatBlock', false, '<h2>');
    });
    
    addButton('Header 3', 'H3', function(){
        document.execCommand('formatBlock', false, '<h3>');
    });
    
    addButton('Paragraph', 'P', function(){
        document.execCommand('formatBlock', false, '<p>');
    });
    
    addButton('Clear formatting', '&#x239A;', function(){
        document.execCommand('removeFormat');
    });

    addButton('Link', '&#x2794;', function(event){
        var url = window.prompt('Enter the link URL');
        if(url) document.execCommand('createLink', false, url);
        chilliEditor.focus();
    });
    
    addButton('Line', '-', function(event){
        document.execCommand('insertHorizontalRule', false); 
    });
    
    addColorbutton('Text color', 'color1', function(event){
        var cssObj = window.getComputedStyle(event.currentTarget, null);
        document.execCommand('foreColor', false, cssObj.getPropertyValue('color'));
    });
    
    addColorbutton('Text color', 'color2', function(event){
        var cssObj = window.getComputedStyle(event.currentTarget, null);
        document.execCommand('foreColor', false, cssObj.getPropertyValue('color'));
    });
    
    addButton('move toolbar', '&ShortDownArrow;', function(event){
        chilliTools.classList.add('chilli-tools-spacer'); // &ShortUpArrow;
    });
    
    var btn = document.createElement('button');
    btn.setAttribute('type', 'button');
    btn.classList.add('chilli-button');
    btn.title = 'HTML';;
    btn.innerHTML = 'HTM';
    btn.addEventListener('click', function(){
        chilliFrame.removeChild(chilliEditor);
        chilliFrame.removeChild(chilliTools);
        textBox.style.display = "block";
    });
    chilliTools.appendChild(btn);
}

var editor = new chilliEdit('.post-edit-content');
(function(){

    let slug = document.getElementById('slug');
    let sluglabel = document.getElementById('sluglabel');
    let sluglock = document.getElementById('sluglock');
    if(sluglock.value == 0){
        slug.style.display = 'none';
        sluglabel.style.display = slug.style.display;
    }
    sluglock.addEventListener('click', function(){
        if(sluglock.value == 0){
            slug.style.display = 'none';
        }
        else{
            slug.style.display = 'block';
        }
        sluglabel.style.display = slug.style.display;        
    });

    const imgInp = document.getElementById('imageinput');
    const postImage = document.getElementById('postimage');
    if(postImage.src == ''){
        postImage.style.display = 'none';
    }
    imgInp.addEventListener('change', function(){
        const path = imgInp.files[0];
        if(path){
            postImage.src = URL.createObjectURL(path);
        }
    });
    
    title = document.getElementById('title');
    submit = document.getElementById('submit');
    submit.addEventListener('click', function(event){
        if(slug.value.trim() == '' || title.value.trim() == ''){
            event.preventDefault();
            alert('Title and Slug must be filled out!');
        }
    });
})();


