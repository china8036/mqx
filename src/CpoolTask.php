<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Qqes\Mqx;

/**
 * Description of CpoolTask
 *
 * @author wang
 */
interface CpoolTask {

    //put your code here
    public function run(Message $message);

    public function fail(Message $message);
    
    
}
