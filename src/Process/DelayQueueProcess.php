<?php


namespace Queue\Process;


use Queue\DelayQueue;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Process\Process;
use Swoft\Process\UserProcess;
use Swoole\Coroutine;
use Swoft;

/**
 * Class DelayQueueProcess
 * @package Queue\Process
 * @Bean()
 */
class DelayQueueProcess extends UserProcess
{

    public function run(Process $process): void
    {

        //每次对比的元素数量
        $contrast = config('queue.contrast', 10);
        //空数据时等待时长
        $interval = config('queue.interval', 1);
        $delayQueue = Swoft::getBean(DelayQueue::class);
        while (true){
            $delayQueue->touchTimer($contrast);
            Coroutine::sleep($interval);
        }
    }
}
