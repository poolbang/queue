<?php
/**
 * Created by PhpStorm.
 * User: su_
 * Date: 18-8-27
 * Time: 上午11:20
 */

namespace Queue;


/**
 * Job
 *
 */
class Job  implements \ArrayAccess
{
    private $attribute = [
        'id'    => '',
        'topic' => '',
        'class' => '',
        'args'  => '',
        'delay' => 0,
        'ttr'   => 0,
    ];

    /**
     * 获取属性字段
     * @return array
     */
    public function getAttribute(){
        return $this->attribute;
    }

    /**
     * 调用 isset($job[$offset]) 时自动触发
     * @param  string $offset
     * @return string
     */
    public function offsetExists($offset)
    {
        return isset($this->attribute[$offset]);
    }

    /**
     * 调用 $job[$offset] 时自动触发
     * @param  string $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->attribute[$offset];
    }

    /**
     * 调用 $job[$offset]=$value 时自动触发
     * @param  string $offset
     * @param  mixed  $value
     */
    public function offsetSet($offset, $value)
    {
        if(array_key_exists($offset,$this->attribute)){
            $this->attribute[$offset] = $value;
        }
    }

    /**
     * 调用 unset($job[$offset]) 时自动触发
     * @param  string $offset
     * @return string
     */
    public function offsetUnset($offset)
    {
        unset($this->attribute[$offset]);
    }


}