<?php

    // Change the following line on installation to point to the absolute path of the configuration file.
    require(__DIR__ . '/config.php');

    // $token: Security token for authorized users of this notification service
    $token = $_POST['token'];

    // $recipients: Comma-separated list of phone numbers to notify.  Numbers should be 9 digit US phone numbers
    $recipients = $_POST['recipients'];

    // $message: Message to send to recipients
    $message = $_POST['message'];

    // Ensure incoming token exists and is the correct token
    if (!isset($token)) {
        throw_error("No incoming token");
    } elseif ($token != $notification_token) {
        throw_error("Incoming token incorrect");
    }

    // Ensure require fields are present and valid
    if (!isset($recipients)) {
        throw_error("No incoming recipients");
    } else {
        $recipients = explode(',', $recipients);
        foreach ($recipients as $n => $r) {
            if (!preg_match("/\d{9}/", $r)) {
                throw_error("$r is not a valid recipient", true);
                unset($recipients[$n]);
            }
        }
        if (!count($recipients)) {
            throw_error("No valid recipients");
        }
    }

    // Ensure that a message is included
    if (!isset($message)) {
        throw_error("No incoming message");
    }

    // Log message and recipents to Slack
    if (isset($slack_webhook_url) && strlen($slack_webhook_url)) {
        send_to_slack($slack_webhook_url, $message);
    }

    if (!isset($_POST['slackOnly'])) {

        foreach ($recipients as $r) {

            try {

					$post_data= array(
						'To' => '+1' . $r,
						'From' => '+1' . $twilio_phone,
						'Body' => $message
					);

					$curl_options = array(
						CURLOPT_URL => $twilio_api,
						CURLOPT_USERPWD => $twilio_sid . ":" . $twilio_token,
						CURLOPT_POST => true,
						CURLOPT_POSTFIELDS => http_build_query($post_data),
						CURLOPT_SSL_VERIFYPEER => false
					);

					$ch = curl_init();
					curl_setopt_array($ch, $curl_options);
					$ret = curl_exec($ch);

	 				error_log("SMS Notifier: Message Send Successful: Recipient: $r: " .
                    "Message: $message");

            } catch (Exception $e) {

                throw_error("Twilio SMS Failure: " . $e, true);

            }
        }
    }

    function send_to_slack($url, $text)
    {
        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode(array(
                'text' => $text
            ))
        ));

        curl_exec($ch);
        curl_close($ch);
    }

    function throw_error($error_message, $continue = false)
    {
        $error_message = "SMS Notifier: $error_message";
        error_log($error_message);
        if (!$continue) {
            exit;
        }
    }
