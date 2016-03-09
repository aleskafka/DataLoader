<?php

namespace DataLoader;


abstract class Loader
{

	/** @var DataLoader\DataLoader */
	private $loader;

	/** @var DataLoader\WhenLiteral */
	private static $whenPrototype;


	/**
	 * @return void
	 **/
	public function __construct()
	{
		if (self::$whenPrototype===NULL) {
			self::$whenPrototype = new WhenLiteral;
		}
	}


	/**
	 * @param DataLoader\DataLoader
	 * @return self
	 **/
	public function setLoader(DataLoader $loader)
	{
		$this->loader = $loader;
		return $this;
	}


	/**
	 * @param string
	 * @param scalar
	 * @return mixed
	 * @throws Exception
	 **/
	public function view($name, $id)
	{
		if (is_scalar($id)) {
			return $this->loader->id($name, $id);

		} else if (is_array($id)) {
			return $this->loader->set($name, $id);
		}

		throw new \Exception('Invalid loader required.');
	}


	/**
	 * @param mixed
	 * @param Closure
	 * @return void
	 **/
	public function when()
	{
		$args = func_get_args();
		$closure = end($args);

		if (!$closure instanceof \Closure) {
			throw new \Exception('Invalid arguments in when, Closure expected.');
		}

		$when = clone self::$whenPrototype;
		$when->closure = $closure;

		foreach (array_slice($args, 0, -1) as $i => $arg) {
			if ($arg instanceof Literal) {
				$when->wait[$i] = $arg;
				$when->data[$i] = NULL;

			} else {
				$when->data[$i] = $arg;
			}
		}

		if ($when->resolve()) {
			return $when->packed;

		} else {
			$this->loader->when($when);
			return $when;
		}
	}


	/**
	 * @param array
	 * @return array
	 **/
	public function load(array $ids)
	{
		return array_fill_keys($ids, []);
	}


	/**
	 * @param stdClass
	 * @param scalar
	 * @param array
	 * @return mixed
	 **/
	abstract public function pack(\stdClass $p, array $data);

}
