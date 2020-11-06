<?php


namespace Queue\Packer;

use Swoft\Bean\Annotation\Mapping\Bean;

/**
 * Class JsonPacker
 * @package Queue\Packer
 * @Bean()
 */
class JsonPacker implements PackerInterface
{

    public function pack($data)
    {
        return json_encode($data);
    }

    public function unpack($data)
    {
        return json_decode($data);
    }
}
