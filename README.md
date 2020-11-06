# Introduction
基于swoft2.0的redis延时队列
详细内容参考[有赞的延迟队列 ](https://tech.youzan.com/queuing_delay/) 

### 欢迎使用queue 2.0
queue V2.0版本的，配置更简单了，使用更简洁。
去除msg扩展，直接用json代替


### 安装 composer require poolbang/queue

添加config/queue.php文件，然后内容为：
```
reutrn [
        'contrast' => 10, //每次对比的元素数量， 默认: 10
        'interval' => 2, //空数据时等待时长， 默认: 1
        'log' => false, //是否写入日志， 默认: true
    ];
```

##### job任务的类完成时执行的逻辑
```
namespace App\Models\Logic;


use Queue\JobHandler;
use Swoft\Bean\Annotation\Mapping\Bean;

/**
 * @Bean(scope=Bean::PROTOTYPE)
 * Class QueueLogic
 * @package App\Models\Logic
 */
class QueueLogic extends JobHandler
{

    protected function perform()
    {
        echo  'JobId: ' . $this->id . PHP_EOL;
        var_dump($this->args);
    }
}
```
#### 用法

```
use Queue\DelayQueue;

/**
 * 添加
 *
 * @param  string $topic 一组相同类型Job的集合（队列）。
 * @param  string $jobName job任务的类名，是延迟队列里的基本单元。与具体的Topic关联在一起。
 * @param  integer $delay job任务延迟时间 传入相对于当前时间的延迟时间即可 例如延迟10分钟执行 传入 10*60
 * @param  integer $ttr job任务超时时间,保证job至少被消费一次,如果时间内未删除Job方法,则会再次投入ready队列中
 * @param  array $args 执行Job任务时传递的可选参数。
 * @param  string $jobId 任务id可传入或默认生成
 */
DelayQueue::enqueue('test',QueueLogic::class,5,10,['order_id'=>uniqid('queue_')]);
//获取
DelayQueue::get($jobId);
//删除
DelayQueue::remove($jobId);
```

#### [future]
用redis的发布订阅来处理消息

由于现在swoft的发布订阅出现问题，暂时不能用发布订阅来处理消息
