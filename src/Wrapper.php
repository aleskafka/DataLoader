<?php

namespace DataLoader;


class Wrapper
{

	/** @var DataLoader\DataLoader */
	private $parent;

	/** @var array */
	private $track = [];

	/** @var stdClass */
	private $data;


	/**
	 * @param DataLoader\DataLoader
	 * @param array
	 * @return void
	 **/
	public function __construct(DataLoader $parent, array $data=[])
	{
		$this->parent = $parent;
		$this->data = (object) $data;
		$this->track($this->data, "data");
	}


	/**
	 * @param string
	 * @param mixed
	 * @return self
	 **/
	public function __set($name, $view)
	{
		$this->data->$name = $view;
		$this->track($this->data->$name, "data.$name");
		return $this;
	}


	/**
	 * @return array
	 **/
	public function toArray()
	{
		do {
			$found = FALSE;
			foreach ($this->track as $path => $track) {
				if ($track[0]->packed) {
					unset($this->track[$path]);

					$track[0] = $track[0]->packed;
					$this->track($track[0], $path);
					$found = TRUE;

				} else if ($track[0] instanceof IdLiteral) {
					$this->parent->resolve($track[0]);
					$found = TRUE;
					continue;
				}
			}

		} while ($found);

		if ($this->track) {
			throw new \Exception('Error in packing data.');

		} else {
			return json_decode(json_encode($this->data), TRUE);
		}
	}


	private function track(&$data, $path)
	{
		if (is_array($data)) {
			foreach (array_keys($data) as $key) {
				!is_scalar($data[$key]) && $this->track($data[$key], "$path.$key");
			}

		} elseif ($data instanceof \stdClass) {
			foreach (array_keys((array) $data) as $key) {
				!is_scalar($data->$key) && $this->track($data->$key, "$path.$key");
			}

		} elseif ($data instanceof Literal) {
			$this->track[$path] = [&$data];
		}
	}

}
