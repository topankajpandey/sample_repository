<?php

/*
 * Function to send Email 
 */

function email_send_function($to, $sub, $msg) {
    $CI = & get_instance();
    $CI->load->library('email');
    $config['protocol'] = "smtp";
    $config['smtp_host'] = "ssl://smtp.gmail.com";
    $config['smtp_port'] = "465";
    $config['smtp_user'] = "testinguser231@gmail.com";
    $config['smtp_pass'] = "testinguser";
    $config['charset'] = "utf-8";
    $config['mailtype'] = "html";
//    $config['newline'] = "\r\n";
    $config['crlf'] = "\r\n";      //should be "\r\n"
    $config['newline'] = "\r\n";   //should be "\r\n"
    $config['validation'] = TRUE;
    $CI->load->library('email', $config);
    $CI->email->set_mailtype("html");
    $CI->email->from('noreply@faveapp.com', 'FaveApp');
    $CI->email->to($to);
    $CI->email->subject($sub);
    $CI->email->message(mailheader() . $msg . mailfooter());
    if ($CI->email->send()) {
        return true;
    }
    return false;
}

function mailheader() {
    return '<!DOCTYPE html>
			<html>
<body style="font-family: Arial;">
<table style="width:600px;max-width:100%;margin:0 auto;">
<tr>
<td>
<table style="width:100%;margin:5px 0 0 0;padding:10px 0px;background-color:#1f1f1f; border-radius:4px 4px 0 0;">
<tr>
 <td colspan="4" style="text-align:left;color:#fff;font-size:20px;font-weight:bold;padding-left:10px; text-transform: uppercase">
  <img src="http://dev614.trigma.us/favapp/assets/admin/images/logo.png" />
 </td>
 <td style="text-align:left;color:#fff;font-size:20px;font-weight:bold;padding-left:10px; text-transform: uppercase">
  &nbsp;
 </td>
</tr>
</table>
</td>
</tr>';
}

function mailfooter() {
    return ' <tr style="width:100%; height:auto;"><td style="float:left;"><p style="color:#000;padding:12px;">
  <b>Thanks,<br/>
Support Team </b>
  </p></td></tr>
</table>
</td>
</tr>
                       <tr>
<td>
<table style="margin:0; padding:10px 0px;background-color:#1f1f1f; width:100%; border-radius:0 0 4px 4px;">
<tr>
 <td colspan="4" style="text-align:left;color:#fff;font-size:15px;padding-left:10px; text-align:center;">
  Fave App
 </td>
</tr>
</table>
</td>
</tr>
</table>
		 </body>
		</html>	';
}

/* for time ago displya function */

function time_ago($date) {

    if (empty($date)) {

        return "No date provided";
    }

    $periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");

    $lengths = array("60", "60", "24", "7", "4.35", "12", "10");

    $now = time();

    $unix_date = strtotime($date);

    // check validity of date

    if (empty($unix_date)) {

        return "Bad date";
    }

    // is it future date or past date

    if ($now > $unix_date) {

        $difference = $now - $unix_date;

        $tense = "ago";
    } else {

        $difference = $unix_date - $now;
        $tense = "from now";
    }

    for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths) - 1; $j++) {

        $difference /= $lengths[$j];
    }

    $difference = round($difference);

    if ($difference != 1) {

        $periods[$j].= "s";
    }

    return "$difference $periods[$j] {$tense}";
}

// Function to send Push Notification
function send_notification($registatoin_ids, $message) {

    //$apiKey = 'AIzaSyBrCBdu35JuW6QLqDm6syzMjpXRWPf-h0g';
    //echo "<pre>";
    // print_r($message);
    // $apiKey = 'AIzaSyAqnH1LCtTB3afzboYVNbs8vOHCe2kWCXw';
    $apiKey = 'AIzaSyCafUUgPMWeHaMkoH7dKDq7A0b3noOzHlU';
    // Set POST variables
    $url = 'https://android.googleapis.com/gcm/send';

    if ($message['user_type'] == 1) {
        if ($message['job_type'] == 'apply_job' || $message['job_type'] == 'pause_job' || $message['job_type'] == 'resume_job') {
            $message['notification_type'] = 1;
            $fields = array(
                'registration_ids' => $registatoin_ids,
                'data' => $message
            );
        } else if ($message['job_type'] == 'start_job') {
            $message['notification_type'] = 2;
            $fields = array(
                'registration_ids' => $registatoin_ids,
                'data' => $message
            );
        } else {
            $message['notification_type'] = 3;
            $fields = array(
                'registration_ids' => $registatoin_ids,
                'data' => $message
            );
        }
    } else {
        $message['notification_type'] = 'customer';
        $fields = array(
            'registration_ids' => $registatoin_ids,
            'data' => $message
        );
    }




    $headers = array(
        'Authorization: key=' . $apiKey,
        'Content-Type: application/json'
    );
    // Open connection
    $ch = curl_init();
    // Set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Disabling SSL Certificate support temporarly
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

    // Execute post
    $result = curl_exec($ch);
    if ($result === FALSE) {
        // die('Curl failed: ' . curl_error($ch));
        //return FALSE;
    }
    //return TRUE;
    // Close connection
    curl_close($ch);
    //echo $result;
}

