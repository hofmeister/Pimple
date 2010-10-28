<?php
require_once 'Browser.php';
class Cli {
	protected static $instance;
	protected $parmCharPrefix 	= '-';
	protected $parmNamePrefix 	= '--';
	protected $requiredParams 	= array();
	protected $optionalParams 	= array();
	protected $params 			= array();
	protected $tableHeaderFields = array();
	protected $simulateCli = false;
	protected $cursorPos = array('x' => 0,'y' => 0);
	protected $cursorPosStore = array();
	protected $progressCount = 0;
	protected $currentProgress = 0;
    protected $currentPid = null;
    protected $daemon = false;
    /**
     *
     * @var Basic_View_Form
     */
    protected $view = null;
    protected $inputCount = 0;

	public function __construct() {
		
		$params = $_SERVER['argv'];
		if (!is_array($params)) return;
		array_shift($params);
		foreach($params as $param) {
			$this->parseParam($param);
		}
	}
    public function enableSimulateCli($view) {
        $this->simulateCli = true;
        $this->view = $view;
    }
    public function isSimulation() {
        return $this->simulateCli;
    }


    public function onlyConsole() {
        if (!Browser::isConsole() && !$this->isSimulation())
			throw new Exception(T("This program can only be run from command line."));
    }
	public function saveProgress() {
		if ($this->currentProgress > $this->progressCount) {
			$this->progressCount++;
			return false;
		}
		
		file_put_contents('progress.sav',$this->currentProgress);
		$this->progressCount++;
		$this->currentProgress = $this->progressCount;
		return true;
	}
	public function loadProgress() {
		if (file_exists('progress.sav'))
			$this->currentProgress = (int) file_get_contents('progress.sav',$this->currentProgress);

	}
	public function clearProgress() {
		unlink('progress.sav');
	}
	protected function parseParam($param) {
		if (substr($param,0,strlen($this->parmNamePrefix)) == $this->parmNamePrefix) {
			$param = substr($param,strlen($this->parmNamePrefix));
			if(stristr($param,'=')) {
				$parts = explode('=',$param,2);	
				if (substr($parts[1],0,1) == '"')
					$parts[1] = trim($parts[1],'"');
				elseif(substr($parts[1],0,1) == "'")
					$parts[1] = trim($parts[1],"'");
				$this->params[$parts[0]] = $parts[1];
			} else {
				$this->params[$param] = true;
			}
		} elseif (substr($param,0,strlen($this->parmCharPrefix)) == $this->parmCharPrefix) {
			$param = substr($param,strlen($this->parmCharPrefix));
			for($i = 0;$i < strlen($param) ; $i++) {
				$this->params[$param[$i]] = true;
			}
		} else
			$this->params[] = $param;
	}
	public function setRequiredParams() {
		$this->requiredParams = func_get_args();	
		$this->checkParams();
	}
	public function setOptionalParams() {
		$this->optionalParams = func_get_args();
	}
    public function getRequiredParams() {
        return $this->requiredParams;
    }

