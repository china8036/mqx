<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include '../src/Mqx.php';
include '../src/Consumer.php';
include '../src/Producer.php';
include '../src/MqxException.php';

$mqx = new Qqes\Mqx\Consumer('192.168.1.200', '56379', '2d524045429941cc15f59e@pipaw.net');


$values = $mqx->getValueBySetAndLimit(0, 100);
foreach($values as $value => $score){
    echo $value . PHP_EOL;
    $mqx->removeBysetAndValue(0, $value);
}