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
    function __construct($redis_host, $redis_port = 6379, $passwd = '', $project = 'default') {
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
     * add vlaue 2 list
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
     * gen format key
     * @param int $key
     * @return string
     */
    private function genKey($key) {
        return self::MQ_SET_KEY_PRE . $this->project . '_' . $key;
    }

    /**
     * add  value  to format key list
     * @param type $key
     * @param type $value
     * @return type
     */
    public function addValue2FormatKey($key, $value) {
        return $this->addValue2List($this->genKey($key), $value);
    }
    
    /**
     *  add format value  to format key list
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
     * get last value and move it to second key list
     * @param int $key
     * @param int $timeout
     * @return string|false
     */
    public function brppGetValueByKeyWithTimeout($key,  $timeout = 3) {
        return $this->redis->bRPopLPush($this->genKey($key), $this->genKey($key + 1), $timeout);
    }
    
    /**
     *  get and remove the last value
     * @param int $key
     * @param int $timeout
     */
    public function getValueByKeyWithTimeout($key,  $timeout = 3){
        return $this->redis->brPop($this->genKey($key), $timeout);
    }
    
    
    /**
     * remove all match value in key list
     * @param int $key
     * @param string $value
     * @return long|bool
     */
    public function delByKeyAndValue($key, $value){
        return $this->redis->lRem($this->genKey($key), $value, 0);
    }

}
