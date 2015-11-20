<?php


class CustomLogger extends CLogRoute
{
	
	public function init()
	{
		// Unused init method.
	}
	
	protected function processLogs($logs)
	{
		if(sizeof($logs))
		{
			foreach($logs as $log)
			{
				$message='';
				
				// If we have an error that's not a 404
				if($log[1] == 'error' && !preg_match('@404@', $log[2]) && !preg_match('@CSRF@', $log[0]))
				{
					// Break up the log message into chunks
					$message = explode('Stack trace:', $log[0]);
					
					// First chunk is the error itself
					$error['error'] = $message[0];
					
					// Start a blank stack stack variable
					$error['stackTrace'] = '';
					
					// Loop through the lines in the stack trace and drop them into paragraphs
					foreach(preg_split("/(\r?\n)/", $message[1]) as $line)
					{
						$error['stackTrace'] .= '<p>'.$line.'</p>';
					}
					
					// Error Level
					$error['level'] = $log[1];
					
					// Error Category
					$error['category'] = $log[2];
					
					// Add the time
					$error['time'] = date('jS F Y - H:i:s', $log[3]);
					
					// Start to build the email
					$message = '<html><head></head><body style="font-family: Helvetica; font-size: 13px;">
			<div style="padding: 10px 20px;">';
					
					$message .= '<h1><strong>'.ucfirst($error['level']).' - '.$error['category'].'</strong></h1>';
					
					$message .= '<p style="color: #666;">'.$error['time'].'<p>';
					
					$message .= '<p style="font-weight:bold;">'.$error['error'].'<p>';
					$message .= '<p>'.$error['stackTrace'].'<p>';
					
					$message .= '<p>'.$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'].'<p></div>';
					
					// To send HTML mail, the Content-type header must be set
					$headers  = 'MIME-Version: 1.0' . "\r\n";
					$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
					
					// Additional headers
					$headers .= 'From: Error Reporter <'.Yii::app()->params['fromEmail'].'>' . "\r\n";
					
					// Send it
					mail(Yii::app()->params['adminEmail'], 'Web Application Error - ' . Yii::app()->name, $message, $headers);
				}
			}
		}
	}
}

?>