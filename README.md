# phpProcess
#### 简述
```
环境要求:
  OSX, Linux, Unix
当前版本所提供的功能:
  1.创建一个或多个进程
  2.删除某个或多个进程
  3.暂停某个或多个进程的运行
  4.恢复某个或多个程序的运行
```
### 使用教程
#### 创建类对象
```php
// 首先引入类文件,并进行实例化.
$this->Process = new fockClass();
```
#### 添加一个进程
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
