<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>MouseZver</title>
		<script src = "//php/77084/jquery-3.1.0.js"></script>
		<style>
			body { background-color:#595959;margin: 0 auto; margin-top: 50px; width: 1000px; }
			input[type=text], input[type=password] { border: 1px solid red; border-radius: 3px; display: block; margin: 5px 2px;  }
			.errorPanel { color: red }
		</style>
		<script>
			$(function()
			{
				$( 'body' ).on( 'submit', 'form', function ( e )
				{
					e.preventDefault();
		
					var obj = new FormData( $( this ).get(0) );
					
					$.ajax(
					{
						url: $( this ).attr( 'action' ),
						type: $( this ).attr( 'method' ),
						contentType: false,
						processData: false,
						data: obj,
						dataType: 'JSON',
						success: function ( json )
						{
							$( '.errorPanel' ).remove();
							
							if ( typeof json.err !== 'undefined' )
							{
								$( '.content' ).prepend( '<div class = "errorPanel">' + json.err.message + '</div>' );
							}
							else if ( typeof json.content !== 'undefined' )
							{
								$( '.content' ).html( json.content );
							}
						}
					})
				})
			})
		</script>
	</head>
	<body >
		<div class = "content" align = "center">
			<!-- content -->
		</div>
	</body>
	<script>
		$(function()
		{
			$.get( '//php/77084/auth.php', function( data )
			{
				if ( typeof ( obj = JSON.parse( data ) ).err === 'undefined' )
				{
					if ( typeof obj.content !== 'undefined' )
					{
						$( '.content' ).html( obj.content );
					}
				}
			})
		})
	</script>
</html>