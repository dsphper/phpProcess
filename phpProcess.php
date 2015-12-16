<?php
class fockClass {
    public $queue = null;
    public $arrPid = [];
    public $obj = null;
    public function __construct($obj) {
        set_time_limit(0);
        $this->obj = $obj;
        $this->checkPcntl();
    }
    public function checkPcntl() {
        // php < 5.3 将不支持 pcntl_signal_dispatch
        if( ! function_exists('pcntl_signal_dispatch')) {
            // 这里程序将每执行1条低级语句就检测一下信号量
            declare(ticks = 1);
        }
        /*
            SIGINT,SIGQUIT,SIGTERM

            SIGINT:用户按下一个中断键（一般为Ctr+C）后，内核就向与该终端有关联的进程发送这种信号
            SIGQUIT:这个是Ctr+\
            SIGTERM:这种信号时有系统提供给普通程序使用的，按照规定，他被用来种植一个进程

            上面几个信号都可以用来终止一个进程
         */
        // 终止程序信号
        pcntl_signal(SIGTERM, [__CLASS__, 'signalHandler'], false);
        // 终止程序运行信号
        pcntl_signal(SIGINT, [__CLASS__, 'signalHandler'], false);
        // 终止程序并生成转储核心文件
        pcntl_signal(SIGQUIT, [__CLASS__, 'signalHandler'], false);
        if(function_exists('gc_enable')) {
            // 开启gc垃圾回收机制（激活循环引用收集器）
            gc_enable();
            // 获取垃圾回收机制状态 （获取循环引用收集器的状态）
            gc_enabled();
        }
    }
    /**
     * [push 增加一个进程]
     * @param  [type] $obj [description]
     * @return [type]      [description]
     */
    public function push() {
        $param = func_get_args();
        $this->queue[] = [
            'name' => $param[0]['name'],
            'type' => $param[0]['type'],
            'obj' => $param[0]['obj'],
            'param' => count($param) > 1 ? array_slice($param, 1) : null
            ];
    }
    /**
     * [pop 删除一条进程]
     * @return [type] [description]
     */
    public function pop($name) {
        foreach ($this->queue as $key => &$value) {
            if($value['name'] == $name) {
                unset($value);
            }
        }
    }
    /**
     * [run 开始运行进程]
     * @return [type] [description]
     */
    public function run() {
        for ($i=0; $i < count($this->queue); $i++) {
            $pid = pcntl_fork();
            // 创建失败
            if($pid == -1) {
                die();
            } else if($pid == 0) { // 子进程运行体
                $this->arrPid[$this->queue[$i]['name']] = getmypid();
                if($this->queue[$i]['param']) {
                    eval("call_user_func([\$this->queue[\$i]['obj'], \$this->queue[\$i]['name']], '"  . implode('\',\'', $this->queue[$i]['param']) . "');");
                } else {
                    call_user_func([$this->queue[$i]['obj'], $this->queue[$i]['name']]);
                }
                exit();
            } else { // 父进程运行体
                $this->arrPid[$this->queue[$i]['name']] = $pid;
                // call_user_func($this->queue['console']);
            }
        }
        foreach ($this->arrPid as $key => $value) {
            pcntl_waitpid($value, $status);
        }
    }
    /**
     * [signalHandler 信号量处理器]
     * @param  [type] $pid [description]
     * @return [type]      [description]
     */
    public function signalHandler($signo) {
        switch ($signo) {
            case SIGCHLD:
                # code...
                break;
            case SIGINT:
                posix_kill(0, SIGKILL);
                break;
            case SIGTERM:
                if(function_exists('posix_kill')){
                    posix_kill(getmypid(), SIGTERM);
                } else {
                    system('kill -9 '. getmypid());
                }
                break;
            case SIGHUP:
            case SIGQUIT:
                // 中断进程
                $this->terminate = true;
            break;
        }
    }
    public function allQuit() {
        foreach ($this->arrPid as $key => $value) {
            $this->kill($key);
        }
    }
    /**
     * [stop 终止进程运行]
     * @return [type] [description]
     */
    public function stop($name) {
        $this->_exec($name, SIGSTOP);
    }
    public function cont($name) {
        $this->_exec($name, SIGCONT);

    }
    public function kill($name) {
        $this->_exec($name, SIGKILL);
    }
    private function _exec($name, $sig) {
        if(array_key_exists($name, $this->arrPid)) {
            return posix_kill($this->arrPid[$name], $sig);
        } else {
            return false;
        }
    }
}
