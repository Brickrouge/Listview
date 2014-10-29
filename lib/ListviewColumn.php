<?php

/*
 * This file is part of the Brickrouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brickrouge;

/**
 * Representation of a listview column.
 */
class ListViewColumn
{
	use \ICanBoogie\GetterTrait;

	protected $id;
	protected $options;
	protected $listview;

	public function __construct(ListView $listview, $id, array $options=[])
	{
		$this->id = $id;
		$this->listview = $listview;
		$this->options = $options + [

			'class' => null,
			'title' => null

		];
	}

	protected function get_class()
	{
		return $this->options['class'];
	}

	protected function get_title()
	{
		return $this->options['title'];
	}

	/**
	 * Render the header of the column.
	 */
	public function render_header()
	{
		return $this->title;
	}

	/**
	 * Render a cell of the column.
	 *
	 * @param mixed $row
	 */
	public function render_cell($row)
	{
		return $row->{ $this->id };
	}
}
