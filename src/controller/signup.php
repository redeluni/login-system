<?php

if (!isset($_POST)) { header("Location: index.php"); die; }

header("Content-type: application/json; charset=utf-8;"); 


if (!file_exists("../helper/ExecutionTime.php")) { die('{ "code": "-20", "page": "signup", "status": "bug", "bug": "php", "message": "Errore: file ExecutionTime.php non trovato" }'); }
require "../helper/ExecutionTime.php";

$mark = new ExecutionTime();
$mark->start();

if (!file_exists("../helper/MyException.php")) { die('{ "code": "-20", "page": "signup", "status": "bug", "bug": "php", "message": "Errore: file MyException.php non trovato" }'); }
require "../helper/MyException.php";

if (!file_exists("../helper/Log.php")) { die('{ "code": "-20", "page": "signup", "status": "bug", "bug": "php", "message": "Errore: file Log.php non trovato" }'); }
require "../helper/Log.php";



try {

if (!filter_has_var(INPUT_POST, "signupp")) { throw new MyException('Errore: variabile POST con indice signup non trovata', -20, null, 'http'); }

$str = $_POST["signup"];
$page = key($_POST);


/**
 * IS EMAIL STORED
 * Controlla se l'email è già presente nel database.
 * Quando viene creato un nuovo account bisogna verificare
 * che l' email che l'utente inserisce nel form non sia già stata utilizza da un altro utente
 * quindi già registrata nel database
 * L'email deve avere un valore univoco, non ci possono essere duplicati nel database
 */
function isEmailStored($mysqli, $email) {

    $sql = "SELECT email FROM users WHERE email = ?";

    if (!$stmt = $mysqli->prepare($sql)) { throw new MyException('Errore: mysqli_stmt::prepare', -30, null, 'mysqli'); }

    $stmt->bind_param('s', $param);

    $param = $email;

    if (!$stmt->execute()) { throw new MyException('Errore: mysqli_stmt::execute', -30, null, 'mysqli'); }

    $stmt->store_result(); // [!] //$result = $stmt->get_result();

    if ($stmt->num_rows > 0) { throw new MyException('Un account con L\' email <strong>' . $email . '</strong> è stata già registrata.', -10, null, 'email'); }

    $stmt = null; // [!] $stmt->close();

    return false;
}

/**
 * status ( typo: VARCHAR, length: 100, esempio: utente)
 * name ( typo: VARCHAR, length: 100, esempio: Rossi)
 * email ( typo: VARCHAR, length: 100, esempio: utente )
 * password ( typo: VARCHAR, length: 255, esempio: $2y$10$KimdfbZihiepECDtVLZPBu9.VFgj.Y.GQAceGLPvn89ZiFnQgg4ji )
 *  descrizione: archiviare il risultato in una colonna del database in grado di espandersi oltre i 60 caratteri (255 caratteri sarebbero una buona scelta).
 * registered ( typo: DATE, length: 10, format: yyyy-mm-dd 00:00:00 )
 * hash ( typo: VARCHAR, length: 32, format: [a-z0-9]{32} )
 * verified ( typo: BOOLEAN, length: 1 )
 */
function signup($mysqli, $name, $email, $password) {

    $status = 'utente';
    $password = password_hash($password, PASSWORD_DEFAULT);
    $registered = date('Y-m-d H:i:s');
    $hash = md5(strval(rand(0, 1000)));
    $verified = 0;

    if (!$mysqli->ping()) { throw new MyException('Errore: mysqli_stmt::ping', -30, null, 'mysqli'); }

    $sql = "INSERT INTO users ( status, name, email, password, registered, hash, verified ) VALUES ( ?, ?, ?, ?, ?, ?, ? )";

    if (!$stmt = $mysqli->prepare($sql)) { throw new MyException('Errore: mysqli_stmt::prepare', -30, null, 'mysqli'); }

        $stmt->bind_param('ssssssi', $param1, $param2, $param3, $param4, $param5, $param6, $param7);

        $param1 = $status;
        $param2 = $name;
        $param3 = $email;
        $param4 = $password;
        $param5 = $registered;
        $param6 = $hash;
        $param7 = $verified;

        if (!$stmt->execute()) { throw new MyException('Errore: mysqli_stmt::execute', -30, null, 'mysqli'); }

        $stmt->close();

        $mysqli->close();

        return ['status' => $status, 'name' => $name, 'email' => $email, 'password' => $password, 'registered' => $registered, 'hash' => $hash, 'verified' => $verified];
}



if (!file_exists("../helper/Validation.php")) { throw new MyException('Errore: file Validation.php non trovato', -20, null, 'php'); }

require "../helper/Validation.php";

if (!class_exists("Validation")) { throw new MyException('Errore: classe Validation non trovata', -20, null, 'php'); }

$obj = json_decode($str);

$name = $obj->name;
$email = $obj->mail;
$password = $obj->pass;

$validation = new Validation(
    [
        "name" => $name,
        "email" => $email,
        "password" => $password
    ]
);

if ($validation->validate()) {

    if (!file_exists("../../config/db.php")) { throw new MyException('Errore: file db.php non trovato', -20, null, 'php'); }
    require "../../config/db.php";

    if (!isEmailStored($mysqli, $email)) {

        $user = signup($mysqli, $name, $email, $password);

        if (!file_exists('../model/EmailSubscription.php')) { throw new MyException('Errore: file EmailSubscription.php non trovato', -20, null, 'php'); }

        require "../model/EmailSubscription.php";

        if (!class_exists("EmailSubscription")) { throw new MyException('Errore: classe EmailSubscription non trovata', -20, null, 'php'); }

        $emailSubscription = new EmailSubscription($user);

        $emailSubscription->send();

        // <LOG>
        $userData = [
            "name" => $name,
            "email" => $email,
            "password" => $password
        ];
        $logB = Log::setSingleton();
        $logB->createLogUser('user', $userData);


        $mark->end();
        $diff = $mark->diff();

        $logC = Log::setSingleton();
        $logC->createLogPerf('perf', $diff);
         // </LOG>

        die('{ "code": "10", "page": "'.$page.'", "status": "success", "type": "registration", "message": "Per completare la registrazione ti è stata inviata un\' email al tuo indirizzo ' .$email . ', aprila e clicca sul link all\' interno" }');
    }
} else {

    $multimessageError = $validation->getAllErrors();
    throw new MyException($multimessageError, -15, null, 'validation');
}
}  catch (MyException $myException) {

    if (!file_exists('../model/EmailSubscription.php')) { throw new MyException('Errore: file EmailSubscription.php non trovato', -20, null, 'php'); }

    $data = $myException->getData();
    $kind = $myException->getKind();

    $logA = Log::setSingleton();
    $logA->createLogError($kind, $data);

    echo $myException->getMessage();
}






// echo '<pre>';var_dump( $obj ); die;// object(stdClass)
// $file = basename(__FILE__); // DEBUG
// die('{ "status": "test", "test": "Test: file '.$file.' - linea: '.__LINE__.'" }'); // DEBUG
//die('{ "page": "'.$page.'", "status": "test", "test": "php", "message": "file ' . $file . ' - linea: ' . __LINE__ . '" }');
// die('{ "status": "test", "root": "'.$_SERVER['DOCUMENT_ROOT'].'" }'); // DEBUG C:/xampp/htdocs
// EOF
