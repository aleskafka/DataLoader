<?php

namespace App\DataLoader;

use DataLoader\Loader;


class ItemCreated extends Loader
{


	public function pack(\stdClass $p, array $data)
	{
		$p->created = microtime(TRUE);
	}

}
