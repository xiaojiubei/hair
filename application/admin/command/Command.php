<?php

namespace app\admin\command;

use think\console\Command as CommandClass;
use think\console\Input;
use think\console\Output;

class Command extends CommandClass
{
    protected function configure()
    {
        $this->setName('make:command')
            ->addArgument('name')
            ->setDescription('Create a new command class');
    }

    protected function execute(Input $input, Output $output)
    {
        $argument = $input->getArgument('name');

        $commandContent = $this->generateCommand($argument);
        $this->writeFile($commandContent, $this->getCommandPath($argument));

        $output->writeln("Success");
    }

    protected function generateCommand($command)
    {
        $commandName = $this->getCommandName($command);
        $content = <<<PHP
<?php

namespace app\admin\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;

class $commandName extends Command
{

    protected function configure()
    {
        \$this->setName('$commandName');
    }
    
    protected function execute(Input \$input, Output \$output)
    {
        \$output->writeln("Success");
    }

}

PHP;
        return $content;
    }

    protected function getCommandPath($command)
    {
        return APP_PATH . 'admin/command/' . $this->getCommandName($command) . '.php';
    }

    protected function getCommandName($command)
    {
        return ucfirst(strtolower($command));
    }

    protected function writeFile($content, $fileNameFull)
    {
        // 获取文件路径和文件名
        $filePath = dirname($fileNameFull); // 获取文件路径
        $fileName = basename($fileNameFull); // 获取文件名

        // 检查文件路径是否存在，如果不存在则创建
        if (!file_exists($filePath)) {
            mkdir($filePath, 0777, true); // 创建目录，权限为0777，第三个参数为true表示递归创建
        }

        // 写入文件内容
        file_put_contents($fileNameFull, $content);
    }
}