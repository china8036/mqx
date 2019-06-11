<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Qqes\Mqx;

/**
 * Description of Consumer
 *
 * @author wang
 */
class Consumer extends Mqx {
    //put your code here
    
    /**
     *  getMsg if not found value it will block util timeout or have value
     * @param int $timeout  seconds
     * @return type
     */
    public function getMsg($timeout = 3){
       return $this->brppGetValueByKeyWithTimeout(0, $timeout);
    }
}
