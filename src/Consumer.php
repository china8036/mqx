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
        $msg = $this->brpopValueByLListKeyWithTimeout(Mqx::QUEUE_LIST_KEY, $timeout);
        if ($msg == false) {
            return false;
        }
        $message = new Message($msg);
        $message->setDealTime(time());
        if(!$this->addValue2FormatKey(Mqx::FAILED_LIST_KEY, $message->toString())){// and to faild queue but if call done method it will be remove
            throw new MqxException('can not add msg to redis:' . $message->toString(), MqxException::REDIS_CONNECT_ERROR);
        }
        return $message;
    }

    /**
     *  remove msg from next list after done
     * @param \Qqes\Mqx\Message $msg
     */
    public function doneMsg($msg) {
        if (!$msg instanceof Message) {
            return;
        }
        return $this->delByListKeyAndValue(Mqx::FAILED_LIST_KEY, $msg->toString());

    }
    
    /**
     *  get faild msg
     * @param int $timeout  seconds after join the failed queue 
     * @return boolean
     */
    public function getFaildMsg($timeout = 3600){
        $msg = $this->getLastValueByListKey(Mqx::FAILED_LIST_KEY);//can not del if from list and than add to list this can cause doneMsg cant found del msg bewtween del and add interval 
        if(empty($msg[0])){
            return false;
        }
        $message = new Message($msg[0]);
        if($message->getDealTime() < (time() -  $timeout) ){
            return $message;
        }
        return false;
    }
    
    
    /**
     * del faild msg
     * @param \Qqes\Mqx\Message $msg
     * @return type
     */
    public function delFaildMsg(Message $msg){
        return $this->delByListKeyAndValue(Mqx::FAILED_LIST_KEY, $msg->toString());
    }
    
}
