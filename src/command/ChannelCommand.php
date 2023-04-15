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
    protected $tpl = <<<TPL
<?php

declare(strict_types=1);

namespace process\channel;

use gaia\Process;
use Channel\Client;
use process\Channel;
use Workerman\Worker;
use gaia\interfaces\ProcessInterface;

/**
 * %s 通道进程
 *
 * Class %s
 * @author Mon <985558837@qq.com>
 * @copyright Gaia
 * @version 1.0.0 %s
 */
class %s extends Process implements ProcessInterface
{
    /**
     * 启用进程
     *
     * @var boolean
     */
    protected static \$enable = true;

    /**
     * 进程配置
     *
     * @var array
     */
    protected static \$processConfig = [
        // 默认单个进程
        'count' => 1,
    ];

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
            $content = sprintf($this->tpl, $name, $class, $now, $class);
            $path = PROCESS_PATH . DIRECTORY_SEPARATOR . 'channel' . DIRECTORY_SEPARATOR . $class . '.php';
            $save = File::instance()->createFile($content, $path, false);
            if (!$save) {
                $output->write("[error] Make {$name} process faild!");
                continue;
            }
            $output->write("Make {$name} process success!");
        }
    }
}
