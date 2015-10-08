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

use ICanBoogie\Accessor\AccessorTrait;

/**
 * Representation of a listview column.
 *
 * @property-read string $class
 * @property-read string $title
 */
class ListViewColumn
{
	use AccessorTrait;

	protected $id;
	protected $options;
	protected $listview;

	public function __construct(ListView $listview, $id, array $options = [])
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
	 * Render the content of the column's header.
	 */
	public function render_header()
	{
		return $this->title;
	}

	/**
	 * Render the content of a column cell.
	 *
	 * @param object $record
	 */
	public function render_cell($record)
	{
		return $record->{ $this->id };
	}

	/**
	 * Translates and formats a string.
	 *
	 * @param string $native
	 * @param array $args
	 * @param array $options
	 *
	 * @return string
	 */
	protected function t($native, array $args = [], array $options = [])
	{
		return $this->listview->t($native, $args, $options);
	}
}
