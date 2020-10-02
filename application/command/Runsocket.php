<?php
namespace app\command;
 
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Controller;
use think\Request;
 
class Runsocket extends Command
{
    protected function configure()
    {
        $this->setName('Runsocket')->setDescription('Here is the remark ');
    }
 
    protected function execute(Input $input, Output $output)
    {
        $request = Request::instance([                          //如果在希望代码中像浏览器一样使用input()等函数你需要示例化一个Request并手动赋值
            'get'=>$input->getArguments(),                    //示例1: 将input->Arguments赋值给Request->get  在代码中可以直接使用input('get.')获取参数
            'route'=>$input->getOptions()                       //示例2: 将input->Options赋值给Request->route   在代码中可以直接使用request()->route(false)获取 
            //...
        ]);
        $request->module("Index");  
        $output->writeln(controller('index/Pubear')->OPenSocket());
        //$output->writeln("TestCommand:");
    }
}