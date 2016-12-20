<?php namespace Illuminate\Foundation;

use Illuminate\Support\Collection;

class Inspiring {

	/**
	 * Get an inspiring quote.
	 *
	 * Taylor & Dayle made this commit from Jungfraujoch. (11,333 ft.)
	 *
	 * @return string
	 */
	public static function quote()
	{	//从集合数组中随机取一个单元
		return Collection::make([

			'1.When there is no desire, all things are at peace. - Laozi',
			'2.Simplicity is the ultimate sophistication. - Leonardo da Vinci',
			'3.Simplicity is the essence of happiness. - Cedric Bledsoe',
			'4.Smile, breathe, and go slowly. - Thich Nhat Hanh',
			'5.Simplicity is an acquired taste. - Katharine Gerould',

		])->random();
	}

}
