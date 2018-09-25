<?php
/**
 * Created by PhpStorm.
 * User: su_
 * Date: 18-8-27
 * Time: 上午11:20
 */

namespace Queue;
use Queue\Packer\MsgPacker;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;


/**
 * Job
 * @Bean()
 */
class JobPool
{

    /**
     * @Inject("queueRedis")
     * @var \Swoft\Redis\Redis
     */
    private $redis;

    /**
     * @Inject()
     * @var MsgPacker
     */
    private $msgPacker;

    /**
     * 获取job元数据
     *
     * @param  string $jobId job id
     * @return mixed
     */
    public function getJob($jobId)
    {
        $data = $this->redis->get($jobId);
        if(empty($data)){
            return [];
        }
        return $this->msgPacker->unpack($data);
    }


    /**
     * 放入job元数据
     *
     * @param  \Queue\Job  $job
     * @return boolean
     */
    public function putJob(Job $job)
    {
        $data = $this->msgPacker->pack($job->getAttribute());
        return $this->redis->set($job['id'],$data);

    }

    /**
     * 删除job元数据
     *
     * @param  string $jobId job id
     * @return mixed
     */
    public function removeJob($jobId)
    {
        return $this->redis->delete($jobId);
    }


}