<?php

namespace Aero\Supports;

use Aero;
use Aero\Supports\Lerma;

final class Akame extends Aero\Authentication\AuthContract
{
	# проверка юзера на ремку
	public static function ifRemember(): bool
	{
		// return $_SESSION['logged'] ?: static :: instance() -> logged;
		
		return static :: instance() -> ifLogged;
	}
	
	# сохранить аутентифицированного юзера на одну интерацию пхп
	/* public static function once( array $items ): bool
	{
		if ( isset ( $items['email'], $items['password'] ) )
		{
			static :: instance() -> AuthEmail( $items['email'], $items['password'], false );
			
			return 
		}
		
		if ( isset ( $items['login'], $items['password'] ) )
		{
			static :: instance() -> AuthLogin( $items['login'], $items['password'], false );
			
			return 
		}
		
		return false;
	} */
	
	public static function addRemember( Lerma $lerma, string $password ): bool
	{
		[ 
			'id' => $id,
			static :: instance( false ) -> config -> unique => $unique, 
			'password' => $password_hash,
		] = $lerma -> fetch( Lerma :: FETCH_ASSOC );
		
		if ( password_verify ( $password, $password_hash ) )
		{
			$new_hash = md5 ( $id - 1 . $unique . ( $password_hash = password_hash ( $password, PASSWORD_DEFAULT ) ) );
			
			Lerma :: query( [ 'UPDATE `%s` SET password = "%s", hash = "%s", remember = 1, online = UNIX_TIMESTAMP( now() ) WHERE id = %d',
				static :: instance( false ) -> config -> table,
				$password_hash, 
				$new_hash, 
				$id 
			] );
			
			setcookie ( static :: instance( false ) -> config -> cookie, $new_hash, strtotime ( static :: instance( false ) -> config -> duration ), '/' );
			
			return true;
		}
		
		return false;
	}
}