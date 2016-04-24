<?php
//静态延迟实例化 

require_once 'parentA.php';

class subB extends parentA
{
    

	public function getDebugParam() {
		return array(
					'sName'=>$this->sName,//名字
					'aParams'=>$this->aParams,//参数
					'iParamsCount'=>$this->iParamsCount
		);
	
	}

}

