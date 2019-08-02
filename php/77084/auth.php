<?php

error_reporting ( E_ALL );

use Aero\Supports\{ Lerma, Akame };

session_start ();

require 'autoload.php';



if ( Akame :: ifRemember() )
{
	// u детектед
	echo json_encode ( [ 'content' => 'Ты авторизован' ], JSON_UNESCAPED_UNICODE );
}
elseif ( getenv ( 'REMOTE_ADDR' ) == 'POST' )
{
	/* $input = filter_input_array ( INPUT_POST, [
		'login' => FILTER_DEFAULT,
		'password' => FILTER_DEFAULT
	] ); */
	
	if ( empty ( $_POST['login'] ) || empty ( $_POST['password'] ) )
	{
		// Заполни поля login & password
		echo json_encode ( [ 'err' => [ 'message' => 'Заполни поля login & password' ] ], JSON_UNESCAPED_UNICODE );
	}
	elseif ( ( $lrm = Lerma :: prepare( 'SELECT id, login, password FROM usraccount WHERE login = ?', [ $_POST['login'] ] ) ) -> rowCount() < 1 )
	{
		// юзер не найден
		echo json_encode ( [ 'err' => [ 'message' => 'юзер не найден' ] ], JSON_UNESCAPED_UNICODE );
	}
	else
	{
		if ( Akame :: addRemember( $lrm, $_POST['password'] ) )
		{
			header ( 'Location: //php/77084/auth.php' );
			exit;
		}
		
		// иначе неправильный пароль, восстановить ? узузу(С)
		echo json_encode ( [ 'err' => [ 'message' => 'неправильный пароль, восстановить ?' ] ], JSON_UNESCAPED_UNICODE );
	}
}
else
{
	$form = <<<'EOT'
<form action = "//php/77084/auth.php" method = "post">
	<input type = "text" name = "login" placeholder = "Логин" required>
	<input type = "password" name = "password" placeholder = "Пароль" required>
	<input type = "submit">
</form>
EOT;

	echo json_encode ( [ 'content' => $form ], JSON_UNESCAPED_UNICODE );
}
# END
