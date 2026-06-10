
/* Part of the Chilli kit — https://chillikit.com | Free to use, modify, and share. Please keep this note. */

(function(){
    document.querySelector('#header').addEventListener('click', function(){
        document.querySelector('.dropdown').classList.toggle('dropdown-open');
        document.querySelector('.hamburger').classList.toggle('hamburger-open');
    });
    
    let gotopButton = document.querySelector('#gotopbutton');

    window.addEventListener('scroll', function(e){
        if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
            gotopButton.style.display = "block";
        }
        else{
            gotopButton.style.display = "none";
        }        
    });

    gotopButton.addEventListener('click', function(){
        document.body.scrollTop = 0; // For Safari
        document.documentElement.scrollTop = 0; // and the rest       
        document.querySelector('.dropdown').classList.add('dropdown-open');
        document.querySelector('.hamburger').classList.add('hamburger-open');
    });
    
})();
