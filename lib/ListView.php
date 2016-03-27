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
 * A listview element.
 *
 * @property-read ListViewColumn[] $columns
 * @property array $records
 */
class ListView extends Element
{
	const COLUMNS = '#listview-columns';
	const RECORDS = '#listview-records';

	/**
	 * Columns use to display the data of the records.
	 *
	 * @var ListViewColumn[]
	 */
	protected $columns;

	public function __construct(array $attributes = [])
	{
		unset($this->columns);

		parent::__construct('div', $attributes);
	}

	/**
	 * Adds the following class names:
	 *
	 * - `listview`
	 *
	 * @inheritdoc
	 */
	protected function alter_class_names(array $class_names)
	{
		return parent::alter_class_names($class_names) + [

			'listview' => true

		];
	}

	/**
	 * Returns the columns of the listview.
	 *
	 * @return ListViewColumn[]
	 */
	protected function lazy_get_columns()
	{
		$columns = $this[self::COLUMNS];
		$columns = $this->resolve_columns($columns);

		return $columns;
	}

	protected function resolve_columns(array $columns)
	{
		$resolved_columns = $columns;

		foreach ($resolved_columns as $id => &$column)
		{
			if (is_string($column))
			{
				$column = [ $column, [] ];
			}

			if (is_array($column))
			{
				list($construct, $options) = $column;
				$column = new $construct($this, $id, $options);
			}
			else
			{
				throw new \UnexpectedValueException("Expected column definition to be a string or an array.");
			}
		}

		return $resolved_columns;
	}

	/**
	 * Returns the records to display.
	 *
	 * @return array[]
	 */
	protected function get_records()
	{
		return $this[self::RECORDS];
	}

	/**
	 * @return Element
	 */
	protected function render_inner_html()
	{
		$records = $this->records;

		if (!$records)
		{
			return $this->render_no_records();
		}

		$headers = $this->render_headers();
		$rendered_cells = $this->render_cells();

		$this->alter_headers($headers);
		$this->alter_cells($rendered_cells);

		$columns_classes = [];

		foreach ($this->columns as $column_id => $column)
		{
			$columns_classes[$column_id] = trim('cell--' . normalize($column_id) . ' ' . $column->class);
		}

		$decorated_headers = $this->decorate_headers($headers, $columns_classes);
		$decorated_cells = $this->decorate_cells($rendered_cells, $columns_classes);

		$this->alter_decorated_headers($decorated_headers);
		$this->alter_decorated_cells($decorated_cells);

		$rendered_rows = $this->render_rows($decorated_cells);
		$this->alter_rows($rendered_rows);

		return $this->render_table($decorated_headers, $rendered_rows);
	}

	/**
	 * Renders the column headers.
	 *
	 * @return string[]
	 */
	protected function render_headers()
	{
		$headers = [];

		foreach ($this->columns as $id => $column)
		{
			$headers[$id] = $column->render_header();
		}

		return $headers;
	}

	/**
	 * Renders the cells of the columns.
	 *
	 * The method returns an array with the following layout:
	 *
	 *     [<column_id>][] => <cell_content>
	 *
	 * @return array
	 */
	protected function render_cells()
	{
		$rendered_cells = [];

		foreach ($this->columns as $id => $column)
		{
			foreach ($this->records as $record)
			{
				try
				{
					$content = (string) $column->render_cell($record);
				}
				catch (\Exception $e)
				{
					$content = render_exception($e);
				}

				$rendered_cells[$id][] = $content;
			}
		}

		return $rendered_cells;
	}

	/**
	 * Alters headers content.
	 *
	 * @param array $headers
	 */
	protected function alter_headers(array &$headers)
	{

	}

	/**
	 * Alters cells content.
	 *
	 * @param array $rendered_cells
	 */
	protected function alter_cells(array &$rendered_cells)
	{

	}

	/**
	 * Decorates headers content with `TH.<column_class>/DIV` elements.
	 *
	 * @param array $headers
	 * @param array $columns_classes
	 *
	 * @return array
	 */
	protected function decorate_headers(array $headers, array $columns_classes)
	{
		$decorated_headers = [];

		foreach ($headers as $column_id => $html)
		{
			$decorated_header = $this->decorate_header($html, $column_id);
			$decorated_header->add_class($columns_classes[$column_id]);

			$decorated_headers[$column_id] = $decorated_header;
		}

		return $decorated_headers;
	}

