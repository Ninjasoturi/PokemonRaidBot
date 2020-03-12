<?php
$new_user = newuser($update['message']['from']['id']);
if($new_user) {
	$msg = $tutorial[0]['msg_new'];
}else {
	$msg = $tutorial[0]['msg'];
}
$keys = [
[
	[
		'text'          => 'Seuraava',
		'callback_data' => '0:tutorial:0'
	]
]
];
$photo = "";
send_photo($update['message']['from']['id'], $photo, $msg, $keys, ['disable_web_page_preview' => 'true'],false);
?>
