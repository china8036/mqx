<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'Task.php';
require_once '../src/Mqx.php';
require_once '../src/Consumer.php';
require_once '../src/Producer.php';
require_once '../src/Message.php';
require_once '../src/MqxException.php';
require_once '../src/Cpool.php';
require_once '../src/CpoolTask.php';



$cpool = new Qqes\Mqx\Cpool(new Task(), 2, 1, 10);
$cpool->setConsumerConfig('192.168.1.200', '56379', '2d524045429941cc15f59e@pipaw.net'
        )->run();

