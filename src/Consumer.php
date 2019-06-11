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
     * getMsg if not found value it will block util timeout or have value
     * @param int $timeout  seconds
     * @return type
     */
    public function getMsg($key =0, $timeout = 3){
      $msg = $this->brppGetValueByListKeyWithTimeout($key, $timeout);
      if($msg === false){
          return false;
      }
      return new Message($msg, $key);
    }
    
    /**
     *  remove msg from back list
     * @param \Qqes\Mqx\Message $msg
     */
    public function doneMsg(Message $msg){
             $this->delByKeyAndValue($key, $msg->payload);
    }
    
}
