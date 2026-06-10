<?php
/* ------------------------------------------------------------------------
   Part of the Chilli kit — https://chillikit.com

   Free to use, modify, and share (including commercially).
   Please keep this note as a reference to the origin.
------------------------------------------------------------------------ */
/*===============================================
    Basic admin functions
===============================================*/
$app->ini->preset('passphrase', password_hash('pass', PASSWORD_DEFAULT));
$app->ini->preset('logindelay', 0);

function q_login(){
    echoManageheader();
?>
<form method="post" action="?q=dologin">
<h1>Login</h1>
<label>Your pass phrase</label>
<input type="password" name="passphrase" autofocus>
<input type="submit" value="Log in">
</form>
<?php
    echoManagefooter();
}

function q_dologin(){
    global $app;
    if(time() > (int)$app->ini->get('logindelay')){
        $passPhrase = getPost('passphrase');
        $app->session->setadmin(password_verify($passPhrase, $app->ini->get('passphrase')));
        if($app->session->admin){
            gotoUrl('?q=manage&m=Welcome. You are now logged in!');
        }
        else{
            $app->ini->set('logindelay', time()+10);
            gotoUrl('?q=login&m=Try again');
        }
    }
    else{
        $app->ini->set('logindelay', time()+10);
        gotoUrl('?q=login&m=Try again');        
    }
}

// Only expose functions below if user is admin.
if($app->session->admin):

function q_logout(){
    global $app;
    $app->session->setadmin(false);
    gotoUrl();
}

function q_dochangepassphrase(){
    global $app;
    if(password_verify(getPost('oldword'), $app->ini->get('passphrase'))){
        if(getPost('newword') === getPost('checkword')){
            $app->ini->set('passphrase', password_hash(getPost('newword'), PASSWORD_DEFAULT));
            gotoUrl('?q=setup&m=Password saved!');
        }
        else{
            gotoUrl('?q=setup&m=New password had a mismatch');
        }
    }
    else{
        gotoUrl('?q=setup&m=Incorrect old password');
    }    
}

function showPassBlock(){
    global $app;
?>
<hr>
<form method="post" action="?q=dochangepassphrase">
<h3>Change password</h3>
<p><label>Enter your <b>old</b> password</label>
<input type="password" name="oldword"></p>
<p><label>Enter your new password</label>
<input type="password" name="newword" ></p>
<p><label>Enter your new password again</label>
<input type="password" name="checkword">
<input type="submit" value="Change"></p>
</form>
<p>Change your password. It is a good idea to keep your site secure by changing it regularly.</p>

<?php
}
$app->steps->add('showsetup', 'showPassBlock');

endif;
/*===============================================
    /Basic admin functions
===============================================*/
?>