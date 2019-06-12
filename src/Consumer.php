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

    /**
     * getMsg if not found value it will block util timeout or have value
     * brpp will get the value and move it to next list
     * so if deal msg  successful  it must be removed from next list
     * @param int $key  
     * @param int $timeout  seconds
     * @return type
     */
    public function getMsg($timeout = 3) {
        $msg = $this->brppGetValueByListKeyWithTimeout(Mqx::QUEUE_LIST_KEY, $timeout);
        if ($msg == false) {
            return false;
        }
       return new Message($msg);
    }

    /**
     *  remove msg from next list after done
     * @param \Qqes\Mqx\Message $msg
     */
    public function doneMsg($msg) {
        if (!$msg instanceof Message) {
            return;
        }
        $this->delByListKeyAndValue(Mqx::FAILED_LIST_KEY, $msg->payload);

    }
    
    /**
     *  get faild msg
     * @param int $out_seconds  seconds after join the failed queue 
     * @param int $timeout
     * @return boolean
     */
    public function getFaildMsg($out_seconds = 3600, $timeout = 3){
        $msg = $this->getValueByListKeyWithTimeout(Mqx::FAILED_LIST_KEY, $timeout);
        if($msg == false || !isset($msg[1])){
            return false;
        }
        $payload = $msg[1];
        $message = new Message($payload);
        if($message->time < (time() -  $out_seconds) ){
            return $message;
        }
        // cant not think this is fail because it may be hava time to remove from the queue; so add it to the queue again; 
        $this->addValue2FormatKey(Mqx::FAILED_LIST_KEY, $payload);
        return false;
    }
    
}
