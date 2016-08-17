<div id="center-block">
<?
$error = $_SESSION['error'];
$username_value = '';
$password_value = '';
if( isset($error) )
{
    echo '<div class="message">';
    foreach($error['messages'] as $message)
    {
        echo '<p>'.$message.'</p>';
    }
    echo '</div>';

    $username_value = ' value="'.$error['username'].'"';
    if( $error['focus'] == 'username' ) { $username_value .= ' autofocus="true"'; }
    if( $error['focus'] == 'password' ) { $password_value .= ' autofocus="true"'; }
}
unset($_SESSION['error']);
?>
    <form class="login-form" action="act/login.php" method="post">
        <label>帳號<input type="text" name="username"<?=$username_value?> /></label>
        <label>密碼<input type="password" name="password"<?=$password_value?> /></label>
        <button class="singleton" type="submit">登入</button>
    </form>
</div>