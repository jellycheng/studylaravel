<?php
//静态延迟实例化 

abstract class parentA
{
    protected $sAbstract;
    protected $sName;
    protected $aParams;
    protected $aResult;
    protected $iParamsCount;
    protected $oErrorHandler;

    public function __construct($sName, $aParams)
    {
        $this->sName        = $sName; //名字
        $this->aParams      = $aParams; //参数
        $this->iParamsCount = count($aParams); //参数个数
    }

	# subB::hello("jellyfun", array(1,3,4)); 则
    public static function __callStatic($sName, $aParams)
    {
        return new static($sName, $aParams); //静态延迟实例化，可以这么简单的认为： 实例化的是子类,并调用子类的构造方法
    }




}

