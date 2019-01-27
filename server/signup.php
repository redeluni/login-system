<?php
if ( !isset($_POST) ) { header("Location: index.php"); die; } 

header( "Content-type: application/json" );


if(!filter_has_var(INPUT_POST, 'signup')){ die('{ "status": "error", "error": "variabile POST con indice signup non trovata"}'); }



$str = $_POST["signup"];

$obj = json_decode($str); // echo '<pre>';var_dump( $obj ); die;// object(stdClass)

$name = $obj->name;
$email = $obj->mail;
$password = $obj->pass;





require "model/Validation.php";


if ( Validation::validateName($name) && Validation::validateEmail($email) && Validation::validatePassword($password) ){

    signup($name, $email, $password);
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
function signup($name, $email, $password){

    $status = 'utente';
    $password= password_hash($password, PASSWORD_DEFAULT); 
    $registered = date('Y-m-d H:i:s');
    $hash = md5(strval(rand(0, 1000))); 
    $verified = 0;
    
    require "db.php";

    if (!$mysqli->ping()) {
       
        die('{ "status": "error", "error": "La connessione al server è chiusa" }');
    }

    $sql = "INSERT INTO users ( status, name, email, password, registered, hash, verified ) VALUES ( ?, ?, ?, ?, ?, ?, ? )"; 
    
    if ( $stmt = $mysqli->prepare($sql) ) {

        $stmt->bind_param('ssssssi', $param1, $param2, $param3, $param4, $param5, $param6, $param7);
        
        $param1 = $status; 
        $param2 = $name;
        $param3 = $email;
        $param4 = $password;
        $param5 = $registered;
        $param6 = $hash;
        $param7 = $verified;

        if ( $stmt->execute() ) {

            $righe_generate = $mysqli->affected_rows;
            $ultimo_ID_inserito = $mysqli->insert_id;

            if( file_exists('model/EmailSubscription.php') ) {

                require "model/EmailSubscription.php"; 

                if ( class_exists("EmailSubscription") ) {

                    $emailSubscription = new EmailSubscription($name, $email, $hash);
                    $emailSubscription->send();
                    die('{ "status": "success", "success": "Inserite '.$righe_generate.' riga nel database con id '. $ultimo_ID_inserito.'" }');
                } else {
                    die('{ "status": "error", "error": "Errore nella con la classe EmailSubscription" }');
                }
            } else {
                    die('{ "status": "error", "error": "Errore il file non esiste" }');
            }
            

       
        } else { die('{ "status": "error", "error": "Impossibile inserire nuove righe nel database - execute" }'); }

        $stmt->close();

    } else { die('{ "status": "error", "error": "Impossibile inserire nuove righe nel database - prepare" }'); }

    $mysqli->close();
}
