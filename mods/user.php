<?php
// Write to log.
debug_log('user()');

$id = $data['id'];
$action = $data['arg'];

if ($update['callback_query']['message']['chat']['type'] == 'private') {
	$keys = [
		[
			[
				'text'          => '-',
				'callback_data' => 'minus:user:level'
			],
			[
				'text'          => '30',
				'callback_data' => '30:user:level'
			],
			[
				'text'          => '35',
				'callback_data' => '35:user:level'
			],
			[
				'text'          => '40',
				'callback_data' => '40:user:level'
			],
			[
				'text'          => '+',
				'callback_data' => 'plus:user:level'
			]
		],
		[
			[
				'text'          => 'Team Mystic',
				'callback_data' => 'mystic:user:team'
			],
			[
				'text'          => 'Team Instinct',
				'callback_data' => 'instinct:user:team'
			],
			[
				'text'          => 'Team Valor',
				'callback_data' => 'valor:user:team'
			]
		],
		[
			[
				'text'          => 'Valmis',
				'callback_data' => '0:user:exit'
			]
		]
	];
    if ($action == "level") {
		if($id == "minus") {
			$level = "level = IF(level = 0, 35, level-1)";
		}else if ($id == "plus") {
			$level = "level = IF(level = 0, 35, IF(level = 40,40,level+1))";
		}else if ($id == "30") {
			$level = "level = 30";
		}else if ($id == "35") {
			$level = "level = 35";
		}else if ($id == "40") {
			$level = "level = 40";
		}
		// Increase users level.
		my_query(
			"
			UPDATE    users
			SET       {$level}
			  WHERE   user_id = {$update['callback_query']['from']['id']}
			"
		);
		$qmsg = 'Tiedot päivitetty!';
    }else if($action == "team" ) { 
		// Update team in users table.
		#if(in_array($id,['mystic','instinct','valor'])) {
			my_query(
				"
				UPDATE    users
				SET    team = '{$id}'
				  WHERE   user_id = {$update['callback_query']['from']['id']}
				"
			);
		#}
		$qmsg = 'Tiedot päivitetty!';

	}else if ($action == "exit" ) {
		// Set empty keys.
		$keys = [];

		// Build message string.
		$msg = getTranslation('done') . '!';

		// Answer callback.
		answerCallbackQuery($update['callback_query']['id'], $msg);

		// Edit the message.
		edit_message($update, $msg, $keys);
		exit();
	}

    // Empty keys?
    if (!$keys) {
	$msg = getTranslation('mods_not_found');
    }
	
	
	 // Update gym name in raid table.
	$result = my_query("
		SELECT id, name, team, level 
		FROM users
		WHERE
			user_id = '{$update['callback_query']['from']['id']}'
		
	");
		   $row = $result->fetch_assoc();
		   $team = TEAM_UNKNOWN." Ei asetettu";
		  if($row['team']=="instinct") {
			   $team=TEAM_Y.' Instinct';
		   }else if($row['team']=="valor"){
			   $team=TEAM_R.' Valor';
		   }else if($row['team']=="mystic"){
			   $team=TEAM_B.' Mystic';
		   }
	$userinfo = CR.'Nimi: '.$row['name']. CR.'Level: '.$row['level'].CR.'Team: '.$team;
	$msg = '<b>Muokkaa omaa leveliä ja joukkuetta:</b>'.CR.'<i>Nimesi voit vaihtaa Telegramin asetuksista</i>'.$userinfo;

    // Edit message.
    edit_message($update, $msg, $keys, false);
	
	if($qmsg=="") $qmsg = getTranslation('done') . '!';
	// Answer callback.
	answerCallbackQuery($update['callback_query']['id'], $qmsg);

	} 

// Exit.
exit();
