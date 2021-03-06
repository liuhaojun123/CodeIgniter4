<?php namespace CodeIgniter\Debug\Toolbar\Collectors;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2017 British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package      CodeIgniter
 * @author       CodeIgniter Dev Team
 * @copyright    2014-2017 British Columbia Institute of Technology (https://bcit.ca/)
 * @license      https://opensource.org/licenses/MIT	MIT License
 * @link         https://codeigniter.com
 * @since        Version 4.0.0
 * @filesource
 */
use CodeIgniter\Services;
use CodeIgniter\View\RendererInterface;

/**
 * Views collector
 */
class Events extends BaseCollector
{

	/**
	 * Whether this collector has data that can
	 * be displayed in the Timeline.
	 *
	 * @var bool
	 */
	protected $hasTimeline = false;

	/**
	 * Whether this collector needs to display
	 * content in a tab or not.
	 *
	 * @var bool
	 */
	protected $hasTabContent = true;

	/**
	 * Whether this collector has data that
	 * should be shown in the Vars tab.
	 *
	 * @var bool
	 */
	protected $hasVarData = false;

	/**
	 * The 'title' of this Collector.
	 * Used to name things in the toolbar HTML.
	 *
	 * @var string
	 */
	protected $title = 'Events';

	/**
	 * Instance of the Renderer service
	 * @var RendererInterface
	 */
	protected $viewer;

	//--------------------------------------------------------------------

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->viewer = Services::renderer(null, true);
	}

	//--------------------------------------------------------------------

	/**
	 * Child classes should implement this to return the timeline data
	 * formatted for correct usage.
	 *
	 * @return mixed
	 */
	protected function formatTimelineData(): array
	{
		$data = [];

		$rows = $this->viewer->getPerformanceData();

		foreach ($rows as $name => $info)
		{
			$data[] = [
				'name'		 => 'View: ' . $info['view'],
				'component'	 => 'Views',
				'start'		 => $info['start'],
				'duration'	 => $info['end'] - $info['start']
			];
		}

		return $data;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the HTML to fill the Events tab in the toolbar.
	 *
	 * @return string The data formatted for the toolbar.
	 */
	public function display(): string
	{
		$parser = \Config\Services::parser(BASEPATH . 'Debug/Toolbar/Views/', null,false);

		$data = [
			'events' => []
		];

		foreach (\CodeIgniter\Events\Events::getPerformanceLogs() as $row)
		{
			$key = $row['event'];

			if (! array_key_exists($key, $data['events']))
			{
				$data['events'][$key] = [
					'event' => $key,
					'duration' => number_format(($row['end']-$row['start']) * 1000, 2),
					'count' => 1,
				];

				continue;
			}

			$data['events'][$key]['duration'] += number_format(($row['end']-$row['start']) * 1000, 2);
			$data['events'][$key]['count']++;
		}

		$output = $parser->setData($data)
		                 ->render('_events.tpl');

		return $output;
	}

	//--------------------------------------------------------------------

	/**
	 * Gets the "badge" value for the button.
	 */
	public function getBadgeValue()
	{
		return count(\CodeIgniter\Events\Events::getPerformanceLogs());
	}
}
