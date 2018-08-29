# queue
基于swoft的redis队列
在swoft启动的时候开启一个进程来扫描redis的延迟队列，
详细内容参考[有赞的延迟队列 ](https://tech.youzan.com/queuing_delay/)  
## 安装 composer require webphplove/Queue

#### 配置
在config/properties/app.php添加
```
    'bootScan'     => [
        'App\Commands',
        'App\Boot',
        'Queue\Boostrap\Process',
    ],
    'beanScan' => [
        'Queue',
        'App\Models',
        'App\Controllers',
        'App\Middlewares',
        'App\Task',
        'App\Tasks',
        'App\WebSocket',
    ]
```

### 用法
```
use webphplove\DelayQueue;
//添加
DelayQueue::enqueue('topic', \App\Models\Logic\QueueLogic::class, 10, 3, ['id' => 8008]);
//获取
DelayQueue::get($jobId);
//删除
DelayQueue::remove($jobId);
```

