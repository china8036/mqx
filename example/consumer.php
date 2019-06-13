<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include '../src/Mqx.php';
include '../src/Consumer.php';
include '../src/Producer.php';
include '../src/Message.php';
include '../src/MqxException.php';

$consumer = new Qqes\Mqx\Consumer('192.168.1.200', '56379', '2d524045429941cc15f59e@pipaw.net');


while(true){
    $message = $consumer->getMsg(1);
    if($message == false){
        continue;
}
    $pid = pcntl_fork();
    if($pid == -1){
        echo 'system error:can not fork' . PHP_EOL;
        exit;
    }elseif($pid > 0){ //parent process can get child pid
        echo $pid . ' begin' . PHP_EOL;
        pcntl_waitpid($pid, $status);
         echo $pid . ' exit' . PHP_EOL;
        if(pcntl_wifexited($status)){
            $consumer->doneMsg($message);
            echo $pid . ' done' . PHP_EOL;
        }
    }else{//child process
        var_dump($message);
        exit;
    }

}


//if($message !== false){
//    
//}
//var_dump($message);
//$consumer->doneMsg($message);
//
//var_dump($consumer->getFaildMsg(12));
