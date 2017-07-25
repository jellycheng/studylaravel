<?php namespace Illuminate\Http;

use ArrayObject;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Renderable;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

class Response extends BaseResponse {

	use ResponseTrait;

	/**
	 * The original content of the response.
	 * 未处理前的内容
	 * @var mixed
	 */
	public $original;

	/**
	 * Set the content on the response.
	 *
	 * @param  mixed  $content
	 * @return $this
	 */
	public function setContent($content)
	{
		$this->original = $content;
		if ($this->shouldBeJson($content))
		{//判断内容是否数组 or ArrayObject子类对象 or 实现了Illuminate\Contracts\Support\Jsonable接口类对象 是则转成json字符串
			$this->headers->set('Content-Type', 'application/json');
			$content = $this->morphToJson($content);//转成json字符串
		} elseif ($content instanceof Renderable)
		{//是实现了的Illuminate\Contracts\Support\Renderable接口类对象
			$content = $content->render();
		}
		//设置相应内容content属性值
		return parent::setContent($content);
	}

	/**
	 * Morph the given content into JSON.
	 * 转成json字符串
	 * @param  mixed   $content
	 * @return string
	 */
	protected function morphToJson($content)
	{
		if ($content instanceof Jsonable) return $content->toJson();
		return json_encode($content);
	}

	/**
	 * Determine if the given content should be turned into JSON. 判断是否可以转成json字符串
	 * 判断是否数组 or ArrayObject子类对象 or 实现了Illuminate\Contracts\Support\Jsonable接口类对象
	 * @param  mixed  $content
	 * @return bool
	 */
	protected function shouldBeJson($content)
	{
		return $content instanceof Jsonable ||
			   $content instanceof ArrayObject ||
			   is_array($content);
	}

	/**
	 * Get the original response content.
	 * 获取未处理的内容
	 * @return mixed
	 */
	public function getOriginalContent()
	{
		return $this->original;
	}

}
