<?php

define( 'NB_SLUG', 'jodysergisondev' );
define( 'NB_API_TOKEN', 'a178aafad502ac0311dca3fe2409cd5f23bd2eec41e1c2afa513cc745bb92cc4' );
class PEOPLE
{
	var $error;
	var $slug;
	var $api_token;
	var $status;
	var $person_id;

	function __construct( )
	{
		$this->slug			= ( defined( 'NB_SLUG' ) ) ? NB_SLUG : false;
		$this->api_token	= ( defined( 'NB_API_TOKEN' ) ) ? NB_API_TOKEN : false;;
	}

	private function is_valid_slug( )
	{
		if ( !$this->slug || !$this->api_token )
		{
			$this->error = 'The NB Slug or API Token is not configured.';
			return false;
		}
		return true;
	}

	private function curl( $url, $data = array( ), $crud = 'POST' )
	{
		$header[] = 'Content-Type: application/json';
		$ch = curl_init( );
		if ( !empty( $data ) )
		{
			$data_json = json_encode( $data );
			$header[] = 'Content-Length: ' . strlen( $data_json );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $data_json );
		}
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
		curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, $crud );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_USERAGENT, 'S1980' );
		$response  = curl_exec( $ch );
		$this->status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		if ( $response === false )
		{
			$this->error = 'Curl error: ' . curl_error( $ch );
			return false;
		}
		return json_decode( $response );
	}

	public function create( $person = array( ) )
	{
		if ( !$this->is_valid_slug( ) )
			return false;
		$endpoint	= 'https://' . $this->slug . '.nationbuilder.com' . '/api/v1/people/push/?access_token=' . $this->api_token;
		if ( empty( $person ) )
			return false;
		$create = $this->curl( $endpoint, $person, 'PUT' );
		if ( 201 == $this->status || 200 == $this->status )
		{
			$this->person_id = $create->person->id;
			return true;
		}
		return false;
	}

	public function update( $id, $person = array( ) )
	{
		if ( !$this->is_valid_slug( ) )
			return false;
		$endpoint	= 'https://' . $this->slug . '.nationbuilder.com' . '/api/v1/people/' . $id . '/?access_token=' . $this->api_token;
		if ( empty( $person ) )
			return false;
		$update = $this->curl( $endpoint, $person, 'PUT' );
		if ( 201 == $this->status || 200 == $this->status )
		{
			$this->person_id = $update->person->id;
			return true;
		}
		return false;
	}

	public function delete( $id )
	{
		if ( !$this->is_valid_slug( ) )
			return false;
		$endpoint	= 'https://' . $this->slug . '.nationbuilder.com' . '/api/v1/people/' . $id . '/?access_token=' . $this->api_token;
		$data		= array( );
		$delete = $this->curl( $endpoint, $data, 'DELETE' );
		if ( 204 == $this->status )
			return true;
		return false;
	}

}
$mode_type		= 'create_person';
if ( $_POST ):
	$mode	= $_POST['mode'];
	$people = new PEOPLE( );
	switch ( $mode ):
		case 'create_person':
			$first_name	= $_POST['first_name'];
			$last_name	= $_POST['last_name'];
			$email	= $_POST['email'];
			if ( $first_name && $last_name ):
				$person = array(
					'person' => array(
						'first_name'	=> trim( $first_name ),
						'last_name'		=> trim( $last_name ),
						'full_name'		=> trim( $first_name . ' ' . $last_name ),
						'email'				=> trim( $email ),
					)
				);
				$new_person = $people->create( $person );
				if ( $new_person ):
					$id				= $people->person_id;
					$mode_type		= 'update_person';
				endif;
			else:

			endif;
			break;
		case 'update_person':
			$mode_type		= 'update_person';
			$id				= $_POST['id'];
			$email	= $_POST['email'];
			if ( $id && $email ):
				$updated_person = array(
					'person' => array(
						'email'	=> trim( $email ),
					)
				);
				$update_person = $people->update( $id, $updated_person );
				if ( $update_person ):
					$mode_type		= 'delete_person';
				endif;
			else:

			endif;
			break;
		case 'delete_person':
			$mode_type		= 'delete_person';
			$id				= $_POST['id'];
			if ( $id  ):
				$delete_person = $people->delete( $id );
				if ( $delete_person ):
					$complete 		= true;
					$mode_type		= NULL;
				endif;
			else:

			endif;
			break;

	endswitch;
endif;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">

<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>

<style type="text/css">
.container {
  min-height: 100%;
 }
 .col-md-12, .col-md-6 {
	 margin-bottom:10px !important;
 }
 .form-control {
    height: 50px;
    font-size: 25px;
	}
</style>
</head>
<body>
<div class="container">
    <?php if ( $complete ): ?>
    <div class="row">
        <div class="col-md-12">
            <div class="callout success">
                <h1>Congratulations</h1>
                <p class="text-success">You have successfully created, updated and delete a person in your Nation.</p>
            </div>
        </div>
    </div>
    <?php else: ?>
    <form action="" method="post">
    <input type="hidden" name="mode" value="<?php echo $mode_type; ?>" />

    	<?php
        switch ( $mode_type ):
        	case 'create_person':
		?>
		<div class="row">
				<div class="col-md-12">
						<h1>Create your new member</h1>
				</div>
		</div>
			  <div class="row">
            <div class="col-md-6">

                    <input class="form-control" type="text" name="first_name" value="<?php echo $first_name; ?>" placeholder="First Name" maxlength="64" />
            </div>
            <div class="col-md-6">
                    <input class="form-control" type="text" name="last_name" value="<?php echo $last_name; ?>" placeholder="Last Name" maxlength="64" />
            </div>
        </div>

				<div class="row">
						<div class="col-md-12">
										<input class="form-control" type="text" name="email" value="<?php echo $email; ?>" placeholder="Email" maxlength="64" />
						</div>
				</div>

        <div class="row">
            <div class="col-md-12">
                <button type="submit" class="btn btn-lg btn-primary btn-block">Create</button>
            </div>
        </div>

        <?php
       		break;
		case 'update_person':
		?>
		<div class="row">
				<div class="col-md-12">
						<h1>Now update the email address</h1>
				</div>
		</div>
        <div class="row">
            <input type="hidden" name="id" value="<?php echo $id; ?>" />
            <div class="col-md-12">
                    <input class="form-control" type="text" name="email" value="<?php echo $email; ?>" placeholder="Update Email" maxlength="64" />
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <button type="submit" class="btn btn-lg btn-primary btn-block">Update</button>
            </div>
        </div>
        <?php
       		break;
		case 'delete_person':
		?>
		<div class="row">
				<div class="col-md-12">
						<h1>Finally delete what you made</h1>
				</div>
		</div>
        <input type="hidden" name="id" value="<?php echo $id; ?>" />
        <div class="row">
            <div class="col-md-12">
                <button type="submit" class="btn btn-lg btn-danger btn-block">DELETE</button>
            </div>
        </div>
        <?php
        	break;
		endswitch;
		?>

    </form>
    <?php endif; ?>
</div>
</body>
</html>
