<?php
$SQL = '';
$SQL_eggs = '';
$SQL_file = __DIR__ . '/sql/gohub-raid-boss-pokedex.sql';

$proto_url = "https://raw.githubusercontent.com/Furtif/POGOProtos/master/src/POGOProtos/Enums/Form.proto";
$game_master_url = "https://raw.githubusercontent.com/PokeMiners/game_masters/master/latest/latest.json";
$GoHub_URL = 'https://db.pokemongohub.net/api/pokemon/';

$form_translation = array("ALOLA"=>"Alolan");
//Parse the form ID's from pogoprotos
$proto = file($proto_url);
$count = count($proto);
$form_ids = array();
for($i=4;$i<$count;$i++) {
    $data = explode("=",str_replace(";","",$proto[$i]));
    if(count($data) == 2) $form_ids[trim($data[0])] = trim($data[1]);
}

// Get JSON from GOHub
function getPokemonData($pokedex_id, $pokemon_form = 'Normal') {
    // Set DB URL.
    $DB_URL = 'https://db.pokemongohub.net/api/pokemon/';

    // Build URL for CURL.
    $URL = $DB_URL . $pokedex_id . (($pokemon_form != 'Normal') ? ('?form=' . $pokemon_form) : '');

    // Get data.
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $URL);
    $data = curl_exec($ch);
    curl_close($ch);

    return $data;
}


$master = json_decode(file_get_contents($game_master_url),true);
foreach($master as $row) {
    $part = explode("_",$row['templateId']);
    $form_data = [];
    // Found Pokemon form data
    if($part[0] == "FORMS") {
        // Get pokemon ID
        $pokemon_id = ltrim(str_replace("V","",$part[1]),'0');
        
        // Pokemon name 
        $pokemon_name = $row['data']['formSettings']['pokemon'];
        // Get pokemon forms
        if(!isset($row['data']['formSettings']['forms'])) {
            $form_data[] = array("form"=>$pokemon_name."_NORMAL");
        }else {
            $form_data = $row['data']['formSettings']['forms'];
        }
         foreach($form_data as $form) {
            $form_name = str_replace($pokemon_name."_","",$form['form']);
            if($form_name != "PURIFIED" && $form_name != "SHADOW") {
                if(isset($form_translation[$form_name])) {
                    $gohub_form = $form_translation[$form_name];
                }else if($pokemon_id == 150 && $form_name=="A") {
                    // Because logic and consistency
                    $gohub_form = "Armored";
                }else {
                    $gohub_form = ucfirst(strtolower($form_name));
                }
                // Get data from GOHub
                $gohubjson = getPokemonData($pokemon_id,$gohub_form);
                $gohub_pokemon = json_decode($gohubjson, true);
                
                // If form data not found, use normal form
                if(!is_array($gohub_pokemon)) {
                    $gohubjson = getPokemonData($pokemon_id);
                    $gohub_pokemon = json_decode($gohubjson, true);
                    echo 'No form data found for pokemon id ' . $pokemon_id . ' (Form: ' . $gohub_form . ')' . PHP_EOL;
                    echo 'Using data for normal form instead.' . PHP_EOL;
                }else {
                    echo 'Formatting data for pokemon id ' . $pokemon_id . ' (Form: ' . $gohub_form . ')' . PHP_EOL;
                }
                
                $poke_name = ucfirst(strtolower(str_replace(["_FEMALE","_MALE"],["♀","♂"],$pokemon_name)));
                $poke_name = str_replace("_","-",$poke_name);
                $poke_form = $gohub_form;
                
                $poke_min_cp = $gohub_pokemon['CPs']['raidCaptureMin'];
                $poke_max_cp = $gohub_pokemon['CPs']['raidCaptureMax'];
                $poke_min_weather_cp = $gohub_pokemon['CPs']['raidCaptureBoostMin'];
                $poke_max_weather_cp = $gohub_pokemon['CPs']['raidCaptureBoostMax'];
                $poke_weather = implode(',',$gohub_pokemon['weatherInfluences']);
                $poke_shiny = 0;
                
                
                // Replace weather names with values.
                $poke_weather = str_replace('sunny',12,$poke_weather);
                $poke_weather = str_replace('rain',3,$poke_weather);
                $poke_weather = str_replace('partlyCloudy',4,$poke_weather);
                $poke_weather = str_replace('cloudy',5,$poke_weather);
                $poke_weather = str_replace('windy',6,$poke_weather);
                $poke_weather = str_replace('snow',7,$poke_weather);
                $poke_weather = str_replace('fog',8,$poke_weather);
                $poke_weather = str_replace(',','',$poke_weather);

                $form_id = $form_ids[$form['form']];
                $form_asset_suffix = (isset($form['assetBundleValue']) ? $form['assetBundleValue'] : (isset($form['assetBundleSuffix'])?$form['assetBundleSuffix']:"00"));
                
                $SEP = ',';
                $QM = "'";

                $SQL.= "INSERT INTO pokemon (pokedex_id, pokemon_name, pokemon_form_name, pokemon_form_id, asset_suffix, min_cp, max_cp, min_weather_cp, max_weather_cp, weather, shiny) ";
                $SQL.= "VALUES (". $QM . $pokemon_id . $QM . $SEP . $QM . $poke_name . $QM . $SEP . $QM . $poke_form . $QM . $SEP . $QM . $form_id . $QM . $SEP . $QM . $form_asset_suffix . $QM . $SEP . $QM . $poke_min_cp . $QM . $SEP . $QM . $poke_max_cp . $QM . $SEP . $QM . $poke_min_weather_cp . $QM . $SEP . $QM . $poke_max_weather_cp . $QM . $SEP . $QM . $poke_weather . $QM . $SEP . $QM . $poke_shiny . $QM .");".PHP_EOL;
            }
        }
    }
}
// Save data to file.
if(!empty($SQL)) {
    // Add eggs to SQL data.
    echo 'Adding raids eggs to pokemons' . PHP_EOL;
    for($e = 1; $e <= 5; $e++) {
        $pokemon_id = '999'.$e;
        $form_name = 'normal';
        $pokemon_name = 'Level '. $e .' Egg';
        
        $SQL.= "INSERT INTO pokemon (pokedex_id, pokemon_name, pokemon_form_name, pokemon_form_id, asset_suffix, min_cp, max_cp, min_weather_cp, max_weather_cp, weather, shiny) ";
        $SQL.= "VALUES (". $QM . $pokemon_id . $QM . $SEP . $QM . $pokemon_name . $QM . $SEP . $QM . $form_name . $QM .", 0, 0, 0, 0, 0, 0, 0, 0);".PHP_EOL;
    }

    // Add delete command to SQL data.
    echo 'Adding delete sql command to the beginning' . PHP_EOL;
    $DEL = 'DELETE FROM `pokemon`;' . PHP_EOL;
    $DEL .= 'TRUNCATE `pokemon`;' . PHP_EOL;
    $SQL = $DEL . $SQL;

    // Save data.
    //echo $SQL . PHP_EOL;
    echo 'Saving data to ' . $SQL_file . PHP_EOL;
    file_put_contents($SQL_file, $SQL);
} else {
    echo 'Failed to get pokemon data!' . PHP_EOL;
}

// File successfully created?
if(is_file($SQL_file)) {
    echo 'Finished!' . PHP_EOL;
} else {
    echo 'Failed to save file: ' . $SQL_file . PHP_EOL;
}

?>