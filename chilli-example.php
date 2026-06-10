<?php
/* ------------------------------------------------------------------------
   Part of the Chilli kit — https://chillikit.com

   Free to use, modify, and share (including commercially).
   Please keep this note as a reference to the origin.
------------------------------------------------------------------------ */

function q_saygoodbye(){
?>
    <!doctype html><html lang="en-US"><head><meta charset="utf-8">
    <title>Bye bye</title>
    </head><body>
    <h1>Bye bye!</h1>
    <p>If you see this, you have successfully added a function you can request from an URL.</p>
    </body></html>
<?php
}
// call it like this: http://localhost/?q=saygoodbye


function myResponse(){
    global $app;
?>
    <!doctype html>
    <html lang="en-US">
    <head>
        <meta charset="utf-8">
        <title>My own response</title>
        <style>
            body{
                font-family: Verdana, sans-serif;
                font-size: 16px;
                line-height: 1.4;
            }
            h1{
                font-size: 2.5rem;
            }
        </style>
    </head>
    <body>
        <h1>My own custom response</h1>
        <p>If you see this, you have successfully assigned a new default response to your project.</p>
        <?php $app->steps->run('sendcontent'); ?>
    </body>
    </html>
<?php
}
$app->respond = 'myResponse';

// call it like this: http://localhost
?>