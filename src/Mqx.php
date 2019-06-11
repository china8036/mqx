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
     * 
     */
    const MQ_SET_KEY_PRE = 'qqes_mqx_';

    /**
     * redis
     * @var Redis 
     */
    protected $redis;

    /**
     * $project
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
     * @param sting $set
     * @param integer $score
     * @param mix $value
     * @return type
     */
    private function addValue2SortSet($set, $score, $value) {
        return $this->redis->zAdd($set, $score, $value);
    }

    /**
     * 
     * @param type $set
     * @param type $value
     * @return type
     */
    private function addValueWithTimeScore($set, $value) {
        if (is_array($value)) {
            $value = serialize($value);
        }
        return $this->addValue2SortSet($set, time(), $value);
    }

    /**
     * 
     * @param type $set
     * @return type
     */
    private function genSetWithPre($set) {
        return self::MQ_SET_KEY_PRE . $this->project . '_' . $set;
    }

    /**
     * 
     * @param type $set
     * @param type $value
     * @return type
     */
    public function addValue2Set($set, $value) {
        return $this->addValueWithTimeScore($this->genSetWithPre($set), $value);
    }
    
    /**
     * 
     * @param type $set
     * @param type $value
     * @return type
     */
    public function addFormatValue2Set($set,  $value){
        $format_value = [
            'unique' => md5(uniqid() . rand()),
            'value' => $value
        ];
       return $this->addValue2Set($set, $format_value);
    }
    

    /**
     * 
     * @param type $set
     * @param type $limit
     * @return type
     */
    public function getValueBySetAndLimit($set, $limit) {
        return $this->redis->zRangeByScore($this->genSetWithPre($set), 0, time(), array('withscores' => TRUE, 'limit' => array(0, $limit)));
    }

}
