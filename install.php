<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once(__DIR__ . '/app.php');

require_once('./config.php');
require_once(__DIR__ . '/vendor/autoload.php');

$bride = new \Bride\Bride(DB_NAME, DB_USER, DB_PASSWORD);

$bride->tablePrefix('ucm_pota');

echo "<small>Meekro-api version: " . APP_VERSION . '</small></br></br>';

/** UCM_POTA_PROFILES */
$profileModel = $bride->initModel('profiles');

$profileModel->defineColumn('nickname')->type('varchar')->size(55)->null(false);
$profileModel->defineColumn('password')->type('varchar')->size(255)->null(false);
$profileModel->defineColumn('id_active_avatar')->type('int')->size(1)->default(1)->null(false);
$profileModel->defineColumn('score')->type('varchar')->size(10)->default(0)->null(false);
$profileModel->defineColumn('total_kills')->type('int')->size(11)->default(0)->null(false);
$profileModel->defineColumn('total_deaths')->type('int')->size(11)->default(0)->null(false);
$profileModel->initTable();

/** UCM_POTA_AVATARS */
$avatarModel = $bride->initModel('avatars');

$avatarModel->defineColumn('avatar')->type('varchar')->size(20)->null(false);
$avatarModel->defineColumn('sprite')->type('varchar')->size(20)->null(false);
$avatarModel->defineColumn('speed')->type('int')->size(3)->null(false);
$avatarModel->defineColumn('health')->type('int')->size(3)->null(false);
$avatarModel->defineColumn('strength')->type('int')->size(3)->null(false);
$avatarModel->defineColumn('weapons_slot')->type('int')->size(1)->null(false);
$avatarModel->initTable();

$avatars = [
  [
    'avatar' => 'Ryan',
    'sprite' => 'player_1',
    'speed' => 50,
    'health' => 50,
    'strength' => 50,
    'weapons_slot' => 1
  ],
  [
    'avatar' => 'Lisa',
    'sprite' => 'player_2',
    'speed' => 80,
    'health' => 60,
    'strength' => 30,
    'weapons_slot' => 2
  ],
  [
    'avatar' => 'Michael',
    'sprite' => 'player_3',
    'speed' => 100,
    'health' => 100,
    'strength' => 100,
    'weapons_slot' => 3
  ],
];

foreach ($avatars as $avatar) {
  $avatarModel->insert([
    'avatar' => $avatar['avatar'],
    'sprite' => $avatar['sprite'],
    'speed' => $avatar['speed'],
    'health' => $avatar['health'],
    'strength' => $avatar['strength'],
    'weapons_slot' => $avatar['weapons_slot']
  ]);
}

/**
 * CLEAR ___FILES DIR
*/

function rrmdir(string $directory): bool {
  array_map(fn (string $file) => is_dir($file) ? rrmdir($file) : unlink($file), glob($directory . '/' . '*'));

  return rmdir($directory);
}

if (rrmdir(FILES_DIR)) {
  echo "Úspešne odstránene ___files <br>";
  if (mkdir(FILES_DIR)) {
    echo "Uspesne vytvorene ___files <br>";
    if (mkdir(FILES_DIR . '/nelegalne-skladky')) {
      echo "Uspesne vytvorene ___files/nelegalne-skladky <br>";
    } else {
      echo "Nastala chyba pri vytvarani ___files/nelegalne-skladky<br>";
    }
  } else {
    echo "Nastala chyba pri vytvarani ___files<br>";
  }
} else {
  echo "Nastala chyba pri odstraneni ___files<br>";
}
