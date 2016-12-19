<?php namespace Illuminate\Events;

use Illuminate\Contracts\Queue\Job; //job接口
use Illuminate\Contracts\Container\Container;//容器接口

class CallQueuedHandler {

	/**
	 * The container instance.
	 * 容器对象
	 * @var \Illuminate\Contracts\Container\Container
	 */
	protected $container;

	/**
	 * Create a new job instance.
	 *
	 * @param  \Illuminate\Contracts\Container\Container  $container
	 * @return void
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * Handle the queued job.
	 * 1.类中引入了job trait方法则注入job对象
     * 2.调用类的method方法
	 * @param  \Illuminate\Contracts\Queue\Job  $job  job对象
	 * @param  array  $data = ['class'=>'类名或类别名或代号','method'=>'类方法', 'data'=>[传递给类方法的参数]]
	 * @return void
	 */
	public function call(Job $job, array $data)
	{
		$handler = $this->setJobInstanceIfNecessary(
			$job, $this->container->make($data['class'])
		);

		call_user_func_array(
			[$handler, $data['method']], unserialize($data['data'])
		);

		if ( ! $job->isDeletedOrReleased())
		{//删除job
			$job->delete();
		}
	}

	/**
	 * Set the job instance of the given class if necessary.
	 * 为$instance对象注入job对象
	 * @param  \Illuminate\Contracts\Queue\Job  $job
	 * @param  mixed  $instance
	 * @return mixed
	 */
	protected function setJobInstanceIfNecessary(Job $job, $instance)
	{
		if (in_array('Illuminate\Queue\InteractsWithQueue', class_uses_recursive(get_class($instance))))
		{//$instance对象对应的类名有引用Illuminate\Queue\InteractsWithQueue trait类
			$instance->setJob($job);//如果job对象
		}

		return $instance;
	}

	/**
	 * Call the failed method on the job instance.
	 * 调用class对应类的failed(序列化后的”其它数据“字符串值)方法
	 * @param  array  $data = ['class'=>'类名or类代号or别名', 其它数据]
	 * @return void
	 */
	public function failed(array $data)
	{
		$handler = $this->container->make($data['class']);

		if (method_exists($handler, 'failed'))
		{
			call_user_func_array([$handler, 'failed'], unserialize($data));
		}
	}

}
