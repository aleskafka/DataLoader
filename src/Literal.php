<?php

namespace DataLoader;


class Literal
{

	/** @var mixed|NULL */
	public $packed;

}


class IdLiteral extends Literal
{

	/** @var string */
	public $name;

	/** @var string */
	public $id;

}


class WhenLiteral extends Literal
{

	/** @var array */
	public $wait = [];

	/** @var array */
	public $data = [];

	/** @var Closure */
	public $closure;


	/**
	 * @return bool
	 **/
	public function resolve()
	{
		foreach ($this->wait as $i => $literal) {
			if ($literal->packed instanceof Literal) {
				$this->wait[$i] = $literal->packed;
				return $this->resolve();

			} elseif ($literal->packed!==NULL) {
				unset($this->wait[$i]);
				$this->data[$i] = $literal->packed;
			}
		}

		if (empty($this->wait)) {
			$this->packed = call_user_func_array($this->closure, $this->data);
		}

		return empty($this->wait);
	}

}
