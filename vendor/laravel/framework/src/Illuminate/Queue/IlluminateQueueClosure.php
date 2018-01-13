<?php

use Illuminate\Contracts\Encryption\Encrypter as EncrypterContract;
//该类是自动查找的【即可以直接new IlluminateQueueClosure(Encrypter接口类);】，在 vendor/composer/autoload_classmap.php中配置
class IlluminateQueueClosure {

	/**
	 * The encrypter instance.
	 * 接口类对象
	 * @var \Illuminate\Contracts\Encryption\Encrypter  $crypt
	 */
	protected $crypt;

	/**
	 * Create a new queued Closure job.
	 *
	 * @param  \Illuminate\Contracts\Encryption\Encrypter  $crypt
	 * @return void
	 */
	public function __construct(EncrypterContract $crypt)
	{
		$this->crypt = $crypt;
	}

	/**
	 * Fire the Closure based queue job.
	 *
	 * @param  \Illuminate\Contracts\Queue\Job  $job
	 * @param  array  $data
	 * @return void
	 */
	public function fire($job, $data)
	{
		$closure = unserialize($this->crypt->decrypt($data['closure']));//解密且解序列化，返回闭包

		$closure($job);
	}

}
