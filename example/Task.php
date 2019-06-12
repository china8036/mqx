<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../src/Message.php';
require_once '../src/CpoolTask.php';
/**
 * Description of Task
 *
 * @author wang
 */
class Task implements \Qqes\Mqx\CpoolTask{

    public function dealFaildTask(\Qqes\Mqx\Message $message) {
        echo 'faile message' . PHP_EOL;
        var_dump($message);
    }

    public function runTask(\Qqes\Mqx\Message $message) {
        echo 'new message' . PHP_EOL;
        var_dump($message);
    }


}