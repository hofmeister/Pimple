<?php

class Model {

	private $data;
	private $columns = array();
    private $primKey;
	private $name;
	private $new = true;

	public function __construct($name,$columns = array(), $data = null) {
		$this->name = $name;
		$this->data = new stdClass();
		foreach ($columns as $colName) {
			if (!isset($this->data->$colName)) {
				$this->data->$colName = null;
			}
		}
		if ($data)
			$this->setData($data);
	}
    public function getPrimKey() {
        return $this->primKey;
    }

    public function setPrimKey($primKey) {
        $this->primKey = $primKey;
    }

	public function setData($data) {
		$this->new = false;
        if (is_array($data) || is_object($data)) {
            foreach($data as $key=>$value) {
                $this->$key($value);
            }
        }
        $this->unserialize();
		return $this;
	}

	public function __call($name, $arguments) {
		$colName = $name;
		$data = get_object_vars($this->data);
		
		if (array_key_exists($colName, $data)) {
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
		
		foreach($this->data as $colName=>$value) {
			$sqlFields[] = sprintf('`%s` = %s',ucfirst($colName),DB::value($value));
		}
		$sql .= ' '.implode(',',$sqlFields);
		return DB::q($sql);
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

}