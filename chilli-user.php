<?php
/* ------------------------------------------------------------------------
   Part of the Chilli kit — https://chillikit.com

   Free to use, modify, and share (including commercially).
   Please keep this note as a reference to the origin.
------------------------------------------------------------------------ */
/*===============================================
    Basic admin functions
===============================================*/
$app->ini->preset('username', 'root');
$app->ini->preset('pw', password_hash('pass', PASSWORD_DEFAULT));


function q_login(){
    echoManageheader();
?>
<form method="post" action="?q=dologin">
<h1>Login</h1>
<label>Your user name</label>
<input type="text" name="username" autofocus>
<label>Your pass word</label>
<input type="password" name="password">
<input type="submit" value="Log in">
</form>
<?php
    echoManagefooter();
}
function q_dologin(){
    global $app;
    $passWord = getPost('password');
    $userName = getPost('username');
    $access = ($userName === $app->ini->get('username'));
    if($access){
        $access = password_verify($passWord, $app->ini->get('pw'));
    }
    $app->session->setadmin($access);
    if($app->session->admin){
        $app->session->set('username', $userName);
        gotoUrl('?q=manage&m=Welcome. You are now logged in!');
    }
    else{
        gotoUrl('?q=login&m=Try again');
    }
}


// Only expose functions below if user is admin.
if($app->session->admin):

function q_logout(){
    global $app;
    $app->session->remove('username');    
    $app->session->setadmin(false);
    gotoUrl();
}

function q_dochangeusername(){
    global $app;
    $app->ini->set('username', getPost('username'));
    gotoUrl('?q=setup&m=New user name saved!');
}
function q_dochangepassword(){
    global $app;
    if(password_verify(getPost('oldword'), $app->ini->get('pw'))){
        if(getPost('newword') === getPost('checkword')){
            $app->ini->set('pw', password_hash(getPost('newword'), PASSWORD_DEFAULT));
            gotoUrl('?q=setup&m=Password saved!');
        }
        else{
            gotoUrl('?q=setup&m=New password had a mismatch');
        }
    }else{
        gotoUrl('?q=setup&m=Incorrect old password');
    }    
}


function showUserBlock(){
    global $app;
?>
<hr>
<h2>Change password</h2>
<form method="post" action="?q=dochangepassword">
<p><label>Enter your <b>old</b> password</label>
<input type="password" name="oldword"></p>
<p><label>Enter your new password</label>
<input type="password" name="newword" ></p>
<p><label>Enter your new password again</label>
<input type="password" name="checkword">
<input type="submit" value="Change"></p>
</form>
<p>Change your password. It is a good idea to keep your site secure by changing it regularly.</p>

<hr>
<h2>User name</h2>
<form method="post" action="?q=dochangeusername">
    <input type="text" name="username" value="<?php echo($app->ini->get('username')); ?>">
    <input type="submit" value="Change">
</form>
<p>Change your user name.</p>
<?php
}
$app->steps->add('showsetup', 'showUserBlock');
/*===============================================
    /Basic admin functions
===============================================*/

endif;

?>