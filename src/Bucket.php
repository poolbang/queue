<?php
/**
 * Created by PhpStorm.
 * User: su_
 * Date: 18-8-27
 * Time: 上午11:09
 */

namespace webphplove\Queue;

use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;

/**
 * Bucket
 * @Bean()
 */
class Bucket
{
    /**
     * @Inject()
     * @var \Swoft\Redis\Redis
     */
    private $redis;

    /**
     * 添加JobId到bucket中
     *
     * @param  string $jobId 任务id
     * @param  integer $delay 触发时间
     * @return boolean
     */
    public function pushBucket($jobId, $delay)
    {
        $bucketName = $this->generateBucketName();
        return $this->redis->zAdd($bucketName, intval($delay), $jobId);

    }

    /**
     * 从bucket中获取延迟时间最小的一批Job任务
     *
     * @param  integer $index 索引位置
     * @return array
     */
    public function getJobsMinDelayTime($index)
    {
        $bucketName = $this->generateBucketName();
        return $this->redis->zRange($bucketName, 0, $index - 1, 'WITHSCORES');
    }

    /**
     * 从bucket中删除JobId
     *
     * @param  string $jobId 任务id
     * @return boolean
     */
    public function removeBucket($jobId)
    {
        $bucketName = $this->generateBucketName();
        return $this->redis->zRem($bucketName, $jobId);
    }

    /**
     * 获取bucket
     * @return string
     */
    public function generateBucketName()
    {
        return 'bucket';
    }
}