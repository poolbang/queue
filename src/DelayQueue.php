<?php
/**
 * Created by PhpStorm.
 * User: su_
 * Date: 18-8-27
 * Time: 上午11:29
 */

namespace webphplove\Queue;


use webphplove\Queue\Exception\InvalidJobException;
use Swoft\App;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;
use Swoft\Console\Helper\ConsoleUtil;

/**
 * DelayQueue
 * @Bean()
 */
class DelayQueue
{

    /**
     * @Inject()
     * @var Bucket
     */
    private $bucket;


    /**
     * @Inject()
     * @var JobPool
     */
    private $jobPool;


    /**
     * @Inject()
     * @var ReadyQueue
     */
    private $readyQueue;

    /**
     * 创建Job任务并将其保存到延迟队列中
     *
     * @example DelayQueue::enqueue('order',Job\Order\UpdateMoney::class,20,10,['order_id'=>20847]);
     *
     * @param  string $topic 一组相同类型Job的集合（队列）。供消费者来订阅。
     * @param  string $jobName job任务的类名，是延迟队列里的基本单元。与具体的Topic关联在一起。
     * @param  integer $delay job任务延迟时间 传入相对于当前时间的延迟时间即可 例如延迟10分钟执行 传入 10*60
     * @param  integer $ttr job任务超时时间,保证job至少被消费一次,如果时间内未删除Job方法,则会再次投入ready队列中
     * @param  array $args 执行Job任务时传递的可选参数。
     * @param  string $jobId 任务id可传入或默认生成
     * @return string|boolean
     *
     */
    public static function enqueue($topic, $jobName, $delay, $ttr, $args = null, $jobId = null)
    {
        if (empty($topic) || empty($jobName)) {
            return false;
        }
        $job          = new Job();
        $job['id']    = is_null($jobId) ? md5(uniqid(microtime(true), true)) : $jobId;
        $job['class'] = $jobName;
        $job['topic'] = $topic;
        $job['args']  = $args;
        $job['delay'] = time() + intval($delay);
        $job['ttr']   = intval($ttr);
        if (!static::push($job)) {
            return false;
        }
        return $job['id'];
    }

    /**
     *
     * @param Job $job
     * @return bool|mixed
     * @throws InvalidJobException
     */
    private static function push(Job $job)
    {
        if (
            empty($job['id'])
            || empty($job['topic'])
            || empty($job['class'])
            || $job['delay'] < 0
            || $job['ttr'] < 0
        ) {
            throw new InvalidJobException("job attribute cannot be empty.");
        }
        $jobPool = App::getBean(JobPool::class);
        $result  = $jobPool->putJob($job);
        if (!$result) {
            return false;
        }
        $result = App::getBean(Bucket::class)->pushBucket($job['id'], $job['delay']);
        //Bucket添加失败 删除元数据
        if (!$result) {
            $jobPool->removeJob($job['id']);
            return false;
        }
        return $job['id'];
    }

    /**
     * 删除job任务,元数据和bucket等信息都会删除
     * 在job任务处理结束后调用,不删除在达到超时时间后，会再次投递到可消费队列中,等待再次消费
     *
     * @param  string $jobId 任务id
     * @return boolean
     */
    public static function remove($jobId)
    {
        App::getBean(Bucket::class)->removeBucket($jobId);
        return App::getBean(JobPool::class)->removeJob($jobId);
    }

    /**
     * 获取job任务信息
     *
     * @param  string $jobId 任务id
     * @return array
     */
    public static function get($jobId)
    {
        return App::getBean(JobPool::class)->getJob($jobId);
    }

    /**
     * 立即弹出
     *
     * @param  array $topics 一组相同类型Job的集合（队列）。
     * @return array
     */
    public function pop(array $topics)
    {
        $readyJob   = $this->readyQueue->popReadyQueue($topics);
        if (empty($readyJob)) {
            return [];
        }
        $jobInfo = static::get($readyJob);
        if (empty($jobInfo)) {
            return [];
        }
        $this->bucket->pushBucket($jobInfo['id'], time() + $jobInfo['ttr']);
        return $jobInfo;
    }

    /**
     * 阻塞等待弹出
     *
     * @param  array $topics 一组相同类型Job的集合（队列）。
     * @param  integer $timeout 阻塞等待超时时间
     * @return array
     */
    public function bpop(array $topics, $timeout)
    {
        $readyJob   = $this->readyQueue->bpopReadyQueue($topics, $timeout);
        if (empty($readyJob) || count($readyJob) != 2) {
            return [];
        }
        $jobInfo = static::get($readyJob[1]);
        if (empty($jobInfo)) {
            return [];
        }
        $this->bucket->pushBucket($jobInfo['id'], time() + $jobInfo['ttr']);
        return $jobInfo;
    }

    /**
     * Timer触发器 扫描bucket, 将符合执行时间的任务放到readyqueue中
     * @param int $index
     * @return bool
     */
    public function touchTimer(int $index)
    {
        while (true) {
            $bucketJobs = $this->bucket->getJobsMinDelayTime($index);
            // 集合为空
            if (empty($bucketJobs)) {
                return false;
            }
            $isBreak = false;
            foreach ($bucketJobs as $jobId => $time) {
                if ($time > time()) {
                    $isBreak = true;
                    break;
                }
                $jobInfo = $this->jobPool->getJob($jobId);
                // job元信息不存在, 从bucket中删除
                if (empty($jobInfo)) {
                    $this->bucket->removeBucket($jobId);
                    continue;
                }
                // 元信息中delay是否小于等于当前时间
                if ($jobInfo['delay'] > time()) {
                    $this->bucket->removeBucket($jobInfo['id']);
                    $this->bucket->pushBucket($jobInfo['id'], $jobInfo['delay']);
                    continue;
                }
                ConsoleUtil::log('Found job {id} on Bucket. ' . json_encode(['id' => $jobId, 'time' => $time], true), [], 'info');
                $this->readyQueue->pushReadyQueue($jobInfo['topic'], $jobInfo['id']);
                ConsoleUtil::log('Push job {id} to {topic} ' . json_encode($jobInfo, true), [], 'notice');
                $this->bucket->removeBucket($jobInfo['id']);
                if(App::hasBean($jobInfo['class'])){
                    $queueClass = App::getBean($jobInfo['class']);
                    call_user_func_array([$queueClass, $jobInfo['topic']], $jobInfo['args']);
                }
            }
            if ($isBreak) {
                return false;
            }
        }
    }


}