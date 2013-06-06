<?php
/*
    Login Template for the default admin area

    This does no use the header or footer templates, thus keeping the loading as light weight as possible.
*/
    echo 'Login Page!';

    $bcrypt    = Registry::getObject('bcrypt');
    $sanitizor = Registry::getObject('sanitizor');
    $validator = Registry::getObject('validator');
    $html      = Registry::getObject('html');

    $formFields = array(
        'email' => array(
            'element' => 'input',
            'label' => 'Email',
            'id' => 'email',
            'name' => 'email',
            'type' => 'text',
            'placeholder' => 'john.doe@gmail.com',
            'value' => $sanitizor->sanitizePOST( 'email', 'plain' ),
            'validate' => array(
                'name' => 'Email',
                'value' => $sanitizor->sanitizePOST( 'email', 'plain' ),
                'validations' => array( 'required' )
            )
        ),
    'password' => array(
            'element' => 'input',
            'label' => 'Password',
            'id' => 'password',
            'name' => 'password',
            'type' => 'text',
            'placeholder' => '',
            'value' => '',
            'validate' => array(
                'name' => 'Password',
                'value' => $sanitizor->sanitizePOST( 'password', 'str' ),
                'validations' => array( 'required' )
            )
        ),
        'submit_button' => array(
            'element' => 'button',
            'type' => 'submit',
            'class' => 'pure-button pure-input-1 notice',
            'text' => 'Log In'
        )
    );
?>

<html>
    <head>
        [[sb.css]]
    </head>
    <body>
        <form id="form-login" class="pure-form pure-form-stacked" action="/admin/login.php" method="POST">
            <?php foreach( $formFields as $field ) { ?>
            <div class="pure-control-group">
                <?php echo $html->generateElement( $field ) ?>
            </div>
            <?php } ?>
        </form>
    </body>
</html>
