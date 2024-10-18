<?php

namespace app\admin\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;

class Codes extends Command
{

    protected function configure()
    {
        $this->setName('Codes');
    }

    protected function execute(Input $input, Output $output)
    {
        // 生成一个包含100000到999999的数组，确保随机数的范围足够大，以避免重复
        $numbers = range(100000, 999999);

        // 打乱数组，这样就可以随机选择元素
        shuffle($numbers);

        // 从打乱的数组中取出前1000个元素
        $randomNumbers = array_slice($numbers, 0, 1000);

        // 打印随机数
        foreach ($randomNumbers as $number) {
            $codes[] = [
                'random_code' => $number,
            ];
        }
        Db::name('hair_code')->insertAll($codes);
        $output->writeln("Success Codes");
    }

}
