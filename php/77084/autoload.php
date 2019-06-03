<?php

spl_autoload_register ( function ( $name )
{
	$replaces = [ 
		'\\' => DIRECTORY_SEPARATOR, 
		
		# Aero Lerma
		'Aero\\Supports\\Lerma' => 'src/Lerma/Supports/Lerma',
		'Aero\\Database' => 'src/Lerma/Database',
		
		# Aero Akame
		'Aero\\Supports\\Akame' => 'src/Auth/Supports/Akame',
		'Aero\\Authentication' => 'src/Auth/Authentication'
	];
	
	include strtr ( $name, $replaces ) . '.php';
} );