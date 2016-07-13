<?php

define( 'NB_SLUG', 'jodysergisondev' );
define( 'NB_API_TOKEN', 'a178aafad502ac0311dca3fe2409cd5f23bd2eec41e1c2afa513cc745bb92cc4' );
class EVENT
{
	var $error;
	var $slug;
	var $api_token;
	var $status;
	var $event_id;

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

	public function create( $event = array( ) )
	{
		if ( !$this->is_valid_slug( ) )
			return false;
		$endpoint	= 'https://' . $this->slug . '.nationbuilder.com' . '/api/v1/sites/' . $this->slug . '/pages/events/?access_token=' . $this->api_token;
		if ( empty( $event ) )
			return false;
		$create = $this->curl( $endpoint, $event, 'POST' );
		if ( 201 == $this->status || 200 == $this->status )
		{
			$this->event_id = $create->event->id;
			return true;
		}
		return false;
	}

	public function update( $id, $event = array( ) )
	{
		if ( !$this->is_valid_slug( ) )
			return false;
		$endpoint	= 'https://' . $this->slug . '.nationbuilder.com' . '/api/v1/sites/' . $this->slug . '/pages/events/' . $id . '/?access_token=' . $this->api_token;
		if ( empty( $event ) )
			return false;
		$update = $this->curl( $endpoint, $event, 'PUT' );
		if ( 201 == $this->status || 200 == $this->status )
		{
			$this->event_id = $update->event->id;
			return true;
		}
		return false;
	}

	public function delete( $id )
	{
		if ( !$this->is_valid_slug( ) )
			return false;
		$endpoint	= 'https://' . $this->slug . '.nationbuilder.com' . '/api/v1/sites/' . $this->slug . '/pages/events/' . $id . '/?access_token=' . $this->api_token;
		$data		= array( );
		$delete = $this->curl( $endpoint, $data, 'DELETE' );
		if ( 204 == $this->status )
			return true;
		return false;
	}

}
$mode_type		= 'create_event';
if ( $_POST ):
	$mode	= $_POST['mode'];
	$event = new EVENT( );
	switch ( $mode ):
		case 'create_event':
			$event_name	= $_POST['event_name'];
			$status			= $_POST['status'];
			if ( $event_name ):
				$start_time	= date( 'c', strtotime( date( 'Y-m-d H:i:s' ) ) );
				$end_time	= date( 'c', strtotime( date( 'Y-m-d H:i:s' ) . ' +1 day' ) );
				$details = array(
					'event' => array(
						'status'		=> $status,
						'name'			=> $event_name,
						'start_time'	=> $start_time,
						'end_time'		=> $end_time,
					)
				);
				$new_event = $event->create( $details );
				if ( $new_event ):
					$id				= $event->event_id;
					$mode_type		= 'update_event';
				endif;
			else:

			endif;
			break;
		case 'update_event':
			$mode_type		= 'update_event';
			$id				= $_POST['id'];
			$event_name		= $_POST['event_name'];
			$start_time		= $_POST['start_time'];
			$end_time		= $_POST['end_time'];
			$headline		= $_POST['headline'];
			$intro			= $_POST['intro'];
			$status			= $_POST['status'];
			if ( $id && $headline && $intro ):
				$details = array(
					'event' => array(
						'status'		=> $status,
						'name'			=> $event_name,
						'start_time'	=> $start_time,
						'end_time'		=> $end_time,
						'headline'		=> $headline,
						'intro'			=> $intro,
					)
				);
				$update_event = $event->update( $id, $details );
				if ( $update_event ):
					$mode_type		= 'delete_event';
				endif;
			else:

			endif;
			break;
			case 'delete_event':
				$mode_type		= 'delete_event';
				$id				= $_POST['id'];
				if ( $id  ):
					$delete_event = $event->delete( $id );
					if ( $delete_event ):
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
  margin: 0 auto;
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
                <p class="text-success">You have deleted your event</p>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <form action="" method="post">
    <input type="hidden" name="mode" value="<?php echo $mode_type; ?>" />

    	<?php
        switch ( $mode_type ):
        	case 'create_event':
		?>
		<div class="row">
				<div class="col-md-12">
						<h1>Create your Event</h1>
				</div>
		</div>
        <div class="row">
            <div class="col-md-12">
                    <input class="form-control" type="text" name="event_name" value="<?php echo $event_name; ?>" placeholder="Enter the name of your event" maxlength="64" />
            </div>
						<div class="col-md-12">
                <label>Status *
                    <select name="status">
                    	<option value="unlisted" <?php if ( 'unlisted' == $status ): ?>selected<?php endif; ?>>Unlisted</option>
                        <option value="published" <?php if ( 'published' == $status ): ?>selected<?php endif; ?>>Published</option>
                    </select>
                </label>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary btn-lg btn-block">Create</button>
            </div>
        </div>
        <?php
       		break;
		case 'update_event':
		?>
		<div class="row">
				<div class="col-md-12">
						<h1>Edit your Event</h1>
				</div>
		</div>
        <div class="row">
            <input type="hidden" name="id" value="<?php echo $id; ?>" />
            <input type="hidden" name="start_time" value="<?php echo $start_time; ?>" />
            <input type="hidden" name="end_time" value="<?php echo $end_time; ?>" />
						<div class="col-md-12">
                    <input class="form-control" type="text" name="event_name" value="<?php echo $event_name; ?>" placeholder="Edit the name of your event" maxlength="64" />
            </div>
            <div class="col-md-12">
                    <input class="form-control" type="text" name="headline" value="<?php echo $headline; ?>" placeholder="Enter your event headline" maxlength="64" />
            </div>
            <div class="col-md-12">
                    <input  class="form-control"type="text" name="intro" value="<?php echo $intro; ?>" placeholder="Enter your event intro" maxlength="64" />
            </div>
            <div class="col-md-12">
                <label>Status *
                    <select name="status">
                    	<option value="unlisted" <?php if ( 'unlisted' == $status ): ?>selected<?php endif; ?>>Unlisted</option>
                        <option value="published" <?php if ( 'published' == $status ): ?>selected<?php endif; ?>>Published</option>
                    </select>
                </label>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary btn-lg btn-block">Update</button>
            </div>
        </div>
				<?php
					break;
		case 'delete_event':
		?>
		<div class="row">
				<div class="col-md-12">
						<h1>Finally delete what you made</h1>
				</div>
		</div>
        <input type="hidden" name="id" value="<?php echo $id; ?>" />
        <div class="row">
            <div class="col-md-12">
                <button type="submit" class="btn btn-danger btn-lg btn-block">DELETE</button>
            </div>
        </div>
        <?php
       		break;
		endswitch;
		?>
    </form>
</div>
</body>
</html>