	/**
	 * Decorates a header content with a `TH` element.
	 *
	 * @param string $content
	 * @param string $column_column_id
	 *
	 * @return Element
	 */
	protected function decorate_header($content, $column_column_id)
	{
		return new Element('th', [

			Element::INNER_HTML => $content ?: '&nbsp;',

			'class' => 'header--' . \ICanBoogie\normalize($column_column_id)

		]);
	}

	/**
	 * Decorates cells content in `TD.<column_class>` elements.
	 *
	 * @param array $cells
	 * @param array $columns_classes
	 *
	 * @return array
	 */
	protected function decorate_cells(array $cells, $columns_classes)
	{
		$decorated_cells = [];

		foreach ($cells as $column_id => $rows)
		{
			foreach ($rows as $i => $html)
			{
				$decorated_cells[$column_id][$i] = new Element('td', [

					Element::INNER_HTML => $html ?: '&nbsp;',

					'class' => $columns_classes[$column_id]

				]);
			}
		}

		return $decorated_cells;
	}

	/**
	 * Alters decorated headers
	 *
	 * @param Element[] $decorated_headers
	 */
	protected function alter_decorated_headers(array &$decorated_headers)
	{

	}

	/**
	 * Alters decorated cells.
	 *
	 * @param array $decorated_cells
	 */
	protected function alter_decorated_cells(array &$decorated_cells)
	{

	}

	/**
	 * Renders the specified rows.
	 *
	 * The rows are rendered as an array of {@link Element} instances representing `TR` elements.
	 *
	 * @param array $decorated_cells
	 *
	 * @return Element[]
	 */
	protected function render_rows(array $decorated_cells)
	{
		$rendered_rows = [];

		foreach ($this->columns_to_rows($decorated_cells) as $cells)
		{
			$rendered_rows[] = new Element('tr', [

				Element::CHILDREN => $cells

			]);
		}

		return $rendered_rows;
	}

	/**
	 * Converts rendered cells to rows.
	 *
	 * @param array $rendered_cells
	 *
	 * @return array
	 */
	protected function columns_to_rows(array $rendered_cells)
	{
		$rows = [];

		foreach ($rendered_cells as $column_id => $cells)
		{
			foreach ($cells as $i => $cell)
			{
				$rows[$i][$column_id] = $cell;
			}
		}

		return $rows;
	}

	/**
	 * Alters rows.
	 *
	 * @param Element[] $rendered_rows Reference to the rendered rows.
	 */
	protected function alter_rows(array &$rendered_rows)
	{

	}

	/**
	 * Renders `TABLE` markup.
	 *
	 * @param Element[] $decorated_headers
	 * @param Element[] $rendered_rows
	 *
	 * @return Element
	 */
	protected function render_table(array $decorated_headers, array $rendered_rows)
	{
		return new Element('table', [

			Element::CHILDREN => [

				$this->render_head($decorated_headers),
				$this->render_foot(),
				$this->render_body($rendered_rows)

			]

		]);
	}

	/**
	 * Renders `THEAD` markup.
	 *
	 * @param Element[] $decorated_headers
	 *
	 * @return Element
	 */
	protected function render_head($decorated_headers)
	{
		return new Element('thead', [

			Element::CHILDREN => [

				new Element('tr', [

					Element::CHILDREN => $decorated_headers

				])
			]
		]);
	}

	/**
	 * Renders `TFOOT` markup.
	 */
	protected function render_foot()
	{

	}

	/**
	 * Renders `TBODY` markup.
	 *
	 * @return Element An {@link Element} instance representing a `tbody` element. Its children
	 * are the rendered rows returned by {@link render_rows()}.
	 *
	 * @param Element[] $rendered_rows
	 */
	protected function render_body(array $rendered_rows)
	{
		return new Element('tbody', [ Element::CHILDREN => $rendered_rows ]);
	}

	/**
	 * Renders a notice when there is no record to render.
	 *
	 * @return Alert
	 */
	protected function render_no_records()
	{
		return new Alert("There is no record to display.", [

			Alert::CONTEXT => Alert::CONTEXT_INFO,

			'class' => 'alert alert-block listview-alert'

		]);
	}
}
