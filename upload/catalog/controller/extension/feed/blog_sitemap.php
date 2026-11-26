<?php
class ControllerExtensionFeedBlogSitemap extends Controller {
	private function excluded($route, array $params) {
		if (!$this->config->get('seo_noindex_rules')) return false;
		require_once DIR_SYSTEM . 'library/novacart/seo_noindex.php';
		return \Novacart\SeoNoindex::matches($this->config, (int)$this->config->get('config_store_id'), $route, $params);
	}

	public function index() {
		if ($this->config->get('feed_blog_sitemap_status')) {
			$this->load->model('blog/article');
			$this->load->model('blog/category');
			$this->load->model('localisation/language');
			
			$output = '<?xml version="1.0" encoding="UTF-8"?>';
			$output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">';
			$output .= $this->getCategoriesAndArticles(0);
			$output .= '</urlset>';

			$this->response->addHeader('Content-Type: application/xml');
			$this->response->setOutput($output);
		}
	}

	protected function getCategoriesAndArticles($parent_id, $current_path = '') {
		$output = '';
		
		$lang_id = $this->config->get('config_language_id');
		$languages = $this->model_localisation_language->getLanguages();
		$results = $this->model_blog_category->getCategoriesByParentId($parent_id);
	
		foreach ($results as $result) {
			if (!$current_path) {
				$blog_category_id = $result;
			} else {
				$blog_category_id = $current_path.'_'.$result;
			}

			if (!$this->excluded('blog/category', array('blog_category_id' => $blog_category_id))) {
				$output .= '<url>';
				$output .= '<loc>'.$this->url->link('blog/category', 'blog_category_id='.$blog_category_id).'</loc>';
			
				if (count($languages) > 1) {
					foreach ($languages as $lang) {
						$this->config->set('config_language_id', $lang['language_id']);
						$output .= '<xhtml:link rel="alternate" hreflang="'.$lang['code'].'" href="'.$this->url->link('blog/category', 'blog_category_id='.$blog_category_id).'" />';
					}
			
					$this->config->set('config_language_id', $lang_id);
				}
			
				$output .= '<changefreq>weekly</changefreq>';
				$output .= '<priority>0.7</priority>';
				$output .= '</url>';

			}
			$articles = $this->model_blog_article->getArticles(['filter_blog_category_id' => $result]);
			
			foreach ($articles as $article) {
				if ($this->excluded('blog/article', array('article_id' => $article['article_id']))) continue;
				$output .= '<url>';
				$output .= '<loc>'.$this->url->link('blog/article', 'blog_category_id='.$blog_category_id.'&article_id='.$article['article_id']).'</loc>';
				
				if (count($languages) > 1) {
					foreach ($languages as $lang) {
						$this->config->set('config_language_id', $lang['language_id']);
						$output .= '<xhtml:link rel="alternate" hreflang="'.$lang['code'].'" href="'.$this->url->link('blog/article', 'blog_category_id='.$blog_category_id . '&article_id='.$article['article_id']).'" />';
					}
				
					$this->config->set('config_language_id', $lang_id);
				}
				
				$output .= '<lastmod>'.date('c', strtotime($article['date_added'])).'</lastmod>';
				$output .= '<changefreq>weekly</changefreq>';
				$output .= '<priority>1.0</priority>';
				$output .= '</url>';
			}

			$output .= $this->getCategoriesAndArticles($result, $blog_category_id);
		}

		return $output;
	}
}
