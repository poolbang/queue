<?php
/**
 * Created by PhpStorm.
 * User: su_
 * Date: 18-8-25
 * Time: 上午10:17
 */

namespace webphplove\Queue\Packer;


/**
 * the interface of packer
 *
 */
interface PackerInterface
{
    /**
     * pack data
     *
     * @param mixed $data
     *
     * @return mixed
     */
    public function pack($data);

    /**
     * unpack data
     *
     * @param mixed $data
     *
     * @return mixed
     */
    public function unpack($data);
}
