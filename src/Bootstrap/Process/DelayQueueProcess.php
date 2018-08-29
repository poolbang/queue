<?php
/**
 * Created by PhpStorm.
 * User: su_
 * Date: 18-8-28
 * Time: 下午3:18
 */

namespace webphplove\Queue\Bootstrap\Process;


use webphplove\Queue\DelayQueue;
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

    /**
     * 每次对比的元素数量
     */
    protected $contrast = 10;

    /**
     * 空数据时等待时长
     * @Value()
     */
    protected $interval = 1;

    public function run(SwoftProcess $process)
    {
        $self = $this;
        // Swoole/HttpServer
        $server     = App::$server->getServer();
        $delayQueue = App::getBean(DelayQueue::class);
        $server->tick($this->interval * 1000, function () use ($delayQueue, $self) {
            $delayQueue->touchTimer($self->contrast);
            ConsoleUtil::log('sleeping {interval}.'. json_encode(['interval' => $self->interval], true), [], 'debug');
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