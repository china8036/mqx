<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Qqes\Mqx;

/**
 * Description of Cpool
 *
 * @author wang
 */
class Cpool {
    //put your code here

    /**
     * $consumer
     * @var Consumer 
     */
    protected $consumerConfig;

    /**
     * $cptask
     * @var CpoolTask 
     */
    protected $cptask;

    /**
     * faild queue msg not deal time think as a faild job
     * @var int
     */
    protected $faild_message_seconds;

    /**
     *
     * @var integer 
     */
    protected $new_job_thread_num;

    /**
     *
     * @var integer
     */
    protected $faild_job_thread_num;

    /**
     * project
     * @var string 
     */
    protected $project;
    
    /**
     * 
     */
    const THREAD_FOR_FAILD_TASK = 'mqx_thread_for_%s_failed';

    /**
     * 
     */
    const THREAD_FOR_RUN_TASK = 'mqx_thread_for_%s_new';

    /**
     * 
     */
    const THREAD_MAIN = 'mqx_thread_for_%s_manager';
    
    /**
     * 
     */
    const THREAD_TASK_RUN = 'mqx_thread_for_%s_etask_running';
    
    /**
     * 
     * @param \Qqes\Mqx\CpoolTask $cptask
     * @param int $new_job_thread_num
     * @param int $faild_job_thread_num
     * @param int $faild_message_seconds
     */
    function __construct(CpoolTask $cptask, $new_job_thread_num = 2, $faild_job_thread_num = 1, $faild_message_seconds = 3600) {
        $this->new_job_thread_num = $new_job_thread_num;
        $this->faild_job_thread_num = $faild_job_thread_num;
        $this->cptask = $cptask;
        $this->faild_message_seconds = $faild_message_seconds;
    }

    public static function killAllThread() {
        $cmd = "ps -ef|grep 'mqx_thread_for'|awk '{print $2}'|xargs kill";
        $ret = shell_exec($cmd);
        return $ret;
    }
    
    /**
     * stop
     * @return type
     */
    public function stop(){
        $thread_key = 'mqx_thread_for_' . $this->project ;
        $cmd = "ps -ef|grep '{$thread_key}'|awk '{print $2}'|xargs kill";
        $ret = shell_exec($cmd);
        return $ret;
    }

    /**
     *  set consumer config
     * @param string $redis_host
     * @param int $redis_port
     * @param string $passwd
     * @param string $project
     * @return $this
     */
    public function setConsumerConfig($redis_host, $redis_port = 6379, $passwd = '', $project = 'default') {
        $this->consumerConfig = func_get_args();
        $this->project = $project;
        return $this;
    }

    /**
     * run whit gen a damon thread to manage child thread
     */
    public function run() {
        if (empty($this->consumerConfig)) {
            throw new MqxException('not set consumer config', MqxException::REDIS_CONNECT_ERROR);
        }
        $this->createThread(function() {//gen a daemon thread
            while (true) {
                $this->holdThread();
                sleep(10);
            }
        }, self::THREAD_MAIN);
    }

    /**
     * daemon thread and hold child thread
     */
    protected function holdThread() {
        $faild_job_thread_num = $this->checkThreadNum(self::THREAD_FOR_FAILD_TASK);
        if ($faild_job_thread_num < $this->faild_job_thread_num) {
            for ($i = 0; $i < ($this->faild_job_thread_num - $faild_job_thread_num); $i++) {
                $this->createThread([$this, 'dealFailTaskMessage'], self::THREAD_FOR_FAILD_TASK);
            }
        }
        $new_task_thread = $this->checkThreadNum(self::THREAD_FOR_RUN_TASK);
        if ($new_task_thread < $this->new_job_thread_num) {
            for ($i = 0; $i < ($this->new_job_thread_num - $new_task_thread); $i++) {
                $this->createThread([$this, 'runTask'], self::THREAD_FOR_RUN_TASK);
            }
        }
    }

    /**
     * 
     * @param callback $child_callback
     * @throws MqxException
     */
    protected function createThread(callable $child_callback, $child_key = '',  $args = []) {
        $thread_key = $this->formatThreadName($child_key);
        $pid = pcntl_fork();
        if ($pid < 0) {// create thread error
            throw new MqxException('can not creat thread ', MqxException::SYSTEM_ERROR);
        }
        if ($pid > 0) {// parent thread
            return;
        } else {//child thread to just get faile job
            if ($thread_key) {
                cli_set_process_title($thread_key);
            }
            $child_callback(...$args);
            exit; // exit
        }
    }
    
    /**
     * format thread
     * @param type $key
     * @return type
     */
    protected function formatThreadName($key){
        return sprintf($key, $this->project);
    }

    /**
     * check thread num by key
     * @param type $child_key
     * @return type
     */
    protected function checkThreadNum($child_key) {
        $thread_key = $this->formatThreadName($child_key);
        $cmd = 'ps axu|grep "' . $thread_key . '"|grep -v "grep"|wc -l';
        $ret = shell_exec($cmd);
        return intval($ret);
    }

    /**
     * deal faild message
     */
    protected function dealFailTaskMessage() {
        $consumer = new Consumer(...$this->consumerConfig);
        while (true) {
            sleep(1);
            $msg = $consumer->getFaildMsg($this->faild_message_seconds);
            if ($msg == false) {
                continue;
            }
            $this->createThread([$this, 'dealEachFaildTask'], self::THREAD_TASK_RUN, [$msg]);
             pcntl_wait($status);
            if (pcntl_wifexited($status)) {
                $consumer->delFaildMsg($msg);
            }
        }
    }

    /**
     * deal new msg
     */
    protected function runTask() {
        $consumer = new Consumer(...$this->consumerConfig);
        while (true) {
            $msg = $consumer->getMsg(3);
            if ($msg == false) {
                continue;
            }
            $this->createThread([$this, 'runEachTask'], self::THREAD_TASK_RUN, [$msg]);
            pcntl_wait($status);
            if (pcntl_wifexited($status)) {
                $consumer->doneMsg($msg);
            }
        }
    }
    
    /**
     * 
     * @param type $msg
     */
    protected function runEachTask($msg){
        $this->cptask->runTask($msg);
    }
    
    /**
     * 
     * @param type $msg
     */
    protected function dealEachFaildTask($msg){
        $this->cptask->dealFaildTask($msg);
    }

}
