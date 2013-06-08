<?php
$template = Registry::getObject('template');
$template->getPage()->setTitle( 'Login' );

$sanitizor = Registry::getObject('sanitizor');

$formFields = array(
    'username' => array(
        'element' => 'input',
        'label' => 'Username',
        'id' => 'username',
        'name' => 'username',
        'type' => 'text',
        'value' => $sanitizor->sanitizePOST( 'username', 'plain' ),
        'validate' => array(
            'name' => 'Username',
            'value' => $sanitizor->sanitizePOST( 'username', 'plain' ),
            'validations' => array( 'required' )
        )
    ),
'password' => array(
        'element' => 'input',
        'label' => 'Password',
        'id' => 'password',
        'name' => 'password',
        'type' => 'password',
        'validate' => array(
            'name' => 'Password',
            'value' => $sanitizor->sanitizePOST( 'password', 'str' ),
            'validations' => array( 'required' )
        )
    )
);

$formControls = array (
    'submit_button' => array(
        'element' => 'button',
        'type' => 'submit',
        'class' => 'pure-button notice',
        'text' => 'Log In'
    )
);

if( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
    if( $errors = Registry::getObject('validator')->validate( $formFields ) ) {
        $page = $template->getPage();
        foreach( $errors as $key => $errorData ) {
            $page->addErrorTag( $key, implode( "</br>", $errorData ) );
        }
    } else {

        $sql = <<<SQL
SELECT username, password
FROM sb_member
WHERE username = :username
SQL;
        $sth = Registry::getObject('db')->getActiveConnection()->prepare( $sql );
        $sth->execute(
            array( 'username' => $formFields['username']['value'] )
        );

        if( $sth->rowCount() === 1 ) {
            $row = $sth->fetch(PDO::FETCH_ASSOC);
            if( Registry::getObject('bcrypt')->verify( $formFields['password']['validate']['value'], $row['password'] ) ) {
                $_SESSION['logged_in'] = true;
                header( 'Location: ' . Registry::getSetting('site_url') . 'admin/' );
            } else {
                $_SESSION['logged_in'] = false;
            }
        } else {
            Registry::logIt( 'LOGIN: error authenticating user, to many results returned from lookup' );
        }
    }
}

$content = '
<html>
    <title></title>
    <head>
        [[sb.css]]
    </head>
    <body>
        <form id="form-login" class="pure-form pure-form-stacked" action="/admin/index.php" method="POST">
            <h1>Dashboard Login</h1>
';

    foreach( $formFields as $field ) {
        $content .= '<div class="pure-control-group">' . Registry::getObject('html')->generateElement( $field ) . '</div>';
    }

    $content .= '<div class="pure-controls">';
    foreach( $formControls as $control ) {
        $content .= Registry::getObject('html')->generateElement( $control );
    }
    $content .= '</div>';

$content .= '
        </form>
    </body>
</html>
';

// parse it all, and spit it out
$template->getPage()->setContent( $content );
$template->parseOutput();
print $template->getPage()->getContent();
?>
