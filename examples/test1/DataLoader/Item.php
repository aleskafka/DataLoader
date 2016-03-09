<?php

namespace App\DataLoader;

use DataLoader\Loader;


class Item extends Loader
{


	public function pack(\stdClass $p, array $data)
	{
		$p->stamp = microtime(TRUE);
		$p->item = $this->view('item.data', $p->id);

		$p->created = $this->when($p->item, function($item) {
			return $this->view('item.created', $item->id);
		});
	}

}
