<?php
$action = $data['arg'];
$user_id = $update['callback_query']['from']['id'];
$new_user = new_user($user_id);
$end = false;
$tutorial_count = count($tutorial)-1;

if($action == "end") {
	if($new_user) {
		my_query("UPDATE users SET tutorial = '1' WHERE user_id = '{$user_id}'");

        foreach($config->RESTRICTED_CHAT_ID as $chat_id) {
            // Create content array.
            $content = [
                'method'     => 'restrictChatMember',
                'chat_id'    => $chat_id,
                'user_id'	 => $user_id,
                'can_send_messages'             => $config->RESTRICIONS_AFTER_TUTORIAL[0],
                'can_send_media_messages'       => $config->RESTRICIONS_AFTER_TUTORIAL[1],
                'can_send_polls'                => $config->RESTRICIONS_AFTER_TUTORIAL[2],
                'can_send_other_messages'       => $config->RESTRICIONS_AFTER_TUTORIAL[3],
                'can_add_web_page_previews'     => $config->RESTRICIONS_AFTER_TUTORIAL[4],
                'can_change_info'               => $config->RESTRICIONS_AFTER_TUTORIAL[5],
                'can_invite_users'              => $config->RESTRICIONS_AFTER_TUTORIAL[6],
                'can_pin_messages'              => $config->RESTRICIONS_AFTER_TUTORIAL[7]
                
            ];

            // Encode data to json.
            $json = json_encode($content);

            // Set header to json.
            header('Content-Type: application/json');


            // Send request to telegram api.
            curl_json_request($json);
        }

	}
	delete_message($update['callback_query']['message']['chat']['id'],$update['callback_query']['message']['message_id']);
	$end = true;
		
	$q = my_query("SELECT level, team FROM users WHERE user_id='{$user_id}'");
	$row = $q->fetch_assoc();

	if(($row['level']==0 or $row['team']=="" or $row['team']==NULL)) {
		$msg= "Et ole tallentanut tietoja levelistäsi tai teamistasi. Haluaisitko tehdä sen nyt?";
		$keys = [
		[
			[
				'text'          => 'Kyllä',
				'callback_data' => '0:user:0'
			],
			[
				'text'          => 'En',
				'callback_data' => '0:exit:1'
			]
		]
		];
		send_message($user_id,$msg,$keys);
	}


}else {

	if($new_user && isset($tutorial[($action+1)]['msg_new'])) {
		$msg = $tutorial[($action+1)]['msg_new'];
	}else {
		$msg =  $tutorial[($action+1)]['msg'];
	}
	$photo =  $tutorial[$action+1]['photo'];
	$keys = [];
	if($action > 0) {
		$keys = [
		[
			[
				'text'          => "Edellinen (".($action)."/".($tutorial_count).")",
				'callback_data' => "0:tutorial:".($action-1)
			]
		]
		];	
	}
	if($action < ($tutorial_count-1)) {
		$keys[0][] = [
				'text'          => "Seuraava (".($action+2)."/".($tutorial_count).")",
				'callback_data' => "0:tutorial:".($action+1)
		];
	}else {
		$keys[0][] = [
				'text'          => "Lopeta",
				'callback_data' => "0:tutorial:end"
		];
	}
	if($data['id']=="1"){
		editMessageText($update['callback_query']['message']['message_id'], $tutorial[0]['msg_new'], [], $update['callback_query']['message']['chat']['id'],false);
		send_photo($update['callback_query']['message']['chat']['id'],$photo, $msg, $keys, ['disable_web_page_preview' => 'true'],false);
		$end = true;
	}
}
answerCallbackQuery($update['callback_query']['id'], "OK!");
if(!$end) editMessageMedia($update['callback_query']['message']['message_id'], $msg, $keys, $update['callback_query']['message']['chat']['id'], ['disable_web_page_preview' => 'true'],false,$photo);
?>
