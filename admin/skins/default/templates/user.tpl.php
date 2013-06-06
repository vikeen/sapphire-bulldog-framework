<?php
    // store core modules needed for this
    $sanitizor = Registry::getObject( 'sanitizor' );
    $dbh = Registry::getObject('db')->getActiveConnection();
    $bcrypt = Registry::getObject('bcrypt');
    $html = Registry::getObject('html');

    $formFields = array(
        'first_name' => array(
            'element' => 'input',
            'label' => 'First Name',
            'id' => 'first_name',
            'name' => 'first_name',
            'type' => 'text',
            'placeholder' => 'John',
            'value' => $sanitizor->sanitizePOST( 'first_name', 'ucfirst' ),
            'validate' => array(
                'name' => 'First Name',
                'value' => $sanitizor->sanitizePOST( 'first_name', 'ucfirst' ),
                'validations' => array( 'required' )
            )
        ),
        'middle_initial' => array(
            'element' => 'input',
            'label' => 'Middle Initial',
            'id' => 'middle_initial',
            'name' => 'middle_initial',
            'type' => 'text',
            'placeholder' => 'D',
            'value' => $sanitizor->sanitizePOST( 'middle_initial', 'ucfirst' ),
            'maxlength' => 1,
            'validate' => array(
                'name' => 'Middle Initial',
                'value' => $sanitizor->sanitizePOST( 'middle_initial', 'ucfirst' ),
                'validations' => array( 'required' )
            )
        ),
        'last_name' => array(
            'element' => 'input',
            'label' => 'Last Name',
            'id' => 'last_name',
            'name' => 'last_name',
            'type' => 'text',
            'placeholder' => 'Doe',
            'value' => $sanitizor->sanitizePOST( 'last_name', 'ucfirst' ),
            'validate' => array(
                'name' => 'Last Name',
                'value' => $sanitizor->sanitizePOST( 'last_name', 'ucfirst' ),
                'validations' => array( 'required' )
            )
        ),
        'email' => array(
            'element' => 'input',
            'label' => 'Email',
            'id' => 'email',
            'name' => 'email',
            'type' => 'email',
            'placeholder' => 'john.doe@gmail.com',
            'value' => $sanitizor->sanitizePOST( 'email', 'plain' ),
            'confirmation' => array(
                'value' => $sanitizor->sanitizePOST( 'confirm_email', 'str' ),
            ),
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
            'type' => 'password',
            'value' => '',
            'confirmation' => array(
                'value' => '',
            ),
            'validate' => array(
                'name' => 'Password',
                'value' => $sanitizor->sanitizePOST( 'password', 'str' ),
                'validations' => array( 'required' )
            )
        ),
        'reset_button' => array(
            'element' => 'button',
            'type' => 'reset',
            'class' => 'pure-button pure-input-1-3 notice',
            'text' => 'Reset'
        ),
        'submit_button' => array(
            'element' => 'button',
            'type' => 'submit',
            'class' => 'pure-button pure-input-1-3 notice',
            'text' => 'Add User'
        )
    );

    $validationError = false;
    if( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
        $validator = Registry::getObject('validator');
        $errors = $validator->validate( $formFields );
        if( count($errors) > 0 ) {
            $page = Registry::getObject('template')->getPage();
            foreach( $errors as $key => $errorData ) {
                $page->addErrorTag( $key, implode( "</br>", $errorData ) );
            }
            $validationError = true;
        }

        if( ! $validationError ) {
            $sqlBinds = array (
                'first_name'       => $formFields['first_name']['value'],
                'middle_initial'   => $formFields['middle_initial']['value'],
                'last_name'        => $formFields['last_name']['value'],
                'email'            => $formFields['email']['value'],
                'password'         => $bcrypt->hash($formFields['password']['validate']['value']),
                'member_status_id' => 0
            );

            $sql = <<<SQL
INSERT INTO sb_member ( first_name, middle_initial, last_name, email, password, member_status_id )
VALUES ( :first_name, :middle_initial, :last_name, :email, :password, :member_status_id )
SQL;
            $sth = $dbh->prepare( $sql );
            $sth->execute( $sqlBinds );

            // we have added our user ... remove the job tag
            Registry::storeSetting( array( 'page_job' => '' ) );
            header( 'Location: /admin/?page=user' ) ;
        }
    }
    if( strtolower(Registry::getSetting('page_job')  === 'insert' ) ) {
?>
        <form id="form-user-add" class="pure-form pure-form-aligned" action="/admin/?page=user&job=insert" method="POST">
            <?php foreach( $formFields as $field ) { ?>
            <div class="pure-control-group">
                <?php echo $html->generateElement( $field ) ?>
            </div>
            <?php } ?>
        </form>
<?php
    } else {
        $sql = <<<SQL
SELECT m.first_name as 'First Name',
       m.last_name as 'Last Name',
       m.middle_initial as 'Middle Initial',
       m.email as 'Email',
       '*****' as 'Password',
       ms.text as 'Account Status'
FROM sb_member m,
     sb_r_member_status ms
WHERE ( m.member_status_id = ms.member_status_id )
SQL;

        $sth = $dbh->prepare( $sql );
        $sth->execute();
        $data = $sth->fetchAll(PDO::FETCH_ASSOC);
        $table = array(
            'element' => 'table',
            'id' => 'user_table',
            'class' => 'pure-table pure-table-horizontal',
            'row_class' => array( 'odd' => 'pure-table-odd' ),
            'data' => $data,
        );

        echo $html->generateElement( $table );
    }
?>
