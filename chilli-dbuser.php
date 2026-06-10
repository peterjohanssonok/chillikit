<?php
/* ------------------------------------------------------------------------
   Part of the Chilli kit — https://chillikit.com

   Free to use, modify, and share (including commercially).
   Please keep this note as a reference to the origin.
------------------------------------------------------------------------ */
/*===============================================
    Basic admin functions
===============================================*/
if(! $app->db->tableExist('users')){
    $app->db->exec('create table users (id INTEGER PRIMARY KEY, lastedit INTEGER, name TEXT, password TEXT, fullname TEXT);');
    $sql = "insert into users (lastedit, name, password, fullname)";
    $sql.= " values(".time().", 'root', '".password_hash('pass', PASSWORD_DEFAULT)."', 'Root admin');";
    $sql.= "insert into users (lastedit, name, password, fullname)";
    $sql.= " values(".time().", 'owner', '".password_hash('pass', PASSWORD_DEFAULT)."', 'Owner admin');";
    $app->db->exec($sql);
}

function q_login(){
    global $app;
    echoManageheader();
?>
<form method="post" action="?q=dologin">
<h1>Login to database</h1>
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
    
    $dbUser = $app->db->querySingle("select * from users where name = '".$userName."'", true);
    $app->session->setadmin(password_verify($passWord, $dbUser['password']));
    if($app->session->admin){
        $app->session->set('username', $dbUser['name']);
        $app->session->set('userid', $dbUser['id']);
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
    $app->session->setadmin(false);
    $app->session->remove('username');
    $app->session->remove('userid');
    gotoUrl();
}


function showDbUserBlock(){
    global $app;
    echo('<h2>User list</h2>');
    echo('<div class="userlist spacer">');
    $res = $app->db->query('select * from users');
    while ($row = $app->db->fetch($res)){
        echo('<a href="?q=editthisuser&id='.$row['id'].'">');
        echo($row['name']);
        echo(', ');
        echo( $row['fullname']);
        echo('</a>');
    }
    echo('<a href="?q=createnewuser">Create a new user</a>');
    echo('</div>');
    echo('<p>You: '.$app->session->get('username').'</p>');
?>
<hr>
<form method="post" action="?q=dochangepassword">
<h2>Change password</h2>
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
$app->steps->add('showsetup', 'showDbUserBlock');



function q_createnewuser(){
    global $app;
    $sql.= "insert into users (lastedit, name, password, fullname)";
    $sql.= " values(".time().", 'new', '".password_hash('pass', PASSWORD_DEFAULT)."', 'New user');";
    $app->db->exec($sql);
    $id = $app->db->lastInsertRowID();
    gotoUrl('?q=editthisuser&id='.$id);
}
function q_editthisuser(){
    global $app;
    $id = (integer)getGet('id');
    echoManageheader();
    $row = $app->db->querySingle('select * from users where id = '.$id, true);
    echo('<form method="post" action="?q=savethisuser">'.PHP_EOL);
    echo('<p><label>users name</label>');
    echo('<input type="text" name="name" value="'.$row['name'].'">'.PHP_EOL);
    echo('<label>users full name</label>');
    echo('<input type="text" name="fullname" value="'.$row['fullname'].'"></p>'.PHP_EOL);
    echo('<input type="hidden" name="id" value="'.$id.'">'.PHP_EOL);
    echo('<input type="submit" value="save">'.PHP_EOL);
    echo('<a class="button" href="?q=deletethisuser&id='.$id.'" onclick="return confirm(\'Delete user?\')">Delete user</a>'.PHP_EOL);
    echo('<a class="button" href="?q=manage">Cancel</a>'.PHP_EOL);
    echo('</form>'.PHP_EOL);
    echoManagefooter();
}
function q_savethisuser(){
    global $app;
$sql = '
update users set
name = :name,
fullname = :fullname,
lastedit = :lastedit
where id = :id;
';
    $stmt = $app->db->prepare($sql);
    $stmt->bindValue(':name', getPost('name'));
    $stmt->bindValue(':fullname', getPost('fullname'));
    $stmt->bindValue(':lastedit', time());
    $stmt->bindValue(':id', getPost('id'));
    $stmt->execute();    
    gotoUrl('?q=manage&m=User info changed');
}
function q_deletethisuser(){
    global $app;
    $id = (integer)getGet('id');
    if($app->db->querySingle('select count(*) from users where id <> '.$id) > 0){
        $app->db->exec('delete from users where id = '. $id);
        gotoUrl('?q=manage&m=User deleted');        
    }
    else{
        echoManageheader();
        echo('<p>Cannot delete neither you or the last user</p>');
        echo('<p><a href="?q=manage">Cancel</a></p>');
        echoManagefooter();
    }
}

function q_dochangepassword(){
    global $app;
    $id = $app->session->get('userid');
    $pass = $app->db->querySingle('select password from users where id = '.$id);
    if(password_verify(getPost('oldword'), $pass)){
        if(getPost('newword') === getPost('checkword')){
            $stmt = $app->db->prepare('update users set password = :password where id = :id;');
            $stmt->bindValue(':password', password_hash(getPost('newword'), PASSWORD_DEFAULT));
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            gotoUrl('?q=manage&m=Password saved!');
        }
        else{
            gotoUrl('?q=manage&m=New password had a mismatch');
        }
    }else{
        gotoUrl('?q=manage&m=Incorrect old password');
    }
}
/*===============================================
    /Basic admin functions
===============================================*/

endif;

?>