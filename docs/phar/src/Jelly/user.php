<?php
namespace Jelly;
//$obj = new \Jelly\user(); $data = $obj->setUserName('jelly')->getAll();
class user {

	protected $_userName = "default name";
	protected $_pwd = "default pwd";
	protected $_age = "30";


	public function setUserName($userName) {
		$this->_userName = $userName;
		return $this;
	}

	public function getUserName() {
		return $this->_userName;
	}

	public function getAll() {
		return array(
					'userName'=>$this->_userName,
					'pwd'=>$this->_pwd,
					'age'=>$this->_age,
					);
	
	}

}
