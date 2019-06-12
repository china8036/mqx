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
    protected $consumer;

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
     * child pids
     * @var array
     */
    protected $child_pids = [];

    /**
     * 
     */
    const THREAD_FOR_FAILD_TASK = 'mqx_thread_for_filed';

    /**
     * 
     */
    const THREAD_FOR_RUN_TASK = 'mqx_thread_for_new';

    /**
     * 
     */
    const THREAD_MAIN = 'mqx_thread_for manager';

    /**
     * 
     * @param \Qqes\Mqx\Consumer $consumer
     * @param \Qqes\Mqx\CpoolTack $cptask
     * @param type $faild_message_seconds
     */
    function __construct(Consumer $consumer, CpoolTask $cptask, $faild_message_seconds = 3600) {
        $this->consumer = $consumer;
        $this->cptask = $cptask;
        $this->faild_message_seconds = $faild_message_seconds;
    }

    /**
     * run whit gen a damon thread to manage child thread
     */
    public function run() {
        $this->createThread(function(){//gen a daemon thread
            while (true) {
                $this->holdThread();
                sleep(10);
            }
        }, self::THREAD_MAIN);
    }

    /**
     * daemon thread hold child thread
     */
    protected function holdThread() {
        if (empty($this->child_pids)) {
            $this->createThread([$this, 'dealFailTaskMessage'], self::THREAD_FOR_FAILD_TASK);
            $this->createThread([$this, 'runTask'], self::THREAD_FOR_RUN_TASK);
            $this->createThread([$this, 'runTask'], self::THREAD_FOR_RUN_TASK);
        }
    }

    /**
     * 
     * @param callback $child_callback
     * @throws MqxException
     */
    protected function createThread($child_callback, $child_key) {
        $pid = pcntl_fork();
        if ($pid < 0) {// create thread error
            throw new MqxException('can not creat thread ', MqxException::SYSTEM_ERROR);
        }
        if ($pid > 0) {// parent thread
            $this->child_pids[$child_key][] = $pid;
        } else {//child thread to just get faile job
            cli_set_process_title($child_key);
            $child_callback();
            exit; // exit
        }
    }

    /**
     * deal faild message
     */
    protected function dealFailTaskMessage() {

        while (true) {
            $msg = $this->consumer->getFaildMsg(3600, 3);
            if ($msg == false) {
                continue;
            }
            $this->cptask->fail($msg);
        }
    }

    /**
     * deal new msg
     */
    protected function runTask() {
        while (true) {
            $msg = $this->consumer->getMsg(3);
            if ($msg == false) {
                continue;
            }
            $this->cptask->run($msg);
        }
    }

}
