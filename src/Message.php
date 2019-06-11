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

    public $id;
    public $key;
    public $message;
    public $payload;
    protected $call_params;

    function __construct($payload, $key) {
        $this->payload = $payload;
        $this->key = $key;
        $this->message = unserialize($payload);
        if ($this->message === false) {
            throw new MqxException('wrong message format:' . $message, MqxException::MESSAGE_EXCEPTION);
        }
        if (!isset($this->message[Mqx::SET_VALUE_UNIQUE_KEY])) {
            throw new MqxException('not found id value:' . $message, MqxException::MESSAGE_EXCEPTION);
        }
        $this->id = $this->message[Mqx::SET_VALUE_UNIQUE_KEY];
        if (!isset($this->message[Mqx::SET_VALUE_CALL_PARAMS_KEY])) {
            throw new MqxException('not found call_params value:' . $message, MqxException::MESSAGE_EXCEPTION);
        }
        $this->call_params = $this->message[Mqx::SET_VALUE_CALL_PARAMS_KEY];
    }

    /**
     * 
     * @return type
     * @throws Exception
     */
    public function getClass() {
        if (!isset($this->call_params[Mqx::CLASS_KEY])) {
            throw new MqxException('Can not found call class:' . $this->message, MqxException::MESSAGE_EXCEPTION);
        }
        return $this->call_params[Mqx::CLASS_KEY];
    }

    /**
     * 
     * @return type
     * @throws Exception
     */
    public function getMethod() {
        if (!isset($this->call_params[Mqx::METHOD_KEY])) {
            throw new Exception('Can not found call method:' . $this->message, MqxException::MESSAGE_EXCEPTION);
        }
        return $this->call_params[Mqx::METHOD_KEY];
    }

    /**
     * 
     * @return type
     */
    public function getArgs() {
        if (!isset($this->call_params[Mqx::ARGS_KEY])) {
            return [];
        }
        return $this->call_params[Mqx::ARGS_KEY];
    }

}
