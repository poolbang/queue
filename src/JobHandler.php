<?php
/**
 * Created by PhpStorm.
 * User: su_
 * Date: 18-8-29
 * Time: 上午10:56
 */

namespace Queue;

use Swoft\App;
use Swoft\Console\Helper\ConsoleUtil;

/**
 * Job处理抽象类
 * Class JobHandler
 * @package Queue
 */
abstract class JobHandler
{

    /**
     * @var string Job唯一标识
     */
    protected $id;

    /**
     * @var mixed
     */
    protected $args;

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param mixed $args
     */
    public function setArgs($args)
    {
        $this->args = $args;
    }

    public function run()
    {
        $this->setUp();
        $delayQueue = App::getBean(DelayQueue::class);
        try {
            $this->perform();
            $delayQueue->remove($this->id);
        } catch (\Exception $exception) {
            ConsoleUtil::log(sprintf('Job execution failed %s', $exception->getMessage()), [], 'warning');
            //失败时删除job任务避免重复的投递到bucket中,一直触发执行报错的job任务,如果需要执行重载次方法删除下面一行代码即可
            $delayQueue->remove($this->id);
        }

        $this->tearDown();
    }

    protected function setUp() { }

    protected function tearDown() { }

    abstract protected function perform();
}