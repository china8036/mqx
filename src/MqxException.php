<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Qqes\Mqx;

/**
 * Description of Mexception
 *
 * @author wang
 */
use Exception;
class MqxException extends Exception{
    //put your code here
    
    /**
     * redis connect error
     */
    const REDIS_CONNECT_ERROR = -1;
    
    
    const MESSAGE_EXCEPTION = -2;
    
}
