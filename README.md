# phpProcess
```简洁的使用多进程
```此类对PHP多进程进行了简单够用的封装
###使用教程
首先引入类文件,并进行实例化.
```php
$this->Process = new fockClass();
```
####添加一个进程
```php
$this->Process->push(
    [
        'name' => 'MyFunc1', // 为当前进程设置一个独一无二的名称
        'funcname' => 'calc', // 传入需要调用的类方法
        'obj' => $this, // 传入类对象
        'type' => '',
    ],
    'test1', 
    'test11',... // 这里是你传入类方法中的参数
);
```
