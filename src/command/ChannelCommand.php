<?php

declare(strict_types=1);

namespace gaia\channel\command;

use mon\util\File;
use mon\console\Input;
use mon\console\Output;
use mon\console\Command;

/**
 * 自动生成进程文件指令
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
class ChannelCommand extends Command
{
    /**
     * 指令名
     *
     * @var string
     */
    protected static $defaultName = 'make:channel';

    /**
     * 指令描述
     *
     * @var string
     */
    protected static $defaultDescription = 'Make channel process file. Use make:channel name';

    /**
     * 指令分组
     *
     * @var string
     */
    protected static $defaultGroup = 'channel';

    /**
     * 文件模板
     *
     * @var string
     */
    protected $channel_tpl = <<<TPL
<?php

declare(strict_types=1);

namespace process\channel;

use Channel\Client;
use mon\\env\Config;
use process\Channel;
use Workerman\Worker;
use gaia\ProcessTrait;
use gaia\interfaces\ProcessInterface;

/**
 * %s 通道进程
 *
 * Class %s
 * @copyright Gaia
 * @version 1.0.0 %s
 */
class %s implements ProcessInterface
{
    use ProcessTrait;

    /**
     * 是否启用进程
     *
     * @return boolean
     */
    public static function enable(): bool
    {
        return Config::instance()->get('channel.%s.enable', false);
    }

    /**
     * 获取进程配置
     *
     * @return array
     */
    public static function getProcessConfig(): array
    {
        return Config::instance()->get('channel.%s.config', []);
    }

    /**
     * 进程启动
     *
     * @param Worker \$worker
     * @return void
     */
    public function onWorkerStart(Worker \$worker)
    {
        // 链接channel通道服务
        \$serverHost = Channel::getListenHost();
        \$serverPort = Channel::getListenPort();
        Client::connect(\$serverHost == '0.0.0.0' ? '127.0.0.1' : \$serverHost, \$serverPort);

        // 监听服务，执行回调

    }
}    
TPL;

    /**
     * 配置文件模板
     *
     * @var string
     */
    protected $config_tpl = <<<TPL
<?php

/*
|--------------------------------------------------------------------------
| channel进程 %s 服务启动配置文件
|--------------------------------------------------------------------------
| 定义channel进程 %s 服务启动配置
|
*/

return [
    // 启用
    'enable'    => true,
    // 进程配置
    'config'    => [
        // 监听协议端口，channel进程一般为空即可
        'listen'        => '',
        // 额外参数
        'context'       => [],
        // 进程数
        'count'         => 1,
        // 通信协议，一般不需要修改
        'transport'     => 'tcp',
        // 进程用户
        'user'          => '',
        // 进程用户组
        'group'         => '',
        // 是否开启端口复用
        'reusePort'     => false,
        // 是否允许进程重载
        'reloadable'    => true,
    ]
];
TPL;

    /**
     * 执行指令
     *
     * @param  Input  $in  输入实例
     * @param  Output $out 输出实例
     * @return mixed
     */
    public function execute(Input $input, Output $output)
    {
        $args = $input->getArgs();
        $now = date('Y-m-d');
        foreach ($args as $name) {
            $class = ucfirst($name);
            // 创建进程文件
            $content = sprintf($this->channel_tpl, $name, $class, $now, $class, $name, $name);
            $path = PROCESS_PATH . DIRECTORY_SEPARATOR . 'channel' . DIRECTORY_SEPARATOR . $class . '.php';
            $save = File::instance()->createFile($content, $path, false);
            if (!$save) {
                $output->write("[error] Make {$name} process faild!");
                continue;
            }

            // 创建配置文件
            $config_path = CONFIG_PATH . DIRECTORY_SEPARATOR . 'channel' . DIRECTORY_SEPARATOR . $name . '.php';
            $config_content = sprintf($this->config_tpl, $class, $class);
            $save = File::instance()->createFile($config_content, $config_path, false);
            if (!$save) {
                $output->write("Make {$name} process config faild!");
                continue;
            }

            $output->write("Make {$name} channel process success!");
        }
    }
}