function IOSNotification($msg, $Token, $appointment_id = null, $type = null, $user_type = NULL, $job_type = NULL, $bedge_count = NULL) {

    $productCertificate = dirname(BASEPATH) . "/IOScertificate/ck.pem";

    $deviceToken = $Token;
    //pr($deviceToken);die();
// Put your private key's passphrase here:
    $passphrase = '1234';

// Put your alert message here:
    //$message = 'Rajeev testing!';
    $message = array();

    $message = $msg;

////////////////////////////////////////////////////////////////////////////////

    $ctx = stream_context_create();
    stream_context_set_option($ctx, 'ssl', 'local_cert', $productCertificate);
    stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

// Open a connection to the APNS server
//    $fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
 $fp = stream_socket_client('ssl://gateway.push.apple.com:2195', $err,$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

    if (!$fp)
        exit("Failed to connect: $err $errstr" . PHP_EOL);

    // echo 'Connected to APNS' . PHP_EOL;
// Create the payload body

    if ($user_type == 1) {
        if ($job_type == 'apply_job' || $job_type == 'pause_job' || $job_type == 'resume_job') {
            $body['aps'] = array(
                'alert' => $message,
                'sound' => 'default',
                'notification_type' => 1,
                'badge' => (int) $bedge_count
            );
        } else if ($job_type == 'start_job') {
            $body['aps'] = array(
                'alert' => $message,
                'sound' => 'default',
                'notification_type' => 2,
                'badge' => (int) $bedge_count
            );
        } else {
            $body['aps'] = array(
                'alert' => $message,
                'sound' => 'default',
                'notification_type' => 3,
                'badge' => (int) $bedge_count
            );
        }
    } else {
        $body['aps'] = array(
            'alert' => $message,
            'sound' => 'default',
            'notification_type' => 'customer',
            'badge' => (int) $bedge_count
        );
    }

// Encode the payload as JSON
    $payload = json_encode($body);

// Build the binary notification
    $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;

// Send it to the server
    $result = fwrite($fp, $msg, strlen($msg));

    if (!$result)
        return 0; // echo 'Message not delivered' . PHP_EOL;
    else
    //echo 'Message successfully delivered' . PHP_EOL;
        return 1;

// Close the connection to the server
    fclose($fp);
}

/*
 * Function to send SMS
 */

function sms_send($contact, $message = '') {

    $otpsent = $message;

    $url = 'https://rest.nexmo.com/sms/json?api_key=16a5e924&api_secret=c1d8da3b&from=12134657623&to=' . $contact . '&text=' . $otpsent . '&selfid=true&alert=1&dlrreq=true';

    return file_get_contents($url);
}

function get_address($lat, $lng) {
    $key = 'AIzaSyA0upcoKCrcK-UXfMxqj9pROV-rQq5ClAs';
    $url = 'https://maps.googleapis.com/maps/api/geocode/json?key=' . $key . '&address=' . $lat . ',' . $lng . '&sensor=false';
    //$url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . $lat . ',' . $lng . '&sensor=true&key=AIzaSyAB-f-JyoLmAkZmfmvORPv9Jy1kvclbkts';

    $json = file_get_contents($url);

    return json_decode($json);
}



//Dummy Mail Sent

function dummy_email_send_function($to, $sub, $msg) {
    $CI = & get_instance();
    $CI->load->library('email');
    $config['protocol'] = "smtp";
    $config['smtp_host'] = "ssl://smtp.gmail.com";
    $config['smtp_port'] = "465";
    $config['smtp_user'] = "testinguser231@gmail.com";
    $config['smtp_pass'] = "testinguser";
    $config['smtp_timeout'] = 25;
    $config['priority'] = 1;
    $config['charset'] = "utf-8";
    $config['mailtype'] = "html";
//    $config['newline'] = "\r\n";
    $config['crlf'] = "\r\n";      //should be "\r\n"
    $config['newline'] = "\r\n";   //should be "\r\n"
    $config['validation'] = TRUE;
    $CI->load->library('email', $config);
    $CI->email->set_mailtype("html");
     $CI->email->from('noreply@faveapp.com', 'Trigma');
    $CI->email->to($to);
    $CI->email->subject($sub);
    $CI->email->message(maildummyheader() . $msg . maildummyfooter());
    if($CI->email->send()){
		return true;
	}else{
		return false;
	}
}

function maildummyheader() {
    return "<!DOCTYPE html>
			<html>
			<head>
			<meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
			<title>email-template</title>
			<meta name='viewport' content='width=device-width, initial-scale=1.0'/>
			</head>
			<body style='margin:0; padding:0; font-family:helvetica'>";
}

function maildummyfooter() {
    return " <table style='width:100%; padding:20px 0; margin-top:20px; border-top:1px solid #ccc;'>
							<tr>
								<td style='font-size:12px; line-height:18px; padding:0 10px;'>
									This email was sent by Trigma
								</td>
							</tr>
						</table>
					</td></tr></table></body></html>	";
}