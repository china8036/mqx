<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Qqes\Mqx;

/**
 * Description of Message
 *
 * @author wang
 */
class Message {

    public $message = [];

    function __construct($payload = '') {
        if (!$payload) {
            return;
        }
        $this->message = unserialize($payload);
       
    }

    /**
     * 
     * @return type
     * @throws MqxException
     */
    public function getId() {
        if (!isset($this->message[Mqx::LIST_VALUE_UNIQUE_KEY])) {
            throw new MqxException('not found id value:' . $this->encode(), MqxException::MESSAGE_EXCEPTION);
        }
        return $this->message[Mqx::LIST_VALUE_UNIQUE_KEY];
    }

    /**
     * 
     * @return type
     * @throws MqxException
     */
    public function getTime() {
        if (!isset($this->message[Mqx::LIST_VALUE_TIMESTAMP_KEY])) {
            throw new MqxException('not found time value:' . $this->encode(), MqxException::MESSAGE_EXCEPTION);
        }
        return $this->message[Mqx::LIST_VALUE_TIMESTAMP_KEY];
    }

    /**
     * 
     * @return type
     * @throws MqxException
     */
    public function getDealTime() {
        if (!isset($this->message[Mqx::LIST_VALUE_DEALTIME_KEY])) {
            throw new MqxException('not found deal time  value:' . $this->encode(), MqxException::MESSAGE_EXCEPTION);
        }
        return $this->message[Mqx::LIST_VALUE_DEALTIME_KEY];
    }

    /**
     * 
     * @return type
     * @throws Exception
     */
    public function getClass() {
        if (!isset($this->message[Mqx::LIST_VALUE_CALL_PARAMS_KEY][Mqx::CLASS_KEY])) {
            throw new MqxException('Can not found call class:' . $this->message, MqxException::MESSAGE_EXCEPTION);
        }
        return $this->message[Mqx::LIST_VALUE_CALL_PARAMS_KEY][Mqx::CLASS_KEY];
    }

    /**
     * 
     * @return type
     * @throws Exception
     */
    public function getMethod() {
        if (!isset($this->message[Mqx::LIST_VALUE_CALL_PARAMS_KEY][Mqx::METHOD_KEY])) {
            throw new Exception('Can not found call method:' . $this->message, MqxException::MESSAGE_EXCEPTION);
        }
        return $this->message[Mqx::LIST_VALUE_CALL_PARAMS_KEY][Mqx::METHOD_KEY];
    }

    /**
     * 
     * @return type
     */
    public function getArgs() {
        if (!isset($this->message[Mqx::LIST_VALUE_CALL_PARAMS_KEY][Mqx::ARGS_KEY])) {
            return [];
        }
        return $this->message[Mqx::LIST_VALUE_CALL_PARAMS_KEY][Mqx::ARGS_KEY];
    }

    /**
     *  call time
     * @param int $id
     */
    public function setId($id) {
        $this->dealtime = $this->message[Mqx::LIST_VALUE_UNIQUE_KEY] = $id;
        return $this;
    }

    /**
     * call class name
     * @param string $class
     */
    public function setClass($class) {
        $this->message[Mqx::LIST_VALUE_CALL_PARAMS_KEY][Mqx::CLASS_KEY] = $class;
        return $this;
    }

    /**
     * 
     * @param string $method
     */
    public function setMethod($method) {
        $this->message[Mqx::LIST_VALUE_CALL_PARAMS_KEY][Mqx::METHOD_KEY] = $method;
        return $this;
    }

    /**
     *  call args
     * @param array args
     */
    public function setArgs(array $args = []) {
        $this->message[Mqx::LIST_VALUE_CALL_PARAMS_KEY][Mqx::ARGS_KEY] = $args;
        return $this;
    }

    /**
     *  call time
     * @param int $time
     */
    public function setTime($time) {
        $this->message[Mqx::LIST_VALUE_TIMESTAMP_KEY] = $time;
        return $this;
    }

    /**
     *  deal time
     * @param int $time
     */
    public function setDealTime($time) {
        $this->message[Mqx::LIST_VALUE_DEALTIME_KEY] = $time;
        return $this;
    }

    
    /**
     * toString
     * @return string
     */
    public function toString(){
        return serialize($this->message);
    }

}
