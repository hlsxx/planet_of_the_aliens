<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

// App
require_once(__DIR__ . '/app.php');

// Common
require_once(__DIR__ . '/vendor/autoload.php');
require_once(__DIR__ . '/helpers.php');
require_once(__DIR__ . '/response.php');
require_once(__DIR__ . '/request.php');
require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/common.php');

// Debug
require_once(__DIR__ . '/debug.php');

DB::$user = DB_USER;
DB::$password = DB_PASSWORD;
DB::$dbName = DB_NAME;

$bride = new \Bride\Bride(DB_NAME, DB_USER, DB_PASSWORD);
$bride->tablePrefix('ucm_pota');

// Models
require_once(__DIR__ . '/lib/Model.php');
require_once(__DIR__ . '/lib/ProfileModel.php');

// Logs
$logInfo = new Monolog\Logger('MeekroAPI-Log-System');
$logInfo->pushHandler(
  new Monolog\Handler\StreamHandler(__DIR__ . '/logs/info.log', Monolog\Logger::INFO)
);

$logError = new Monolog\Logger('MeekroAPI-Log-System');
$logError->pushHandler(
  new Monolog\Handler\StreamHandler(__DIR__ . '/logs/error.log', Monolog\Logger::ERROR)
);

// Test file
require_once(__DIR__ . '/test.php');

try {
  Common::securiter();
  
  //$logInfo->info("REQUEST from {$_SERVER['REMOTE_ADDR']}");

  if (!Request::getParam('page')) {
    Response::throwException('GET param: {page} not set');
  }
  
  Common::androidOrIos();

  switch (Request::getParam('page')) {
    /**
     * @method GET
     * @param idPlayer
     */
    case 'profile':
      $profileModel = $bride->initModel('profile');

      Request::validateGetParam('idPlayer');

      $idPlayer = (int) Request::getParam('idPlayer');

      $profileData = $profileModel->getById($idPlayer);

      echo Response::getJson([
        'status' => 'success',
        'profileData' => $profileData
      ]); 
    break;

    /**
     * @method POST
     * @param nickname
     * @param password
     */
    case 'login':
      $profileModel = $bride->initModel('profile');

      Request::validatePostParam('nickname');
      Request::validatePostParam('password');

      $postData = Request::getPostData();

      $profileData = $profileModel->getByCustom("nickname", $postData['nickname']);
      
      if (empty($profileData)) Response::throwException('Zadaný nickname sa nenašiel');

      if (!password_verify($postData['password'], $profileData['password'])) {
        Response::throwException('Heslo je nesprávne');
      }
 
      echo Response::getJson([
        'status' => 'success',
        'profileData' => $profileData
      ]); 
    break;

    /**
     * @method POST
     * @param nickname
     * @param password
     * @param uid
     */
    case 'register':
      $profileModel = $bride->initModel('profile');

      Request::validatePostParam('nickname');
      Request::validatePostParam('password');

      $postData = Request::getPostData();

      if ($postData['nickname'] == '') Response::throwException('Nezadali ste nickname');
      if ($postData['password'] == '') Response::throwException('Nezadali heslo');

      if (strlen($postData['nickname']) < 3) {
        Response::throwException('Heslo musí obsahovať aspoň 3 znaky');
      }

      $profileAlreadyExists = $profileModel->getByCustom('nickname', $postData['nickname']);

      if (!empty($profileAlreadyExists)) Response::throwException('Tento nickname je už použitý');

      if (strlen($postData['password']) < 8) {
        Response::throwException('Heslo musí obsahovať aspoň 8 znakov');
      }

      $idProfile = $profileModel->insert([
        'nickname' => $postData['nickname'],
        'password' => password_hash($postData['password'], PASSWORD_BCRYPT)
      ]);

      $profileData = $profileModel->getById($idProfile);
 
      echo Response::getJson([
        'status' => 'success',
        'profileData' => $profileData
      ]); 
    break;

    /**
     * @method PATCH
     * @param idPlayer
     * @param score
     * @param totalKills
     * @param totalDeaths
     */
    case 'profile-update':
      $profileModel = $bride->initModel('profile');

      Request::validatePostParam('idPlayer');
      Request::validatePostParam('score');
      Request::validatePostParam('totalKills');
      Request::validatePostParam('totalDeaths');

      $postData = Request::getPostData();

      $profileData = $profileModel->getById((int) $postData['idPlayer']);

      if (empty($profileData)) Response::throwException('Nenašiel sa herný účet');

      $profileModel->update([
        'score' => $postData['score'],
        'total_kills' => (int) $postData['totalKills'],
        'total_deaths' => (int) $postData['totalDeaths'],
      ], (int) $postData['idPlayer']);
 
      echo Response::getJson([
        'status' => 'success'
      ]); 
    break;

    default: Response::throwException('PAGE: {' . Request::getParam('page') . '} doesnt exists');
    break;
  }
} catch(\Exception $e) {
  $requestParams = isset($postData) ? $postData : (isset($getData) ? $getData : []);
  if (!empty($requestParams['image'])) $requestParams['image'] = '';

  $logError->error($e->getMessage() . json_encode($requestParams));
  
  echo Response::getErrorJson($e);
}

?>
