<?php
// Session, includes, additional authentication...

// Configuration
define('ALLOWED_USERS', serialize(array('test')));
define('LOCKED_USERS', serialize(array('test')));
define('PASSWD_FILE', '/var/www/.test');
define('PASSWD_EXEC', '/usr/bin/htpasswd');
define('BASE_URL', 'https://myurl.com/hteditor/');
define('SCRIPT_FILE', BASE_URL . basename(__FILE__));

$passwd_file_exists = false;
$htp_users = array();
if(file_exists(PASSWD_FILE)){
    $passwd_file_exists = true;
    $htp_raw = file_get_contents(PASSWD_FILE);
    $htp_arr = explode("\n", $htp_raw);
    for($i = 0; $i < count($htp_arr); $i++){
        $line = trim($htp_arr[$i]);
        if(!empty($line)){
            $le = explode(':', $line);
            $htp_users[] = $le[0];
        }
    }
}

$notify = '';
if(!empty($_GET['e'])){
    $event = (int)$_GET['e'];
    if($event == 1){
        $notify = 'Password updated for: &gt;&gt; ' . $_GET['u'] . ' &lt;&lt;';
    }
    if($event == 2){
        $notify = 'Created user: &gt;&gt; ' . $_GET['u'] . ' &lt;&lt;';
    }
    if($event == 3){
        $notify = 'Deleted user: &gt;&gt; ' . $_GET['u'] . ' &lt;&lt;';
    }
}

if(!empty($_POST['uname']) && !empty($_POST['upwd'])){
    $targetuser = $_POST['uname'];
    $targetpassword = $_POST['upwd'];
    $command = PASSWD_EXEC . ' -b ' . PASSWD_FILE . ' ' . $targetuser . ' ' . $targetpassword;
    exec($command, $output, $retval);
    $ev = 2;
    if(in_array($targetuser, $htp_users)) $ev = 1;
    header('Location: ' . SCRIPT_FILE . '?e=' . $ev . '&u=' . $targetuser);
    exit();
}

if(!empty($_GET['a'])){
    $action = $_GET['a'];
    if($action == 'up'){
        $targetuser = $_GET['u'];
        $targetpassword = base64_decode(urldecode($_GET['p']));
        $command = PASSWD_EXEC . ' -b ' . PASSWD_FILE . ' ' . $targetuser . ' ' . $targetpassword;
        exec($command, $output, $retval);
        header('Location: ' . SCRIPT_FILE . '?e=1&u=' . $targetuser);
        exit();
    }
    if($action == 'del'){
        $targetuser = $_GET['u'];
        if(!in_array($targetuser, unserialize(LOCKED_USERS))){
            $command = PASSWD_EXEC . ' -D ' . PASSWD_FILE . ' ' . $targetuser;
            exec($command, $output, $retval);
            header('Location: ' . SCRIPT_FILE . '?e=3&u=' . $targetuser);
            exit();
        } else {
            header('Location: ' . SCRIPT_FILE);
            exit();
        }
    }
}
?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Htpasswd Editor</title>
    <meta name="description" content="Htpasswd Editor">
    <meta name="author" content="Igor Davidov">
    <link rel="stylesheet" href="htpasswd.css">
</head>
<body>
    <div id="htcontainer">
        <?php if($passwd_file_exists === true){ ?>
            <?php if(!empty($notify)){ ?>
                <div id="notify">
                    <div class="in_notify">
                        <span>!</span><?php echo $notify; ?>
                    </div>
                </div>
            <?php } ?>
            <div id="htnewuser">
                <h3>Add New User</h3>
                <form method="post" action="<?php echo SCRIPT_FILE; ?>" id="userform">
                    <table class="userform_table">
                        <tr>
                            <td class="label_column">Username:</td>
                            <td><input type="text" name="uname" value="" autocomplete="off" size="10"></td>
                        </tr>
                        <tr>
                            <td class="label_column">Password:</td>
                            <td><input type="text" name="upwd" value="" autocomplete="off" size="10"></td>
                        </tr>
                        <tr>
                            <td class="label_column">&nbsp;</td>
                            <td><button class="create_new_user">Create User</button></td>
                        </tr>
                    </table>
                </form>
            </div>
            <div id="htexistingusers">
                <h3>Existing Users</h3>
                <table class="user_table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th colspan="2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            if(count($htp_users) == 0){
                                echo '<td class="no_users_column">No users found.</td>';
                            } else {
                                for($i = 0; $i < count($htp_users); $i++){
                                    $symbollocked = (in_array($htp_users[$i], unserialize(LOCKED_USERS))) ? '&#9741;' : '';
                                    $datalocked = (in_array($htp_users[$i], unserialize(LOCKED_USERS))) ? 'yes' : '';
                                    echo '<tr>';
                                    echo '<td>' . $htp_users[$i] . ' ' . $symbollocked . '</td>';
                                    echo '<td class="actions_column"><button class="update_password" data-user="' . $htp_users[$i] . '">Update Password</button></td>';
                                    echo '<td class="actions_column"><button class="delete_user" data-user="' . $htp_users[$i] . '" data-locked="' . $datalocked . '">Delete</button></td>';
                                    echo '</tr>';
                                }
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        <?php } else { ?>
            <div id="hterror">
                Could not find file <strong><?php echo PASSWD_FILE; ?></strong>. Please create one.
            </div>
        <?php } ?>
    </div>
    <input type="hidden" name="script_file" value="<?php echo SCRIPT_FILE; ?>">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="htpasswd.js"></script>
</body>
</html>