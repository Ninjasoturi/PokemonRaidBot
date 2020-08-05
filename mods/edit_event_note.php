<?php
///////////
// TODO
// - Make the cancel button actually work

// Set the id.
$raid_id = $data['id'];

// Set the arg.
$arg = $data['arg'];

// Set the user id.
$userid = $update['callback_query']['from']['id'];

$msg = "";
$keys = [];

$callback_response = "asd";

// Get the raid
$raid = get_raid($raid_id);

$msg = '';
$msg .= getTranslation('raid_saved') . CR;
$msg .= show_raid_poll_small($raid, false) . CR2;

if($arg == "edit") {
	$msg.= "Muokkaa tapahtuman lisätietoja: ";
	my_query("UPDATE raids SET event_note='{$userid}' WHERE id='{$raid_id}'");
}elseif($arg== 1) {
    
}else    {
	$q = my_query("SELECT * FROM events WHERE id='{$raid['event']}'");
    $res = $q->fetch_assoc();

	$msg.="Selected event: <b>".$res['name']."</b>".CR;
	$msg.="Add more info for this specific raid regarding the event.";
	my_query("UPDATE raids SET event_note='{$userid}' WHERE id='{$raid_id}'");
    /*
    $keys[] = [
                [
                    'text'          => getTranslation('cancel'),
                    'callback_data' => $id . ':edit_event_note:0'
                ]
            ];
    */
}

// Answer callback.
answerCallbackQuery($update['callback_query']['id'], $callback_response);

// Edit message.
edit_message($update, $msg, $keys, false);
?>