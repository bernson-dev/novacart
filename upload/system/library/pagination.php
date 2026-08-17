<?php
/**
* Pagination class
*/
class Pagination {
	public $total = 0;
	public $page = 1;
	public $limit = 20;
	public $num_links = 5; // Количество ссылок вокруг текущей страницы
	public $url = '';
	//public $text_go_to_page = '&#8594;'; // Стрелка вправо, символ для кнопки Go
	public $text_dots = '...';

	/**
	* Генерирует HTML-код пагинации.
	*
	* @return string HTML-код пагинации
	*/
	public function render() {
		$total = $this->total;

		if ($this->page < 1) {
			$page = 1;
		} else {
			$page = $this->page;
		}

		if (!(int)$this->limit) {
			$limit = 10;
		} else {
			$limit = $this->limit;
		}

		$num_links = $this->num_links;
		$num_pages = ceil($total / $limit);

		$this->url = str_replace('%7Bpage%7D', '{page}', $this->url);

		$output = '<ul class="pagination" data-url="' . $this->url . '" style="display: flex; flex-wrap: wrap;">';

		// --- Логика отображения страниц с точками ---
		if ($num_pages > 1) {
			$max_visible_links = $num_links;

			$start = $page - floor($max_visible_links / 2);
			$end = $page + floor($max_visible_links / 2);

			if ($start < 1) {
				$end += abs($start) + 1;
				$start = 1;
			}

			if ($end > $num_pages) {
				$start -= ($end - $num_pages);
				$end = $num_pages;
			}

			if ($start < 1) {
				$start = 1;
			}

			// Всегда показываем первую страницу, если она не в основном блоке
			if ($start > 1) {
				$output .= '<li><a href="' . str_replace(array('&amp;page={page}', '?page={page}', '&page={page}'), '', $this->url) . '">' . 1 . '</a></li>';
				// Кнопка с тремя точками для перехода назад
				if ($start > 2) {
					$prev_chunk_page = max(1, $page - $max_visible_links);
					$output .= '<li><a href="' . str_replace('{page}', $prev_chunk_page, $this->url) . '">' . $this->text_dots . '</a></li>';
				}
			}

			// Вывод основного блока страниц
			for ($i = $start; $i <= $end; $i++) {
				if ($page == $i) {
					$output .= '<li class="active"><span>' . $i . '</span></li>';
				} else {
					if ($i === 1) {
						$output .= '<li><a href="' . str_replace(array('&amp;page={page}', '?page={page}', '&page={page}'), '', $this->url) . '">' . $i . '</a></li>';
					} else {
						$output .= '<li><a href="' . str_replace('{page}', $i, $this->url) . '">' . $i . '</a></li>';
					}
				}
			}

			// Всегда показываем последнюю страницу, если она не в основном блоке
			if ($end < $num_pages) {
				// Кнопка с тремя точками для перехода вперед
				if ($end < $num_pages - 1) {
					$next_chunk_page = min($num_pages, $page + $max_visible_links);
					$output .= '<li><a href="' . str_replace('{page}', $next_chunk_page, $this->url) . '">' . $this->text_dots . '</a></li>';
				}
				$output .= '<li><a href="' . str_replace('{page}', $num_pages, $this->url) . '">' . $num_pages . '</a></li>';
			}
		}

		// Добавляем поле ввода для перехода на страницу и кнопку "Go"
		/*
		if ($num_pages > 1) {
			$output .= '<li class="pagination-goto" style="margin-left: 6px; display: flex; align-items: center;">';
			// Поле ввода
			$output .= '<input type="number" min="1" max="' . $num_pages . '" value="' . $page . '" class="form-control pagination-goto-input" ';
			$output .= 'style="width: 60px; height: 34px; padding: 6px 8px; margin-right: 5px; box-sizing: border-box; vertical-align: middle;"/>';
			// Символ '/' и общее количество страниц после поля ввода
			$output .= '<span style="margin-right: 6px; color: #777; vertical-align: middle;"> / ' . $num_pages . '</span>';
			// Кнопка "Перейти" с onclick
			$output .= '<button type="button" class="btn btn-default pagination-goto-button" onclick="location = \'' . str_replace('{page}', '\' + this.parentNode.querySelector(\'.pagination-goto-input\').value + \'', $this->url) . '\';">' . $this->text_go_to_page . '</button>';
			$output .= '</li>';
		}
		*/

		$output .= '</ul>';

		if ($total > 0 && $num_pages > 0) {
			return $output;
		} else {
			return '';
		}
	}
}
