<?php

namespace DataLoader;

use Nette\DI\Container;
use Nette\DI\MissingServiceException;


class DataLoader
{

	/** @var Nette\DI\Container */
	private $container;

	/** @var array */
	private $services = [];

	/** @var SplObjectStorage */
	private $track;

	/** @var SplObjectStorage */
	private $when;

	/** @var DataLoader\IdLiteral */
	private static $idPrototype;


	/**
	 * @param Nette\DI\Container
	 * @return void
	 **/
	public function __construct(Container $container)
	{
		$this->container = $container;
		$this->track = new \SplObjectStorage;
		$this->when = new \SplObjectStorage;

		if (self::$idPrototype===NULL) {
			self::$idPrototype = new IdLiteral;
		}
	}


	/**
	 * @param array
	 * @return DataLoader\Wrapper
	 **/
	public function createWrapper(array $data=[])
	{
		return new Wrapper($this, $data);
	}


	/**
	 * @param array
	 * @return array
	 **/
	public function toArray(array $data=[])
	{
		return $this->createWrapper($data)->toArray();
	}


	/**
	 * @param string
	 * @return mixed
	 * @throws Exception
	 **/
	public function getService($name)
	{
		$service = NULL;

		try {
			$service = $this->container->getByType($name);

		} catch (MissingServiceException $e) {
			if ($this->container->hasService("dataloader.$name")) {
				$service = $this->container->getService("dataloader.$name");
			}
		}

		if ($service instanceof Loader) {
			$service->setLoader($this);
			return $service;
		}

		throw new \Exception("Missing loader service $name.");
	}


	/**
	 * @param string
	 * @param scalar
	 * @return DataLoader\Id
	 **/
	public function id($name, $id)
	{
		$literal = clone self::$idPrototype;
		$literal->name = $name;
		$literal->id = $id;

		$this->track->attach($literal);
		return $literal;
	}


	/**
	 * @param string
	 * @param array
	 * @return DataLoader\Set
	 **/
	public function set($name, array $ids)
	{
		foreach ($ids as $key => $id) {
			$ids[$key] = $this->id($name, $id);
		}

		return $ids;
	}


	/**
	 * @param DataLoader\WhenLiteral
	 * @return void
	 **/
	public function when(WhenLiteral $when)
	{
		$this->when->attach($when);
	}


	/**
	 * @param DataLoader\*
	 * @return mixed
	 **/
	public function resolve($literal)
	{
		if ($literal instanceof Literal) {
			if ($literal->packed===NULL) {
				$this->track->attach($literal);
				$this->resolveLoader($literal->name);

				foreach (iterator_to_array($this->when) as $when) {
					if ($when->resolve()) {
						$this->when->detach($when);
					}
				}
			}

			return $literal->packed;
		}
	}


	/**
	 * @param string
	 * @return void
	 **/
	private function resolveLoader($name)
	{
		if (empty($this->services[$name])) {
			$this->services[$name] = $this->getService($name);
		}

		$ids = [];
		foreach ($this->track as $track) {
			if ($track->packed===NULL && $track->name===$name) {
				$ids[$track->id][] = $track;
			}
		}

		if ($ids) {
			$data = $this->services[$name]->load(array_keys($ids));

			foreach ($ids as $id => $tracks) {
				$package = (object) ['id' => $id];
				isset($data[$id]) && $this->services[$name]->pack($package, $data[$id]);

				foreach ($tracks as $track) {
					$track->packed = $package;
				}
			}
		}
	}

}
