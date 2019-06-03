<?php

namespace Aero\Authentication;

use Aero\Supports\{ Lerma, Akame };

class AuthContract
{
	protected static $instance;
	
	protected $ifCookie, $ifHash, $ifLogged, $config, $sync;
	
	protected static function instance( bool $load = true ): Akame
	{
		return static :: $instance ?: ( static :: $instance = ( new static ) -> load( 'conf.php', $load ) );
	}
	
	protected function load( string $config, bool $load ): Akame
	{
		$this -> config = json_decode ( include $config );
		
		if ( $load ) // с этой хренью надо заняться
		{
			Lerma :: query( [ 'UPDATE `%s` SET `hash` = null WHERE `online` <= %d', 
				$this -> config -> table,
				strtotime ( strtr ( $this -> config -> duration, '+', '-' ) ) 
			] );
			
			$this -> ifCookie = $this -> ifCookie();
			$this -> ifHash = $this -> ifHash();
			$this -> ifLogged = $this -> ifLogged();
		}
		
		return $this;
	}
	
	protected function ifCookie(): bool
	{
		return isset ( $_COOKIE[$this -> config -> cookie] );
	}
	
	protected function ifHash(): bool
	{
		if ( $this -> ifCookie )
		{
			$this -> sync = Lerma :: prepare( [ 'SELECT `id`, `%s`, `password` FROM `%s` WHERE hash = ? AND remember = 1 LIMIT 1', 
					$this -> config -> unique,
					$this -> config -> table 
				], 
				[ $_COOKIE[$this -> config -> cookie] ] 
			);
			
			return $this -> sync -> rowCount() > 0;
		}
		
		return false;
	}
	
	protected function ifLogged(): bool
	{
		if ( isset ( $_SESSION['logged'] ) )
		{
			return $this -> ifHash ?: $this -> AuthMe();
		}
		
		if ( $this -> ifHash )
		{
			return $this -> AuthMe();
		}
		elseif ( $this -> ifCookie )
		{
			$this -> dropCookie(); // !!!!!!!!!!
		}
		
		return false;
	}
	
	/* protected function setFlags(): bool
	{
		if ( $this -> ifHash )
		{
			$this -> username = $_SESSION['username'];
			$this -> status = $_SESSION['status'];
			
			return true;
		}
		else
		{
			return $this -> AuthMe();
		}
		
		return $this -> ifHash ?: $this -> AuthMe();
	} */
	
	protected function AuthMe(): bool
	{
		if ( isset ( $_SESSION['logged'], $_SESSION['id'] ) )
		{
			if ( !$this -> ifHash )
			{
				$this -> sync = Lerma :: query( [ 'SELECT `id`, `%s`, `password` FROM `%s` WHERE `id` = %d AND `remember` = 1', 
					$this ->config -> unique,
					$this ->config -> table,
					$_SESSION['id']
				] );
				
				if ( $this -> sync -> rowCount() < 1 )
				{
					$params = session_get_cookie_params ();
					
					setcookie ( session_name (), '', time () - 42000, 
						$params['path'], $params['domain'], $params['secure'], $params['httponly']
					);
					
					session_destroy ();
					
					return false;
				}
				
				[ 
					'id' => $id,
					$this -> config -> unique => $unique, 
					'password' => $password_hash,
				] = $this -> sync -> fetch( Lerma :: FETCH_ASSOC );
				
				Lerma :: query ( [ 'UPDATE `%s` SET online = UNIX_TIMESTAMP( now() ), hash = "%s" WHERE id = ' . $id,
					$this -> config -> table,
					$hash = md5 ( $id - 1 . $unique . $password_hash )
				] );
				
				setcookie ( $this -> config -> cookie, $hash, strtotime ( $this -> config -> duration ), '/' );
			}
		}
		elseif ( $this -> ifHash )
		{
			[ 
				'id' => $id,
				$this -> config -> unique => $unique, 
				'password' => $password_hash,
			] = $this -> sync -> fetch( Lerma :: FETCH_ASSOC );
			
			Lerma :: query ( [ 'UPDATE `%s` SET online = UNIX_TIMESTAMP( now() ) WHERE id = ' . $id, $this -> config -> table ] );
		}
		else
		{
			$this -> dropCookie();
			
			return false;
		}
		
		
		
		$_SESSION['id'] = $id;
		/* $this -> username = $_SESSION['username'] = $account -> username;
		$this -> status = $_SESSION['status'] = $account -> status; */
		
		return $_SESSION['logged'] = true;
	}
	
	protected function dropCookie()
	{
		setcookie ( $this -> config -> cookie, '', time () - 3600, '/' );
	}
}