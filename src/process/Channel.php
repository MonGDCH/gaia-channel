<?php

declare(strict_types=1);

namespace support\process;

use Channel\Server;
use Workerman\Worker;
use gaia\ProcessTrait;
use gaia\interfaces\ProcessInterface;

/**
 * workermn\channel 进程通信服务
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.1   2023-04-13
 */
class Channel extends Server implements ProcessInterface
{
    use ProcessTrait;

    /**
     * 进程配置
     *
     * @var array
     */
    protected static $processConfig = [
        // 监听协议端口
        'listen'    => 'frame://0.0.0.0:2206',
        // 进程数，必须是1
        'count'     => 1,
    ];

    /**
     * 获取进程配置
     *
     * @return array
     */
    public static function getProcessConfig(): array
    {
        return self::$processConfig;
    }

    /**
     * 重载构造方法
     */
    public function __construct() {}

    /**
     * 进程启动
     *
     * @param Worker $worker
     * @return void
     */
    public function onWorkerStart(Worker $worker)
    {
        $worker->channels = [];
        $this->_worker = $worker;
    }
}
