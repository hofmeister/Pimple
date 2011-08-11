<?php
declare(ticks = 1);
/**
 * A JAVA Like Thread class
 */
abstract class Thread {
    private $pid;
    public final function execute() {
        $hasThreads = function_exists('pcntl_signal');
        if (!$hasThreads || Cli::getInstance()->isSimulation()) {
            flush();
            try {
                return $this->executeNoThread();
            } catch(Interrupt $e) {
                throw $e;
            } catch(Exception $e) {
                echo $e;
            }
            return;
        }
        pcntl_signal(SIGCHLD, SIG_IGN);

        $pid = pcntl_fork();
        if ($pid < 1) {
            $this->_run();

            posix_kill (posix_getpid(), 9);
            pcntl_waitpid (posix_getpid(),$temp = 0, WNOHANG);
            pcntl_wifexited ($temp);
            exit();//Make sure we exit...
        } else {
            $this->pid = $pid;
        }
    }
    public final function executeNoThread() {
        $this->_run();
    }
    public function getPid() {
        return $this->pid;
    }
    private function _run() {
        $this->run();
    }
    abstract protected function run();  
}
