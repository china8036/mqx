<?php

namespace Qqes\Mqx;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Mqx
 *
 * @author wang
 */
use Redis;
use RedisException;

class Mqx {

    /**
     * set key pre
     */
    const MQ_SET_KEY_PRE = 'qqes_mqx_';
    
    /**
     * class
     */
    const CLASS_KEY = 'class';
    /**
     * method
     */
    const METHOD_KEY = 'method';
    
    /**
     * args
     */
    const ARGS_KEY = 'args';
    
    
    /**
     *  unqiue
     */
    const SET_VALUE_UNIQUE_KEY = 'id';
    
    
    /**
     *  value
     */
    const SET_VALUE_CALL_PARAMS_KEY = 'value';

    

    /**
     * redis
     * @var Redis 
     */
    protected $redis;

    /**
     * project
     * @var string
     */
    private $project;

    //put your code here
    function __construct($redis_host, $redis_port = 6379, $passwd = '', $project = '') {
        try {
            $this->redis = new Redis();
            $this->redis->connect($redis_host, $redis_port);
            if ($passwd) {//密码鉴权
                $this->redis->auth($passwd);
            }
            //检查redis是否可以ping通
            $this->redis->ping();
            $this->project = $project;
        } catch (RedisException $re) {
            throw new MqxException($re->getMessage(), MqxException::REDIS_CONNET_ERROR);
        }
    }

    /**
     * 
     * @param sting $key
     * @param mix $value
     * @return type
     */
    private function addValue2List($key, $value) {
        if (is_array($value)) {
            $value = serialize($value);
        }
        return $this->redis->lPush($key,  $value);
    }

  

    /**
     * 
     * @param type $key
     * @return type
     */
    private function genKey($key) {
        return self::MQ_SET_KEY_PRE . $this->project . '_' . $key;
    }

    /**
     * 
     * @param type $key
     * @param type $value
     * @return type
     */
    public function addValue2FormatKey($key, $value) {
        return $this->addValue2List($this->genKey($key), $value);
    }
    
    /**
     * 
     * @param type $key
     * @param type $value
     * @return type
     */
    public function addFormatValue2Key($key,  $value){
        $format_value = [
            self::SET_VALUE_UNIQUE_KEY => md5(uniqid() . rand()),
            self::SET_VALUE_CALL_PARAMS_KEY  => $value
        ];
       return $this->addValue2FormatKey($key, $format_value);
    }
    

    /**
     * 
     * @param int $key
     * @param int $timeout
     * @return type
     */
    public function getValueByKeyWithTimeout($key,  $timeout = 3000) {
        return $this->redis->bRPopLPush($this->genKey($key), $this->genKey($key + 1), $timeout);
    }

}
