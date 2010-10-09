<?php

class Model {

	private $data;
	private $columns = array();
	private $noAutoUpdate = array();
    private $primIsAI = true;
    private $primKey;
	private $name;
	private $new = true;

	public function __construct($name,$columns = array(),$primKey = null,$data = null) {
		$this->name = $name;
		$this->data = new stdClass();
		$this->columns = $columns;
        $this->setPrimKey($primKey);
		if ($data)
			$this->setData($data);
	}
    public function setPrimIsAI($primIsAI) {
        $this->primIsAI = $primIsAI;
    }

    public function setNoAutoUpdate() {
		$this->noAutoUpdate = func_get_args();
	}

	public function getPrimKey() {
        return $this->primKey;
    }

    public function setPrimKey($primKey) {
        $this->primKey = $primKey;
    }
    public function getPrimValue() {
        if (!$this->primKey) return null;
        $key = $this->primKey;
        return $this->data->$key;
    }

	public function setData($data) {
        if (is_array($data) || is_object($data)) {
            foreach($data as $key=>$value) {
				$this->$key($value);
            }
        }
        if ($this->primKey) {
            $this->new = $this->getPrimValue() == null;
        }
        
        $this->unserialize();
		return $this;
	}

	public function __call($name, $arguments) {
		$colName = $name;

		if (in_array($colName,$this->columns)) {
			if (count($arguments) > 0) {
				$this->data->$colName = current($arguments);
				return $this;
			} else {
				return $this->data->$colName;
			}
		} else {
			throw new Exception(sprintf('Unknown field: %s.%s', $this->name, $colName), E_ERROR);
		}
	}
	public function  __get($name) {
		return $this->$name();
	}

	public function commit($keyName = null) {
		if ($this->new) {
			$this->insert();
		} else {
            if (!$this->checkKey($keyName)) {
                $keyName = $this->getPrimKey();
            }
            $this->update($keyName);
		}
	}
    public function checkKey($keyName) {
        if (!$keyName) {
            $keyName = $this->getPrimKey();
            return false;
        }
        if (!$keyName)
			throw new Exception ('Cannot update without key name');
        return true;
    }
	public function update($keyName = null) {
        if (!$this->checkKey($keyName)) {
            $keyName = $this->getPrimKey();
        }
        $this->serialize();
		$sql = sprintf('UPDATE `%s` SET ',$this->name);
		$sqlFields = array();
		foreach($this->data as $colName=>$value) {
			if ($keyName == $colName) continue;
			if (!in_array($colName,$this->noAutoUpdate))
				$sqlFields[] = sprintf('`%s` = %s',ucfirst($colName),DB::value($value));
		}
		return DB::q($sql.' '.implode(',',$sqlFields)
				.sprintf(' WHERE `%s` = %s',$keyName,DB::value($this->data->$keyName)));
	}
	public function load($value,$keyName = null) {
        if (!$this->checkKey($keyName)) {
            $keyName = $this->getPrimKey();
        }
		$data = DB::fetchOne(sprintf('SELECT * FROM `%s` WHERE `%s` = %s',$this->name,$keyName,DB::value($value)));
        if ($data)
			$this->setData($data);
		return $this;
	}
	public function insert() {
        $this->serialize();
		$sql = sprintf('INSERT INTO `%s` SET ',$this->name);
		$sqlFields = array();
		$primKey = $this->getPrimKey();
		foreach($this->data as $colName=>$value) {
            if ($colName == $primKey && $this->primIsAI) continue;
			$sqlFields[] = sprintf('`%s` = %s',ucfirst($colName),DB::value($value));
		}
		$sql .= ' '.implode(',',$sqlFields);
		$result =  DB::q($sql);
        if ($result && $primKey && $this->primIsAI) {
            $this->$primKey(DB::lastId());
        }
	}
	public function replace() {
        $this->serialize();
		$sql = sprintf('REPLACE INTO `%s` SET ',$this->name);
		$sqlFields = array();
		$primKey = $this->getPrimKey();
		foreach($this->data as $colName=>$value) {
            if ($colName == $primKey && $this->primIsAI) continue;
			$sqlFields[] = sprintf('`%s` = %s',ucfirst($colName),DB::value($value));
		}
		$sql .= ' '.implode(',',$sqlFields);
		$result =  DB::q($sql);
        if ($result && $primKey && $this->primIsAI) {
            $this->$primKey(DB::lastId());
        }
	}
	public function delete($keyName = null) {
        if (!$this->checkKey($keyName)) {
            $keyName = $this->getPrimKey();
        }
		$sql = sprintf('DELETE FROM `%s` WHERE `%s` = %s',$this->name,$keyName,$this->$keyName());
		return DB::q($sql);
	}
    public function serialize() {
        
    }
    public function unserialize() {

    }
	public function isNew() {
		return $this->new;
	}
    public function toArray() {
        $array = array();
        foreach($this->data as $key=>$val) {
            $array[$key] = $val;
        }
        return $array;
    }

}