    public function getOptionalParams() {
        return $this->optionalParams;
    }
	public function getParam($param,$default = null) {
		return (isset($this->params[$param])) ? $this->params[$param] : $default;
	}
	public function hasParam($param) {
		return isset($this->params[$param]);
	}
	public function checkParams() {
		foreach($this->requiredParams as $parm) {
			if (!$this->hasParam($parm)) {
				$this->displayRequiredParms();
                if (!$this->isSimulation())
                    exit(0);
			}
		}
	}
	public function displayRequiredParms() {
		$this->displayLine(T('Følgende argumenter er krævede:'));
		foreach($this->requiredParams as $parm) {
			$this->displayLine(chr(9)." ".((strlen($parm) > 1) ? $this->parmNamePrefix : $this->parmCharPrefix).$parm.chr(10));
		}
		$this->displayLine('');
	}
	public function displayLine($text) {
        if (Browser::isConsole()) {
			$this->ansiEraseLine();
			$this->renderWidgets();
			
            echo $text.chr(10);
			$this->cursorPos['x'] = 0;
			$this->cursorPos['y']++;
			
		} elseif($this->isSimulation()) {
            echo $text.chr(10);
            ob_flush();
            flush();

        }
	}
	public function displayErrorAndExit($error) {
		$this->displayLine(T("Der skete en fejl:"));
		$this->displayLine(chr(9).' - '.$error);
		exit(0);
	}
	public function displayTableHeader() {
        $args = func_get_args();
        if (count($args) == 1 && is_array($args[0]))
            $args = $args[0];
		$this->tableHeaderFields = $args;
		foreach($this->tableHeaderFields as $i=>$field) {
			$this->tableHeaderFields[$i] = str_replace("\t",'    ',$field);	
		}
		$headerLine = implode('| ',$this->tableHeaderFields);
		$this->displayLine($headerLine);
		$this->displayLine(str_repeat('-',mb_strlen($headerLine)));
	}
	public function displayTableRow() {
        $args = func_get_args();
        if (count($args) == 1 && is_array($args[0]))
            $args = $args[0];
		$row = $args;
		foreach($this->tableHeaderFields as $i=>$field) {
			$row[$i] = $this->displayField($row[$i],mb_strlen($field));
		}
		$this->displayLine(implode('| ',$row));
	}
	public function displayField($value,$length) {
		$tmp1 = substr(utf8_decode(str_replace('?','',$value)),0,$length);
		$tmp1 = preg_replace('/[^\?]/is','',$tmp1);
		//$tmp2 = mb_substr($value,0,$length);
		$diff = strlen($tmp1);//($tmp1) - mb_strlen($tmp2));
		$valueLength = mb_strlen($value) - $diff;
		if ($valueLength > $length) {
			return mb_substr($value,0,$length + $diff);
		} else
			return $value.str_repeat(' ',$length - $valueLength);
	}
	public function displayProgressBar($offset,$limit,$width = 50) {
		$this->ansiSaveCursor();
		$percent = ($offset / $limit);
		$barWidth = ceil($width * $percent);
		$this->ansiMoveCursorPos(0,0);
		$this->ansiEraseLine();
		echo "|";
		for($i = 0;$i < $width;$i++) {
			if ($i < $barWidth)
				echo "#";
			else
				echo " ";
		}
		echo "|\n";
		$this->ansiLoadCursor();
	}
	public function addWidget(Ui_Ansi $widget) {
		$this->widgets[] = $widget;
		$widget->setPosition($this->cursorPos['x'],$this->cursorPos['y']);
		$widget->render();
		for($i = 0; $i < $widget->getHeight();$i++) {
			$this->displayLine('');
		}
	}
	public function renderWidgets() {
		$repaint = false;
		if ($this->cursorPos['y'] == 0) {
			$repaint = true;
		}
		for($i = 0; $i < count($this->widgets); $i++) {
			$this->widgets[$i]->render();
			if ($repaint) {
				$this->cursorPos['y'] += $this->widgets[$i]->getHeight();
			}
		}
	}
	public function ansiMoveCursorPos($x,$y) {
		$this->cursorPos['x'] = $x;
		$this->cursorPos['y'] = $y;
		return $this->ansiOutput($y.';'.$x.'f');
	}
	public function ansiEraseLine() {
		$this->ansiOutput('K');
	}
	public function ansiEraseScreen() {
		for($y = 25; $y > -1; $y--) {
			$this->ansiMoveCursorPos(0,$y);
			$this->ansiEraseLine();
		}
	}
	public function ansiSetGFX() {
		$args = func_get_args();
		$this->ansiOutput(implode(';',$args).'m');
	}
	public function ansiSaveCursor() {
		array_push($this->cursorPosStore,$this->cursorPos);
	}
	public function ansiLoadCursor() {
		$cursorPos = array_pop($this->cursorPosStore);
		if (is_array($cursorPos)) {
			$this->ansiMoveCursorPos($cursorPos['x'],$cursorPos['y']);
		}
	}
	public function ansiMoveCursor($direction,$amount) {
		switch(strtoupper($direction)) {
			case 'BACK':
			case 'B':
				$this->cursorPos['x'] = max(0,$this->cursorPos['x'] - $amount);
				return $this->ansiOutput($amount.'D');
			case 'FORWARD':
			case 'F':
				$this->cursorPos['x'] += $amount;
				return $this->ansiOutput($amount.'C');
			case 'UP':
			case 'U':
				$this->cursorPos['y'] += $amount;
				return $this->ansiOutput($amount.'B');
			case 'DOWN':
			case 'D':
				$this->cursorPos['y'] = max(0,$this->cursorPos['y'] - $amount);
				return $this->ansiOutput($amount.'A');
		}
	}
	private function ansiOutput($sequence) {
		print chr(27).'['.$sequence;
	}
	public function input($untilChar = "\n") {
        if ($this->isSimulation()) {
            $this->inputCount++;
            $name = 'cli_input'.$this->inputCount;
            echo $this->view->FormBuilder()->labelField('',$this->view->Form()->input($name,'text'));
            if ($this->view->getParam($name) != '') {
                $this->displayLine('Value: '.$this->view->getParam($name));
                return $this->view->getParam($name);
            }
            throw new Exception_Interrupt();
        }
		$output = '';
		while (true) {
			$chr = fgetc(STDIN);
			if ($chr == "\n") {
				$this->renderWidgets();
				$this->cursorPos['y']++;
				$this->cursorPos['x'] = 0;
			}
			if ($untilChar == $chr)
				break;
			$output .= $chr;


		}
		return $output;
	}
	public function inputMultipleChoice($text,$choices,$default = NULL) {
		if ($default === NULL || !in_array($default,$choices)) {
			$default = current($choices);
		}
		$default == String::toUpper($default);
		if ($this->isSimulation()) {
            $this->inputCount++;
            $name = 'cli_input'.$this->inputCount;
            
            $choice = $this->view->getParam($name);
            $choice = $choices[$choice];
            echo $this->view->FormBuilder()->labelField($text,
                    $this->view->Form()->selectOne($name,$default,new Basic_DataSet_Array($choices)));
			
            if ($this->view->hasParam($name) && in_array($choice,$choices)) {
                $this->displayLine('Choice: '.$choice);
                return $choice;
            }
            throw new Exception_Interrupt();
        }
        while(true) {
			$this->ansiSetGFX(01);
			echo $text.' ';
			$this->ansiSetGFX(34);
			echo ' ('.String::toUpper(implode('/',$choices)).') ';
			
			$this->ansiSetGFX(31);
			echo '['.$default.']:';
            
			$this->ansiSetGFX(00,30);
            $choice = String::toUpper($this->input());
			
			//$this->ansiMoveCursor('d',1);

            if ($choice === '')
                $choice = $default;
			if (!in_array($choice,$choices))
				continue;

			$this->ansiSetGFX(01);
			echo $text.': '.$choice;
			$this->ansiSetGFX(00);
			$this->displayLine('');

            if (in_array($choice,$choices))
                break;

        }
		return $choice;
	}
    public function inputBoolean($text,$default = true) {
        
        $defChar = (($default) ? 'Y' : 'N');
        if ($this->isSimulation()) {
            $this->inputCount++;
            $defVal = (($default) ? 'true' : '');
            $name = 'cli_input'.$this->inputCount;
            $choice = $this->view->getParam($name);
            echo $this->view->FormBuilder()->labelField($text,$this->view->Form()->checkbox($name,$defVal));
            
            if ($this->view->hasParam($name) && in_array($choice,array('true',''))) {
                $this->displayLine('Choice: '.$choice);
                return ($choice == 'true') ? true : false;
            }

            throw new Exception_Interrupt();
        }
        $result = $this->inputMultipleChoice($text,array('Y','N'),$defChar);

        if ($result == 'Y')
            return true;
        return false;
    }
    public function restartDaemon($pidFile) {
        $this->stopDaemon($pidFile);
        $this->startDaemon($pidFile);
        return true;
    }
    /**
     * Returns true if succesful start - and false if already running...
     *
     * @param string $pidFile
     * @return boolean
     */
    public function startDaemon($pidFile) {
        if (is_file($pidFile)) {
            $pid = file_get_contents($pidFile);
            if ($this->isPidAlive($pid))
                return false;
        }
            
        $pid = pcntl_fork();
        if ($pid) {
            //Parent process - exit...
            $fp = fopen($pidFile,'w');
            if ($fp) {
                fputs($fp,$pid,strlen($pid));
                fclose($fp);
                $this->displayLine('Daemon started');
                Pimple::end();
            } else {
                $this->killPid($pid);
                $this->displayErrorAndExit(sprintf('Could not create pidfile: %s',$pidFile));
            }
        }
        $this->currentPid = $pidFile;
        $this->daemon = true;
        return true;
    }
    public function isDaemonRunning() {
        if (file_exists($this->currentPid)) {
            return true;
        }
        return false;
    }
    public function isDaemon() {
        return $this->daemon;
    }

