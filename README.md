# Introduction
基于swoft的redis队列,
在swoft启动的时候开启一个进程来扫描redis的延迟队列，
详细内容参考[有赞的延迟队列 ](https://tech.youzan.com/queuing_delay/) 
### Requirement
msgpack扩展

### 安装 composer require poolbang/Queue

#### 配置
在config/properties/app.php添加
```
    'bootScan'     => [
        'App\Commands',
        'App\Boot',
        'App\Pool', //需要启动SyncRedis
        'Queue\Boostrap\Process',
    ],
    'beanScan' => [
        'Queue\\',
        'App\Breaker',
        'App\Controllers',
        'App\Exception',
        'App\Fallback',
        'App\Lib',
        'App\Listener',
        'App\Middlewares',
        'App\Models',
        'App\Pool',
        'App\Process',
        'App\Services',
        'App\Tasks',
        'App\WebSocket',
    ],
    'queue' => [
        'contrast' => 10, //每次对比的元素数量， 默认: 10
        'interval' => 2, //空数据时等待时长， 默认: 1
        'log' => false, //是否在控制台显示queue的log， 默认: true
    ],
```
#### 配置 SyncRedis 连接
异步redis的ZRANGE有问题，所以要配置同步的redis， 在app/Pool目录下创建QueueRedisPool.php
```
namespace App\Pool;

use Swoft\Bean\Annotation\Inject;
use Swoft\Bean\Annotation\Pool;
use Swoft\Redis\Pool\RedisPool;
use App\Pool\Config\QueueRedisPoolConfig;
use Swoft\Pool\ConnectionInterface;
use Swoft\Redis\SyncRedisConnection;
/**
 * QueueRedisPool
 *
 * @Pool("queueRedis")
 */
class QueueRedisPool extends RedisPool
{
    /**
     * @Inject()
     * @var QueueRedisPoolConfig
     */
    public $poolConfig;

    /**
     * Create connection
     *
     * @return ConnectionInterface
     */
    public function createConnection(): ConnectionInterface
    {

        $redis = new SyncRedisConnection($this);

        $dbIndex = $this->poolConfig->getDb();
        $redis->select($dbIndex);

        return $redis;
    }
}
```
 在app/Pool/Config目录下创建QueueRedisPoolConfig.php
```
namespace App\Pool\Config;

use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Value;
use Swoft\Redis\Pool\Config\RedisPoolConfig;

/**
 * QueueRedisPoolConfig
 *
 * @Bean()
 */
class QueueRedisPoolConfig extends RedisPoolConfig
{
    /**
     * @Value(name="${config.cache.redis.db}", env="${REDIS_NAME}")
     * @var int
     */
    protected $db = 0;

    /**
     * @Value(name="${config.cache.redis.prefix}", env="${REDIS_MIN_ACTIVE}")
     * @var string
     */
    protected $prefix = '';
}
```
##### job任务的类完成时执行的逻辑
```
namespace App\Models\Logic;

use Queue\JobHandler;
use Swoft\Bean\Annotation\Bean;

/**
 * @Bean()
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
use webphplove\DelayQueue;

/**
 * 添加
 *
 * @param  string $topic 一组相同类型Job的集合（队列）。供消费者来订阅。
 * @param  string $jobName job任务的类名，是延迟队列里的基本单元。与具体的Topic关联在一起。
 * @param  integer $delay job任务延迟时间 传入相对于当前时间的延迟时间即可 例如延迟10分钟执行 传入 10*60
 * @param  integer $ttr job任务超时时间,保证job至少被消费一次,如果时间内未删除Job方法,则会再次投入ready队列中
 * @param  array $args 执行Job任务时传递的可选参数。
 * @param  string $jobId 任务id可传入或默认生成
 */
DelayQueue::enqueue('topic', \App\Models\Logic\QueueLogic::class, 10, 3, ['id' => 8008]);
//获取
DelayQueue::get($jobId);
//删除
DelayQueue::remove($jobId);
```

