<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Qqes\Mqx;

/**
 * Description of Producer
 *
 * @author wang
 */
class Producer extends Mqx{
    //put your code here
    
    /**
     * queue call task
     * @param string $class class name
     * @param string $method method name
     * @param array $args  params
     * @return 1|0
     */
    public function queueCall($class, $method, array $args = []){
        return $this->addFormatValue2Key(Mqx::FIRST_LIST_KEY, [Mqx::CLASS_KEY => $class, Mqx::METHOD_KEY => $method, Mqx::ARGS_KEY => $args]);
    }
}
