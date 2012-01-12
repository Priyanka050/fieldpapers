<?php    
    ini_set('include_path', ini_get('include_path').PATH_SEPARATOR.dirname(__FILE__).'/../lib');
    require_once 'init.php';
    require_once 'data.php';
    require_once 'lib.auth.php';
    require_once 'output.php';
    
    session_start();
    
    $_SESSION['login-attempts'] += 1;
    
    $dbh =& get_db_connection();
    
    // Remember user even if they don't log in
    remember_user($dbh);
           
    switch($_POST['action'])
    {
        case 'register':
            if ($_POST['password1'] != $_POST['password2'])
            {
                die('Passwords do not match. Please try again.');
            }
        
            $prev_registered_user = get_user_by_name($dbh, $_POST['username']);
            
            if($prev_registered_user)
            {
                die('Username exists.');
            }
            
            // Verify that the email address has not been used in a previous registration.
            $mailsearch = sprintf("SELECT email from users WHERE email=%s;", $dbh->quoteSmart($_POST['email']));
            $res_mailsearch = $dbh->query($mailsearch);
            $email_match = $res_mailsearch->fetchRow(DB_FETCHMODE_ASSOC);   
            
            if ($email_match)
            {
                die('Someone has already registered with that email address.');
            }
            
            $new_user = get_user($dbh, $_SESSION['user']['id']);
            
            $new_user['name'] = $_POST['username'];
            $new_user['email'] = $_POST['email'];
            $new_user['password'] = $_POST['password1'];
            
            $registered_user = set_user($dbh, $new_user);
            
            if ($registered_user === false)
            {
                die('User name exists.');
            }
            
            $hash = md5(rand(0,1000));
            $q = sprintf('UPDATE users SET hash=%s WHERE name=%s', $dbh->quoteSmart($hash), $dbh->quoteSmart($_POST['username']));
            $res = $dbh->query($q);   
            
            login_user_by_id($dbh, $registered_user['id']);
            
            $to = $_POST['email'];
            $subject = 'Field Papers Verification';
            
            $url = sprintf('http://%s%s/verify.php?email=%s&hash=%s',get_domain_name(),get_base_dir(),urlencode($_POST['email']),
            urlencode($hash));
            
            
            $message = "Thanks for signing up for Field Papers!
            
            Please verify your account: {$url}
            
            ";
            
            $headers = 'From:noreply@fieldpapers.org' . "\r\n";
            mail($to, $subject, $message, $headers);
            
            // redirect
            header('Location: ' . $_POST['redirect']);
            
            break;
        /*
        case 'log in':
            $registered_user = get_user_by_name($dbh, $_POST['username']);
            
            if (!$registered_user)
            {
                die('You are not registered.');
            }
            
            if (!check_user_password($dbh, $registered_user['id'], $_POST['password']))
            {
                die('That\'s not the correct password!');
            }
            
            login_user_by_name($dbh, $registered_user['name']);
            
            header('Location: ' . $_POST['redirect']);
        
            break;
            
        case 'log out':
            logout_user();
            
            header('Location: ' . $_POST['redirect']);
            
            break;
        */
    }
?>
<html>
    <head>
        <title>Register for Field Papers</title>
    <body>
    
    <? if(!is_logged_in()) { ?>            
        <b>Register</b><br /><br />
    
        <form id='register_form' method='POST' action='login.php'>
            Email: <input type='text' name='email'><br />
            Username: <input type='text' name='username'><br />
            Password: <input type='password' name='password1'><br />
            Password Again: <input type='password' name='password2'><br />
            <input type='submit' id="login_button" value='Register'>
            <input type='hidden' name='action' value='register'>
            
            <input type='hidden' name='redirect' value='http://fieldpapers.org/~mevans/fieldpapers/site/www/'>
            <!-- <input type='hidden' name='redirect' value=<?php echo "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];?>> -->
        </form>
    
    <? } ?>
    </body>
</html>