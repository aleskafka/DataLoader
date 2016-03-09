<?php

namespace App\DataLoader;

use DataLoader\Loader;


class ItemData extends Loader
{


	public function pack(\stdClass $p, array $data)
	{
		$p->data = microtime(TRUE);
	}

}
