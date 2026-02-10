<?php

declare(strict_types=1);

namespace support\channel;

use Channel\Client;
use support\channel\process\Channel;

/**
 * 通道通信服务
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
class ChannelService
{
    /**
     * 单例实体
     *
     * @var static
     */
    protected static $instance = null;

    /**
     * 获取当前对象实例
     *
     * @return static
     */
    public static function instance(): ChannelService
    {
        if (!static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * 初始化
     */
    protected function __construct()
    {
        $serverHost = Channel::getListenHost();
        $serverPort = Channel::getListenPort();
        Client::connect($serverHost == '0.0.0.0' ? '127.0.0.1' : $serverHost, $serverPort);
    }

    /**
     * 监听事件，广播
     *
     * @param string $event 事件名称
     * @param callable $callback 事件回调
     */
    public function on(string $event, callable $callback)
    {
        Client::on($event, $callback);
    }

    /**
     * 取消监听事件，广播
     *
     * @param string $event 事件名称
     */
    public function unsubscribe(string $event)
    {
        Client::unsubscribe($event);
    }

    /**
     * 发布事件，广播
     *
     * @param string $event 事件名称
     * @param array $data 事件数据
     */
    public function publish(string $event, array $data)
    {
        Client::publish($event, $data);
    }

    /**
     * 监听事件，队列
     *
     * @param string $event 事件名称
     * @param callable $callback 事件回调
     */
    public function watch(string $event, callable $callback)
    {
        Client::watch($event, $callback);
    }

    /**
     * 取消监听事件，队列
     *
     * @param string $event 事件名称
     */
    public function unwatch(string $event)
    {
        Client::unwatch($event);
    }

    /**
     * 发布事件，队列
     *
     * @param string $event 事件名称
     * @param array $data 事件数据
     */
    public function enqueue(string $event, array $data)
    {
        Client::enqueue($event, $data);
    }
}
