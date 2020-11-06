<?php
/**
 * Created by PhpStorm.
 * User: su_
 * Date: 18-8-27
 * Time: 上午11:27
 */

namespace Queue;



use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Redis\Pool;

/**
 * ReadyQueue
 * @Bean()
 */
class ReadyQueue
{

    /**
     * @Inject()
     * @var Pool
     */
    private $redis;

    public function getTopicJobs(string $topic, $start, $end){
        return $this->redis->lRange($topic, $start, $end);
    }

    /**
     * 添加JobId到队列中
     *
     * @param  string $queueName 队列名称即主题topic名称
     * @param  string $jobId 任务id
     * @return boolean
     */
    public function pushReadyQueue($queueName, $jobId)
    {
        return $this->redis->rPush($queueName, $jobId);
    }

    /**
     * 从队列中获取JobId 即时性要求不高的
     *
     * @param  array $queueNames 多个队列名称即多个主题topic名称
     * @return mixed
     */
    public function popReadyQueue(array $queueNames)
    {
        foreach ($queueNames as $queueName) {
            $job = $this->redis->lPop($queueName);
            if (!empty($job)) {
                return $job;
            }
        }
        return [];
    }

    /**
     * 从队列中阻塞获取JobId 即时性要求高的时候使用
     *
     * @param  array $queueNames 多个队列名称即多个主题topic名称
     * @param  integer $timeout 超时时间
     * @return array
     */
    public function bPopReadyQueue(array $queueNames, $timeout)
    {
        return $this->redis->blPop($queueNames, $timeout);
    }
}
