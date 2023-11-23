<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once(__DIR__ . '/app.php');

require_once('./config.php');
require_once(__DIR__ . '/vendor/autoload.php');

$bride = new \Bride\Bride(DB_NAME, DB_USER, DB_PASSWORD);

$bride->tablePrefix('ucm');

echo "<small>Meekro-api version: " . APP_VERSION . '</small></br></br>';

/** UCM_POTA_PROFILE */
$skladkyModel = $bride->initModel('pota_profile');

$skladkyModel->defineColumn('nickname')->type('varchar')->size(55)->null(false);
$skladkyModel->defineColumn('password')->type('varchar')->size(255)->null(false);
$skladkyModel->defineColumn('id_active_avatar')->type('int')->size(1)->default(1)->null(false);
$skladkyModel->defineColumn('score')->type('varchar')->size(10)->default(0)->null(false);
$skladkyModel->defineColumn('total_kills')->type('int')->size(11)->default(0)->null(false);
$skladkyModel->defineColumn('total_deaths')->type('int')->size(11)->default(0)->null(false);
$skladkyModel->initTable();

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
