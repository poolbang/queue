<?php
/**
 * Created by PhpStorm.
 * User: su_
 * Date: 18-8-28
 * Time: 下午3:18
 */

namespace Queue\Bootstrap\Process;


use Queue\DelayQueue;
use Swoft\App;
use Swoft\Bean\Annotation\Value;
use Swoft\Console\Helper\ConsoleUtil;
use Swoft\Process\Bean\Annotation\Process;
use Swoft\Process\Process as SwoftProcess;
use Swoft\Process\ProcessInterface;

/**
 * Custom process
 *
 * @Process(name="DelayQueue",boot=true)
 */
class DelayQueueProcess implements ProcessInterface
{

    public function run(SwoftProcess $process)
    {
        $self = $this;
        //每次对比的元素数量
        $contrast = config('queue.contrast', 10);
        //空数据时等待时长
        $interval = config('queue.interval', 1);
        // Swoole/HttpServer
        $server     = App::$server->getServer();
        $delayQueue = App::getBean(DelayQueue::class);
        $server->tick($interval * 1000, function () use ($delayQueue, $interval, $contrast) {
            $delayQueue->touchTimer($contrast);
            if (config('queue.log', true)) {
                ConsoleUtil::log('sleeping {interval}.' . json_encode(['interval' => $interval], true), [], 'debug');
            }
        });
    }

    /**
     * @return bool
     */
    public function check(): bool
    {
        return true;
    }
}