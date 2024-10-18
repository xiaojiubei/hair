<?php

namespace app\admin\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;

class Users extends Command
{

    protected function configure()
    {
        $this->setName('Users');
    }

    protected function execute(Input $input, Output $output)
    {
        // 生成一个包含100000到999999的数组，确保随机数的范围足够大，以避免重复
        $numbers = range(100000, 999999);

        // 打乱数组，这样就可以随机选择元素
        shuffle($numbers);

        // 从打乱的数组中取出前10000个元素
        $randomNumbers = array_slice($numbers, 0, 10000);

        // 中文词汇数组
        $adjectives1 = ['快', '勇', '智', '真', '自', '浪', '温', '坚'];
        $adjectives2 = ['飞', '游', '繁', '流', '清', '彩', '碧', '蓝'];
        $adjectives3 = ['乐', '敢', '慧', '诚', '由', '漫', '柔', '强'];
        $adjectives4 = ['鸟', '鱼', '星', '云', '风', '虹', '海', '天'];
        $adjectives5 = ['快', '敢', '智', '诚', '自', '漫', '温', '强'];

        // 存储昵称的数组
        $nicknames = [];

        // 生成10000个不重复的随机昵称
        while (count($nicknames) < 10000) {
            // 随机选择一个形容词和一个名词
            $nickname = $adjectives1[array_rand($adjectives1)] . $adjectives2[array_rand($adjectives2)] . $adjectives3[array_rand($adjectives3)] . $adjectives4[array_rand($adjectives4)] . $adjectives5[array_rand($adjectives5)];

            // 检查昵称是否已经存在
            if (!in_array($nickname, $nicknames)) {
                // 如果昵称不存在，添加到数组中
                $nicknames[] = $nickname;
            }
        }

        // 打乱数组，这样就可以随机选择元素
        shuffle($nicknames);

        // 打印生成的昵称
        foreach ($nicknames as $index => $nickname) {
            $data[] = [
                'name'    => $nickname,
                'account' => $randomNumbers[$index],
                'balance' => 20,
            ];
        }
        Db::name('hair_user')->insertAll($data);
        $output->writeln("Success Users");
    }

}
