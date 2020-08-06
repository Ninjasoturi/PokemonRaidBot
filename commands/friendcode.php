<?php
// Write to log.
debug_log('FRIENDCODE()');

// For debug.
//debug_log($update);
//debug_log($data);

// Check access.
bot_access_check($update, 'friendcode');

$friendcode = preg_replace( '/[^0-9]/', '', $update['message']['text']);

if(strlen($friendcode) == 12) {
    my_query("UPDATE users SET friendcode='{$friendcode}' WHERE user_id = '{$update['message']['from']['id']}'");
    
    $msg = "Friendcode updated!".CR.substr($friendcode,0,4)." ".substr($friendcode,4,4)." ".substr($friendcode,8,4);
}else {
    $msg = "Updating friendcode failed.".CR."Invalid friendcode!";
}
send_message($update['message']['chat']['id'], $msg, []);
?>