    public function stopDaemon($pidFile,$force = false) {
       $pid = @file_get_contents($pidFile);
       if (!$pid)
            Cli::getInstance()->displayErrorAndExit('Pid file empty or not found!');
       unlink($pidFile);
       if ($force) {
            $this->killPid($pid);
            $this->displayLine('Daemon stopped forcefully');
       } else {
            $i = 0;
            while(true) {
                if ($i > 60) {
                    $this->displayLine('Daemon could not be stopped gracefully');
                    $this->killPid($pid);
                    $this->displayLine('Daemon stopped forcefully');
                    break;
                }
                if (!$this->isPidAlive($pid)) {
                    $this->displayLine('Daemon stopped gracefully');
                    break;
                }
                $i++;
                sleep(1);
            }
       }
       return true;
    }

    public function killPid($pid) {
       posix_kill($pid,9);
       pcntl_waitpid ($pid,$temp = 0, WNOHANG);
       pcntl_wifexited ($temp);
    }
    public function isPidAlive($pid) {
        $cmd = "ps $pid";
        $this->exec($cmd, $output, $result);
        if(count($output) >= 2){
            return true;
        }
        return false;
    }
    public function getCurrentProcessName() {
        $currentPid = posix_getpid();
        $cmd = "ps $currentPid";
        $this->exec($cmd, $output, $result);
        if (count($output) > 1) {
            $name = substr($output[1],27);
            if (!$name) {
                throw new InvalidArgumentException('Command name not found in:'.$output[1]);
            }
            return $name;
        } else
            throw new InvalidArgumentException('Pid not found:'.$currentPid);

        return null;
    }
    public function getProcessCount($commandName = null) {
        if (!$commandName)
            $commandName = $this->getCurrentProcessName();
        if (!$commandName)
            throw new InvalidArgumentException('Command name not found');
        $cmd = "ps -Af | grep \"$commandName\"";
        $this->exec($cmd, $output, $result);
        return count($output) - 1;
    }
    public function exec($cmd, &$output, &$result) {
        return exec($cmd, $output, $result);
    }
	/**
	 * Get singleton instance
	 *
	 * @return Cli
	 */
	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}
    public static function sleep($seconds) {
        $ms = $seconds * 1000;
        $halfsecs = $ms / 500;
        for($i = 0; $i < $halfsecs;$i++) {
            usleep(500);
            if (self::getInstance()->isDaemon() && !self::getInstance()->isDaemonRunning()) {
                throw new Interrupt('Daemon was killed');
            }
        }
    }
    public static function usleep($ms) {
        usleep(500);
        if (self::getInstance()->isDaemon() && !self::getInstance()->isDaemonRunning()) {
            throw new Interrupt('Daemon was killed');
        }
        
    }
}


