<?php
/**
 * Created by PhpStorm.
 * User: su_
 * Date: 18-8-27
 * Time: 上午11:20
 */

namespace Queue;
use Queue\Packer\JsonPacker;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Redis\Pool;

/**
 * Job
 * @Bean()
 */
class JobPool
{

    /**
     * @Inject()
     * @var Pool
     */
    private $redis;

    /**
     * @Inject()
     * @var JsonPacker
     */
    private $packer;

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
        return $this->packer->unpack($data);
    }


    /**
     * 放入job元数据
     *
     * @param  \Queue\Job  $job
     * @return boolean
     */
    public function putJob(Job $job)
    {
        $data = $this->packer->pack($job->getAttribute());
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
        return $this->redis->del($jobId);
    }


}
