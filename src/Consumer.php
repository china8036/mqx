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
    public function getMsg($key = 0, $timeout = 3) {
        $random = rand(1, 10);
        if ($random == 8 || $random == 9) {// 2/10 to get second list
            $key = Mqx::SECOND_LIST_KEY;
        } elseif ($random == 10) {//1/10 to get third list
            $key = Mqx::THIRD_LIST_KEY;
        } else {//7/10 to get first list
            $key = Mqx::FIRST_LIST_KEY;
        }
        $msg = $this->brppGetValueByListKeyWithTimeout($key, $timeout);
        if ($msg !== false) {
            return new Message($msg, $key);
        }
        //if above not found the value continue to get other list value
        foreach ([Mqx::FIRST_LIST_KEY, Mqx::SECOND_LIST_KEY, Mqx::THIRD_LIST_KEY] as $key) {
            $msg = $this->brppGetValueByListKeyWithTimeout($key, 1);// always 1 second
            if ($msg !== false) {
                return new Message($msg, $key);
            }
        }
        return false;
    }

    /**
     *  remove msg from next list after done
     * @param \Qqes\Mqx\Message $msg
     */
    public function doneMsg($msg) {
        if (!$msg instanceof Message) {
            return;
        }
        $this->delByListKeyAndValue($msg->key + 1, $msg->payload);

    }
    
    /**
     *  get faild msg
     * @param int $timeout
     * @param int $timeout
     * @return boolean
     */
    public function getFaildMsg($out_seconds = 3600, $timeout = 3){
        $msg = $this->getValueByListKeyWithTimeout(Mqx::FOURTH_LIST_KEY, $timeout);
        if($msg == false){
            return false;
        }
        $mesage = new Message($msg);
        if($mesage->time < (time() -  $out_seconds) ){
            return $mesage;
        }
        // cant not think this is fail because it may be hava time to remove from the queue; so add it to the queue again; 
        $this->addValue2FormatKey(Mqx::FOURTH_LIST_KEY, $msg);
        return false;
    }
    
}
