<?php
/**
 * Created by PhpStorm.
 * User: su_
 * Date: 18-8-25
 * Time: 上午10:17
 */

namespace Queue\Packer;
use Swoft\Bean\Annotation\Bean;


/**
 * the packer of MsgPack
 * @Bean()
 */
class MsgPacker implements PackerInterface
{
    /**
     * pack data
     *
     * @param mixed $data
     *
     * @return string
     */
    public function pack($data)
    {
        return \msgpack_pack($data);
    }

    /**
     * unpack data
     *
     * @param mixed $data
     *
     * @return mixed
     */
    public function unpack($data)
    {
        return \msgpack_unpack($data);
    }
}
