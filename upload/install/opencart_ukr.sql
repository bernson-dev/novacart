-- Экспорт базы данных
-- Ukraine local

SET NAMES utf8mb4;

--
-- Table structure for table `oc_address`
--

DROP TABLE IF EXISTS `oc_address`;
CREATE TABLE `oc_address` (
  `address_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `firstname` varchar(32) NOT NULL,
  `lastname` varchar(32) NOT NULL,
  `company` varchar(40) NOT NULL,
  `address_1` varchar(128) NOT NULL,
  `address_2` varchar(128) NOT NULL,
  `city` varchar(128) NOT NULL,
  `postcode` varchar(10) NOT NULL,
  `country_id` int(11) NOT NULL DEFAULT '0',
  `zone_id` int(11) NOT NULL DEFAULT '0',
  `custom_field` text NOT NULL,
  PRIMARY KEY (`address_id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_address`
--


----------------------------------------------------------

--
-- Table structure for table `oc_api`
--

DROP TABLE IF EXISTS `oc_api`;
CREATE TABLE `oc_api` (
  `api_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL,
  `key` text NOT NULL,
  `status` tinyint(1) NOT NULL,
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`api_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_api`
--


----------------------------------------------------------

--
-- Table structure for table `oc_api_ip`
--

DROP TABLE IF EXISTS `oc_api_ip`;
CREATE TABLE `oc_api_ip` (
  `api_ip_id` int(11) NOT NULL AUTO_INCREMENT,
  `api_id` int(11) NOT NULL,
  `ip` varchar(40) NOT NULL,
  PRIMARY KEY (`api_ip_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_api_ip`
--


----------------------------------------------------------

--
-- Table structure for table `oc_api_session`
--

DROP TABLE IF EXISTS `oc_api_session`;
CREATE TABLE `oc_api_session` (
  `api_session_id` int(11) NOT NULL AUTO_INCREMENT,
  `api_id` int(11) NOT NULL,
  `session_id` varchar(32) NOT NULL,
  `ip` varchar(40) NOT NULL,
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  `expires` datetime NOT NULL,
  PRIMARY KEY (`api_session_id`),
  KEY `session_id` (`session_id`),
  KEY `api_id` (`api_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_api_session`
--


----------------------------------------------------------

--
-- Table structure for table `oc_article`
--

DROP TABLE IF EXISTS `oc_article`;
CREATE TABLE `oc_article` (
  `article_id` int(11) NOT NULL AUTO_INCREMENT,
  `image` varchar(255) DEFAULT NULL,
  `date_available` date NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `article_review` tinyint(1) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `noindex` tinyint(1) NOT NULL DEFAULT '1',
  `date_added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `viewed` int(5) NOT NULL DEFAULT '0',
  `gstatus` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`article_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_article`
--

INSERT INTO `oc_article` (`article_id`, `image`, `date_available`, `sort_order`, `article_review`, `status`, `noindex`, `date_added`, `date_modified`, `viewed`, `gstatus`) VALUES
(120, 'catalog/cart.png', '0000-00-00', 1, 1, 1, 1, '2014-04-08 04:26:00', '2015-06-29 09:35:55', 8, 0),
(123, 'catalog/demo/canon_eos_5d_2.jpg', '0000-00-00', 1, 1, 1, 1, '2014-03-31 06:55:15', '2015-06-29 09:03:48', 136, 1),
(124, 'catalog/demo/canon_eos_5d_3.jpg', '0000-00-00', 1, 0, 1, 1, '2015-06-29 09:05:38', '2015-06-29 10:11:50', 2, 0),
(125, 'catalog/demo/canon_eos_5d_2.jpg', '0000-00-00', 1, 0, 1, 1, '2015-06-29 09:09:03', '0000-00-00 00:00:00', 2, 0);

----------------------------------------------------------

--
-- Table structure for table `oc_article_description`
--

DROP TABLE IF EXISTS `oc_article_description`;
CREATE TABLE `oc_article_description` (
  `article_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `meta_description` varchar(255) NOT NULL,
  `meta_keyword` varchar(255) NOT NULL,
  `meta_title` varchar(255) NOT NULL,
  `meta_h1` varchar(255) NOT NULL,
  `tag` text NOT NULL,
  PRIMARY KEY (`article_id`,`language_id`),
  KEY `name` (`name`(191))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_article_description`
--

INSERT INTO `oc_article_description` (`article_id`, `language_id`, `name`, `description`, `meta_description`, `meta_keyword`, `meta_title`, `meta_h1`, `tag`) VALUES
(120, 1, 'CMS для интернет магазинов ocStore v3.x', '&lt;p&gt;Рады представить Вашему вниманию ocStore v3.x основанную на OPENCART v3.x&lt;/p&gt;\r\n', 'CMS для интернет магазинов ocStore v3.x это бесплатный функциональный движок для создания качественных продающих магазинов.', 'cms, opencart, ocstore', 'CMS для интернет магазинов ocStore v3.x - Скачать', 'CMS для интернет магазинов ocStore v3.x', ''),
(120, 2, 'CMS for online stores ocStore v3.x', '&lt;p&gt;&lt;span class=&quot;long_text&quot; id=&quot;result_box&quot; lang=&quot;en&quot;&gt;&lt;span class=&quot;hps&quot;&gt;Are pleased to announce&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;ocStore v3.x&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;based on&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;OpenCart v2.x&lt;/span&gt;&lt;/span&gt;&lt;/p&gt;\r\n', 'CMS for online stores ocStore v3.x is a free functional engine to create high-quality shops selling.', 'cms, opencart, ocstore', 'CMS for online stores ocStore v3.x - Download', 'CMS for online stores ocStore v3.x', ''),
(123, 1, 'Обзор Первый', '&lt;p&gt;Это первый фото обзор тут можно написать много какого то текста который описывает фото обзор и говорит что и как и почему для чего :-) Это первый фото обзор тут можно написать много какого то текста который описывает фото обзор и говорит что и как и почему для чего :-) Это первый фото обзор тут можно написать много какого то текста который описывает фото обзор и говорит что и как и почему для чего :-) Это первый фото обзор тут можно написать много какого то текста который описывает фото обзор и говорит что и как и почему для чего :-) Это первый фото обзор тут можно написать много какого то текста который описывает фото обзор и говорит что и как и почему для чего :-) Это первый фото обзор тут можно написать много какого то текста который описывает фото обзор и говорит что и как и почему для чего :-) Это первый фото обзор тут можно написать много какого то текста который описывает фото обзор и говорит что и как и почему для чего :-) Это первый фото обзор тут можно написать много какого то текста который описывает фото обзор и говорит что и как и почему для чего :-) Это первый фото обзор тут можно написать много какого то текста который описывает фото обзор и говорит что и как и почему для чего :-)&lt;/p&gt;\r\n', 'Фото Обзор Первый', 'Фото Обзор Первый', 'Фото Обзор Первый', 'Фото Обзор Первый', ''),
(123, 2, 'First Overview', '&lt;p&gt;&lt;span id=&quot;result_box&quot; lang=&quot;en&quot;&gt;&lt;span class=&quot;hps&quot;&gt;This is the first&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;review of the&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;photos&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;here&lt;/span&gt;&lt;span&gt;, you can write&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;a lot of&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;what that&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;text&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;that describes the&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;photo&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;review and&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;says&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;what and how&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;and why&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;what&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;:-) This is the first&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;review of the&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;photos&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;here&lt;/span&gt;&lt;span&gt;, you can write&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;a lot of&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;what that&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;text&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;that describes the&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;photo&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;review and&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;says&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;what and how&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;and why&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;what&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;:-) This is the first&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;review of the&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;photos&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;here&lt;/span&gt;&lt;span&gt;, you can write&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;a lot of&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;what that&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;text&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;that describes the&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;photo&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;review and&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;says&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;what and how&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;and why&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;what&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;:-) This is the first&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;review of the&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;photos&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;here&lt;/span&gt;&lt;span&gt;, you can write&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;a lot of&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;what that&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;text&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;that describes the&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;photo&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;review and&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;says&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;what and how&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;and why&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;what&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;:-) This is the first&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;review of the&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;photos&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;here&lt;/span&gt;&lt;span&gt;, you can write&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;a lot of&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;what that&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;text&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;that describes the&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;photo&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;review and&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;says&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;what and how&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;and why&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;what&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;:-) This is the first&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;review of the&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;photos&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;here&lt;/span&gt;&lt;span&gt;, you can write&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;a lot of&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;what that&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;text&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;that describes the&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;photo&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;review and&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;says&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;what and how&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;and why&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;what&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;:-) This is the first&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;review of the&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;photos&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;here&lt;/span&gt;&lt;span&gt;, you can write&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;a lot of&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;what that&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;text&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;that describes the&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;photo&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;review and&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;says&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;what and how&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;and why&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;what&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;:-) This is the first&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;review of the&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;photos&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;here&lt;/span&gt;&lt;span&gt;, you can write&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;a lot of&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;what that&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;text&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;that describes the&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;photo&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;review and&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;says&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;what and how&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;and why&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;what&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;:-) This is the first&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;review of the&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;photos&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;here&lt;/span&gt;&lt;span&gt;, you can write&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;a lot of&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;what that&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;text&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;that describes the&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;photo&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;review and&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;says&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;what and how&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;and why&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;what&lt;/span&gt; &lt;span class=&quot;hps&quot;&gt;:-)&lt;/span&gt;&lt;/span&gt;&lt;/p&gt;\r\n', 'First Photo Overview', 'First Photo Overview', 'First Photo Overview', 'First Photo Overview', ''),
(124, 1, 'Важная статья', '&lt;p&gt;Это очень важная статья которую нужно прочитать всем важным людям про важные события важных людей :-)&lt;/p&gt;', '', '', '', '', ''),
(124, 2, 'Важная статья', '&lt;p&gt;Это очень важная статья которую нужно прочитать всем важным людям про важные события важных людей :-)&lt;br&gt;&lt;/p&gt;', '', '', '', '', ''),
(125, 1, 'Первая новость', '&lt;p&gt;Это первая новость всем новостям новость :-)&lt;/p&gt;', '', '', '', '', ''),
(125, 2, 'Первая новость', '&lt;p&gt;Это первая новость всем новостям новость :-)&lt;br&gt;&lt;/p&gt;', '', '', '', '', '');

----------------------------------------------------------

--
-- Table structure for table `oc_article_image`
--

DROP TABLE IF EXISTS `oc_article_image`;
CREATE TABLE `oc_article_image` (
  `article_image_id` int(11) NOT NULL AUTO_INCREMENT,
  `article_id` int(11) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `sort_order` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`article_image_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_article_image`
--


----------------------------------------------------------

--
-- Table structure for table `oc_article_related`
--

DROP TABLE IF EXISTS `oc_article_related`;
CREATE TABLE `oc_article_related` (
  `article_id` int(11) NOT NULL,
  `related_id` int(11) NOT NULL,
  PRIMARY KEY (`article_id`,`related_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_article_related`
--

INSERT INTO `oc_article_related` (`article_id`, `related_id`) VALUES
(120, 123),
(120, 124),
(123, 120),
(123, 124),
(124, 120),
(124, 123);

----------------------------------------------------------

--
-- Table structure for table `oc_article_related_mn`
--

DROP TABLE IF EXISTS `oc_article_related_mn`;
CREATE TABLE `oc_article_related_mn` (
  `article_id` int(11) NOT NULL,
  `manufacturer_id` int(11) NOT NULL,
  PRIMARY KEY (`article_id`,`manufacturer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_article_related_mn`
--

INSERT INTO `oc_article_related_mn` (`article_id`, `manufacturer_id`) VALUES
(120, 8),
(120, 9),
(123, 8),
(124, 7);

----------------------------------------------------------

--
-- Table structure for table `oc_article_related_product`
--

DROP TABLE IF EXISTS `oc_article_related_product`;
CREATE TABLE `oc_article_related_product` (
  `article_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  PRIMARY KEY (`article_id`,`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_article_related_product`
--

INSERT INTO `oc_article_related_product` (`article_id`, `product_id`) VALUES
(30, 123),
(31, 123),
(43, 123),
(45, 123),
(120, 28),
(120, 30),
(120, 41),
(123, 30),
(123, 31),
(123, 43),
(123, 45),
(124, 28),
(124, 30),
(124, 41),
(124, 47);

----------------------------------------------------------

--
-- Table structure for table `oc_article_related_wb`
--

DROP TABLE IF EXISTS `oc_article_related_wb`;
CREATE TABLE `oc_article_related_wb` (
  `article_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`article_id`,`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_article_related_wb`
--

INSERT INTO `oc_article_related_wb` (`article_id`, `category_id`) VALUES
(120, 26),
(123, 20),
(124, 18),
(125, 18),
(125, 27);

----------------------------------------------------------

--
-- Table structure for table `oc_article_to_blog_category`
--

DROP TABLE IF EXISTS `oc_article_to_blog_category`;
CREATE TABLE `oc_article_to_blog_category` (
  `article_id` int(11) NOT NULL,
  `blog_category_id` int(11) NOT NULL,
  `main_blog_category` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`article_id`,`blog_category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_article_to_blog_category`
--

INSERT INTO `oc_article_to_blog_category` (`article_id`, `blog_category_id`, `main_blog_category`) VALUES
(120, 0, 0),
(120, 69, 0),
(120, 71, 1),
(123, 70, 1),
(124, 0, 0),
(124, 71, 1),
(125, 69, 1);

----------------------------------------------------------

--
-- Table structure for table `oc_article_to_download`
--

DROP TABLE IF EXISTS `oc_article_to_download`;
CREATE TABLE `oc_article_to_download` (
  `article_id` int(11) NOT NULL,
  `download_id` int(11) NOT NULL,
  PRIMARY KEY (`article_id`,`download_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_article_to_download`
--


----------------------------------------------------------

--
-- Table structure for table `oc_article_to_layout`
--

DROP TABLE IF EXISTS `oc_article_to_layout`;
CREATE TABLE `oc_article_to_layout` (
  `article_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `layout_id` int(11) NOT NULL,
  PRIMARY KEY (`article_id`,`store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_article_to_layout`
--

INSERT INTO `oc_article_to_layout` (`article_id`, `store_id`, `layout_id`) VALUES
(120, 0, 0),
(123, 0, 0),
(124, 0, 0),
(125, 0, 0);

----------------------------------------------------------

--
-- Table structure for table `oc_article_to_store`
--

DROP TABLE IF EXISTS `oc_article_to_store`;
CREATE TABLE `oc_article_to_store` (
  `article_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`article_id`,`store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_article_to_store`
--

INSERT INTO `oc_article_to_store` (`article_id`, `store_id`) VALUES
(120, 0),
(123, 0),
(124, 0),
(125, 0);

----------------------------------------------------------

--
-- Table structure for table `oc_attribute`
--

DROP TABLE IF EXISTS `oc_attribute`;
CREATE TABLE `oc_attribute` (
  `attribute_id` int(11) NOT NULL AUTO_INCREMENT,
  `attribute_group_id` int(11) NOT NULL,
  `sort_order` int(3) NOT NULL,
  PRIMARY KEY (`attribute_id`),
  KEY `attribute_group_id` (`attribute_group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_attribute`
--

INSERT INTO `oc_attribute` (`attribute_id`, `attribute_group_id`, `sort_order`) VALUES
(1, 6, 1),
(2, 6, 5),
(3, 6, 3),
(4, 3, 1),
(5, 3, 2),
(6, 3, 3),
(7, 3, 4),
(8, 3, 5),
(9, 3, 6),
(10, 3, 7),
(11, 3, 8);

----------------------------------------------------------

--
-- Table structure for table `oc_attribute_description`
--

DROP TABLE IF EXISTS `oc_attribute_description`;
CREATE TABLE `oc_attribute_description` (
  `attribute_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`attribute_id`,`language_id`),
  KEY `attribute_id` (`attribute_id`),
  KEY `language_id` (`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_attribute_description`
--

INSERT INTO `oc_attribute_description` (`attribute_id`, `language_id`, `name`) VALUES
(1, 1, 'Description'),
(1, 2, 'Description'),
(2, 1, 'No. of Cores'),
(2, 2, 'No. of Cores'),
(3, 1, 'Clockspeed'),
(3, 2, 'Clockspeed'),
(4, 1, 'test 1'),
(4, 2, 'test 1'),
(5, 1, 'test 2'),
(5, 2, 'test 2'),
(6, 1, 'test 3'),
(6, 2, 'test 3'),
(7, 1, 'test 4'),
(7, 2, 'test 4'),
(8, 1, 'test 5'),
(8, 2, 'test 5'),
(9, 1, 'test 6'),
(9, 2, 'test 6'),
(10, 1, 'test 7'),
(10, 2, 'test 7'),
(11, 1, 'test 8'),
(11, 2, 'test 8');

----------------------------------------------------------

--
-- Table structure for table `oc_attribute_group`
--

DROP TABLE IF EXISTS `oc_attribute_group`;
CREATE TABLE `oc_attribute_group` (
  `attribute_group_id` int(11) NOT NULL AUTO_INCREMENT,
  `sort_order` int(3) NOT NULL,
  PRIMARY KEY (`attribute_group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_attribute_group`
--

INSERT INTO `oc_attribute_group` (`attribute_group_id`, `sort_order`) VALUES
(3, 2),
(4, 1),
(5, 3),
(6, 4);

----------------------------------------------------------

--
-- Table structure for table `oc_attribute_group_description`
--

DROP TABLE IF EXISTS `oc_attribute_group_description`;
CREATE TABLE `oc_attribute_group_description` (
  `attribute_group_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`attribute_group_id`,`language_id`),
  KEY `attribute_group_id` (`attribute_group_id`),
  KEY `language_id` (`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_attribute_group_description`
--

INSERT INTO `oc_attribute_group_description` (`attribute_group_id`, `language_id`, `name`) VALUES
(3, 1, 'Memory'),
(3, 2, 'Memory'),
(4, 1, 'Technical'),
(4, 2, 'Technical'),
(5, 1, 'Motherboard'),
(5, 2, 'Motherboard'),
(6, 1, 'Processor'),
(6, 2, 'Processor');

----------------------------------------------------------

--
-- Table structure for table `oc_banner`
--

DROP TABLE IF EXISTS `oc_banner`;
CREATE TABLE `oc_banner` (
  `banner_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `status` tinyint(1) NOT NULL,
  PRIMARY KEY (`banner_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_banner`
--

INSERT INTO `oc_banner` (`banner_id`, `name`, `status`) VALUES
(6, 'HP Products', 1),
(7, 'Home Page Slideshow', 1),
(8, 'Manufacturers', 1);

----------------------------------------------------------

--
-- Table structure for table `oc_banner_image`
--

DROP TABLE IF EXISTS `oc_banner_image`;
CREATE TABLE `oc_banner_image` (
  `banner_image_id` int(11) NOT NULL AUTO_INCREMENT,
  `banner_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `title` varchar(64) NOT NULL,
  `link` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `sort_order` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`banner_image_id`),
  KEY `banner_id` (`banner_id`),
  KEY `language_id` (`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_banner_image`
--

INSERT INTO `oc_banner_image` (`banner_image_id`, `banner_id`, `language_id`, `title`, `link`, `image`, `sort_order`) VALUES
(99, 7, 1, 'iPhone 6', 'index.php?route=product/product&amp;path=57&amp;product_id=49', 'catalog/demo/banners/iPhone6.jpg', 0),
(100, 7, 1, 'MacBookAir', '', 'catalog/demo/banners/MacBookAir.jpg', 0),
(101, 7, 2, 'iPhone 6', 'index.php?route=product/product&amp;path=57&amp;product_id=49', 'catalog/demo/banners/iPhone6.jpg', 0),
(102, 7, 2, 'MacBookAir', '', 'catalog/demo/banners/MacBookAir.jpg', 0),
(103, 6, 1, 'HP Banner', 'index.php?route=product/manufacturer/info&amp;manufacturer_id=7', 'catalog/demo/compaq_presario.jpg', 0),
(104, 6, 2, 'HP Banner', 'index.php?route=product/manufacturer/info&amp;manufacturer_id=7', 'catalog/demo/compaq_presario.jpg', 0),
(105, 8, 1, 'NFL', '', 'catalog/demo/manufacturer/nfl.png', 0),
(106, 8, 1, 'RedBull', '', 'catalog/demo/manufacturer/redbull.png', 0),
(107, 8, 1, 'Sony', '', 'catalog/demo/manufacturer/sony.png', 0),
(108, 8, 1, 'Coca Cola', '', 'catalog/demo/manufacturer/cocacola.png', 0),
(109, 8, 1, 'Burger King', '', 'catalog/demo/manufacturer/burgerking.png', 0),
(110, 8, 1, 'Canon', '', 'catalog/demo/manufacturer/canon.png', 0),
(111, 8, 1, 'Harley Davidson', '', 'catalog/demo/manufacturer/harley.png', 0),
(112, 8, 1, 'Dell', '', 'catalog/demo/manufacturer/dell.png', 0),
(113, 8, 1, 'Disney', '', 'catalog/demo/manufacturer/disney.png', 0),
(114, 8, 1, 'Starbucks', '', 'catalog/demo/manufacturer/starbucks.png', 0),
(115, 8, 1, 'Nintendo', '', 'catalog/demo/manufacturer/nintendo.png', 0),
(116, 8, 2, 'NFL', '', 'catalog/demo/manufacturer/nfl.png', 0),
(117, 8, 2, 'RedBull', '', 'catalog/demo/manufacturer/redbull.png', 0),
(118, 8, 2, 'Sony', '', 'catalog/demo/manufacturer/sony.png', 0),
(119, 8, 2, 'Coca Cola', '', 'catalog/demo/manufacturer/cocacola.png', 0),
(120, 8, 2, 'Burger King', '', 'catalog/demo/manufacturer/burgerking.png', 0),
(121, 8, 2, 'Canon', '', 'catalog/demo/manufacturer/canon.png', 0),
(122, 8, 2, 'Harley Davidson', '', 'catalog/demo/manufacturer/harley.png', 0),
(123, 8, 2, 'Dell', '', 'catalog/demo/manufacturer/dell.png', 0),
(124, 8, 2, 'Disney', '', 'catalog/demo/manufacturer/disney.png', 0),
(125, 8, 2, 'Starbucks', '', 'catalog/demo/manufacturer/starbucks.png', 0),
(126, 8, 2, 'Nintendo', '', 'catalog/demo/manufacturer/nintendo.png', 0);

----------------------------------------------------------

--
-- Table structure for table `oc_blog_category`
--

DROP TABLE IF EXISTS `oc_blog_category`;
CREATE TABLE `oc_blog_category` (
  `blog_category_id` int(11) NOT NULL AUTO_INCREMENT,
  `image` varchar(255) DEFAULT NULL,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `top` tinyint(1) NOT NULL,
  `column` int(3) NOT NULL,
  `sort_order` int(3) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL,
  `noindex` tinyint(1) NOT NULL DEFAULT '1',
  `date_added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`blog_category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_blog_category`
--

INSERT INTO `oc_blog_category` (`blog_category_id`, `image`, `parent_id`, `top`, `column`, `sort_order`, `status`, `noindex`, `date_added`, `date_modified`) VALUES
(69, 'catalog/demo/canon_eos_5d_2.jpg', 0, 1, 0, 0, 1, 1, '2014-04-08 03:56:26', '2015-06-18 09:15:42'),
(70, 'catalog/demo/iphone_2.jpg', 0, 1, 0, 0, 1, 1, '2014-04-08 03:58:55', '2015-06-18 09:16:41'),
(71, 'catalog/demo/canon_eos_5d_1.jpg', 69, 1, 1, 0, 1, 1, '2015-06-18 09:13:57', '2015-06-18 09:15:58');

----------------------------------------------------------

--
-- Table structure for table `oc_blog_category_description`
--

DROP TABLE IF EXISTS `oc_blog_category_description`;
CREATE TABLE `oc_blog_category_description` (
  `blog_category_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `meta_description` varchar(255) NOT NULL,
  `meta_keyword` varchar(255) NOT NULL,
  `meta_title` varchar(255) NOT NULL,
  `meta_h1` varchar(255) NOT NULL,
  PRIMARY KEY (`blog_category_id`,`language_id`),
  KEY `name` (`name`(191))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_blog_category_description`
--

INSERT INTO `oc_blog_category_description` (`blog_category_id`, `language_id`, `name`, `description`, `meta_description`, `meta_keyword`, `meta_title`, `meta_h1`) VALUES
(69, 1, 'Новости', '&lt;p&gt;&lt;br&gt;&lt;/p&gt;', '', '', '', ''),
(69, 2, 'News', '&lt;p&gt;&lt;br&gt;&lt;/p&gt;', '', '', '', ''),
(70, 1, 'Обзоры', '&lt;p&gt;&lt;br&gt;&lt;/p&gt;', '', '', '', ''),
(70, 2, 'Reviews', '&lt;p&gt;&lt;br&gt;&lt;/p&gt;', '', '', '', ''),
(71, 1, 'Анонсы', '&lt;p&gt;&lt;br&gt;&lt;/p&gt;', '', '', '', ''),
(71, 2, 'Анонсы', '&lt;p&gt;&lt;br&gt;&lt;/p&gt;', '', '', '', '');

----------------------------------------------------------

--
-- Table structure for table `oc_blog_category_path`
--

DROP TABLE IF EXISTS `oc_blog_category_path`;
CREATE TABLE `oc_blog_category_path` (
  `blog_category_id` int(11) NOT NULL,
  `path_id` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  PRIMARY KEY (`blog_category_id`,`path_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_blog_category_path`
--

INSERT INTO `oc_blog_category_path` (`blog_category_id`, `path_id`, `level`) VALUES
(69, 69, 0),
(70, 70, 0),
(71, 69, 0),
(71, 71, 1);

----------------------------------------------------------

--
-- Table structure for table `oc_blog_category_to_layout`
--

DROP TABLE IF EXISTS `oc_blog_category_to_layout`;
CREATE TABLE `oc_blog_category_to_layout` (
  `blog_category_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `layout_id` int(11) NOT NULL,
  PRIMARY KEY (`blog_category_id`,`store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_blog_category_to_layout`
--

INSERT INTO `oc_blog_category_to_layout` (`blog_category_id`, `store_id`, `layout_id`) VALUES
(69, 0, 0),
(70, 0, 0),
(71, 0, 0);

----------------------------------------------------------

--
-- Table structure for table `oc_blog_category_to_store`
--

DROP TABLE IF EXISTS `oc_blog_category_to_store`;
CREATE TABLE `oc_blog_category_to_store` (
  `blog_category_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  PRIMARY KEY (`blog_category_id`,`store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_blog_category_to_store`
--

INSERT INTO `oc_blog_category_to_store` (`blog_category_id`, `store_id`) VALUES
(69, 0),
(70, 0),
(71, 0);

----------------------------------------------------------

--
-- Table structure for table `oc_cart`
--

DROP TABLE IF EXISTS `oc_cart`;
CREATE TABLE `oc_cart` (
  `cart_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `api_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `session_id` varchar(32) NOT NULL,
  `product_id` int(11) NOT NULL,
  `recurring_id` int(11) NOT NULL,
  `option` text NOT NULL,
  `quantity` int(5) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`cart_id`),
  KEY `cart_id` (`api_id`,`customer_id`,`session_id`,`product_id`,`recurring_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_cart`
--


----------------------------------------------------------

--
-- Table structure for table `oc_category`
--

DROP TABLE IF EXISTS `oc_category`;
CREATE TABLE `oc_category` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `image` varchar(255) DEFAULT NULL,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `top` tinyint(1) NOT NULL,
  `column` int(3) NOT NULL,
  `sort_order` int(3) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL,
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  `noindex` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`category_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_category`
--

INSERT INTO `oc_category` (`category_id`, `image`, `parent_id`, `top`, `column`, `sort_order`, `status`, `date_added`, `date_modified`, `noindex`) VALUES
(17, '', 0, 1, 1, 4, 1, '2009-01-03 21:08:57', '2017-07-26 22:20:22', 0),
(18, 'catalog/demo/hp_2.jpg', 0, 1, 0, 2, 1, '2009-01-05 21:49:15', '2011-05-30 12:13:55', 1),
(20, 'catalog/demo/compaq_presario.jpg', 0, 1, 1, 1, 1, '2009-01-05 21:49:43', '2017-07-26 16:50:08', 1),
(24, '', 0, 1, 1, 5, 1, '2009-01-20 02:36:26', '2011-05-30 12:15:18', 1),
(25, '', 0, 1, 1, 3, 1, '2009-01-31 01:04:25', '2011-05-30 12:14:55', 1),
(26, '', 20, 0, 0, 1, 1, '2009-01-31 01:55:14', '2010-08-22 06:31:45', 1),
(27, '', 20, 0, 0, 2, 1, '2009-01-31 01:55:34', '2010-08-22 06:32:15', 1),
(28, '', 25, 0, 0, 1, 1, '2009-02-02 13:11:12', '2010-08-22 06:32:46', 1),
(29, '', 25, 0, 0, 1, 1, '2009-02-02 13:11:37', '2010-08-22 06:32:39', 1),
(30, '', 25, 0, 0, 1, 1, '2009-02-02 13:11:59', '2010-08-22 06:33:00', 1),
(31, '', 25, 0, 0, 1, 1, '2009-02-03 14:17:24', '2010-08-22 06:33:06', 1),
(32, '', 25, 0, 0, 1, 1, '2009-02-03 14:17:34', '2010-08-22 06:33:12', 1),
(33, '', 0, 1, 1, 6, 1, '2009-02-03 14:17:55', '2017-07-26 21:59:42', 1),
(34, 'catalog/demo/ipod_touch_4.jpg', 0, 1, 4, 7, 1, '2009-02-03 14:18:11', '2011-05-30 12:15:31', 1),
(35, '', 28, 0, 0, 0, 1, '2010-09-17 10:06:48', '2010-09-18 14:02:42', 1),
(36, '', 28, 0, 0, 0, 1, '2010-09-17 10:07:13', '2010-09-18 14:02:55', 1),
(37, '', 34, 0, 0, 0, 1, '2010-09-18 14:03:39', '2011-04-22 01:55:08', 1),
(38, '', 34, 0, 0, 0, 1, '2010-09-18 14:03:51', '2010-09-18 14:03:51', 1),
(39, '', 34, 0, 0, 0, 1, '2010-09-18 14:04:17', '2011-04-22 01:55:20', 1),
(40, '', 34, 0, 0, 0, 1, '2010-09-18 14:05:36', '2010-09-18 14:05:36', 1),
(41, '', 34, 0, 0, 0, 1, '2010-09-18 14:05:49', '2011-04-22 01:55:30', 1),
(42, '', 34, 0, 0, 0, 1, '2010-09-18 14:06:34', '2010-11-07 20:31:04', 1),
(43, '', 34, 0, 0, 0, 1, '2010-09-18 14:06:49', '2011-04-22 01:55:40', 1),
(44, '', 34, 0, 0, 0, 1, '2010-09-21 15:39:21', '2010-11-07 20:30:55', 1),
(45, '', 18, 0, 0, 0, 1, '2010-09-24 18:29:16', '2011-04-26 08:52:11', 1),
(46, '', 18, 0, 0, 0, 1, '2010-09-24 18:29:31', '2011-04-26 08:52:23', 1),
(47, '', 34, 0, 0, 0, 1, '2010-11-07 11:13:16', '2010-11-07 11:13:16', 1),
(48, '', 34, 0, 0, 0, 1, '2010-11-07 11:13:33', '2010-11-07 11:13:33', 1),
(49, '', 34, 0, 0, 0, 1, '2010-11-07 11:14:04', '2010-11-07 11:14:04', 1),
(50, '', 34, 0, 0, 0, 1, '2010-11-07 11:14:23', '2011-04-22 01:16:01', 1),
(51, '', 34, 0, 0, 0, 1, '2010-11-07 11:14:38', '2011-04-22 01:16:13', 1),
(52, '', 34, 0, 0, 0, 1, '2010-11-07 11:16:09', '2011-04-22 01:54:57', 1),
(53, '', 34, 0, 0, 0, 1, '2010-11-07 11:28:53', '2011-04-22 01:14:36', 1),
(54, '', 34, 0, 0, 0, 1, '2010-11-07 11:29:16', '2011-04-22 01:16:50', 1),
(55, '', 34, 0, 0, 0, 1, '2010-11-08 10:31:32', '2010-11-08 10:31:32', 1),
(56, '', 34, 0, 0, 0, 1, '2010-11-08 10:31:50', '2011-04-22 01:16:37', 1),
(57, '', 0, 1, 1, 3, 1, '2011-04-26 08:53:16', '2011-05-30 12:15:05', 1),
(58, '', 52, 0, 0, 0, 1, '2011-05-08 13:44:16', '2011-05-08 13:44:16', 1);

----------------------------------------------------------

--
-- Table structure for table `oc_category_description`
--

DROP TABLE IF EXISTS `oc_category_description`;
CREATE TABLE `oc_category_description` (
  `category_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `meta_title` varchar(255) NOT NULL,
  `meta_description` varchar(255) NOT NULL,
  `meta_keyword` varchar(255) NOT NULL,
  `meta_h1` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`category_id`,`language_id`),
  KEY `category_id` (`category_id`),
  KEY `language_id` (`language_id`),
  KEY `name` (`name`(191))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_category_description`
--

INSERT INTO `oc_category_description` (`category_id`, `language_id`, `name`, `description`, `meta_title`, `meta_description`, `meta_keyword`, `meta_h1`) VALUES
(17, 1, 'Програмное обеспечение', '', '', '', '', ''),
(17, 2, 'Software', '', '', '', '', ''),
(18, 1, 'Ноутбуки', '&lt;p&gt;\r\n Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.&lt;/p&gt;\r\n', 'Laptops &amp; Notebooks', '', '', ''),
(18, 2, 'Laptops &amp; Notebooks', '&lt;p&gt;Shop Laptop feature only the best laptop deals on the market. By \r\ncomparing laptop deals from the likes of PC World, Comet, Dixons, The \r\nLink and Carphone Warehouse, Shop Laptop has the most comprehensive \r\nselection of laptops on the internet. At Shop Laptop, we pride ourselves\r\n on offering customers the very best laptop deals. From refurbished \r\nlaptops to netbooks, Shop Laptop ensures that every laptop - in every \r\ncolour, style, size and technical spec - is featured on the site at the \r\nlowest possible price.&lt;br&gt;&lt;/p&gt;', '', '', '', ''),
(20, 1, 'Компьютеры', '&lt;p&gt;\r\n Пример текста в описания категории&lt;/p&gt;\r\n', '', '', '', ''),
(20, 2, 'Desktops', '&lt;p&gt;Example of category description text&lt;br&gt;&lt;/p&gt;', '', '', '', ''),
(24, 1, 'Телефоны и PDA', '', '', '', '', ''),
(24, 2, 'Phones &amp; PDAs', '', '', '', '', ''),
(25, 1, 'Компоненты', '', 'Components', '', '', ''),
(25, 2, 'Components', '', '', '', '', ''),
(26, 1, 'PC', '', '', '', '', ''),
(26, 2, 'PC', '', '', '', '', ''),
(27, 1, 'Mac', '', '', '', '', ''),
(27, 2, 'Mac', '', '', '', '', ''),
(28, 1, 'Мониторы', '', '', '', '', ''),
(28, 2, 'Monitors', '', '', '', '', ''),
(29, 1, 'Мышки', '', 'Мышки', '', '', ''),
(29, 2, 'Mice and Trackballs', '', '', '', '', ''),
(30, 1, 'Принтеры', '', '', '', '', ''),
(30, 2, 'Printers', '', '', '', '', ''),
(31, 1, 'Сканеры', '', '', '', '', ''),
(31, 2, 'Scanners', '', '', '', '', ''),
(32, 1, 'Веб-камеры', '', '', '', '', ''),
(32, 2, 'Web Cameras', '', '', '', '', ''),
(33, 1, 'Камеры', '', '', '', '', ''),
(33, 2, 'Cameras', '', '', '', '', ''),
(34, 1, 'MP3 Плееры', '&lt;p&gt;\r\n Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.&lt;/p&gt;\r\n', '', '', '', ''),
(34, 2, 'MP3 Players', '', '', '', '', ''),
(35, 1, 'test 1', '', 'test 1', '', '', ''),
(35, 2, 'test 1', '', '', '', '', ''),
(36, 1, 'test 2', '', 'test 2', '', '', ''),
(36, 2, 'test 2', '', '', '', '', ''),
(37, 1, 'test 5', '', '', '', '', ''),
(37, 2, 'test 5', '', '', '', '', ''),
(38, 1, 'test 4', '', '', '', '', ''),
(38, 2, 'test 4', '', '', '', '', ''),
(39, 1, 'test 6', '', '', '', '', ''),
(39, 2, 'test 6', '', '', '', '', ''),
(40, 1, 'test 7', '', '', '', '', ''),
(40, 2, 'test 7', '', '', '', '', ''),
(41, 1, 'test 8', '', '', '', '', ''),
(41, 2, 'test 8', '', '', '', '', ''),
(42, 1, 'test 9', '', '', '', '', ''),
(42, 2, 'test 9', '', '', '', '', ''),
(43, 1, 'test 11', '', '', '', '', ''),
(43, 2, 'test 11', '', '', '', '', ''),
(44, 1, 'test 12', '', '', '', '', ''),
(44, 2, 'test 12', '', '', '', '', ''),
(45, 1, 'Windows', '', '', '', '', ''),
(45, 2, 'Windows', '', '', '', '', ''),
(46, 1, 'Macs', '', '', '', '', ''),
(46, 2, 'Macs', '', '', '', '', ''),
(47, 1, 'test 15', '', '', '', '', ''),
(47, 2, 'test 15', '', '', '', '', ''),
(48, 1, 'test 16', '', '', '', '', ''),
(48, 2, 'test 16', '', '', '', '', ''),
(49, 1, 'test 17', '', '', '', '', ''),
(49, 2, 'test 17', '', '', '', '', ''),
(50, 1, 'test 18', '', '', '', '', ''),
(50, 2, 'test 18', '', '', '', '', ''),
(51, 1, 'test 19', '', '', '', '', ''),
(51, 2, 'test 19', '', '', '', '', ''),
(52, 1, 'test 20', '', '', '', '', ''),
(52, 2, 'test 20', '', '', '', '', ''),
(53, 1, 'test 21', '', '', '', '', ''),
(53, 2, 'test 21', '', '', '', '', ''),
(54, 1, 'test 22', '', '', '', '', ''),
(54, 2, 'test 22', '', '', '', '', ''),
(55, 1, 'test 23', '', '', '', '', ''),
(55, 2, 'test 23', '', '', '', '', ''),
(56, 1, 'test 24', '', '', '', '', ''),
(56, 2, 'test 24', '', '', '', '', ''),
(57, 1, 'Планшеты', '', '', '', '', ''),
(57, 2, 'Tablets', '', '', '', '', ''),
(58, 1, 'test 25', '', '', '', '', ''),
(58, 2, 'test 25', '', '', '', '', '');

----------------------------------------------------------

--
-- Table structure for table `oc_category_filter`
--

DROP TABLE IF EXISTS `oc_category_filter`;
CREATE TABLE `oc_category_filter` (
  `category_id` int(11) NOT NULL,
  `filter_id` int(11) NOT NULL,
  PRIMARY KEY (`category_id`,`filter_id`),
  KEY `category_id` (`category_id`),
  KEY `filter_id` (`filter_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_category_filter`
--


----------------------------------------------------------

--
-- Table structure for table `oc_category_path`
--

DROP TABLE IF EXISTS `oc_category_path`;
CREATE TABLE `oc_category_path` (
  `category_id` int(11) NOT NULL,
  `path_id` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  PRIMARY KEY (`category_id`,`path_id`),
  KEY `category_id` (`category_id`),
  KEY `path_id` (`path_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_category_path`
--

INSERT INTO `oc_category_path` (`category_id`, `path_id`, `level`) VALUES
(17, 17, 0),
(18, 18, 0),
(20, 20, 0),
(24, 24, 0),
(25, 25, 0),
(26, 20, 0),
(26, 26, 1),
(27, 20, 0),
(27, 27, 1),
(28, 25, 0),
(28, 28, 1),
(29, 25, 0),
(29, 29, 1),
(30, 25, 0),
(30, 30, 1),
(31, 25, 0),
(31, 31, 1),
(32, 25, 0),
(32, 32, 1),
(33, 33, 0),
(34, 34, 0),
(35, 25, 0),
(35, 28, 1),
(35, 35, 2),
(36, 25, 0),
(36, 28, 1),
(36, 36, 2),
(37, 34, 0),
(37, 37, 1),
(38, 34, 0),
(38, 38, 1),
(39, 34, 0),
(39, 39, 1),
(40, 34, 0),
(40, 40, 1),
(41, 34, 0),
(41, 41, 1),
(42, 34, 0),
(42, 42, 1),
(43, 34, 0),
(43, 43, 1),
(44, 34, 0),
(44, 44, 1),
(45, 18, 0),
(45, 45, 1),
(46, 18, 0),
(46, 46, 1),
(47, 34, 0),
(47, 47, 1),
(48, 34, 0),
(48, 48, 1),
(49, 34, 0),
(49, 49, 1),
(50, 34, 0),
(50, 50, 1),
(51, 34, 0),
(51, 51, 1),
(52, 34, 0),
(52, 52, 1),
(53, 34, 0),
(53, 53, 1),
(54, 34, 0),
(54, 54, 1),
(55, 34, 0),
(55, 55, 1),
(56, 34, 0),
(56, 56, 1),
(57, 57, 0),
(58, 34, 0),
(58, 52, 1),
(58, 58, 2);

----------------------------------------------------------

--
-- Table structure for table `oc_category_to_layout`
--

DROP TABLE IF EXISTS `oc_category_to_layout`;
CREATE TABLE `oc_category_to_layout` (
  `category_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `layout_id` int(11) NOT NULL,
  PRIMARY KEY (`category_id`,`store_id`),
  KEY `category_id` (`category_id`),
  KEY `store_id` (`store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_category_to_layout`
--


----------------------------------------------------------

--
-- Table structure for table `oc_category_to_store`
--

DROP TABLE IF EXISTS `oc_category_to_store`;
CREATE TABLE `oc_category_to_store` (
  `category_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  PRIMARY KEY (`category_id`,`store_id`),
  KEY `category_id` (`category_id`),
  KEY `store_id` (`store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_category_to_store`
--

INSERT INTO `oc_category_to_store` (`category_id`, `store_id`) VALUES
(17, 0),
(18, 0),
(20, 0),
(24, 0),
(25, 0),
(26, 0),
(27, 0),
(28, 0),
(29, 0),
(30, 0),
(31, 0),
(32, 0),
(33, 0),
(34, 0),
(35, 0),
(36, 0),
(37, 0),
(38, 0),
(39, 0),
(40, 0),
(41, 0),
(42, 0),
(43, 0),
(44, 0),
(45, 0),
(46, 0),
(47, 0),
(48, 0),
(49, 0),
(50, 0),
(51, 0),
(52, 0),
(53, 0),
(54, 0),
(55, 0),
(56, 0),
(57, 0),
(58, 0);

----------------------------------------------------------

--
-- Table structure for table `oc_country`
--

DROP TABLE IF EXISTS `oc_country`;
CREATE TABLE `oc_country` (
  `country_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `iso_code_2` varchar(2) NOT NULL,
  `iso_code_3` varchar(3) NOT NULL,
  `address_format` text NOT NULL,
  `postcode_required` tinyint(1) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`country_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_country`
--

INSERT INTO `oc_country` (`country_id`, `name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `status`) VALUES
(11, 'Армения', 'AM', 'ARM', '', 0, 0),
(15, 'Азербайджан', 'AZ', 'AZE', '', 0, 0),
(20, 'Белоруссия (Беларусь)', 'BY', 'BLR', '', 0, 0),
(44, 'China', 'CN', 'CHN', '', 0, 0),
(53, 'Croatia', 'HR', 'HRV', '', 0, 0),
(56, 'Czech Republic', 'CZ', 'CZE', '', 0, 0),
(67, 'Estonia', 'EE', 'EST', '', 0, 0),
(72, 'Finland', 'FI', 'FIN', '', 0, 0),
(80, 'Грузия', 'GE', 'GEO', '', 0, 0),
(109, 'Казахстан', 'KZ', 'KAZ', '', 0, 0),
(115, 'Киргизия (Кыргызстан)', 'KG', 'KGZ', '', 0, 0),
(117, 'Latvia', 'LV', 'LVA', '', 0, 0),
(123, 'Lithuania', 'LT', 'LTU', '', 0, 0),
(140, 'Молдова', 'MD', 'MDA', '', 0, 0),
(176, 'Российская Федерация', 'RU', 'RUS', '', 0, 0),
(189, 'Slovak Republic', 'SK', 'SVK', '', 0, 0),
(190, 'Slovenia', 'SI', 'SVN', '', 0, 0),
(207, 'Таджикистан', 'TJ', 'TJK', '', 0, 0),
(215, 'Turkey', 'TR', 'TUR', '', 0, 0),
(216, 'Туркменистан', 'TM', 'TKM', '', 0, 0),
(220, 'Украина', 'UA', 'UKR', '', 0, 1),
(226, 'Узбекистан', 'UZ', 'UZB', '', 0, 0),
(243, 'Serbia', 'RS', 'SRB', '', 0, 0),
(253, 'Kosovo, Republic of', 'XK', 'UNK', '', 0, 0);

----------------------------------------------------------

--
-- Table structure for table `oc_coupon`
--

DROP TABLE IF EXISTS `oc_coupon`;
CREATE TABLE `oc_coupon` (
  `coupon_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `code` varchar(20) NOT NULL,
  `type` char(1) NOT NULL,
  `discount` decimal(15,4) NOT NULL,
  `logged` tinyint(1) NOT NULL,
  `shipping` tinyint(1) NOT NULL,
  `total` decimal(15,4) NOT NULL,
  `date_start` date NOT NULL DEFAULT '0000-00-00',
  `date_end` date NOT NULL DEFAULT '0000-00-00',
  `uses_total` int(11) NOT NULL,
  `uses_customer` varchar(11) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`coupon_id`),
  KEY `code` (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_coupon`
--

INSERT INTO `oc_coupon` (`coupon_id`, `name`, `code`, `type`, `discount`, `logged`, `shipping`, `total`, `date_start`, `date_end`, `uses_total`, `uses_customer`, `status`, `date_added`) VALUES
(4, 'Скидка 5%', '2222', 'P', '5.0000', 0, 0, '5000.0000', '2024-10-09', '2024-10-09', 10, '10', 0, '2009-01-27 13:55:03'),
(5, 'Бесплатная доставка', '3333', 'P', '0.0000', 0, 1, '1000.0000', '2024-10-09', '2024-10-09', 10, '10', 0, '2009-03-14 21:13:53'),
(6, 'Фиксированная скидка 100.00', '1111', 'F', '100.0000', 0, 0, '100.0000', '2024-10-09', '2024-10-09', 100000, '10000', 0, '2009-03-14 21:15:18');

----------------------------------------------------------

--
-- Table structure for table `oc_coupon_category`
--

DROP TABLE IF EXISTS `oc_coupon_category`;
CREATE TABLE `oc_coupon_category` (
  `coupon_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`coupon_id`,`category_id`),
  KEY `coupon_id` (`coupon_id`),
  KEY `category_id` (`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_coupon_category`
--


----------------------------------------------------------

--
-- Table structure for table `oc_coupon_history`
--

DROP TABLE IF EXISTS `oc_coupon_history`;
CREATE TABLE `oc_coupon_history` (
  `coupon_history_id` int(11) NOT NULL AUTO_INCREMENT,
  `coupon_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `amount` decimal(15,4) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`coupon_history_id`),
  KEY `coupon_id` (`coupon_id`),
  KEY `order_id` (`order_id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_coupon_history`
--


----------------------------------------------------------

--
-- Table structure for table `oc_coupon_product`
--

DROP TABLE IF EXISTS `oc_coupon_product`;
CREATE TABLE `oc_coupon_product` (
  `coupon_product_id` int(11) NOT NULL AUTO_INCREMENT,
  `coupon_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  PRIMARY KEY (`coupon_product_id`),
  KEY `coupon_id` (`coupon_id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_coupon_product`
--


----------------------------------------------------------

--
-- Table structure for table `oc_currency`
--

DROP TABLE IF EXISTS `oc_currency`;
CREATE TABLE `oc_currency` (
  `currency_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(32) NOT NULL,
  `code` varchar(3) NOT NULL,
  `symbol_left` varchar(12) NOT NULL,
  `symbol_right` varchar(12) NOT NULL,
  `decimal_place` char(1) NOT NULL,
  `value` double(15,8) NOT NULL,
  `correction_rate` decimal(15,8) NOT NULL DEFAULT '1.00000000',
  `status` tinyint(1) NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`currency_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_currency`
--

INSERT INTO `oc_currency` (`title`, `code`, `symbol_left`, `symbol_right`, `decimal_place`, `value`, `correction_rate`, `status`, `date_modified`) VALUES
('Рубль', 'RUB', '', ' р.', '2', 2.52000000, '1.00000000', 0, '2024-11-24 17:44:42'),
('US Dollar', 'USD', '$', '', '2', 0.02325600, '1.00000000', 0, '2025-04-14 10:51:04'),
('Euro', 'EUR', '', '€', '2', 0.02131501, '1.00000000', 0, '2025-04-14 10:48:39'),
('Гривня', 'UAH', '', ' грн', '2', 1.00000000, '1.00000000', 1, '2025-04-14 10:48:39');

----------------------------------------------------------

--
-- Table structure for table `oc_customer`
--

DROP TABLE IF EXISTS `oc_customer`;
CREATE TABLE `oc_customer` (
  `customer_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_group_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL DEFAULT '0',
  `language_id` int(11) NOT NULL,
  `firstname` varchar(32) NOT NULL,
  `lastname` varchar(32) NOT NULL,
  `email` varchar(96) NOT NULL,
  `telephone` varchar(32) NOT NULL,
  `fax` varchar(32) NOT NULL,
  `password` varchar(255) NOT NULL,
  `cart` text,
  `wishlist` text,
  `newsletter` tinyint(1) NOT NULL DEFAULT '0',
  `address_id` int(11) NOT NULL DEFAULT '0',
  `custom_field` text NOT NULL,
  `ip` varchar(40) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `safe` tinyint(1) NOT NULL,
  `token` text NOT NULL,
  `code` varchar(40) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`customer_id`),
  KEY `customer_group_id` (`customer_group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_customer`
--


----------------------------------------------------------

--
-- Table structure for table `oc_customer_activity`
--

DROP TABLE IF EXISTS `oc_customer_activity`;
CREATE TABLE `oc_customer_activity` (
  `customer_activity_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `key` varchar(64) NOT NULL,
  `data` text NOT NULL,
  `ip` varchar(40) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`customer_activity_id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_customer_activity`
--


----------------------------------------------------------

--
-- Table structure for table `oc_customer_affiliate`
--

DROP TABLE IF EXISTS `oc_customer_affiliate`;
CREATE TABLE `oc_customer_affiliate` (
  `customer_id` int(11) NOT NULL,
  `company` varchar(40) NOT NULL,
  `website` varchar(255) NOT NULL,
  `tracking` varchar(64) NOT NULL,
  `commission` decimal(4,2) NOT NULL DEFAULT '0.00',
  `tax` varchar(64) NOT NULL,
  `payment` varchar(6) NOT NULL,
  `cheque` varchar(100) NOT NULL,
  `paypal` varchar(64) NOT NULL,
  `bank_name` varchar(64) NOT NULL,
  `bank_branch_number` varchar(64) NOT NULL,
  `bank_swift_code` varchar(64) NOT NULL,
  `bank_account_name` varchar(64) NOT NULL,
  `bank_account_number` varchar(64) NOT NULL,
  `custom_field` text NOT NULL,
  `status` tinyint(1) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`customer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_customer_affiliate`
--


----------------------------------------------------------

--
-- Table structure for table `oc_customer_approval`
--

DROP TABLE IF EXISTS `oc_customer_approval`;
CREATE TABLE `oc_customer_approval` (
  `customer_approval_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `type` varchar(9) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`customer_approval_id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_customer_approval`
--


----------------------------------------------------------

--
-- Table structure for table `oc_customer_group`
--

DROP TABLE IF EXISTS `oc_customer_group`;
CREATE TABLE `oc_customer_group` (
  `customer_group_id` int(11) NOT NULL AUTO_INCREMENT,
  `approval` int(1) NOT NULL,
  `sort_order` int(3) NOT NULL,
  PRIMARY KEY (`customer_group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_customer_group`
--

INSERT INTO `oc_customer_group` (`customer_group_id`, `approval`, `sort_order`) VALUES
(1, 0, 1);

----------------------------------------------------------

--
-- Table structure for table `oc_customer_group_description`
--

DROP TABLE IF EXISTS `oc_customer_group_description`;
CREATE TABLE `oc_customer_group_description` (
  `customer_group_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `name` varchar(32) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`customer_group_id`,`language_id`),
  KEY `customer_group_id` (`customer_group_id`),
  KEY `language_id` (`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_customer_group_description`
--

INSERT INTO `oc_customer_group_description` (`customer_group_id`, `language_id`, `name`, `description`) VALUES
(1, 1, 'По умолчанию', 'По умолчанию'),
(1, 2, 'За замовчанням', 'За замовчанням'),
(1, 3, 'Default', 'Default');

----------------------------------------------------------

--
-- Table structure for table `oc_customer_history`
--

DROP TABLE IF EXISTS `oc_customer_history`;
CREATE TABLE `oc_customer_history` (
  `customer_history_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`customer_history_id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_customer_history`
--


----------------------------------------------------------

--
-- Table structure for table `oc_customer_ip`
--

DROP TABLE IF EXISTS `oc_customer_ip`;
CREATE TABLE `oc_customer_ip` (
  `customer_ip_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `ip` varchar(40) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`customer_ip_id`),
  KEY `customer_id` (`customer_id`),
  KEY `ip` (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_customer_ip`
--


----------------------------------------------------------

--
-- Table structure for table `oc_customer_login`
--

DROP TABLE IF EXISTS `oc_customer_login`;
CREATE TABLE `oc_customer_login` (
  `customer_login_id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(96) NOT NULL,
  `ip` varchar(40) NOT NULL,
  `total` int(4) NOT NULL,
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`customer_login_id`),
  KEY `email` (`email`),
  KEY `ip` (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_customer_login`
--


----------------------------------------------------------

--
-- Table structure for table `oc_customer_online`
--

DROP TABLE IF EXISTS `oc_customer_online`;
CREATE TABLE `oc_customer_online` (
  `ip` varchar(40) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `url` text NOT NULL,
  `referer` text NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`ip`),
  KEY `customer_id` (`customer_id`),
  KEY `date_added` (`date_added`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_customer_online`
--


----------------------------------------------------------

--
-- Table structure for table `oc_customer_reward`
--

DROP TABLE IF EXISTS `oc_customer_reward`;
CREATE TABLE `oc_customer_reward` (
  `customer_reward_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL DEFAULT '0',
  `order_id` int(11) NOT NULL DEFAULT '0',
  `description` text NOT NULL,
  `points` int(8) NOT NULL DEFAULT '0',
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`customer_reward_id`),
  KEY `customer_id` (`customer_id`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_customer_reward`
--


----------------------------------------------------------

--
-- Table structure for table `oc_customer_search`
--

DROP TABLE IF EXISTS `oc_customer_search`;
CREATE TABLE `oc_customer_search` (
  `customer_search_id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `keyword` varchar(255) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `sub_category` tinyint(1) NOT NULL,
  `description` tinyint(1) NOT NULL,
  `products` int(11) NOT NULL,
  `ip` varchar(40) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`customer_search_id`),
  KEY `customer_id` (`customer_id`),
  KEY `store_id` (`store_id`),
  KEY `language_id` (`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_customer_search`
--


----------------------------------------------------------

--
-- Table structure for table `oc_customer_transaction`
--

DROP TABLE IF EXISTS `oc_customer_transaction`;
CREATE TABLE `oc_customer_transaction` (
  `customer_transaction_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `description` text NOT NULL,
  `amount` decimal(15,4) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`customer_transaction_id`),
  KEY `customer_id` (`customer_id`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_customer_transaction`
--


----------------------------------------------------------

--
-- Table structure for table `oc_customer_wishlist`
--

DROP TABLE IF EXISTS `oc_customer_wishlist`;
CREATE TABLE `oc_customer_wishlist` (
  `customer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`customer_id`,`product_id`),
  KEY `customer_id` (`customer_id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_customer_wishlist`
--


----------------------------------------------------------

--
-- Table structure for table `oc_custom_field`
--

DROP TABLE IF EXISTS `oc_custom_field`;
CREATE TABLE `oc_custom_field` (
  `custom_field_id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(32) NOT NULL,
  `value` text NOT NULL,
  `validation` varchar(255) NOT NULL,
  `location` varchar(10) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `sort_order` int(3) NOT NULL,
  PRIMARY KEY (`custom_field_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_custom_field`
--


----------------------------------------------------------

--
-- Table structure for table `oc_custom_field_customer_group`
--

DROP TABLE IF EXISTS `oc_custom_field_customer_group`;
CREATE TABLE `oc_custom_field_customer_group` (
  `custom_field_id` int(11) NOT NULL,
  `customer_group_id` int(11) NOT NULL,
  `required` tinyint(1) NOT NULL,
  PRIMARY KEY (`custom_field_id`,`customer_group_id`),
  KEY `custom_field_id` (`custom_field_id`),
  KEY `customer_group_id` (`customer_group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_custom_field_customer_group`
--


----------------------------------------------------------

--
-- Table structure for table `oc_custom_field_description`
--

DROP TABLE IF EXISTS `oc_custom_field_description`;
CREATE TABLE `oc_custom_field_description` (
  `custom_field_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `name` varchar(128) NOT NULL,
  PRIMARY KEY (`custom_field_id`,`language_id`),
  KEY `custom_field_id` (`custom_field_id`),
  KEY `language_id` (`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_custom_field_description`
--


----------------------------------------------------------

--
-- Table structure for table `oc_custom_field_value`
--

DROP TABLE IF EXISTS `oc_custom_field_value`;
CREATE TABLE `oc_custom_field_value` (
  `custom_field_value_id` int(11) NOT NULL AUTO_INCREMENT,
  `custom_field_id` int(11) NOT NULL,
  `sort_order` int(3) NOT NULL,
  PRIMARY KEY (`custom_field_value_id`),
  KEY `custom_field_id` (`custom_field_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_custom_field_value`
--


----------------------------------------------------------

--
-- Table structure for table `oc_custom_field_value_description`
--

DROP TABLE IF EXISTS `oc_custom_field_value_description`;
CREATE TABLE `oc_custom_field_value_description` (
  `custom_field_value_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `custom_field_id` int(11) NOT NULL,
  `name` varchar(128) NOT NULL,
  PRIMARY KEY (`custom_field_value_id`,`language_id`),
  KEY `custom_field_value_id` (`custom_field_value_id`),
  KEY `custom_field_id` (`custom_field_id`),
  KEY `language_id` (`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_custom_field_value_description`
--


----------------------------------------------------------

--
-- Table structure for table `oc_download`
--

DROP TABLE IF EXISTS `oc_download`;
CREATE TABLE `oc_download` (
  `download_id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(160) NOT NULL,
  `mask` varchar(128) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`download_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_download`
--


----------------------------------------------------------

--
-- Table structure for table `oc_download_description`
--

DROP TABLE IF EXISTS `oc_download_description`;
CREATE TABLE `oc_download_description` (
  `download_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`download_id`,`language_id`),
  KEY `download_id` (`download_id`),
  KEY `language_id` (`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_download_description`
--


----------------------------------------------------------

--
-- Table structure for table `oc_download_report`
--

DROP TABLE IF EXISTS `oc_download_report`;
CREATE TABLE `oc_download_report` (
  `download_report_id` int(11) NOT NULL AUTO_INCREMENT,
  `download_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `ip` varchar(40) NOT NULL,
  `country` varchar(2) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`download_report_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_download_report`
--


----------------------------------------------------------

--
-- Table structure for table `oc_event`
--

DROP TABLE IF EXISTS `oc_event`;
CREATE TABLE `oc_event` (
  `event_id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(64) NOT NULL,
  `trigger` text NOT NULL,
  `action` text NOT NULL,
  `status` tinyint(1) NOT NULL,
  `sort_order` int(3) NOT NULL,
  PRIMARY KEY (`event_id`),
  KEY `code` (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_event`
--

INSERT INTO `oc_event` (`code`, `trigger`, `action`, `status`, `sort_order`) VALUES
('activity_customer_add', 'catalog/model/account/customer/addCustomer/after', 'event/activity/addCustomer', 1, 0),
('activity_customer_edit', 'catalog/model/account/customer/editCustomer/after', 'event/activity/editCustomer', 1, 0),
('activity_customer_password', 'catalog/model/account/customer/editPassword/after', 'event/activity/editPassword', 1, 0),
('activity_customer_forgotten', 'catalog/model/account/customer/editCode/after', 'event/activity/forgotten', 1, 0),
('activity_transaction', 'catalog/model/account/customer/addTransaction/after', 'event/activity/addTransaction', 1, 0),
('activity_customer_login', 'catalog/model/account/customer/deleteLoginAttempts/after', 'event/activity/login', 1, 0),
('activity_address_add', 'catalog/model/account/address/addAddress/after', 'event/activity/addAddress', 1, 0),
('activity_address_edit', 'catalog/model/account/address/editAddress/after', 'event/activity/editAddress', 1, 0),
('activity_address_delete', 'catalog/model/account/address/deleteAddress/after', 'event/activity/deleteAddress', 1, 0),
('activity_affiliate_add', 'catalog/model/account/customer/addAffiliate/after', 'event/activity/addAffiliate', 1, 0),
('activity_affiliate_edit', 'catalog/model/account/customer/editAffiliate/after', 'event/activity/editAffiliate', 1, 0),
('activity_order_add', 'catalog/model/checkout/order/addOrderHistory/before', 'event/activity/addOrderHistory', 1, 0),
('activity_return_add', 'catalog/model/account/return/addReturn/after', 'event/activity/addReturn', 1, 0),
('mail_transaction', 'catalog/model/account/customer/addTransaction/after', 'mail/transaction', 1, 0),
('mail_forgotten', 'catalog/model/account/customer/editCode/after', 'mail/forgotten', 1, 0),
('mail_customer_add', 'catalog/model/account/customer/addCustomer/after', 'mail/register', 1, 0),
('mail_customer_alert', 'catalog/model/account/customer/addCustomer/after', 'mail/register/alert', 1, 0),
('mail_affiliate_add', 'catalog/model/account/customer/addAffiliate/after', 'mail/affiliate', 1, 0),
('mail_affiliate_alert', 'catalog/model/account/customer/addAffiliate/after', 'mail/affiliate/alert', 1, 0),
('mail_voucher', 'catalog/model/checkout/order/addOrderHistory/after', 'extension/total/voucher/send', 1, 0),
('mail_order_add', 'catalog/model/checkout/order/addOrderHistory/before', 'mail/order', 1, 0),
('mail_order_alert', 'catalog/model/checkout/order/addOrderHistory/before', 'mail/order/alert', 1, 0),
('statistics_review_add', 'catalog/model/catalog/review/addReview/after', 'event/statistics/addReview', 1, 0),
('statistics_return_add', 'catalog/model/account/return/addReturn/after', 'event/statistics/addReturn', 1, 0),
('statistics_order_history', 'catalog/model/checkout/order/addOrderHistory/after', 'event/statistics/addOrderHistory', 1, 0),
('admin_mail_affiliate_approve', 'admin/model/customer/customer_approval/approveAffiliate/after', 'mail/affiliate/approve', 1, 0),
('admin_mail_affiliate_deny', 'admin/model/customer/customer_approval/denyAffiliate/after', 'mail/affiliate/deny', 1, 0),
('admin_mail_customer_approve', 'admin/model/customer/customer_approval/approveCustomer/after', 'mail/customer/approve', 1, 0),
('admin_mail_customer_deny', 'admin/model/customer/customer_approval/denyCustomer/after', 'mail/customer/deny', 1, 0),
('admin_mail_reward', 'admin/model/customer/customer/addReward/after', 'mail/reward', 1, 0),
('admin_mail_transaction', 'admin/model/customer/customer/addTransaction/after', 'mail/transaction', 1, 0),
('admin_mail_return', 'admin/model/sale/return/addReturnHistory/after', 'mail/return', 1, 0),
('admin_mail_forgotten', 'admin/model/user/user/editCode/after', 'mail/forgotten', 1, 0),
('advertise_google', 'admin/model/catalog/product/deleteProduct/after', 'extension/advertise/google/deleteProduct', 1, 0),
('advertise_google', 'admin/model/catalog/product/copyProduct/after', 'extension/advertise/google/copyProduct', 1, 0),
('advertise_google', 'admin/view/common/column_left/before', 'extension/advertise/google/admin_link', 1, 0),
('advertise_google', 'admin/model/catalog/product/addProduct/after', 'extension/advertise/google/addProduct', 1, 0),
('advertise_google', 'catalog/controller/checkout/success/before', 'extension/advertise/google/before_checkout_success', 1, 0),
('advertise_google', 'catalog/view/common/header/after', 'extension/advertise/google/google_global_site_tag', 1, 0),
('advertise_google', 'catalog/view/common/success/after', 'extension/advertise/google/google_dynamic_remarketing_purchase', 1, 0),
('advertise_google', 'catalog/view/product/product/after', 'extension/advertise/google/google_dynamic_remarketing_product', 1, 0),
('advertise_google', 'catalog/view/product/search/after', 'extension/advertise/google/google_dynamic_remarketing_searchresults', 1, 0),
('advertise_google', 'catalog/view/product/category/after', 'extension/advertise/google/google_dynamic_remarketing_category', 1, 0),
('advertise_google', 'catalog/view/common/home/after', 'extension/advertise/google/google_dynamic_remarketing_home', 1, 0),
('advertise_google', 'catalog/view/checkout/cart/after', 'extension/advertise/google/google_dynamic_remarketing_cart', 1, 0);

----------------------------------------------------------

--
-- Table structure for table `oc_extension`
--

DROP TABLE IF EXISTS `oc_extension`;
CREATE TABLE `oc_extension` (
  `extension_id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(32) NOT NULL,
  `code` varchar(32) NOT NULL,
  PRIMARY KEY (`extension_id`),
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_extension`
--

INSERT INTO `oc_extension` (`type`, `code`) VALUES
('payment', 'cod'),
('total', 'shipping'),
('total', 'sub_total'),
('total', 'tax'),
('total', 'total'),
('module', 'banner'),
('module', 'carousel'),
('total', 'credit'),
('shipping', 'flat'),
('total', 'handling'),
('total', 'low_order_fee'),
('total', 'coupon'),
('module', 'category'),
('module', 'account'),
('total', 'reward'),
('total', 'voucher'),
('payment', 'free_checkout'),
('module', 'featured'),
('module', 'slideshow'),
('theme', 'default'),
('dashboard', 'activity'),
('dashboard', 'sale'),
('dashboard', 'recent'),
('dashboard', 'order'),
('dashboard', 'online'),
('dashboard', 'map'),
('dashboard', 'customer'),
('dashboard', 'chart'),
('dashboard', 'chart_by_country_and_region'),
('report', 'sale_coupon'),
('report', 'customer_search'),
('report', 'customer_transaction'),
('report', 'product_purchased'),
('report', 'product_viewed'),
('report', 'sale_return'),
('report', 'sale_order'),
('report', 'sale_shipping'),
('report', 'sale_tax'),
('report', 'customer_activity'),
('report', 'customer_order'),
('report', 'customer_reward'),
('advertise', 'google'),
('module', 'blog_latest'),
('module', 'blog_featured'),
('module', 'blog_category'),
('module', 'featured_article'),
('module', 'featured_product'),
('currency', 'cbr'),
('currency', 'nbu');

----------------------------------------------------------

--
-- Table structure for table `oc_extension_install`
--

DROP TABLE IF EXISTS `oc_extension_install`;
CREATE TABLE `oc_extension_install` (
  `extension_install_id` int(11) NOT NULL AUTO_INCREMENT,
  `extension_download_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `hash` binary(20) DEFAULT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`extension_install_id`),
  UNIQUE KEY `uniq_hash` (`hash`),
  KEY `extension_download_id` (`extension_download_id`),
  KEY `idx_filename` (`filename`(191))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_extension_install`
--


----------------------------------------------------------

--
-- Table structure for table `oc_extension_path`
--

DROP TABLE IF EXISTS `oc_extension_path`;
CREATE TABLE `oc_extension_path` (
  `extension_path_id` int(11) NOT NULL AUTO_INCREMENT,
  `extension_install_id` int(11) NOT NULL,
  `path` varchar(255) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`extension_path_id`),
  KEY `extension_install_id` (`extension_install_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_extension_path`
--


----------------------------------------------------------

--
-- Table structure for table `oc_filter`
--

DROP TABLE IF EXISTS `oc_filter`;
CREATE TABLE `oc_filter` (
  `filter_id` int(11) NOT NULL AUTO_INCREMENT,
  `filter_group_id` int(11) NOT NULL,
  `sort_order` int(3) NOT NULL,
  PRIMARY KEY (`filter_id`),
  KEY `filter_group_id` (`filter_group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_filter`
--


----------------------------------------------------------

--
-- Table structure for table `oc_filter_description`
--

DROP TABLE IF EXISTS `oc_filter_description`;
CREATE TABLE `oc_filter_description` (
  `filter_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `filter_group_id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`filter_id`,`language_id`),
  KEY `filter_id` (`filter_id`),
  KEY `language_id` (`language_id`),
  KEY `filter_group_id` (`filter_group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_filter_description`
--


----------------------------------------------------------

--
-- Table structure for table `oc_filter_group`
--

DROP TABLE IF EXISTS `oc_filter_group`;
CREATE TABLE `oc_filter_group` (
  `filter_group_id` int(11) NOT NULL AUTO_INCREMENT,
  `sort_order` int(3) NOT NULL,
  PRIMARY KEY (`filter_group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_filter_group`
--


----------------------------------------------------------

--
-- Table structure for table `oc_filter_group_description`
--

DROP TABLE IF EXISTS `oc_filter_group_description`;
CREATE TABLE `oc_filter_group_description` (
  `filter_group_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`filter_group_id`,`language_id`),
  KEY `filter_group_id` (`filter_group_id`),
  KEY `language_id` (`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_filter_group_description`
--


----------------------------------------------------------

--
-- Table structure for table `oc_geo_zone`
--

DROP TABLE IF EXISTS `oc_geo_zone`;
CREATE TABLE `oc_geo_zone` (
  `geo_zone_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `description` varchar(255) NOT NULL,
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`geo_zone_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_geo_zone`
--


----------------------------------------------------------

--
-- Table structure for table `oc_googleshopping_category`
--

DROP TABLE IF EXISTS `oc_googleshopping_category`;
CREATE TABLE `oc_googleshopping_category` (
  `google_product_category` varchar(10) NOT NULL,
  `store_id` int(11) NOT NULL DEFAULT '0',
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`google_product_category`,`store_id`),
  KEY `category_id_store_id` (`category_id`,`store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_googleshopping_category`
--


----------------------------------------------------------

--
-- Table structure for table `oc_googleshopping_product`
--

DROP TABLE IF EXISTS `oc_googleshopping_product`;
CREATE TABLE `oc_googleshopping_product` (
  `product_advertise_google_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) DEFAULT NULL,
  `store_id` int(11) NOT NULL DEFAULT '0',
  `has_issues` tinyint(1) DEFAULT NULL,
  `destination_status` enum('pending','approved','disapproved') NOT NULL DEFAULT 'pending',
  `impressions` int(11) NOT NULL DEFAULT '0',
  `clicks` int(11) NOT NULL DEFAULT '0',
  `conversions` int(11) NOT NULL DEFAULT '0',
  `cost` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `conversion_value` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `google_product_category` varchar(10) DEFAULT NULL,
  `condition` enum('new','refurbished','used') DEFAULT NULL,
  `adult` tinyint(1) DEFAULT NULL,
  `multipack` int(11) DEFAULT NULL,
  `is_bundle` tinyint(1) DEFAULT NULL,
  `age_group` enum('newborn','infant','toddler','kids','adult') DEFAULT NULL,
  `color` int(11) DEFAULT NULL,
  `gender` enum('male','female','unisex') DEFAULT NULL,
  `size_type` enum('regular','petite','plus','big and tall','maternity') DEFAULT NULL,
  `size_system` enum('AU','BR','CN','DE','EU','FR','IT','JP','MEX','UK','US') DEFAULT NULL,
  `size` int(11) DEFAULT NULL,
  `is_modified` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`product_advertise_google_id`),
  UNIQUE KEY `product_id_store_id` (`product_id`,`store_id`),
  KEY `product_id` (`product_id`),
  KEY `store_id` (`store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_googleshopping_product`
--


----------------------------------------------------------

--
-- Table structure for table `oc_googleshopping_product_status`
--

DROP TABLE IF EXISTS `oc_googleshopping_product_status`;
CREATE TABLE `oc_googleshopping_product_status` (
  `product_id` int(11) NOT NULL DEFAULT '0',
  `store_id` int(11) NOT NULL DEFAULT '0',
  `product_variation_id` varchar(64) NOT NULL DEFAULT '',
  `destination_statuses` text NOT NULL,
  `data_quality_issues` text NOT NULL,
  `item_level_issues` text NOT NULL,
  `google_expiration_date` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`product_id`,`store_id`,`product_variation_id`),
  KEY `product_id` (`product_id`),
  KEY `store_id` (`store_id`),
  KEY `product_variation_id` (`product_variation_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_googleshopping_product_status`
--


----------------------------------------------------------

--
-- Table structure for table `oc_googleshopping_product_target`
--

DROP TABLE IF EXISTS `oc_googleshopping_product_target`;
CREATE TABLE `oc_googleshopping_product_target` (
  `product_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL DEFAULT '0',
  `advertise_google_target_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`product_id`,`advertise_google_target_id`),
  KEY `product_id` (`product_id`),
  KEY `store_id` (`store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_googleshopping_product_target`
--


----------------------------------------------------------

--
-- Table structure for table `oc_googleshopping_target`
--

DROP TABLE IF EXISTS `oc_googleshopping_target`;
CREATE TABLE `oc_googleshopping_target` (
  `advertise_google_target_id` int(11) unsigned NOT NULL,
  `store_id` int(11) NOT NULL DEFAULT '0',
  `campaign_name` varchar(255) NOT NULL DEFAULT '',
  `country` varchar(2) NOT NULL DEFAULT '',
  `budget` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `feeds` text NOT NULL,
  `status` enum('paused','active') NOT NULL DEFAULT 'paused',
  `date_added` date DEFAULT NULL,
  `roas` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`advertise_google_target_id`),
  KEY `store_id` (`store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_googleshopping_target`
--


----------------------------------------------------------

--
-- Table structure for table `oc_information`
--

DROP TABLE IF EXISTS `oc_information`;
CREATE TABLE `oc_information` (
  `information_id` int(11) NOT NULL AUTO_INCREMENT,
  `bottom` int(1) NOT NULL DEFAULT '0',
  `sort_order` int(3) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `noindex` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`information_id`),
  KEY `bottom` (`bottom`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_information`
--

INSERT INTO `oc_information` (`information_id`, `bottom`, `sort_order`, `status`, `noindex`) VALUES
(3, 1, 3, 1, 1),
(4, 1, 1, 1, 0),
(5, 1, 4, 1, 1),
(6, 1, 2, 1, 1);

----------------------------------------------------------

--
-- Table structure for table `oc_information_description`
--

DROP TABLE IF EXISTS `oc_information_description`;
CREATE TABLE `oc_information_description` (
  `information_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `title` varchar(64) NOT NULL,
  `description` mediumtext NOT NULL,
  `meta_title` varchar(255) NOT NULL,
  `meta_description` varchar(255) NOT NULL,
  `meta_keyword` varchar(255) NOT NULL,
  `meta_h1` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`information_id`,`language_id`),
  KEY `information_id` (`information_id`),
  KEY `language_id` (`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_information_description`
--

INSERT INTO `oc_information_description` (`information_id`, `language_id`, `title`, `description`, `meta_title`, `meta_description`, `meta_keyword`, `meta_h1`) VALUES
(3, 1, 'Политика безопасности', '&lt;p&gt;\r\n Политика безопасности&lt;/p&gt;\r\n', 'Политика безопасности', '', '', 'Политика безопасности'),
(3, 2, 'Політика конфіденційності', '&lt;p&gt;Політика конфіденційності&lt;br&gt;&lt;/p&gt;', 'Політика конфіденційності', '', '', 'Політика конфіденційності'),
(3, 3, 'Privacy Policy', '&lt;p&gt;Privacy Policy&lt;br&gt;&lt;/p&gt;', 'Privacy Policy', '', '', 'Privacy Policy'),
(4, 1, 'О нас', '&lt;p&gt;О нас&lt;br&gt;&lt;/p&gt;\r\n', 'О нас', '', '', 'О нас'),
(4, 2, 'Про нас', '&lt;p&gt;Про нас&lt;br&gt;&lt;/p&gt;', 'Про нас', '', '', 'Про нас'),
(4, 3, 'About Us', '&lt;p&gt;About Us&lt;br&gt;&lt;/p&gt;', 'About Us', '', '', 'About Us'),
(5, 1, 'Условия соглашения', '&lt;p&gt;\r\n Условия соглашения&lt;/p&gt;\r\n', 'Условия соглашения', '', '', 'Условия соглашения'),
(5, 2, 'Правила та умови', '&lt;p&gt;Правила та умови&lt;br&gt;&lt;/p&gt;', 'Правила та умови', '', '', 'Правила та умови'),
(5, 3, 'Terms &amp; Conditions', '&lt;p&gt;Terms &amp;amp; Conditions&lt;br&gt;&lt;/p&gt;', 'Terms &amp; Conditions', '', '', 'Terms &amp; Conditions'),
(6, 1, 'Информация о доставке', '&lt;p&gt;\r\n Информация о доставке&lt;/p&gt;\r\n', 'Информация о доставке', '', '', 'Информация о доставке'),
(6, 2, 'Інформація про доставку', '&lt;p&gt;Інформація про доставку&lt;br&gt;&lt;/p&gt;', 'Інформація про доставку', '', '', 'Інформація про доставку'),
(6, 3, 'Delivery Information', '&lt;p&gt;Delivery Information&lt;br&gt;&lt;/p&gt;', 'Delivery Information', '', '', 'Delivery Information');

----------------------------------------------------------

--
-- Table structure for table `oc_information_to_layout`
--

DROP TABLE IF EXISTS `oc_information_to_layout`;
CREATE TABLE `oc_information_to_layout` (
  `information_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `layout_id` int(11) NOT NULL,
  PRIMARY KEY (`information_id`,`store_id`),
  KEY `information_id` (`information_id`),
  KEY `store_id` (`store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_information_to_layout`
--

INSERT INTO `oc_information_to_layout` (`information_id`, `store_id`, `layout_id`) VALUES
(3, 0, 0),
(4, 0, 0),
(5, 0, 0),
(6, 0, 0);

----------------------------------------------------------

--
-- Table structure for table `oc_information_to_store`
--

DROP TABLE IF EXISTS `oc_information_to_store`;
CREATE TABLE `oc_information_to_store` (
  `information_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  PRIMARY KEY (`information_id`,`store_id`),
  KEY `information_id` (`information_id`),
  KEY `store_id` (`store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_information_to_store`
--

INSERT INTO `oc_information_to_store` (`information_id`, `store_id`) VALUES
(3, 0),
(4, 0),
(5, 0),
(6, 0);

----------------------------------------------------------

--
-- Table structure for table `oc_language`
--

DROP TABLE IF EXISTS `oc_language`;
CREATE TABLE `oc_language` (
  `language_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `code` varchar(5) NOT NULL,
  `locale` varchar(255) NOT NULL,
  `image` varchar(64) NOT NULL,
  `directory` varchar(32) NOT NULL,
  `sort_order` int(3) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL,
  PRIMARY KEY (`language_id`),
  KEY `code` (`code`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_language`
--

INSERT INTO `oc_language` (`name`, `code`, `locale`, `image`, `directory`, `sort_order`, `status`) VALUES
('Русский', 'ru-ru', 'ru_RU.UTF-8,ru_RU,russian', 'ru-ru.png', 'ru-ru', 1, 1),
('Українська', 'uk-ua', 'uk_UA.UTF-8,uk_UA,uk-ua,uk,ukrainian', 'uk-ua.png', 'uk-ua', 2, 1),
('English', 'en-gb', 'en-US,en_US.UTF-8,en_US,en-gb,english', 'en-gb.png', 'en-gb', 3, 0);

----------------------------------------------------------

--
-- Table structure for table `oc_layout`
--

DROP TABLE IF EXISTS `oc_layout`;
CREATE TABLE `oc_layout` (
  `layout_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`layout_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_layout`
--

INSERT INTO `oc_layout` (`layout_id`, `name`) VALUES
(1, 'Главная'),
(2, 'Товар'),
(3, 'Категория'),
(4, 'По-умолчанию'),
(5, 'Список Производителей'),
(6, 'Аккаунт'),
(7, 'Оформление заказа'),
(8, 'Контакты'),
(9, 'Карта сайта'),
(10, 'Партнерская программа'),
(11, 'Информация'),
(12, 'Сравнение'),
(13, 'Поиск'),
(14, 'Блог'),
(15, 'Категории Блога'),
(16, 'Статьи Блога'),
(17, 'Страница Производителя');

----------------------------------------------------------

--
-- Table structure for table `oc_layout_module`
--

DROP TABLE IF EXISTS `oc_layout_module`;
CREATE TABLE `oc_layout_module` (
  `layout_module_id` int(11) NOT NULL AUTO_INCREMENT,
  `layout_id` int(11) NOT NULL,
  `code` varchar(64) NOT NULL,
  `position` varchar(14) NOT NULL,
  `sort_order` int(3) NOT NULL,
  PRIMARY KEY (`layout_module_id`),
  KEY `layout_id` (`layout_id`),
  KEY `code` (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_layout_module`
--

INSERT INTO `oc_layout_module` (`layout_id`, `code`, `position`, `sort_order`) VALUES
(4, '0', 'content_top', 0),
(4, '0', 'content_top', 1),
(5, '0', 'column_left', 2),
(1, 'featured.28', 'content_top', 2),
(1, 'slideshow.27', 'content_top', 1),
(1, 'carousel.29', 'content_top', 3),
(6, 'account', 'column_right', 1),
(10, 'account', 'column_right', 1),
(14, 'blog_category', 'column_left', 0),
(14, 'blog_featured.33', 'column_left', 1),
(14, 'blog_latest.32', 'content_bottom', 0),
(15, 'blog_category', 'column_left', 0),
(15, 'blog_latest.32', 'column_left', 1),
(15, 'blog_featured.33', 'content_bottom', 0),
(16, 'blog_category', 'column_left', 0),
(16, 'blog_featured.33', 'column_left', 1),
(3, 'category', 'column_left', 0),
(3, 'banner.30', 'column_left', 1),
(3, 'featured_article.34', 'column_left', 2),
(3, 'featured_product.35', 'column_left', 3),
(17, 'featured_article.34', 'column_left', 0),
(17, 'featured_product.35', 'column_left', 1),
(2, 'featured_article.34', 'content_bottom', 0);

----------------------------------------------------------

--
-- Table structure for table `oc_layout_route`
--

DROP TABLE IF EXISTS `oc_layout_route`;
CREATE TABLE `oc_layout_route` (
  `layout_route_id` int(11) NOT NULL AUTO_INCREMENT,
  `layout_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `route` varchar(64) NOT NULL,
  PRIMARY KEY (`layout_route_id`),
  KEY `layout_id` (`layout_id`),
  KEY `store_id` (`store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_layout_route`
--

INSERT INTO `oc_layout_route` (`layout_id`, `store_id`, `route`) VALUES
(10, 0, 'affiliate/%'),
(2, 0, 'product/product'),
(7, 0, 'checkout/%'),
(11, 0, 'information/information'),
(8, 0, 'information/contact'),
(9, 0, 'information/sitemap'),
(4, 0, ''),
(6, 0, 'account/%'),
(1, 0, 'common/home'),
(3, 0, 'product/category'),
(5, 0, 'product/manufacturer'),
(12, 0, 'product/compare'),
(13, 0, 'product/search'),
(16, 0, 'blog/article'),
(14, 0, 'blog/latest'),
(15, 0, 'blog/category'),
(17, 0, 'product/manufacturer/info');

----------------------------------------------------------

--
-- Table structure for table `oc_length_class`
--

DROP TABLE IF EXISTS `oc_length_class`;
CREATE TABLE `oc_length_class` (
  `length_class_id` int(11) NOT NULL AUTO_INCREMENT,
  `value` decimal(15,8) NOT NULL,
  PRIMARY KEY (`length_class_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_length_class`
--

INSERT INTO `oc_length_class` (`length_class_id`, `value`) VALUES
(1, '1.00000000'),
(2, '10.00000000'),
(3, '0.39370100'),
(4, '100.00000000');

----------------------------------------------------------

--
-- Table structure for table `oc_length_class_description`
--

DROP TABLE IF EXISTS `oc_length_class_description`;
CREATE TABLE `oc_length_class_description` (
  `length_class_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `title` varchar(32) NOT NULL,
  `unit` varchar(4) NOT NULL,
  PRIMARY KEY (`length_class_id`,`language_id`),
  KEY `length_class_id` (`length_class_id`),
  KEY `language_id` (`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_length_class_description`
--

INSERT INTO `oc_length_class_description` (`length_class_id`, `language_id`, `title`, `unit`) VALUES
(1, 1, 'Сантиметр', 'см'),
(1, 2, 'Сантиметр', 'см'),
(1, 3, 'Centimeter', 'cm'),
(2, 1, 'Миллиметр', 'мм'),
(2, 2, 'Міліметр', 'мм'),
(2, 3, 'Millimeter', 'mm'),
(3, 1, 'Дюйм', 'in'),
(3, 2, 'Дюйм', 'in'),
(3, 3, 'Inch', 'in'),
(4, 1, 'Метр', 'м'),
(4, 2, 'Метр', 'м'),
(4, 3, 'Meter', 'm');

----------------------------------------------------------

--
-- Table structure for table `oc_location`
--

DROP TABLE IF EXISTS `oc_location`;
CREATE TABLE `oc_location` (
  `location_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `address` text NOT NULL,
  `telephone` varchar(32) NOT NULL,
  `fax` varchar(32) NOT NULL,
  `geocode` varchar(32) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `open` text NOT NULL,
  `comment` text NOT NULL,
  PRIMARY KEY (`location_id`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_location`
--


----------------------------------------------------------

--
-- Table structure for table `oc_manufacturer`
--

DROP TABLE IF EXISTS `oc_manufacturer`;
CREATE TABLE `oc_manufacturer` (
  `manufacturer_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `sort_order` int(3) NOT NULL,
  `noindex` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`manufacturer_id`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_manufacturer`
--

INSERT INTO `oc_manufacturer` (`manufacturer_id`, `name`, `image`, `sort_order`, `noindex`) VALUES
(5, 'HTC', 'catalog/demo/htc_logo.jpg', 0, 1),
(6, 'Palm', 'catalog/demo/palm_logo.jpg', 0, 1),
(7, 'Hewlett-Packard', 'catalog/demo/hp_logo.jpg', 0, 1),
(8, 'Apple', 'catalog/demo/apple_logo.jpg', 1, 0),
(9, 'Canon', 'catalog/demo/canon_logo.jpg', 0, 1),
(10, 'Sony', 'catalog/demo/sony_logo.jpg', 0, 1);

----------------------------------------------------------

--
-- Table structure for table `oc_manufacturer_description`
--

DROP TABLE IF EXISTS `oc_manufacturer_description`;
CREATE TABLE `oc_manufacturer_description` (
  `manufacturer_id` int(11) NOT NULL DEFAULT '0',
  `language_id` int(11) NOT NULL DEFAULT '0',
  `description` text NOT NULL,
  `meta_description` varchar(255) NOT NULL,
  `meta_keyword` varchar(255) NOT NULL,
  `meta_title` varchar(255) NOT NULL,
  `meta_h1` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_manufacturer_description`
--

INSERT INTO `oc_manufacturer_description` (`manufacturer_id`, `language_id`, `description`, `meta_description`, `meta_keyword`, `meta_title`, `meta_h1`) VALUES
(5, 1, '', '', '', '', ''),
(5, 2, '', '', '', '', ''),
(6, 1, '', '', '', '', ''),
(6, 2, '', '', '', '', ''),
(7, 1, '', '', '', '', ''),
(7, 2, '', '', '', '', ''),
(8, 1, '', '', '', '', ''),
(8, 2, '', '', '', '', ''),
(9, 1, '', '', '', '', ''),
(9, 2, '', '', '', '', ''),
(10, 1, '', '', '', '', ''),
(10, 2, '', '', '', '', '');

----------------------------------------------------------

--
-- Table structure for table `oc_manufacturer_to_layout`
--

DROP TABLE IF EXISTS `oc_manufacturer_to_layout`;
CREATE TABLE `oc_manufacturer_to_layout` (
  `manufacturer_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `layout_id` int(11) NOT NULL,
  PRIMARY KEY (`manufacturer_id`,`store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_manufacturer_to_layout`
--


----------------------------------------------------------

--
-- Table structure for table `oc_manufacturer_to_store`
--

DROP TABLE IF EXISTS `oc_manufacturer_to_store`;
CREATE TABLE `oc_manufacturer_to_store` (
  `manufacturer_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  PRIMARY KEY (`manufacturer_id`,`store_id`),
  KEY `manufacturer_id` (`manufacturer_id`),
  KEY `store_id` (`store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_manufacturer_to_store`
--

INSERT INTO `oc_manufacturer_to_store` (`manufacturer_id`, `store_id`) VALUES
(5, 0),
(6, 0),
(7, 0),
(8, 0),
(9, 0),
(10, 0);

----------------------------------------------------------

--
-- Table structure for table `oc_marketing`
--

DROP TABLE IF EXISTS `oc_marketing`;
CREATE TABLE `oc_marketing` (
  `marketing_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `description` text NOT NULL,
  `code` varchar(64) NOT NULL,
  `clicks` int(5) NOT NULL DEFAULT '0',
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`marketing_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_marketing`
--


----------------------------------------------------------

--
-- Table structure for table `oc_modification`
--

DROP TABLE IF EXISTS `oc_modification`;
CREATE TABLE `oc_modification` (
  `modification_id` int(11) NOT NULL AUTO_INCREMENT,
  `extension_install_id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  `code` varchar(64) NOT NULL,
  `author` varchar(64) NOT NULL,
  `version` varchar(32) NOT NULL,
  `link` varchar(255) NOT NULL,
  `xml` mediumtext NOT NULL,
  `status` tinyint(1) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`modification_id`),
  KEY `extension_install_id` (`extension_install_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_modification`
--


----------------------------------------------------------

--
-- Table structure for table `oc_modification_backup`
--

DROP TABLE IF EXISTS `oc_modification_backup`;
CREATE TABLE `oc_modification_backup` (
  `backup_id` int(11) NOT NULL AUTO_INCREMENT,
  `modification_id` int(11) NOT NULL,
  `code` varchar(64) NOT NULL,
  `xml` mediumtext NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`backup_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_modification_backup`
--


----------------------------------------------------------

--
-- Table structure for table `oc_module`
--

DROP TABLE IF EXISTS `oc_module`;
CREATE TABLE `oc_module` (
  `module_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `code` varchar(32) NOT NULL,
  `setting` text NOT NULL,
  PRIMARY KEY (`module_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_module`
--

INSERT INTO `oc_module` (`module_id`, `name`, `code`, `setting`) VALUES
(27, 'Home Page', 'slideshow', '{\"name\":\"Home Page\",\"banner_id\":\"7\",\"width\":\"1140\",\"height\":\"380\",\"status\":\"1\"}'),
(28, 'Home Page', 'featured', '{\"name\":\"Home Page\",\"product\":[\"43\",\"40\",\"42\",\"30\"],\"limit\":\"4\",\"width\":\"200\",\"height\":\"200\",\"status\":\"1\"}'),
(29, 'Home Page', 'carousel', '{\"name\":\"Home Page\",\"banner_id\":\"8\",\"width\":\"130\",\"height\":\"100\",\"status\":\"1\"}'),
(30, 'Category', 'banner', '{\"name\":\"Category\",\"banner_id\":\"6\",\"width\":\"182\",\"height\":\"182\",\"status\":\"1\"}'),
(31, 'Banner 1', 'banner', '{\"name\":\"Banner 1\",\"banner_id\":\"6\",\"width\":\"182\",\"height\":\"182\",\"status\":\"1\"}'),
(32, 'Последние статьи', 'blog_latest', '{\"name\":\"\\u041f\\u043e\\u0441\\u043b\\u0435\\u0434\\u043d\\u0438\\u0435 \\u0441\\u0442\\u0430\\u0442\\u044c\\u0438\",\"limit\":\"4\",\"width\":\"200\",\"height\":\"200\",\"status\":\"1\"}'),
(33, 'Рекомендуемые статьи', 'blog_featured', '{\"name\":\"\\u0420\\u0435\\u043a\\u043e\\u043c\\u0435\\u043d\\u0434\\u0443\\u0435\\u043c\\u044b\\u0435 \\u0441\\u0442\\u0430\\u0442\\u044c\\u0438\",\"article_name\":\"\",\"article\":[\"120\",\"123\",\"125\",\"124\"],\"limit\":\"4\",\"width\":\"200\",\"height\":\"200\",\"status\":\"1\"}'),
(34, 'Рекомендуемые статьи в товаре, категории и производителе', 'featured_article', '{\"name\":\"\\u0420\\u0435\\u043a\\u043e\\u043c\\u0435\\u043d\\u0434\\u0443\\u0435\\u043c\\u044b\\u0435 \\u0441\\u0442\\u0430\\u0442\\u044c\\u0438 \\u0432 \\u0442\\u043e\\u0432\\u0430\\u0440\\u0435, \\u043a\\u0430\\u0442\\u0435\\u0433\\u043e\\u0440\\u0438\\u0438 \\u0438 \\u043f\\u0440\\u043e\\u0438\\u0437\\u0432\\u043e\\u0434\\u0438\\u0442\\u0435\\u043b\\u0435\",\"limit\":\"4\",\"width\":\"200\",\"height\":\"200\",\"status\":\"1\"}'),
(35, 'Рекомендуемые товары в категории и производителе', 'featured_product', '{\"name\":\"\\u0420\\u0435\\u043a\\u043e\\u043c\\u0435\\u043d\\u0434\\u0443\\u0435\\u043c\\u044b\\u0435 \\u0442\\u043e\\u0432\\u0430\\u0440\\u044b \\u0432 \\u043a\\u0430\\u0442\\u0435\\u0433\\u043e\\u0440\\u0438\\u0438 \\u0438 \\u043f\\u0440\\u043e\\u0438\\u0437\\u0432\\u043e\\u0434\\u0438\\u0442\\u0435\\u043b\\u0435\",\"limit\":\"4\",\"width\":\"200\",\"height\":\"200\",\"status\":\"1\"}');

----------------------------------------------------------

--
-- Table structure for table `oc_option`
--

DROP TABLE IF EXISTS `oc_option`;
CREATE TABLE `oc_option` (
  `option_id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(32) NOT NULL,
  `sort_order` int(3) NOT NULL,
  PRIMARY KEY (`option_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_option`
--

INSERT INTO `oc_option` (`option_id`, `type`, `sort_order`) VALUES
(1, 'radio', 1),
(2, 'checkbox', 2),
(4, 'text', 3),
(5, 'select', 4),
(6, 'textarea', 5),
(7, 'file', 6),
(8, 'date', 7),
(9, 'time', 8),
(10, 'datetime', 9),
(11, 'select', 10),
(12, 'date', 11);

----------------------------------------------------------

--
-- Table structure for table `oc_option_description`
--

DROP TABLE IF EXISTS `oc_option_description`;
CREATE TABLE `oc_option_description` (
  `option_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `name` varchar(128) NOT NULL,
  PRIMARY KEY (`option_id`,`language_id`),
  KEY `option_id` (`option_id`),
  KEY `language_id` (`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_option_description`
--

INSERT INTO `oc_option_description` (`option_id`, `language_id`, `name`) VALUES
(1, 1, 'Переключатель'),
(1, 2, 'Перемикач'),
(1, 3, 'Radio'),
(2, 1, 'Флажок'),
(2, 2, 'Прапорець'),
(2, 3, 'Checkbox'),
(4, 1, 'Текст'),
(4, 2, 'Текст'),
(4, 3, 'Text'),
(5, 1, 'Список'),
(5, 2, 'Список'),
(5, 3, 'Select'),
(6, 1, 'Текстовая область'),
(6, 2, 'Текстове поле'),
(6, 3, 'Textarea'),
(7, 1, 'Файл'),
(7, 2, 'Файл'),
(7, 3, 'File'),
(8, 1, 'Дата'),
(8, 2, 'Дата'),
(8, 3, 'Date'),
(9, 1, 'Время'),
(9, 2, 'Час'),
(9, 3, 'Time'),
(10, 1, 'Дата и время'),
(10, 2, 'Дата та час'),
(10, 3, 'Date &amp; Time'),
(11, 1, 'Размер'),
(11, 2, 'Розмір'),
(11, 3, 'Size'),
(12, 1, 'Время доставки'),
(12, 2, 'Час доставки'),
(12, 3, 'Delivery Date');

----------------------------------------------------------

--
-- Table structure for table `oc_option_value`
--

DROP TABLE IF EXISTS `oc_option_value`;
CREATE TABLE `oc_option_value` (
  `option_value_id` int(11) NOT NULL AUTO_INCREMENT,
  `option_id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `sort_order` int(3) NOT NULL,
  PRIMARY KEY (`option_value_id`),
  KEY `option_id` (`option_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_option_value`
--

INSERT INTO `oc_option_value` (`option_value_id`, `option_id`, `image`, `sort_order`) VALUES
(23, 2, '', 1),
(24, 2, '', 2),
(31, 1, '', 2),
(32, 1, '', 1),
(39, 5, '', 1),
(40, 5, '', 2),
(41, 5, '', 3),
(42, 5, '', 4),
(43, 1, '', 3),
(44, 2, '', 3),
(45, 2, '', 4),
(46, 11, '', 1),
(47, 11, '', 2),
(48, 11, '', 3);

----------------------------------------------------------

--
-- Table structure for table `oc_option_value_description`
--

DROP TABLE IF EXISTS `oc_option_value_description`;
CREATE TABLE `oc_option_value_description` (
  `option_value_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `option_id` int(11) NOT NULL,
  `name` varchar(128) NOT NULL,
  PRIMARY KEY (`option_value_id`,`language_id`),
  KEY `option_value_id` (`option_value_id`),
  KEY `option_id` (`option_id`),
  KEY `language_id` (`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_option_value_description`
--

INSERT INTO `oc_option_value_description` (`option_value_id`, `language_id`, `option_id`, `name`) VALUES
(23, 1, 2, 'Флажок 1'),
(23, 3, 2, 'Checkbox 1'),
(24, 1, 2, 'Флажок 2'),
(24, 3, 2, 'Checkbox 2'),
(31, 1, 1, 'Средний'),
(31, 3, 1, 'Medium'),
(32, 1, 1, 'Маленький'),
(32, 3, 1, 'Small'),
(39, 1, 5, 'Красный'),
(39, 3, 5, 'Red'),
(40, 1, 5, 'Синий'),
(40, 3, 5, 'Blue'),
(41, 1, 5, 'Зеленый'),
(41, 3, 5, 'Green'),
(42, 1, 5, 'Желтый'),
(42, 3, 5, 'Yellow'),
(43, 1, 1, 'Большой'),
(43, 3, 1, 'Large'),
(44, 1, 2, 'Флажок 3'),
(44, 3, 2, 'Checkbox 3'),
(45, 1, 2, 'Флажок 4'),
(45, 3, 2, 'Checkbox 4'),
(46, 1, 11, 'Маленький'),
(46, 3, 11, 'Small'),
(47, 1, 11, 'Средний'),
(47, 3, 11, 'Medium'),
(48, 1, 11, 'Большой'),
(48, 3, 11, 'Large');

----------------------------------------------------------

--
-- Table structure for table `oc_order`
--

DROP TABLE IF EXISTS `oc_order`;
CREATE TABLE `oc_order` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_no` int(11) NOT NULL DEFAULT '0',
  `invoice_prefix` varchar(26) NOT NULL,
  `store_id` int(11) NOT NULL DEFAULT '0',
  `store_name` varchar(64) NOT NULL,
  `store_url` varchar(255) NOT NULL,
  `customer_id` int(11) NOT NULL DEFAULT '0',
  `customer_group_id` int(11) NOT NULL DEFAULT '0',
  `firstname` varchar(32) NOT NULL,
  `lastname` varchar(32) NOT NULL,
  `email` varchar(96) NOT NULL,
  `telephone` varchar(32) NOT NULL,
  `fax` varchar(32) NOT NULL,
  `custom_field` text NOT NULL,
  `payment_firstname` varchar(32) NOT NULL,
  `payment_lastname` varchar(32) NOT NULL,
  `payment_company` varchar(60) NOT NULL,
  `payment_address_1` varchar(128) NOT NULL,
  `payment_address_2` varchar(128) NOT NULL,
  `payment_city` varchar(128) NOT NULL,
  `payment_postcode` varchar(10) NOT NULL,
  `payment_country` varchar(128) NOT NULL,
  `payment_country_id` int(11) NOT NULL,
  `payment_zone` varchar(128) NOT NULL,
  `payment_zone_id` int(11) NOT NULL,
  `payment_address_format` text NOT NULL,
  `payment_custom_field` text NOT NULL,
  `payment_method` varchar(128) NOT NULL,
  `payment_code` varchar(128) NOT NULL,
  `shipping_firstname` varchar(32) NOT NULL,
  `shipping_lastname` varchar(32) NOT NULL,
  `shipping_company` varchar(40) NOT NULL,
  `shipping_address_1` varchar(128) NOT NULL,
  `shipping_address_2` varchar(128) NOT NULL,
  `shipping_city` varchar(128) NOT NULL,
  `shipping_postcode` varchar(10) NOT NULL,
  `shipping_country` varchar(128) NOT NULL,
  `shipping_country_id` int(11) NOT NULL,
  `shipping_zone` varchar(128) NOT NULL,
  `shipping_zone_id` int(11) NOT NULL,
  `shipping_address_format` text NOT NULL,
  `shipping_custom_field` text NOT NULL,
  `shipping_method` varchar(128) NOT NULL,
  `shipping_code` varchar(128) NOT NULL,
  `comment` text NOT NULL,
  `total` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `order_status_id` int(11) NOT NULL DEFAULT '0',
  `affiliate_id` int(11) NOT NULL,
  `commission` decimal(15,4) NOT NULL,
  `marketing_id` int(11) NOT NULL,
  `tracking` varchar(64) NOT NULL,
  `language_id` int(11) NOT NULL,
  `currency_id` int(11) NOT NULL,
  `currency_code` varchar(3) NOT NULL,
  `currency_value` decimal(15,8) NOT NULL DEFAULT '1.00000000',
  `ip` varchar(40) NOT NULL,
  `forwarded_ip` varchar(40) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `accept_language` varchar(255) NOT NULL,
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`order_id`),
  KEY `store_id` (`store_id`),
  KEY `customer_id` (`customer_id`),
  KEY `firstname` (`firstname`),
  KEY `lastname` (`lastname`),
  KEY `email` (`email`),
  KEY `telephone` (`telephone`),
  KEY `order_status_id` (`order_status_id`),
  KEY `marketing_id` (`marketing_id`),
  KEY `language_id` (`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_order`
--


----------------------------------------------------------

--
-- Table structure for table `oc_order_history`
--

DROP TABLE IF EXISTS `oc_order_history`;
CREATE TABLE `oc_order_history` (
  `order_history_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `order_status_id` int(11) NOT NULL,
  `notify` tinyint(1) NOT NULL DEFAULT '0',
  `comment` text NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`order_history_id`),
  KEY `order_id` (`order_id`),
  KEY `order_status_id` (`order_status_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_order_history`
--


----------------------------------------------------------

--
-- Table structure for table `oc_order_option`
--

DROP TABLE IF EXISTS `oc_order_option`;
CREATE TABLE `oc_order_option` (
  `order_option_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `order_product_id` int(11) NOT NULL,
  `product_option_id` int(11) NOT NULL,
  `product_option_value_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `type` varchar(32) NOT NULL,
  PRIMARY KEY (`order_option_id`),
  KEY `order_id` (`order_id`),
  KEY `order_product_id` (`order_product_id`),
  KEY `product_option_id` (`product_option_id`),
  KEY `product_option_value_id` (`product_option_value_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_order_option`
--


----------------------------------------------------------

--
-- Table structure for table `oc_order_product`
--

DROP TABLE IF EXISTS `oc_order_product`;
CREATE TABLE `oc_order_product` (
  `order_product_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `model` varchar(64) NOT NULL,
  `quantity` int(4) NOT NULL,
  `price` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `cost_price` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `total` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `tax` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `reward` int(8) NOT NULL,
  PRIMARY KEY (`order_product_id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_order_product`
--


----------------------------------------------------------

--
-- Table structure for table `oc_order_recurring`
--

DROP TABLE IF EXISTS `oc_order_recurring`;
CREATE TABLE `oc_order_recurring` (
  `order_recurring_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `reference` varchar(255) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_quantity` int(11) NOT NULL,
  `recurring_id` int(11) NOT NULL,
  `recurring_name` varchar(255) NOT NULL,
  `recurring_description` varchar(255) NOT NULL,
  `recurring_frequency` varchar(25) NOT NULL,
  `recurring_cycle` smallint(6) NOT NULL,
  `recurring_duration` smallint(6) NOT NULL,
  `recurring_price` decimal(10,4) NOT NULL,
  `trial` tinyint(1) NOT NULL,
  `trial_frequency` varchar(25) NOT NULL,
  `trial_cycle` smallint(6) NOT NULL,
  `trial_duration` smallint(6) NOT NULL,
  `trial_price` decimal(10,4) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`order_recurring_id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_order_recurring`
--


----------------------------------------------------------

--
-- Table structure for table `oc_order_recurring_transaction`
--

DROP TABLE IF EXISTS `oc_order_recurring_transaction`;
CREATE TABLE `oc_order_recurring_transaction` (
  `order_recurring_transaction_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_recurring_id` int(11) NOT NULL,
  `reference` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `amount` decimal(10,4) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`order_recurring_transaction_id`),
  KEY `order_recurring_id` (`order_recurring_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_order_recurring_transaction`
--


----------------------------------------------------------

--
-- Table structure for table `oc_order_shipment`
--

DROP TABLE IF EXISTS `oc_order_shipment`;
CREATE TABLE `oc_order_shipment` (
  `order_shipment_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `date_added` datetime NOT NULL,
  `shipping_courier_id` varchar(255) NOT NULL DEFAULT '',
  `tracking_number` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`order_shipment_id`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_order_shipment`
--


----------------------------------------------------------

--
-- Table structure for table `oc_order_status`
--

DROP TABLE IF EXISTS `oc_order_status`;
CREATE TABLE `oc_order_status` (
  `order_status_id` int(11) NOT NULL AUTO_INCREMENT,
  `language_id` int(11) NOT NULL,
  `name` varchar(32) NOT NULL,
  PRIMARY KEY (`order_status_id`,`language_id`),
  KEY `order_status_id` (`order_status_id`),
  KEY `language_id` (`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_order_status`
--

INSERT INTO `oc_order_status` (`order_status_id`, `language_id`, `name`) VALUES
(1, 1, 'Ожидание'),
(1, 2, 'Очікування'),
(1, 3, 'Waiting'),
(2, 1, 'В обработке'),
(2, 2, 'В обробці'),
(2, 3, 'In processing'),
(3, 1, 'Оплачен'),
(3, 2, 'Сплачено'),
(3, 3, 'Paid'),
(4, 1, 'Изменённый'),
(4, 2, 'Змінений'),
(4, 3, 'Modified'),
(5, 1, 'Возврат'),
(5, 2, 'Повернення'),
(5, 3, 'Return'),
(6, 1, 'Отправлен'),
(6, 2, 'Надіслано'),
(6, 3, 'Order sent'),
(7, 1, 'Отмена'),
(7, 2, 'Скасування'),
(7, 3, 'Cancel'),
(8, 1, 'Доставлено'),
(8, 2, 'Доставлено'),
(8, 3, 'Delivered'),
(9, 1, 'Неудавшийся'),
(9, 2, 'Невдалий'),
(9, 3, 'Failed'),
(10, 1, 'Отмена и аннулирование'),
(10, 2, 'Скасування та анулювання'),
(10, 3, 'Cancellation and annulment'),
(11, 1, 'Ожидание оплаты'),
(11, 2, 'Очікування оплати'),
(11, 3, 'Waiting for payment');

----------------------------------------------------------

--
-- Table structure for table `oc_order_total`
--

DROP TABLE IF EXISTS `oc_order_total`;
CREATE TABLE `oc_order_total` (
  `order_total_id` int(10) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `code` varchar(32) NOT NULL,
  `title` varchar(255) NOT NULL,
  `value` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `sort_order` int(3) NOT NULL,
  PRIMARY KEY (`order_total_id`),
  KEY `order_id` (`order_id`),
  KEY `code` (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_order_total`
--


----------------------------------------------------------

--
-- Table structure for table `oc_order_voucher`
--

DROP TABLE IF EXISTS `oc_order_voucher`;
CREATE TABLE `oc_order_voucher` (
  `order_voucher_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `voucher_id` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  `code` varchar(10) NOT NULL,
  `from_name` varchar(64) NOT NULL,
  `from_email` varchar(96) NOT NULL,
  `to_name` varchar(64) NOT NULL,
  `to_email` varchar(96) NOT NULL,
  `voucher_theme_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `amount` decimal(15,4) NOT NULL,
  PRIMARY KEY (`order_voucher_id`),
  KEY `order_id` (`order_id`),
  KEY `voucher_id` (`voucher_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_order_voucher`
--


----------------------------------------------------------

--
-- Table structure for table `oc_product`
--

DROP TABLE IF EXISTS `oc_product`;
CREATE TABLE `oc_product` (
  `product_id` int(11) NOT NULL AUTO_INCREMENT,
  `model` varchar(64) NOT NULL,
  `sku` varchar(64) NOT NULL,
  `upc` varchar(12) NOT NULL,
  `ean` varchar(14) NOT NULL,
  `jan` varchar(13) NOT NULL,
  `isbn` varchar(17) NOT NULL,
  `mpn` varchar(64) NOT NULL,
  `location` varchar(128) NOT NULL,
  `quantity` int(4) NOT NULL DEFAULT '0',
  `stock_status_id` int(11) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `manufacturer_id` int(11) NOT NULL,
  `shipping` tinyint(1) NOT NULL DEFAULT '1',
  `price` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `cost_price` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `points` int(8) NOT NULL DEFAULT '0',
  `tax_class_id` int(11) NOT NULL,
  `date_available` date NOT NULL,
  `weight` decimal(15,8) NOT NULL DEFAULT '0.00000000',
  `weight_class_id` int(11) NOT NULL DEFAULT '0',
  `length` decimal(15,8) NOT NULL DEFAULT '0.00000000',
  `width` decimal(15,8) NOT NULL DEFAULT '0.00000000',
  `height` decimal(15,8) NOT NULL DEFAULT '0.00000000',
  `length_class_id` int(11) NOT NULL DEFAULT '0',
  `subtract` tinyint(1) NOT NULL DEFAULT '1',
  `minimum` int(11) NOT NULL DEFAULT '1',
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `viewed` int(5) NOT NULL DEFAULT '0',
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  `noindex` tinyint(1) NOT NULL DEFAULT '1',
  `comment` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`product_id`),
  KEY `model` (`model`),
  KEY `stock_status_id` (`stock_status_id`),
  KEY `manufacturer_id` (`manufacturer_id`),
  KEY `date_available` (`date_available`),
  KEY `sort_order` (`sort_order`),
  KEY `status` (`status`),
  KEY `date_added` (`date_added`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_product`
--

INSERT INTO `oc_product` (`product_id`, `model`, `sku`, `upc`, `ean`, `jan`, `isbn`, `mpn`, `location`, `quantity`, `stock_status_id`, `image`, `manufacturer_id`, `shipping`, `price`, `cost_price`, `points`, `tax_class_id`, `date_available`, `weight`, `weight_class_id`, `length`, `width`, `height`, `length_class_id`, `subtract`, `minimum`, `sort_order`, `status`, `viewed`, `date_added`, `date_modified`, `noindex`, `comment`) VALUES
(28, 'Product 1', '', '', '', '', '', '', '', 939, 7, 'catalog/demo/htc_touch_hd_1.jpg', 5, 1, '100.0000', '0.0000', 200, 9, '2009-02-03', '146.40000000', 2, '0.00000000', '0.00000000', '0.00000000', 1, 1, 1, 0, 1, 0, '2009-02-03 16:06:50', '2011-09-30 01:05:39', 1, NULL),
(29, 'Product 2', '', '', '', '', '', '', '', 999, 6, 'catalog/demo/palm_treo_pro_1.jpg', 6, 1, '279.9900', '0.0000', 0, 9, '2009-02-03', '133.00000000', 2, '0.00000000', '0.00000000', '0.00000000', 3, 1, 1, 0, 1, 0, '2009-02-03 16:42:17', '2011-09-30 01:06:08', 1, NULL),
(30, 'Product 3', '', '', '', '', '', '', '', 7, 6, 'catalog/demo/canon_eos_5d_1.jpg', 9, 1, '100.0000', '0.0000', 0, 9, '2009-02-03', '0.00000000', 1, '0.00000000', '0.00000000', '0.00000000', 1, 1, 1, 0, 1, 0, '2009-02-03 16:59:00', '2011-09-30 01:05:23', 1, NULL),
(31, 'Product 4', '', '', '', '', '', '', '', 1000, 6, 'catalog/demo/nikon_d300_1.jpg', 0, 1, '80.0000', '0.0000', 0, 9, '2009-02-03', '0.00000000', 1, '0.00000000', '0.00000000', '0.00000000', 3, 1, 1, 0, 1, 0, '2009-02-03 17:00:10', '2011-09-30 01:06:00', 1, NULL),
(32, 'Product 5', '', '', '', '', '', '', '', 999, 6, 'catalog/demo/ipod_touch_1.jpg', 8, 1, '100.0000', '0.0000', 0, 9, '2009-02-03', '5.00000000', 1, '0.00000000', '0.00000000', '0.00000000', 1, 1, 1, 0, 1, 0, '2009-02-03 17:07:26', '2011-09-30 01:07:22', 1, NULL),
(33, 'Product 6', '', '', '', '', '', '', '', 1000, 6, 'catalog/demo/samsung_syncmaster_941bw.jpg', 0, 1, '200.0000', '0.0000', 0, 9, '2009-02-03', '5.00000000', 1, '0.00000000', '0.00000000', '0.00000000', 2, 1, 1, 0, 1, 0, '2009-02-03 17:08:31', '2011-09-30 01:06:29', 1, NULL),
(34, 'Product 7', '', '', '', '', '', '', '', 1000, 6, 'catalog/demo/ipod_shuffle_1.jpg', 8, 1, '100.0000', '0.0000', 0, 9, '2009-02-03', '5.00000000', 1, '0.00000000', '0.00000000', '0.00000000', 2, 1, 1, 0, 1, 0, '2009-02-03 18:07:54', '2011-09-30 01:07:17', 1, NULL),
(35, 'Product 8', '', '', '', '', '', '', '', 1000, 5, '', 0, 0, '100.0000', '0.0000', 0, 9, '2009-02-03', '5.00000000', 1, '0.00000000', '0.00000000', '0.00000000', 1, 1, 1, 0, 1, 0, '2009-02-03 18:08:31', '2011-09-30 01:06:17', 1, NULL),
(36, 'Product 9', '', '', '', '', '', '', '', 994, 6, 'catalog/demo/ipod_nano_1.jpg', 8, 0, '100.0000', '0.0000', 100, 9, '2009-02-03', '5.00000000', 1, '0.00000000', '0.00000000', '0.00000000', 2, 1, 1, 0, 1, 0, '2009-02-03 18:09:19', '2011-09-30 01:07:12', 1, NULL),
(40, 'product 11', '', '', '', '', '', '', '', 970, 5, 'catalog/demo/iphone_1.jpg', 8, 1, '101.0000', '0.0000', 0, 9, '2009-02-03', '10.00000000', 1, '0.00000000', '0.00000000', '0.00000000', 1, 1, 1, 0, 1, 0, '2009-02-03 21:07:12', '2011-09-30 01:06:53', 1, NULL),
(41, 'Product 14', '', '', '', '', '', '', '', 977, 5, 'catalog/demo/imac_1.jpg', 8, 1, '100.0000', '0.0000', 0, 9, '2009-02-03', '5.00000000', 1, '0.00000000', '0.00000000', '0.00000000', 1, 1, 1, 0, 1, 0, '2009-02-03 21:07:26', '2011-09-30 01:06:44', 1, NULL),
(42, 'Product 15', '', '', '', '', '', '', '', 990, 5, 'catalog/demo/apple_cinema_30.jpg', 8, 1, '100.0000', '0.0000', 400, 9, '2009-02-04', '12.50000000', 1, '1.00000000', '2.00000000', '3.00000000', 1, 1, 2, 0, 1, 0, '2009-02-03 21:07:37', '2017-07-26 22:30:20', 0, NULL),
(43, 'Product 16', '', '', '', '', '', '', '', 929, 5, 'catalog/demo/macbook_1.jpg', 8, 0, '500.0000', '0.0000', 0, 9, '2009-02-03', '0.00000000', 1, '0.00000000', '0.00000000', '0.00000000', 2, 1, 1, 0, 1, 0, '2009-02-03 21:07:49', '2011-09-30 01:05:46', 1, NULL),
(44, 'Product 17', '', '', '', '', '', '', '', 1000, 5, 'catalog/demo/macbook_air_1.jpg', 8, 1, '1000.0000', '0.0000', 0, 9, '2009-02-03', '0.00000000', 1, '0.00000000', '0.00000000', '0.00000000', 2, 1, 1, 0, 1, 0, '2009-02-03 21:08:00', '2011-09-30 01:05:53', 1, NULL),
(45, 'Product 18', '', '', '', '', '', '', '', 998, 5, 'catalog/demo/macbook_pro_1.jpg', 8, 1, '2000.0000', '0.0000', 0, 100, '2009-02-03', '0.00000000', 1, '0.00000000', '0.00000000', '0.00000000', 2, 1, 1, 0, 1, 0, '2009-02-03 21:08:17', '2011-09-15 22:22:01', 1, NULL),
(46, 'Product 19', '', '', '', '', '', '', '', 1000, 5, 'catalog/demo/sony_vaio_1.jpg', 10, 1, '1000.0000', '0.0000', 0, 9, '2009-02-03', '0.00000000', 1, '0.00000000', '0.00000000', '0.00000000', 2, 1, 1, 0, 1, 0, '2009-02-03 21:08:29', '2011-09-30 01:06:39', 1, NULL),
(47, 'Product 21', '', '', '', '', '', '', '', 1000, 5, 'catalog/demo/hp_1.jpg', 7, 1, '100.0000', '0.0000', 400, 9, '2009-02-03', '1.00000000', 1, '0.00000000', '0.00000000', '0.00000000', 1, 0, 1, 0, 1, 0, '2009-02-03 21:08:40', '2011-09-30 01:05:28', 1, NULL),
(48, 'product 20', 'test 1', '', '', '', '', '', 'test 2', 995, 5, 'catalog/demo/ipod_classic_1.jpg', 8, 1, '100.0000', '0.0000', 0, 9, '2009-02-08', '1.00000000', 1, '0.00000000', '0.00000000', '0.00000000', 2, 1, 1, 0, 1, 0, '2009-02-08 17:21:51', '2011-09-30 01:07:06', 1, NULL),
(49, 'SAM1', '', '', '', '', '', '', '', 0, 8, 'catalog/demo/samsung_tab_1.jpg', 0, 1, '199.9900', '0.0000', 0, 9, '2011-04-25', '0.00000000', 1, '0.00000000', '0.00000000', '0.00000000', 1, 1, 1, 1, 1, 0, '2011-04-26 08:57:34', '2011-09-30 01:06:23', 1, NULL);

----------------------------------------------------------

--
-- Table structure for table `oc_product_attribute`
--

DROP TABLE IF EXISTS `oc_product_attribute`;
CREATE TABLE `oc_product_attribute` (
  `product_id` int(11) NOT NULL,
  `attribute_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `text` text NOT NULL,
  PRIMARY KEY (`product_id`,`attribute_id`,`language_id`),
  KEY `product_id` (`product_id`),
  KEY `attribute_id` (`attribute_id`),
  KEY `language_id` (`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_product_attribute`
--

INSERT INTO `oc_product_attribute` (`product_id`, `attribute_id`, `language_id`, `text`) VALUES
(42, 3, 1, '100mhz'),
(43, 2, 1, '1'),
(43, 4, 1, '8gb'),
(47, 2, 1, '4'),
(47, 4, 1, '16GB');

----------------------------------------------------------

--
-- Table structure for table `oc_product_description`
--

DROP TABLE IF EXISTS `oc_product_description`;
CREATE TABLE `oc_product_description` (
  `product_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `tag` text NOT NULL,
  `meta_title` varchar(255) NOT NULL,
  `meta_description` varchar(255) NOT NULL,
  `meta_keyword` varchar(255) NOT NULL,
  `meta_h1` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`product_id`,`language_id`),
  KEY `product_id` (`product_id`),
  KEY `language_id` (`language_id`),
  KEY `name` (`name`(191))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_product_description`
--

INSERT INTO `oc_product_description` (`product_id`, `language_id`, `name`, `description`, `tag`, `meta_title`, `meta_description`, `meta_keyword`, `meta_h1`) VALUES
(28, 1, 'HTC Touch HD', '&lt;p&gt;\r\n HTC Touch - in High Definition. Watch music videos and streaming content in awe-inspiring high definition clarity for a mobile experience you never thought possible. Seductively sleek, the HTC Touch HD provides the next generation of mobile functionality, all at a simple touch. Fully integrated with Windows Mobile Professional 6.1, ultrafast 3.5G, GPS, 5MP camera, plus lots more - all delivered on a breathtakingly crisp 3.8&amp;quot; WVGA touchscreen - you can take control of your mobile world with the HTC Touch HD.&lt;/p&gt;\r\n&lt;p&gt;\r\n &lt;strong&gt;Features&lt;/strong&gt;&lt;/p&gt;\r\n&lt;ul&gt;\r\n &lt;li&gt;\r\n  Processor Qualcomm&amp;reg; MSM 7201A&amp;trade; 528 MHz&lt;/li&gt;\r\n &lt;li&gt;\r\n  Windows Mobile&amp;reg; 6.1 Professional Operating System&lt;/li&gt;\r\n &lt;li&gt;\r\n  Memory: 512 MB ROM, 288 MB RAM&lt;/li&gt;\r\n &lt;li&gt;\r\n  Dimensions: 115 mm x 62.8 mm x 12 mm / 146.4 grams&lt;/li&gt;\r\n &lt;li&gt;\r\n  3.8-inch TFT-LCD flat touch-sensitive screen with 480 x 800 WVGA resolution&lt;/li&gt;\r\n &lt;li&gt;\r\n  HSDPA/WCDMA: Europe/Asia: 900/2100 MHz; Up to 2 Mbps up-link and 7.2 Mbps down-link speeds&lt;/li&gt;\r\n &lt;li&gt;\r\n  Quad-band GSM/GPRS/EDGE: Europe/Asia: 850/900/1800/1900 MHz (Band frequency, HSUPA availability, and data speed are operator dependent.)&lt;/li&gt;\r\n &lt;li&gt;\r\n  Device Control via HTC TouchFLO&amp;trade; 3D &amp;amp; Touch-sensitive front panel buttons&lt;/li&gt;\r\n &lt;li&gt;\r\n  GPS and A-GPS ready&lt;/li&gt;\r\n &lt;li&gt;\r\n  Bluetooth&amp;reg; 2.0 with Enhanced Data Rate and A2DP for wireless stereo headsets&lt;/li&gt;\r\n &lt;li&gt;\r\n  Wi-Fi&amp;reg;: IEEE 802.11 b/g&lt;/li&gt;\r\n &lt;li&gt;\r\n  HTC ExtUSB&amp;trade; (11-pin mini-USB 2.0)&lt;/li&gt;\r\n &lt;li&gt;\r\n  5 megapixel color camera with auto focus&lt;/li&gt;\r\n &lt;li&gt;\r\n  VGA CMOS color camera&lt;/li&gt;\r\n &lt;li&gt;\r\n  Built-in 3.5 mm audio jack, microphone, speaker, and FM radio&lt;/li&gt;\r\n &lt;li&gt;\r\n  Ring tone formats: AAC, AAC+, eAAC+, AMR-NB, AMR-WB, QCP, MP3, WMA, WAV&lt;/li&gt;\r\n &lt;li&gt;\r\n  40 polyphonic and standard MIDI format 0 and 1 (SMF)/SP MIDI&lt;/li&gt;\r\n &lt;li&gt;\r\n  Rechargeable Lithium-ion or Lithium-ion polymer 1350 mAh battery&lt;/li&gt;\r\n &lt;li&gt;\r\n  Expansion Slot: microSD&amp;trade; memory card (SD 2.0 compatible)&lt;/li&gt;\r\n &lt;li&gt;\r\n  AC Adapter Voltage range/frequency: 100 ~ 240V AC, 50/60 Hz DC output: 5V and 1A&lt;/li&gt;\r\n &lt;li&gt;\r\n  Special Features: FM Radio, G-Sensor&lt;/li&gt;\r\n&lt;/ul&gt;\r\n', '', '', '', '', ''),
(28, 2, 'HTC Touch HD', '&lt;p&gt;&lt;br&gt;&lt;/p&gt;&lt;p&gt;\r\n HTC Touch - in High Definition. Watch music videos and streaming \r\ncontent in awe-inspiring high definition clarity for a mobile experience\r\n you never thought possible. Seductively sleek, the HTC Touch HD \r\nprovides the next generation of mobile functionality, all at a simple \r\ntouch. Fully integrated with Windows Mobile Professional 6.1, ultrafast \r\n3.5G, GPS, 5MP camera, plus lots more - all delivered on a \r\nbreathtakingly crisp 3.8&quot; WVGA touchscreen - you can take control of \r\nyour mobile world with the HTC Touch HD.&lt;/p&gt;&lt;p&gt;\r\n&lt;br&gt;&lt;/p&gt;&lt;p&gt;\r\n &lt;strong&gt;Features&lt;/strong&gt;&lt;/p&gt;&lt;p&gt;\r\n&lt;br&gt;&lt;/p&gt;&lt;ul&gt;&lt;li&gt;\r\n  Processor Qualcomm® MSM 7201A™ 528 MHz&lt;/li&gt;&lt;li&gt;\r\n  Windows Mobile® 6.1 Professional Operating System&lt;/li&gt;&lt;li&gt;\r\n  Memory: 512 MB ROM, 288 MB RAM&lt;/li&gt;&lt;li&gt;\r\n  Dimensions: 115 mm x 62.8 mm x 12 mm / 146.4 grams&lt;/li&gt;&lt;li&gt;\r\n  3.8-inch TFT-LCD flat touch-sensitive screen with 480 x 800 WVGA resolution&lt;/li&gt;&lt;li&gt;\r\n  HSDPA/WCDMA: Europe/Asia: 900/2100 MHz; Up to 2 Mbps up-link and 7.2 Mbps down-link speeds&lt;/li&gt;&lt;li&gt;\r\n  Quad-band GSM/GPRS/EDGE: Europe/Asia: 850/900/1800/1900 MHz (Band \r\nfrequency, HSUPA availability, and data speed are operator dependent.)&lt;/li&gt;&lt;li&gt;\r\n  Device Control via HTC TouchFLO™ 3D &amp;amp; Touch-sensitive front panel buttons&lt;/li&gt;&lt;li&gt;\r\n  GPS and A-GPS ready&lt;/li&gt;&lt;li&gt;\r\n  Bluetooth® 2.0 with Enhanced Data Rate and A2DP for wireless stereo headsets&lt;/li&gt;&lt;li&gt;\r\n  Wi-Fi®: IEEE 802.11 b/g&lt;/li&gt;&lt;li&gt;\r\n  HTC ExtUSB™ (11-pin mini-USB 2.0)&lt;/li&gt;&lt;li&gt;\r\n  5 megapixel color camera with auto focus&lt;/li&gt;&lt;li&gt;\r\n  VGA CMOS color camera&lt;/li&gt;&lt;li&gt;\r\n  Built-in 3.5 mm audio jack, microphone, speaker, and FM radio&lt;/li&gt;&lt;li&gt;\r\n  Ring tone formats: AAC, AAC+, eAAC+, AMR-NB, AMR-WB, QCP, MP3, WMA, WAV&lt;/li&gt;&lt;li&gt;\r\n  40 polyphonic and standard MIDI format 0 and 1 (SMF)/SP MIDI&lt;/li&gt;&lt;li&gt;\r\n  Rechargeable Lithium-ion or Lithium-ion polymer 1350 mAh battery&lt;/li&gt;&lt;li&gt;\r\n  Expansion Slot: microSD™ memory card (SD 2.0 compatible)&lt;/li&gt;&lt;li&gt;\r\n  AC Adapter Voltage range/frequency: 100 ~ 240V AC, 50/60 Hz DC output: 5V and 1A&lt;/li&gt;&lt;li&gt;\r\n  Special Features: FM Radio, G-Sensor&lt;/li&gt;&lt;/ul&gt;&lt;p&gt;&lt;br&gt;&lt;/p&gt;', '', '', '', '', ''),
(29, 1, 'Palm Treo Pro', '&lt;p&gt;\r\n Redefine your workday with the Palm Treo Pro smartphone. Perfectly balanced, you can respond to business and personal email, stay on top of appointments and contacts, and use Wi-Fi or GPS when you&amp;rsquo;re out and about. Then watch a video on YouTube, catch up with news and sports on the web, or listen to a few songs. Balance your work and play the way you like it, with the Palm Treo Pro.&lt;/p&gt;\r\n&lt;p&gt;\r\n &lt;strong&gt;Features&lt;/strong&gt;&lt;/p&gt;\r\n&lt;ul&gt;\r\n &lt;li&gt;\r\n  Windows Mobile&amp;reg; 6.1 Professional Edition&lt;/li&gt;\r\n &lt;li&gt;\r\n  Qualcomm&amp;reg; MSM7201 400MHz Processor&lt;/li&gt;\r\n &lt;li&gt;\r\n  320x320 transflective colour TFT touchscreen&lt;/li&gt;\r\n &lt;li&gt;\r\n  HSDPA/UMTS/EDGE/GPRS/GSM radio&lt;/li&gt;\r\n &lt;li&gt;\r\n  Tri-band UMTS &amp;mdash; 850MHz, 1900MHz, 2100MHz&lt;/li&gt;\r\n &lt;li&gt;\r\n  Quad-band GSM &amp;mdash; 850/900/1800/1900&lt;/li&gt;\r\n &lt;li&gt;\r\n  802.11b/g with WPA, WPA2, and 801.1x authentication&lt;/li&gt;\r\n &lt;li&gt;\r\n  Built-in GPS&lt;/li&gt;\r\n &lt;li&gt;\r\n  Bluetooth Version: 2.0 + Enhanced Data Rate&lt;/li&gt;\r\n &lt;li&gt;\r\n  256MB storage (100MB user available), 128MB RAM&lt;/li&gt;\r\n &lt;li&gt;\r\n  2.0 megapixel camera, up to 8x digital zoom and video capture&lt;/li&gt;\r\n &lt;li&gt;\r\n  Removable, rechargeable 1500mAh lithium-ion battery&lt;/li&gt;\r\n &lt;li&gt;\r\n  Up to 5.0 hours talk time and up to 250 hours standby&lt;/li&gt;\r\n &lt;li&gt;\r\n  MicroSDHC card expansion (up to 32GB supported)&lt;/li&gt;\r\n &lt;li&gt;\r\n  MicroUSB 2.0 for synchronization and charging&lt;/li&gt;\r\n &lt;li&gt;\r\n  3.5mm stereo headset jack&lt;/li&gt;\r\n &lt;li&gt;\r\n  60mm (W) x 114mm (L) x 13.5mm (D) / 133g&lt;/li&gt;\r\n&lt;/ul&gt;\r\n', '', '', '', '', ''),
(29, 2, 'Palm Treo Pro', '&lt;p&gt;&lt;br&gt;&lt;/p&gt;&lt;p&gt;\r\n Redefine your workday with the Palm Treo Pro smartphone. Perfectly \r\nbalanced, you can respond to business and personal email, stay on top of\r\n appointments and contacts, and use Wi-Fi or GPS when you’re out and \r\nabout. Then watch a video on YouTube, catch up with news and sports on \r\nthe web, or listen to a few songs. Balance your work and play the way \r\nyou like it, with the Palm Treo Pro.&lt;/p&gt;&lt;p&gt;\r\n&lt;br&gt;&lt;/p&gt;&lt;p&gt;\r\n &lt;strong&gt;Features&lt;/strong&gt;&lt;/p&gt;&lt;p&gt;\r\n&lt;br&gt;&lt;/p&gt;&lt;ul&gt;&lt;li&gt;\r\n  Windows Mobile® 6.1 Professional Edition&lt;/li&gt;&lt;li&gt;\r\n  Qualcomm® MSM7201 400MHz Processor&lt;/li&gt;&lt;li&gt;\r\n  320x320 transflective colour TFT touchscreen&lt;/li&gt;&lt;li&gt;\r\n  HSDPA/UMTS/EDGE/GPRS/GSM radio&lt;/li&gt;&lt;li&gt;\r\n  Tri-band UMTS — 850MHz, 1900MHz, 2100MHz&lt;/li&gt;&lt;li&gt;\r\n  Quad-band GSM — 850/900/1800/1900&lt;/li&gt;&lt;li&gt;\r\n  802.11b/g with WPA, WPA2, and 801.1x authentication&lt;/li&gt;&lt;li&gt;\r\n  Built-in GPS&lt;/li&gt;&lt;li&gt;\r\n  Bluetooth Version: 2.0 + Enhanced Data Rate&lt;/li&gt;&lt;li&gt;\r\n  256MB storage (100MB user available), 128MB RAM&lt;/li&gt;&lt;li&gt;\r\n  2.0 megapixel camera, up to 8x digital zoom and video capture&lt;/li&gt;&lt;li&gt;\r\n  Removable, rechargeable 1500mAh lithium-ion battery&lt;/li&gt;&lt;li&gt;\r\n  Up to 5.0 hours talk time and up to 250 hours standby&lt;/li&gt;&lt;li&gt;\r\n  MicroSDHC card expansion (up to 32GB supported)&lt;/li&gt;&lt;li&gt;\r\n  MicroUSB 2.0 for synchronization and charging&lt;/li&gt;&lt;li&gt;\r\n  3.5mm stereo headset jack&lt;/li&gt;&lt;li&gt;\r\n  60mm (W) x 114mm (L) x 13.5mm (D) / 133g&lt;/li&gt;&lt;/ul&gt;&lt;p&gt;&lt;br&gt;&lt;/p&gt;', '', '', '', '', ''),
(30, 1, 'Canon EOS 5D', '&lt;p&gt;\r\n Canon\'s press material for the EOS 5D states that it \'defines (a) new D-SLR category\', while we\'re not typically too concerned with marketing talk this particular statement is clearly pretty accurate. The EOS 5D is unlike any previous digital SLR in that it combines a full-frame (35 mm sized) high resolution sensor (12.8 megapixels) with a relatively compact body (slightly larger than the EOS 20D, although in your hand it feels noticeably \'chunkier\'). The EOS 5D is aimed to slot in between the EOS 20D and the EOS-1D professional digital SLR\'s, an important difference when compared to the latter is that the EOS 5D doesn\'t have any environmental seals. While Canon don\'t specifically refer to the EOS 5D as a \'professional\' digital SLR it will have obvious appeal to professionals who want a high quality digital SLR in a body lighter than the EOS-1D. It will also no doubt appeal to current EOS 20D owners (although lets hope they\'ve not bought too many EF-S lenses...) äë&lt;/p&gt;\r\n', '', '', '', '', ''),
(30, 2, 'Canon EOS 5D', '&lt;p&gt;Canon\'s press material for the EOS 5D states that it \'defines (a) new \r\nD-SLR category\', while we\'re not typically too concerned with marketing \r\ntalk this particular statement is clearly pretty accurate. The EOS 5D is\r\n unlike any previous digital SLR in that it combines a full-frame (35 mm\r\n sized) high resolution sensor (12.8 megapixels) with a relatively \r\ncompact body (slightly larger than the EOS 20D, although in your hand it\r\n feels noticeably \'chunkier\'). The EOS 5D is aimed to slot in between \r\nthe EOS 20D and the EOS-1D professional digital SLR\'s, an important \r\ndifference when compared to the latter is that the EOS 5D doesn\'t have \r\nany environmental seals. While Canon don\'t specifically refer to the EOS\r\n 5D as a \'professional\' digital SLR it will have obvious appeal to \r\nprofessionals who want a high quality digital SLR in a body lighter than\r\n the EOS-1D. It will also no doubt appeal to current EOS 20D owners \r\n(although lets hope they\'ve not bought too many EF-S lenses...) äë&lt;br&gt;&lt;/p&gt;', '', '', '', '', ''),
(31, 1, 'Nikon D300', '&lt;div class=&quot;cpt_product_description &quot;&gt;\r\n &lt;div&gt;\r\n  Engineered with pro-level features and performance, the 12.3-effective-megapixel D300 combines brand new technologies with advanced features inherited from Nikon&amp;#39;s newly announced D3 professional digital SLR camera to offer serious photographers remarkable performance combined with agility.&lt;br /&gt;\r\n  &lt;br /&gt;\r\n  Similar to the D3, the D300 features Nikon&amp;#39;s exclusive EXPEED Image Processing System that is central to driving the speed and processing power needed for many of the camera&amp;#39;s new features. The D300 features a new 51-point autofocus system with Nikon&amp;#39;s 3D Focus Tracking feature and two new LiveView shooting modes that allow users to frame a photograph using the camera&amp;#39;s high-resolution LCD monitor. The D300 shares a similar Scene Recognition System as is found in the D3; it promises to greatly enhance the accuracy of autofocus, autoexposure, and auto white balance by recognizing the subject or scene being photographed and applying this information to the calculations for the three functions.&lt;br /&gt;\r\n  &lt;br /&gt;\r\n  The D300 reacts with lightning speed, powering up in a mere 0.13 seconds and shooting with an imperceptible 45-millisecond shutter release lag time. The D300 is capable of shooting at a rapid six frames per second and can go as fast as eight frames per second when using the optional MB-D10 multi-power battery pack. In continuous bursts, the D300 can shoot up to 100 shots at full 12.3-megapixel resolution. (NORMAL-LARGE image setting, using a SanDisk Extreme IV 1GB CompactFlash card.)&lt;br /&gt;\r\n  &lt;br /&gt;\r\n  The D300 incorporates a range of innovative technologies and features that will significantly improve the accuracy, control, and performance photographers can get from their equipment. Its new Scene Recognition System advances the use of Nikon&amp;#39;s acclaimed 1,005-segment sensor to recognize colors and light patterns that help the camera determine the subject and the type of scene being photographed before a picture is taken. This information is used to improve the accuracy of autofocus, autoexposure, and auto white balance functions in the D300. For example, the camera can track moving subjects better and by identifying them, it can also automatically select focus points faster and with greater accuracy. It can also analyze highlights and more accurately determine exposure, as well as infer light sources to deliver more accurate white balance detection.&lt;/div&gt;\r\n&lt;/div&gt;\r\n&lt;!-- cpt_container_end --&gt;', '', '', '', '', ''),
(31, 2, 'Nikon D300', '&lt;p&gt;Engineered with pro-level features and performance, the \r\n12.3-effective-megapixel D300 combines brand new technologies with \r\nadvanced features inherited from Nikon\'s newly announced D3 professional\r\n digital SLR camera to offer serious photographers remarkable \r\nperformance combined with agility.&lt;br&gt;\r\n  &lt;br&gt;\r\n  Similar to the D3, the D300 features Nikon\'s exclusive EXPEED Image \r\nProcessing System that is central to driving the speed and processing \r\npower needed for many of the camera\'s new features. The D300 features a \r\nnew 51-point autofocus system with Nikon\'s 3D Focus Tracking feature and\r\n two new LiveView shooting modes that allow users to frame a photograph \r\nusing the camera\'s high-resolution LCD monitor. The D300 shares a \r\nsimilar Scene Recognition System as is found in the D3; it promises to \r\ngreatly enhance the accuracy of autofocus, autoexposure, and auto white \r\nbalance by recognizing the subject or scene being photographed and \r\napplying this information to the calculations for the three functions.&lt;br&gt;\r\n  &lt;br&gt;\r\n  The D300 reacts with lightning speed, powering up in a mere 0.13 \r\nseconds and shooting with an imperceptible 45-millisecond shutter \r\nrelease lag time. The D300 is capable of shooting at a rapid six frames \r\nper second and can go as fast as eight frames per second when using the \r\noptional MB-D10 multi-power battery pack. In continuous bursts, the D300\r\n can shoot up to 100 shots at full 12.3-megapixel resolution. \r\n(NORMAL-LARGE image setting, using a SanDisk Extreme IV 1GB CompactFlash\r\n card.)&lt;br&gt;\r\n  &lt;br&gt;\r\n  The D300 incorporates a range of innovative technologies and features \r\nthat will significantly improve the accuracy, control, and performance \r\nphotographers can get from their equipment. Its new Scene Recognition \r\nSystem advances the use of Nikon\'s acclaimed 1,005-segment sensor to \r\nrecognize colors and light patterns that help the camera determine the \r\nsubject and the type of scene being photographed before a picture is \r\ntaken. This information is used to improve the accuracy of autofocus, \r\nautoexposure, and auto white balance functions in the D300. For example,\r\n the camera can track moving subjects better and by identifying them, it\r\n can also automatically select focus points faster and with greater \r\naccuracy. It can also analyze highlights and more accurately determine \r\nexposure, as well as infer light sources to deliver more accurate white \r\nbalance detection.&lt;br&gt;&lt;/p&gt;', '', '', '', '', ''),
(32, 1, 'iPod Touch', '&lt;p&gt;\r\n &lt;strong&gt;Revolutionary multi-touch interface.&lt;/strong&gt;&lt;br /&gt;\r\n iPod touch features the same multi-touch screen technology as iPhone. Pinch to zoom in on a photo. Scroll through your songs and videos with a flick. Flip through your library by album artwork with Cover Flow.&lt;/p&gt;\r\n&lt;p&gt;\r\n &lt;strong&gt;Gorgeous 3.5-inch widescreen display.&lt;/strong&gt;&lt;br /&gt;\r\n Watch your movies, TV shows, and photos come alive with bright, vivid color on the 320-by-480-pixel display.&lt;/p&gt;\r\n&lt;p&gt;\r\n &lt;strong&gt;Music downloads straight from iTunes.&lt;/strong&gt;&lt;br /&gt;\r\n Shop the iTunes Wi-Fi Music Store from anywhere with Wi-Fi.1 Browse or search to find the music youre looking for, preview it, and buy it with just a tap.&lt;/p&gt;\r\n&lt;p&gt;\r\n &lt;strong&gt;Surf the web with Wi-Fi.&lt;/strong&gt;&lt;br /&gt;\r\n Browse the web using Safari and watch YouTube videos on the first iPod with Wi-Fi built in&lt;br /&gt;\r\n &amp;nbsp;&lt;/p&gt;\r\n', '', '', '', '', ''),
(32, 2, 'iPod Touch', '&lt;p&gt;&lt;strong&gt;Revolutionary multi-touch interface.&lt;/strong&gt;&lt;br&gt;\r\n iPod touch features the same multi-touch screen technology as iPhone. \r\nPinch to zoom in on a photo. Scroll through your songs and videos with a\r\n flick. Flip through your library by album artwork with Cover Flow.&lt;/p&gt;&lt;p&gt;\r\n&lt;br&gt;&lt;/p&gt;&lt;p&gt;\r\n &lt;strong&gt;Gorgeous 3.5-inch widescreen display.&lt;/strong&gt;&lt;br&gt;\r\n Watch your movies, TV shows, and photos come alive with bright, vivid color on the 320-by-480-pixel display.&lt;/p&gt;&lt;p&gt;\r\n&lt;br&gt;&lt;/p&gt;&lt;p&gt;\r\n &lt;strong&gt;Music downloads straight from iTunes.&lt;/strong&gt;&lt;br&gt;\r\n Shop the iTunes Wi-Fi Music Store from anywhere with Wi-Fi.1 Browse or \r\nsearch to find the music youre looking for, preview it, and buy it with \r\njust a tap.&lt;/p&gt;&lt;p&gt;\r\n\r\n &lt;strong&gt;Surf the web with Wi-Fi.&lt;/strong&gt;&lt;br&gt;\r\n Browse the web using Safari and watch YouTube videos on the first iPod with Wi-Fi built in&lt;br&gt;&lt;/p&gt;', '', '', '', '', ''),
(33, 1, 'Samsung SyncMaster 941BW', '&lt;div&gt;\r\n Imagine the advantages of going big without slowing down. The big 19&amp;quot; 941BW monitor combines wide aspect ratio with fast pixel response time, for bigger images, more room to work and crisp motion. In addition, the exclusive MagicBright 2, MagicColor and MagicTune technologies help deliver the ideal image in every situation, while sleek, narrow bezels and adjustable stands deliver style just the way you want it. With the Samsung 941BW widescreen analog/digital LCD monitor, it&amp;#39;s not hard to imagine.&lt;/div&gt;\r\n', '', '', '', '', ''),
(33, 2, 'Samsung SyncMaster 941BW', '&lt;p&gt;Imagine the advantages of going big without slowing down. The big 19&quot; \r\n941BW monitor combines wide aspect ratio with fast pixel response time, \r\nfor bigger images, more room to work and crisp motion. In addition, the \r\nexclusive MagicBright 2, MagicColor and MagicTune technologies help \r\ndeliver the ideal image in every situation, while sleek, narrow bezels \r\nand adjustable stands deliver style just the way you want it. With the \r\nSamsung 941BW widescreen analog/digital LCD monitor, it\'s not hard to \r\nimagine.&lt;br&gt;&lt;/p&gt;', '', '', '', '', ''),
(34, 1, 'iPod Shuffle', '&lt;div&gt;\r\n &lt;strong&gt;Born to be worn.&lt;/strong&gt;\r\n &lt;p&gt;\r\n  Clip on the worlds most wearable music player and take up to 240 songs with you anywhere. Choose from five colors including four new hues to make your musical fashion statement.&lt;/p&gt;\r\n &lt;p&gt;\r\n  &lt;strong&gt;Random meets rhythm.&lt;/strong&gt;&lt;/p&gt;\r\n &lt;p&gt;\r\n  With iTunes autofill, iPod shuffle can deliver a new musical experience every time you sync. For more randomness, you can shuffle songs during playback with the slide of a switch.&lt;/p&gt;\r\n &lt;strong&gt;Everything is easy.&lt;/strong&gt;\r\n &lt;p&gt;\r\n  Charge and sync with the included USB dock. Operate the iPod shuffle controls with one hand. Enjoy up to 12 hours straight of skip-free music playback.&lt;/p&gt;\r\n&lt;/div&gt;\r\n', '', '', '', '', ''),
(34, 2, 'iPod Shuffle', '&lt;p&gt;&lt;strong&gt;Born to be worn.&lt;/strong&gt;\r\n &lt;/p&gt;&lt;div&gt;&lt;p&gt;\r\n  Clip on the worlds most wearable music player and take up to 240 songs\r\n with you anywhere. Choose from five colors including four new hues to \r\nmake your musical fashion statement.&lt;/p&gt;\r\n &lt;p&gt;\r\n  &lt;strong&gt;Random meets rhythm.&lt;/strong&gt;&lt;/p&gt;\r\n &lt;p&gt;\r\n  With iTunes autofill, iPod shuffle can deliver a new musical \r\nexperience every time you sync. For more randomness, you can shuffle \r\nsongs during playback with the slide of a switch.&lt;/p&gt;\r\n &lt;strong&gt;Everything is easy.&lt;/strong&gt;\r\n &lt;p&gt;\r\n  Charge and sync with the included USB dock. Operate the iPod shuffle \r\ncontrols with one hand. Enjoy up to 12 hours straight of skip-free music\r\n playback.&lt;/p&gt;\r\n&lt;/div&gt;&lt;p&gt;&lt;br&gt;&lt;/p&gt;', '', '', '', '', ''),
(35, 1, 'Product 8', '&lt;p&gt;\r\n Product 8&lt;/p&gt;\r\n', '', '', '', '', ''),
(35, 2, 'Product 8', '&lt;p&gt;Product 8&lt;br&gt;&lt;/p&gt;', '', '', '', '', ''),
(36, 1, 'iPod Nano', '&lt;div&gt;\r\n &lt;p&gt;\r\n  &lt;strong&gt;Video in your pocket.&lt;/strong&gt;&lt;/p&gt;\r\n &lt;p&gt;\r\n  Its the small iPod with one very big idea: video. The worlds most popular music player now lets you enjoy movies, TV shows, and more on a two-inch display thats 65% brighter than before.&lt;/p&gt;\r\n &lt;p&gt;\r\n  &lt;strong&gt;Cover Flow.&lt;/strong&gt;&lt;/p&gt;\r\n &lt;p&gt;\r\n  Browse through your music collection by flipping through album art. Select an album to turn it over and see the track list.&lt;strong&gt;&amp;nbsp;&lt;/strong&gt;&lt;/p&gt;\r\n &lt;p&gt;\r\n  &lt;strong&gt;Enhanced interface.&lt;/strong&gt;&lt;/p&gt;\r\n &lt;p&gt;\r\n  Experience a whole new way to browse and view your music and video.&lt;/p&gt;\r\n &lt;p&gt;\r\n  &lt;strong&gt;Sleek and colorful.&lt;/strong&gt;&lt;/p&gt;\r\n &lt;p&gt;\r\n  With an anodized aluminum and polished stainless steel enclosure and a choice of five colors, iPod nano is dressed to impress.&lt;/p&gt;\r\n &lt;p&gt;\r\n  &lt;strong&gt;iTunes.&lt;/strong&gt;&lt;/p&gt;\r\n &lt;p&gt;\r\n  Available as a free download, iTunes makes it easy to browse and buy millions of songs, movies, TV shows, audiobooks, and games and download free podcasts all at the iTunes Store. And you can import your own music, manage your whole media library, and sync your iPod or iPhone with ease.&lt;/p&gt;\r\n&lt;/div&gt;\r\n', '', '', '', '', ''),
(36, 2, 'iPod Nano', '&lt;p&gt;&lt;br&gt;&lt;/p&gt;&lt;p&gt;\r\n  &lt;strong&gt;Video in your pocket.&lt;/strong&gt;&lt;/p&gt;&lt;p&gt;\r\n &lt;br&gt;&lt;/p&gt;&lt;p&gt;\r\n  Its the small iPod with one very big idea: video. The worlds most \r\npopular music player now lets you enjoy movies, TV shows, and more on a \r\ntwo-inch display thats 65% brighter than before.&lt;/p&gt;&lt;p&gt;\r\n &lt;br&gt;&lt;/p&gt;&lt;p&gt;\r\n  &lt;strong&gt;Cover Flow.&lt;/strong&gt;&lt;/p&gt;&lt;p&gt;\r\n &lt;br&gt;&lt;/p&gt;&lt;p&gt;\r\n  Browse through your music collection by flipping through album art. Select an album to turn it over and see the track list.&lt;strong&gt;&amp;nbsp;&lt;/strong&gt;&lt;/p&gt;&lt;p&gt;\r\n &lt;br&gt;&lt;/p&gt;&lt;p&gt;\r\n  &lt;strong&gt;Enhanced interface.&lt;/strong&gt;&lt;/p&gt;&lt;p&gt;\r\n &lt;br&gt;&lt;/p&gt;&lt;p&gt;\r\n  Experience a whole new way to browse and view your music and video.&lt;/p&gt;&lt;p&gt;\r\n &lt;br&gt;&lt;/p&gt;&lt;p&gt;\r\n  &lt;strong&gt;Sleek and colorful.&lt;/strong&gt;&lt;/p&gt;&lt;p&gt;\r\n &lt;br&gt;&lt;/p&gt;&lt;p&gt;\r\n  With an anodized aluminum and polished stainless steel enclosure and a choice of five colors, iPod nano is dressed to impress.&lt;/p&gt;&lt;p&gt;\r\n &lt;br&gt;&lt;/p&gt;&lt;p&gt;\r\n  &lt;strong&gt;iTunes.&lt;/strong&gt;&lt;/p&gt;&lt;p&gt;&lt;br&gt;&lt;/p&gt;', '', '', '', '', ''),
(40, 1, 'iPhone', '&lt;p class=&quot;intro&quot;&gt;\r\n iPhone is a revolutionary new mobile phone that allows you to make a call by simply tapping a name or number in your address book, a favorites list, or a call log. It also automatically syncs all your contacts from a PC, Mac, or Internet service. And it lets you select and listen to voicemail messages in whatever order you want just like email.&lt;/p&gt;\r\n', '', '', '', '', ''),
(40, 2, 'iPhone', '&lt;p&gt;iPhone is a revolutionary new mobile phone that allows you to make a \r\ncall by simply tapping a name or number in your address book, a \r\nfavorites list, or a call log. It also automatically syncs all your \r\ncontacts from a PC, Mac, or Internet service. And it lets you select and\r\n listen to voicemail messages in whatever order you want just like \r\nemail.&lt;br&gt;&lt;/p&gt;', '', '', '', '', ''),
(41, 1, 'iMac', '&lt;div&gt;\r\n Just when you thought iMac had everything, now there´s even more. More powerful Intel Core 2 Duo processors. And more memory standard. Combine this with Mac OS X Leopard and iLife ´08, and it´s more all-in-one than ever. iMac packs amazing performance into a stunningly slim space.&lt;/div&gt;\r\n', '', '', '', '', ''),
(41, 2, 'iMac', '&lt;p&gt;Just when you thought iMac had everything, now there´s even more. More \r\npowerful Intel Core 2 Duo processors. And more memory standard. Combine \r\nthis with Mac OS X Leopard and iLife ´08, and it´s more all-in-one than \r\never. iMac packs amazing performance into a stunningly slim space.&lt;br&gt;&lt;/p&gt;', '', '', '', '', ''),
(42, 1, 'Apple Cinema 30&quot;', '&lt;p&gt;\r\n &lt;font face=&quot;helvetica,geneva,arial&quot; size=&quot;2&quot;&gt;&lt;font face=&quot;Helvetica&quot; size=&quot;2&quot;&gt;The 30-inch Apple Cinema HD Display delivers an amazing 2560 x 1600 pixel resolution. Designed specifically for the creative professional, this display provides more space for easier access to all the tools and palettes needed to edit, format and composite your work. Combine this display with a Mac Pro, MacBook Pro, or PowerMac G5 and there\'s no limit to what you can achieve. &lt;br&gt;\r\n &lt;br&gt;\r\n &lt;/font&gt;&lt;font face=&quot;Helvetica&quot; size=&quot;2&quot;&gt;The Cinema HD features an active-matrix liquid crystal display that produces flicker-free images that deliver twice the brightness, twice the sharpness and twice the contrast ratio of a typical CRT display. Unlike other flat panels, it\'s designed with a pure digital interface to deliver distortion-free images that never need adjusting. With over 4 million digital pixels, the display is uniquely suited for scientific and technical applications such as visualizing molecular structures or analyzing geological data. &lt;br&gt;\r\n &lt;br&gt;\r\n &lt;/font&gt;&lt;font face=&quot;Helvetica&quot; size=&quot;2&quot;&gt;Offering accurate, brilliant color performance, the Cinema HD delivers up to 16.7 million colors across a wide gamut allowing you to see subtle nuances between colors from soft pastels to rich jewel tones. A wide viewing angle ensures uniform color from edge to edge. Apple\'s ColorSync technology allows you to create custom profiles to maintain consistent color onscreen and in print. The result: You can confidently use this display in all your color-critical applications. &lt;br&gt;\r\n &lt;br&gt;\r\n &lt;/font&gt;&lt;font face=&quot;Helvetica&quot; size=&quot;2&quot;&gt;Housed in a new aluminum design, the display has a very thin bezel that enhances visual accuracy. Each display features two FireWire 400 ports and two USB 2.0 ports, making attachment of desktop peripherals, such as iSight, iPod, digital and still cameras, hard drives, printers and scanners, even more accessible and convenient. Taking advantage of the much thinner and lighter footprint of an LCD, the new displays support the VESA (Video Electronics Standards Association) mounting interface standard. Customers with the optional Cinema Display VESA Mount Adapter kit gain the flexibility to mount their display in locations most appropriate for their work environment. &lt;br&gt;\r\n &lt;br&gt;\r\n &lt;/font&gt;&lt;font face=&quot;Helvetica&quot; size=&quot;2&quot;&gt;The Cinema HD features a single cable design with elegant breakout for the USB 2.0, FireWire 400 and a pure digital connection using the industry standard Digital Video Interface (DVI) interface. The DVI connection allows for a direct pure-digital connection.&lt;br&gt;\r\n &lt;/font&gt;&lt;/font&gt;&lt;/p&gt;\r\n&lt;h3&gt;\r\n Features:&lt;/h3&gt;\r\n&lt;p&gt;\r\n Unrivaled display performance&lt;/p&gt;\r\n&lt;ul&gt;\r\n &lt;li&gt;\r\n  30-inch (viewable) active-matrix liquid crystal display provides breathtaking image quality and vivid, richly saturated color.&lt;/li&gt;\r\n &lt;li&gt;\r\n  Support for 2560-by-1600 pixel resolution for display of high definition still and video imagery.&lt;/li&gt;\r\n &lt;li&gt;\r\n  Wide-format design for simultaneous display of two full pages of text and graphics.&lt;/li&gt;\r\n &lt;li&gt;\r\n  Industry standard DVI connector for direct attachment to Mac- and Windows-based desktops and notebooks&lt;/li&gt;\r\n &lt;li&gt;\r\n  Incredibly wide (170 degree) horizontal and vertical viewing angle for maximum visibility and color performance.&lt;/li&gt;\r\n &lt;li&gt;\r\n  Lightning-fast pixel response for full-motion digital video playback.&lt;/li&gt;\r\n &lt;li&gt;\r\n  Support for 16.7 million saturated colors, for use in all graphics-intensive applications.&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;p&gt;\r\n Simple setup and operation&lt;/p&gt;\r\n&lt;ul&gt;\r\n &lt;li&gt;\r\n  Single cable with elegant breakout for connection to DVI, USB and FireWire ports&lt;/li&gt;\r\n &lt;li&gt;\r\n  Built-in two-port USB 2.0 hub for easy connection of desktop peripheral devices.&lt;/li&gt;\r\n &lt;li&gt;\r\n  Two FireWire 400 ports to support iSight and other desktop peripherals&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;p&gt;\r\n Sleek, elegant design&lt;/p&gt;\r\n&lt;ul&gt;\r\n &lt;li&gt;\r\n  Huge virtual workspace, very small footprint.&lt;/li&gt;\r\n &lt;li&gt;\r\n  Narrow Bezel design to minimize visual impact of using dual displays&lt;/li&gt;\r\n &lt;li&gt;\r\n  Unique hinge design for effortless adjustment&lt;/li&gt;\r\n &lt;li&gt;\r\n  Support for VESA mounting solutions (Apple Cinema Display VESA Mount Adapter sold separately)&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;h3&gt;\r\n Technical specifications&lt;/h3&gt;\r\n&lt;p&gt;\r\n &lt;b&gt;Screen size (diagonal viewable image size)&lt;/b&gt;&lt;/p&gt;\r\n&lt;ul&gt;\r\n &lt;li&gt;\r\n  Apple Cinema HD Display: 30 inches (29.7-inch viewable)&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;p&gt;\r\n &lt;b&gt;Screen type&lt;/b&gt;&lt;/p&gt;\r\n&lt;ul&gt;\r\n &lt;li&gt;\r\n  Thin film transistor (TFT) active-matrix liquid crystal display (AMLCD)&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;p&gt;\r\n &lt;b&gt;Resolutions&lt;/b&gt;&lt;/p&gt;\r\n&lt;ul&gt;\r\n &lt;li&gt;\r\n  2560 x 1600 pixels (optimum resolution)&lt;/li&gt;\r\n &lt;li&gt;\r\n  2048 x 1280&lt;/li&gt;\r\n &lt;li&gt;\r\n  1920 x 1200&lt;/li&gt;\r\n &lt;li&gt;\r\n  1280 x 800&lt;/li&gt;\r\n &lt;li&gt;\r\n  1024 x 640&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;p&gt;\r\n &lt;b&gt;Display colors (maximum)&lt;/b&gt;&lt;/p&gt;\r\n&lt;ul&gt;\r\n &lt;li&gt;\r\n  16.7 million&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;p&gt;\r\n &lt;b&gt;Viewing angle (typical)&lt;/b&gt;&lt;/p&gt;\r\n&lt;ul&gt;\r\n &lt;li&gt;\r\n  170° horizontal; 170° vertical&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;p&gt;\r\n &lt;b&gt;Brightness (typical)&lt;/b&gt;&lt;/p&gt;\r\n&lt;ul&gt;\r\n &lt;li&gt;\r\n  30-inch Cinema HD Display: 400 cd/m2&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;p&gt;\r\n &lt;b&gt;Contrast ratio (typical)&lt;/b&gt;&lt;/p&gt;\r\n&lt;ul&gt;\r\n &lt;li&gt;\r\n  700:1&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;p&gt;\r\n &lt;b&gt;Response time (typical)&lt;/b&gt;&lt;/p&gt;\r\n&lt;ul&gt;\r\n &lt;li&gt;\r\n  16 ms&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;p&gt;\r\n &lt;b&gt;Pixel pitch&lt;/b&gt;&lt;/p&gt;\r\n&lt;ul&gt;\r\n &lt;li&gt;\r\n  30-inch Cinema HD Display: 0.250 mm&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;p&gt;\r\n &lt;b&gt;Screen treatment&lt;/b&gt;&lt;/p&gt;\r\n&lt;ul&gt;\r\n &lt;li&gt;\r\n  Antiglare hardcoat&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;p&gt;\r\n &lt;b&gt;User controls (hardware and software)&lt;/b&gt;&lt;/p&gt;\r\n&lt;ul&gt;\r\n &lt;li&gt;\r\n  Display Power,&lt;/li&gt;\r\n &lt;li&gt;\r\n  System sleep, wake&lt;/li&gt;\r\n &lt;li&gt;\r\n  Brightness&lt;/li&gt;\r\n &lt;li&gt;\r\n  Monitor tilt&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;p&gt;\r\n &lt;b&gt;Connectors and cables&lt;/b&gt;&lt;br&gt;\r\n Cable&lt;/p&gt;\r\n&lt;ul&gt;\r\n &lt;li&gt;\r\n  DVI (Digital Visual Interface)&lt;/li&gt;\r\n &lt;li&gt;\r\n  FireWire 400&lt;/li&gt;\r\n &lt;li&gt;\r\n  USB 2.0&lt;/li&gt;\r\n &lt;li&gt;\r\n  DC power (24 V)&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;p&gt;\r\n Connectors&lt;/p&gt;\r\n&lt;ul&gt;\r\n &lt;li&gt;\r\n  Two-port, self-powered USB 2.0 hub&lt;/li&gt;\r\n &lt;li&gt;\r\n  Two FireWire 400 ports&lt;/li&gt;\r\n &lt;li&gt;\r\n  Kensington security port&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;p&gt;\r\n &lt;b&gt;VESA mount adapter&lt;/b&gt;&lt;br&gt;\r\n Requires optional Cinema Display VESA Mount Adapter (M9649G/A)&lt;/p&gt;\r\n&lt;ul&gt;\r\n &lt;li&gt;\r\n  Compatible with VESA FDMI (MIS-D, 100, C) compliant mounting solutions&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;p&gt;\r\n &lt;b&gt;Electrical requirements&lt;/b&gt;&lt;/p&gt;\r\n&lt;ul&gt;\r\n &lt;li&gt;\r\n  Input voltage: 100-240 VAC 50-60Hz&lt;/li&gt;\r\n &lt;li&gt;\r\n  Maximum power when operating: 150W&lt;/li&gt;\r\n &lt;li&gt;\r\n  Energy saver mode: 3W or less&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;p&gt;\r\n &lt;b&gt;Environmental requirements&lt;/b&gt;&lt;/p&gt;\r\n&lt;ul&gt;\r\n &lt;li&gt;\r\n  Operating temperature: 50° to 95° F (10° to 35° C)&lt;/li&gt;\r\n &lt;li&gt;\r\n  Storage temperature: -40° to 116° F (-40° to 47° C)&lt;/li&gt;\r\n &lt;li&gt;\r\n  Operating humidity: 20% to 80% noncondensing&lt;/li&gt;\r\n &lt;li&gt;\r\n  Maximum operating altitude: 10,000 feet&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;p&gt;\r\n &lt;b&gt;Agency approvals&lt;/b&gt;&lt;/p&gt;\r\n&lt;ul&gt;\r\n &lt;li&gt;\r\n  FCC Part 15 Class B&lt;/li&gt;\r\n &lt;li&gt;\r\n  EN55022 Class B&lt;/li&gt;\r\n &lt;li&gt;\r\n  EN55024&lt;/li&gt;\r\n &lt;li&gt;\r\n  VCCI Class B&lt;/li&gt;\r\n &lt;li&gt;\r\n  AS/NZS 3548 Class B&lt;/li&gt;\r\n &lt;li&gt;\r\n  CNS 13438 Class B&lt;/li&gt;\r\n &lt;li&gt;\r\n  ICES-003 Class B&lt;/li&gt;\r\n &lt;li&gt;\r\n  ISO 13406 part 2&lt;/li&gt;\r\n &lt;li&gt;\r\n  MPR II&lt;/li&gt;\r\n &lt;li&gt;\r\n  IEC 60950&lt;/li&gt;\r\n &lt;li&gt;\r\n  UL 60950&lt;/li&gt;\r\n &lt;li&gt;\r\n  CSA 60950&lt;/li&gt;\r\n &lt;li&gt;\r\n  EN60950&lt;/li&gt;\r\n &lt;li&gt;\r\n  ENERGY STAR&lt;/li&gt;\r\n &lt;li&gt;\r\n  TCO \'03&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;p&gt;\r\n &lt;b&gt;Size and weight&lt;/b&gt;&lt;br&gt;\r\n 30-inch Apple Cinema HD Display&lt;/p&gt;\r\n&lt;ul&gt;\r\n &lt;li&gt;\r\n  Height: 21.3 inches (54.3 cm)&lt;/li&gt;\r\n &lt;li&gt;\r\n  Width: 27.2 inches (68.8 cm)&lt;/li&gt;\r\n &lt;li&gt;\r\n  Depth: 8.46 inches (21.5 cm)&lt;/li&gt;\r\n &lt;li&gt;\r\n  Weight: 27.5 pounds (12.5 kg)&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;p&gt;\r\n &lt;b&gt;System Requirements&lt;/b&gt;&lt;/p&gt;\r\n&lt;ul&gt;\r\n &lt;li&gt;\r\n  Mac Pro, all graphic options&lt;/li&gt;\r\n &lt;li&gt;\r\n  MacBook Pro&lt;/li&gt;\r\n &lt;li&gt;\r\n  Power Mac G5 (PCI-X) with ATI Radeon 9650 or better or NVIDIA GeForce 6800 GT DDL or better&lt;/li&gt;\r\n &lt;li&gt;\r\n  Power Mac G5 (PCI Express), all graphics options&lt;/li&gt;\r\n &lt;li&gt;\r\n  PowerBook G4 with dual-link DVI support&lt;/li&gt;\r\n &lt;li&gt;\r\n  Windows PC and graphics card that supports DVI ports with dual-link digital bandwidth and VESA DDC standard for plug-and-play setup&lt;/li&gt;\r\n&lt;/ul&gt;\r\n', '', '', '', '', ''),
(42, 2, 'Apple Cinema 30&quot;', '&lt;p&gt;\r\n &lt;font face=&quot;helvetica,geneva,arial&quot; size=&quot;2&quot;&gt;&lt;font face=&quot;Helvetica&quot; size=&quot;2&quot;&gt;The 30-inch Apple Cinema HD Display delivers an amazing 2560 x 1600 pixel resolution. Designed specifically for the creative professional, this display provides more space for easier access to all the tools and palettes needed to edit, format and composite your work. Combine this display with a Mac Pro, MacBook Pro, or PowerMac G5 and there\'s no limit to what you can achieve. &lt;br&gt;\r\n &lt;br&gt;\r\n &lt;/font&gt;&lt;font face=&quot;Helvetica&quot; size=&quot;2&quot;&gt;The Cinema HD features an active-matrix liquid crystal display that produces flicker-free images that deliver twice the brightness, twice the sharpness and twice the contrast ratio of a typical CRT display. Unlike other flat panels, it\'s designed with a pure digital interface to deliver distortion-free images that never need adjusting. With over 4 million digital pixels, the display is uniquely suited for scientific and technical applications such as visualizing molecular structures or analyzing geological data. &lt;br&gt;\r\n &lt;br&gt;\r\n &lt;/font&gt;&lt;font face=&quot;Helvetica&quot; size=&quot;2&quot;&gt;Offering accurate, brilliant color performance, the Cinema HD delivers up to 16.7 million colors across a wide gamut allowing you to see subtle nuances between colors from soft pastels to rich jewel tones. A wide viewing angle ensures uniform color from edge to edge. Apple\'s ColorSync technology allows you to create custom profiles to maintain consistent color onscreen and in print. The result: You can confidently use this display in all your color-critical applications. &lt;br&gt;\r\n &lt;br&gt;\r\n &lt;/font&gt;&lt;font face=&quot;Helvetica&quot; size=&quot;2&quot;&gt;Housed in a new aluminum design, the display has a very thin bezel that enhances visual accuracy. Each display features two FireWire 400 ports and two USB 2.0 ports, making attachment of desktop peripherals, such as iSight, iPod, digital and still cameras, hard drives, printers and scanners, even more accessible and convenient. Taking advantage of the much thinner and lighter footprint of an LCD, the new displays support the VESA (Video Electronics Standards Association) mounting interface standard. Customers with the optional Cinema Display VESA Mount Adapter kit gain the flexibility to mount their display in locations most appropriate for their work environment. &lt;br&gt;\r\n &lt;br&gt;\r\n &lt;/font&gt;&lt;font face=&quot;Helvetica&quot; size=&quot;2&quot;&gt;The Cinema HD features a single cable design with elegant breakout for the USB 2.0, FireWire 400 and a pure digital connection using the industry standard Digital Video Interface (DVI) interface. The DVI connection allows for a direct pure-digital connection.&lt;br&gt;\r\n &lt;/font&gt;&lt;/font&gt;&lt;/p&gt;\r\n&lt;h3&gt;\r\n Features:&lt;/h3&gt;\r\n&lt;p&gt;\r\n Unrivaled display performance&lt;/p&gt;\r\n&lt;ul&gt;\r\n &lt;li&gt;\r\n  30-inch (viewable) active-matrix liquid crystal display provides breathtaking image quality and vivid, richly saturated color.&lt;/li&gt;\r\n &lt;li&gt;\r\n  Support for 2560-by-1600 pixel resolution for display of high definition still and video imagery.&lt;/li&gt;\r\n &lt;li&gt;\r\n  Wide-format design for simultaneous display of two full pages of text and graphics.&lt;/li&gt;\r\n &lt;li&gt;\r\n  Industry standard DVI connector for direct attachment to Mac- and Windows-based desktops and notebooks&lt;/li&gt;\r\n &lt;li&gt;\r\n  Incredibly wide (170 degree) horizontal and vertical viewing angle for maximum visibility and color performance.&lt;/li&gt;\r\n &lt;li&gt;\r\n  Lightning-fast pixel response for full-motion digital video playback.&lt;/li&gt;\r\n &lt;li&gt;\r\n  Support for 16.7 million saturated colors, for use in all graphics-intensive applications.&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;p&gt;\r\n Simple setup and operation&lt;/p&gt;\r\n&lt;ul&gt;\r\n &lt;li&gt;\r\n  Single cable with elegant breakout for connection to DVI, USB and FireWire ports&lt;/li&gt;\r\n &lt;li&gt;\r\n  Built-in two-port USB 2.0 hub for easy connection of desktop peripheral devices.&lt;/li&gt;\r\n &lt;li&gt;\r\n  Two FireWire 400 ports to support iSight and other desktop peripherals&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;p&gt;\r\n Sleek, elegant design&lt;/p&gt;\r\n&lt;ul&gt;\r\n &lt;li&gt;\r\n  Huge virtual workspace, very small footprint.&lt;/li&gt;\r\n &lt;li&gt;\r\n  Narrow Bezel design to minimize visual impact of using dual displays&lt;/li&gt;\r\n &lt;li&gt;\r\n  Unique hinge design for effortless adjustment&lt;/li&gt;\r\n &lt;li&gt;\r\n  Support for VESA mounting solutions (Apple Cinema Display VESA Mount Adapter sold separately)&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;h3&gt;\r\n Technical specifications&lt;/h3&gt;\r\n&lt;p&gt;\r\n &lt;b&gt;Screen size (diagonal viewable image size)&lt;/b&gt;&lt;/p&gt;\r\n&lt;ul&gt;\r\n &lt;li&gt;\r\n  Apple Cinema HD Display: 30 inches (29.7-inch viewable)&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;p&gt;\r\n &lt;b&gt;Screen type&lt;/b&gt;&lt;/p&gt;\r\n&lt;ul&gt;\r\n &lt;li&gt;\r\n  Thin film transistor (TFT) active-matrix liquid crystal display (AMLCD)&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;p&gt;\r\n &lt;b&gt;Resolutions&lt;/b&gt;&lt;/p&gt;\r\n&lt;ul&gt;\r\n &lt;li&gt;\r\n  2560 x 1600 pixels (optimum resolution)&lt;/li&gt;\r\n &lt;li&gt;\r\n  2048 x 1280&lt;/li&gt;\r\n &lt;li&gt;\r\n  1920 x 1200&lt;/li&gt;\r\n &lt;li&gt;\r\n  1280 x 800&lt;/li&gt;\r\n &lt;li&gt;\r\n  1024 x 640&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;p&gt;\r\n &lt;b&gt;Display colors (maximum)&lt;/b&gt;&lt;/p&gt;\r\n&lt;ul&gt;\r\n &lt;li&gt;\r\n  16.7 million&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;p&gt;\r\n &lt;b&gt;Viewing angle (typical)&lt;/b&gt;&lt;/p&gt;\r\n&lt;ul&gt;\r\n &lt;li&gt;\r\n  170° horizontal; 170° vertical&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;p&gt;\r\n &lt;b&gt;Brightness (typical)&lt;/b&gt;&lt;/p&gt;\r\n&lt;ul&gt;\r\n &lt;li&gt;\r\n  30-inch Cinema HD Display: 400 cd/m2&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;p&gt;\r\n &lt;b&gt;Contrast ratio (typical)&lt;/b&gt;&lt;/p&gt;\r\n&lt;ul&gt;\r\n &lt;li&gt;\r\n  700:1&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;p&gt;\r\n &lt;b&gt;Response time (typical)&lt;/b&gt;&lt;/p&gt;\r\n&lt;ul&gt;\r\n &lt;li&gt;\r\n  16 ms&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;p&gt;\r\n &lt;b&gt;Pixel pitch&lt;/b&gt;&lt;/p&gt;\r\n&lt;ul&gt;\r\n &lt;li&gt;\r\n  30-inch Cinema HD Display: 0.250 mm&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;p&gt;\r\n &lt;b&gt;Screen treatment&lt;/b&gt;&lt;/p&gt;\r\n&lt;ul&gt;\r\n &lt;li&gt;\r\n  Antiglare hardcoat&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;p&gt;\r\n &lt;b&gt;User controls (hardware and software)&lt;/b&gt;&lt;/p&gt;\r\n&lt;ul&gt;\r\n &lt;li&gt;\r\n  Display Power,&lt;/li&gt;\r\n &lt;li&gt;\r\n  System sleep, wake&lt;/li&gt;\r\n &lt;li&gt;\r\n  Brightness&lt;/li&gt;\r\n &lt;li&gt;\r\n  Monitor tilt&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;p&gt;\r\n &lt;b&gt;Connectors and cables&lt;/b&gt;&lt;br&gt;\r\n Cable&lt;/p&gt;\r\n&lt;ul&gt;\r\n &lt;li&gt;\r\n  DVI (Digital Visual Interface)&lt;/li&gt;\r\n &lt;li&gt;\r\n  FireWire 400&lt;/li&gt;\r\n &lt;li&gt;\r\n  USB 2.0&lt;/li&gt;\r\n &lt;li&gt;\r\n  DC power (24 V)&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;p&gt;\r\n Connectors&lt;/p&gt;\r\n&lt;ul&gt;\r\n &lt;li&gt;\r\n  Two-port, self-powered USB 2.0 hub&lt;/li&gt;\r\n &lt;li&gt;\r\n  Two FireWire 400 ports&lt;/li&gt;\r\n &lt;li&gt;\r\n  Kensington security port&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;p&gt;\r\n &lt;b&gt;VESA mount adapter&lt;/b&gt;&lt;br&gt;\r\n Requires optional Cinema Display VESA Mount Adapter (M9649G/A)&lt;/p&gt;\r\n&lt;ul&gt;\r\n &lt;li&gt;\r\n  Compatible with VESA FDMI (MIS-D, 100, C) compliant mounting solutions&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;p&gt;\r\n &lt;b&gt;Electrical requirements&lt;/b&gt;&lt;/p&gt;\r\n&lt;ul&gt;\r\n &lt;li&gt;\r\n  Input voltage: 100-240 VAC 50-60Hz&lt;/li&gt;\r\n &lt;li&gt;\r\n  Maximum power when operating: 150W&lt;/li&gt;\r\n &lt;li&gt;\r\n  Energy saver mode: 3W or less&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;p&gt;\r\n &lt;b&gt;Environmental requirements&lt;/b&gt;&lt;/p&gt;\r\n&lt;ul&gt;\r\n &lt;li&gt;\r\n  Operating temperature: 50° to 95° F (10° to 35° C)&lt;/li&gt;\r\n &lt;li&gt;\r\n  Storage temperature: -40° to 116° F (-40° to 47° C)&lt;/li&gt;\r\n &lt;li&gt;\r\n  Operating humidity: 20% to 80% noncondensing&lt;/li&gt;\r\n &lt;li&gt;\r\n  Maximum operating altitude: 10,000 feet&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;p&gt;\r\n &lt;b&gt;Agency approvals&lt;/b&gt;&lt;/p&gt;\r\n&lt;ul&gt;\r\n &lt;li&gt;\r\n  FCC Part 15 Class B&lt;/li&gt;\r\n &lt;li&gt;\r\n  EN55022 Class B&lt;/li&gt;\r\n &lt;li&gt;\r\n  EN55024&lt;/li&gt;\r\n &lt;li&gt;\r\n  VCCI Class B&lt;/li&gt;\r\n &lt;li&gt;\r\n  AS/NZS 3548 Class B&lt;/li&gt;\r\n &lt;li&gt;\r\n  CNS 13438 Class B&lt;/li&gt;\r\n &lt;li&gt;\r\n  ICES-003 Class B&lt;/li&gt;\r\n &lt;li&gt;\r\n  ISO 13406 part 2&lt;/li&gt;\r\n &lt;li&gt;\r\n  MPR II&lt;/li&gt;\r\n &lt;li&gt;\r\n  IEC 60950&lt;/li&gt;\r\n &lt;li&gt;\r\n  UL 60950&lt;/li&gt;\r\n &lt;li&gt;\r\n  CSA 60950&lt;/li&gt;\r\n &lt;li&gt;\r\n  EN60950&lt;/li&gt;\r\n &lt;li&gt;\r\n  ENERGY STAR&lt;/li&gt;\r\n &lt;li&gt;\r\n  TCO \'03&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;p&gt;\r\n &lt;b&gt;Size and weight&lt;/b&gt;&lt;br&gt;\r\n 30-inch Apple Cinema HD Display&lt;/p&gt;\r\n&lt;ul&gt;\r\n &lt;li&gt;\r\n  Height: 21.3 inches (54.3 cm)&lt;/li&gt;\r\n &lt;li&gt;\r\n  Width: 27.2 inches (68.8 cm)&lt;/li&gt;\r\n &lt;li&gt;\r\n  Depth: 8.46 inches (21.5 cm)&lt;/li&gt;\r\n &lt;li&gt;\r\n  Weight: 27.5 pounds (12.5 kg)&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;p&gt;\r\n &lt;b&gt;System Requirements&lt;/b&gt;&lt;/p&gt;\r\n&lt;ul&gt;\r\n &lt;li&gt;\r\n  Mac Pro, all graphic options&lt;/li&gt;\r\n &lt;li&gt;\r\n  MacBook Pro&lt;/li&gt;\r\n &lt;li&gt;\r\n  Power Mac G5 (PCI-X) with ATI Radeon 9650 or better or NVIDIA GeForce 6800 GT DDL or better&lt;/li&gt;\r\n &lt;li&gt;\r\n  Power Mac G5 (PCI Express), all graphics options&lt;/li&gt;\r\n &lt;li&gt;\r\n  PowerBook G4 with dual-link DVI support&lt;/li&gt;\r\n &lt;li&gt;\r\n  Windows PC and graphics card that supports DVI ports with dual-link digital bandwidth and VESA DDC standard for plug-and-play setup&lt;/li&gt;\r\n&lt;/ul&gt;', '', '', '', '', ''),
(43, 1, 'MacBook', '&lt;div&gt;\r\n &lt;p&gt;\r\n  &lt;b&gt;Intel Core 2 Duo processor&lt;/b&gt;&lt;/p&gt;\r\n &lt;p&gt;\r\n  Powered by an Intel Core 2 Duo processor at speeds up to 2.16GHz, the new MacBook is the fastest ever.&lt;/p&gt;\r\n &lt;p&gt;\r\n  &lt;b&gt;1GB memory, larger hard drives&lt;/b&gt;&lt;/p&gt;\r\n &lt;p&gt;\r\n  The new MacBook now comes with 1GB of memory standard and larger hard drives for the entire line perfect for running more of your favorite applications and storing growing media collections.&lt;/p&gt;\r\n &lt;p&gt;\r\n  &lt;b&gt;Sleek, 1.08-inch-thin design&lt;/b&gt;&lt;/p&gt;\r\n &lt;p&gt;\r\n  MacBook makes it easy to hit the road thanks to its tough polycarbonate case, built-in wireless technologies, and innovative MagSafe Power Adapter that releases automatically if someone accidentally trips on the cord.&lt;/p&gt;\r\n &lt;p&gt;\r\n  &lt;b&gt;Built-in iSight camera&lt;/b&gt;&lt;/p&gt;\r\n &lt;p&gt;\r\n  Right out of the box, you can have a video chat with friends or family,2 record a video at your desk, or take fun pictures with Photo Booth&lt;/p&gt;\r\n&lt;/div&gt;\r\n', '', '', '', '', ''),
(43, 2, 'MacBook', '&lt;p&gt;&lt;b&gt;Intel Core 2 Duo processor&lt;/b&gt;\r\n &lt;/p&gt;&lt;div&gt;&lt;p&gt;\r\n  Powered by an Intel Core 2 Duo processor at speeds up to 2.16GHz, the new MacBook is the fastest ever.&lt;/p&gt;\r\n &lt;p&gt;\r\n  &lt;b&gt;1GB memory, larger hard drives&lt;/b&gt;&lt;/p&gt;\r\n &lt;p&gt;\r\n  The new MacBook now comes with 1GB of memory standard and larger hard \r\ndrives for the entire line perfect for running more of your favorite \r\napplications and storing growing media collections.&lt;/p&gt;\r\n &lt;p&gt;\r\n  &lt;b&gt;Sleek, 1.08-inch-thin design&lt;/b&gt;&lt;/p&gt;\r\n &lt;p&gt;\r\n  MacBook makes it easy to hit the road thanks to its tough \r\npolycarbonate case, built-in wireless technologies, and innovative \r\nMagSafe Power Adapter that releases automatically if someone \r\naccidentally trips on the cord.&lt;/p&gt;\r\n &lt;p&gt;\r\n  &lt;b&gt;Built-in iSight camera&lt;/b&gt;&lt;/p&gt;\r\n &lt;p&gt;\r\n  Right out of the box, you can have a video chat with friends or \r\nfamily,2 record a video at your desk, or take fun pictures with Photo \r\nBooth&lt;/p&gt;\r\n&lt;/div&gt;&lt;p&gt;&lt;br&gt;&lt;/p&gt;', '', '', '', '', ''),
(44, 1, 'MacBook Air', '&lt;div&gt;\r\n MacBook Air is ultrathin, ultraportable, and ultra unlike anything else. But you don&amp;rsquo;t lose inches and pounds overnight. It&amp;rsquo;s the result of rethinking conventions. Of multiple wireless innovations. And of breakthrough design. With MacBook Air, mobile computing suddenly has a new standard.&lt;/div&gt;\r\n', '', '', '', '', ''),
(44, 2, 'MacBook Air', '&lt;p&gt;MacBook Air is ultrathin, ultraportable, and ultra unlike anything else.\r\n But you don’t lose inches and pounds overnight. It’s the result of \r\nrethinking conventions. Of multiple wireless innovations. And of \r\nbreakthrough design. With MacBook Air, mobile computing suddenly has a \r\nnew standard.&lt;br&gt;&lt;/p&gt;', '', '', '', '', ''),
(45, 1, 'MacBook Pro', '&lt;div class=&quot;cpt_product_description &quot;&gt;\r\n &lt;div&gt;\r\n  &lt;p&gt;\r\n   &lt;b&gt;Latest Intel mobile architecture&lt;/b&gt;&lt;/p&gt;\r\n  &lt;p&gt;\r\n   Powered by the most advanced mobile processors from Intel, the new Core 2 Duo MacBook Pro is over 50% faster than the original Core Duo MacBook Pro and now supports up to 4GB of RAM.&lt;/p&gt;\r\n  &lt;p&gt;\r\n   &lt;b&gt;Leading-edge graphics&lt;/b&gt;&lt;/p&gt;\r\n  &lt;p&gt;\r\n   The NVIDIA GeForce 8600M GT delivers exceptional graphics processing power. For the ultimate creative canvas, you can even configure the 17-inch model with a 1920-by-1200 resolution display.&lt;/p&gt;\r\n  &lt;p&gt;\r\n   &lt;b&gt;Designed for life on the road&lt;/b&gt;&lt;/p&gt;\r\n  &lt;p&gt;\r\n   Innovations such as a magnetic power connection and an illuminated keyboard with ambient light sensor put the MacBook Pro in a class by itself.&lt;/p&gt;\r\n  &lt;p&gt;\r\n   &lt;b&gt;Connect. Create. Communicate.&lt;/b&gt;&lt;/p&gt;\r\n  &lt;p&gt;\r\n   Quickly set up a video conference with the built-in iSight camera. Control presentations and media from up to 30 feet away with the included Apple Remote. Connect to high-bandwidth peripherals with FireWire 800 and DVI.&lt;/p&gt;\r\n  &lt;p&gt;\r\n   &lt;b&gt;Next-generation wireless&lt;/b&gt;&lt;/p&gt;\r\n  &lt;p&gt;\r\n   Featuring 802.11n wireless technology, the MacBook Pro delivers up to five times the performance and up to twice the range of previous-generation technologies.&lt;/p&gt;\r\n &lt;/div&gt;\r\n&lt;/div&gt;\r\n&lt;!-- cpt_container_end --&gt;', '', '', '', '', ''),
(45, 2, 'MacBook Pro', '&lt;p&gt;&lt;br&gt;&lt;/p&gt;&lt;div class=&quot;cpt_product_description &quot;&gt;\r\n &lt;div&gt;\r\n  &lt;p&gt;\r\n   &lt;b&gt;Latest Intel mobile architecture&lt;/b&gt;&lt;/p&gt;\r\n  &lt;p&gt;\r\n   Powered by the most advanced mobile processors from Intel, the new \r\nCore 2 Duo MacBook Pro is over 50% faster than the original Core Duo \r\nMacBook Pro and now supports up to 4GB of RAM.&lt;/p&gt;\r\n  &lt;p&gt;\r\n   &lt;b&gt;Leading-edge graphics&lt;/b&gt;&lt;/p&gt;\r\n  &lt;p&gt;\r\n   The NVIDIA GeForce 8600M GT delivers exceptional graphics processing \r\npower. For the ultimate creative canvas, you can even configure the \r\n17-inch model with a 1920-by-1200 resolution display.&lt;/p&gt;\r\n  &lt;p&gt;\r\n   &lt;b&gt;Designed for life on the road&lt;/b&gt;&lt;/p&gt;\r\n  &lt;p&gt;\r\n   Innovations such as a magnetic power connection and an illuminated \r\nkeyboard with ambient light sensor put the MacBook Pro in a class by \r\nitself.&lt;/p&gt;\r\n  &lt;p&gt;\r\n   &lt;b&gt;Connect. Create. Communicate.&lt;/b&gt;&lt;/p&gt;\r\n  &lt;p&gt;\r\n   Quickly set up a video conference with the built-in iSight camera. \r\nControl presentations and media from up to 30 feet away with the \r\nincluded Apple Remote. Connect to high-bandwidth peripherals with \r\nFireWire 800 and DVI.&lt;/p&gt;\r\n  &lt;p&gt;\r\n   &lt;b&gt;Next-generation wireless&lt;/b&gt;&lt;/p&gt;\r\n  &lt;p&gt;\r\n   Featuring 802.11n wireless technology, the MacBook Pro delivers up to\r\n five times the performance and up to twice the range of \r\nprevious-generation technologies.&lt;/p&gt;\r\n &lt;/div&gt;\r\n&lt;/div&gt;&lt;p&gt;&lt;br&gt;&lt;/p&gt;', '', '', '', '', ''),
(46, 1, 'Sony VAIO', '&lt;div&gt;\r\n Unprecedented power. The next generation of processing technology has arrived. Built into the newest VAIO notebooks lies Intel&amp;#39;s latest, most powerful innovation yet: Intel&amp;reg; Centrino&amp;reg; 2 processor technology. Boasting incredible speed, expanded wireless connectivity, enhanced multimedia support and greater energy efficiency, all the high-performance essentials are seamlessly combined into a single chip.&lt;/div&gt;\r\n', '', '', '', '', ''),
(46, 2, 'Sony VAIO', '&lt;p&gt;Unprecedented power. The next generation of processing technology has \r\narrived. Built into the newest VAIO notebooks lies Intel\'s latest, most \r\npowerful innovation yet: Intel® Centrino® 2 processor technology. \r\nBoasting incredible speed, expanded wireless connectivity, enhanced \r\nmultimedia support and greater energy efficiency, all the \r\nhigh-performance essentials are seamlessly combined into a single chip.&lt;br&gt;&lt;/p&gt;', '', '', '', '', ''),
(47, 1, 'HP LP3065', '&lt;p&gt;\r\n Stop your co-workers in their tracks with the stunning new 30-inch diagonal HP LP3065 Flat Panel Monitor. This flagship monitor features best-in-class performance and presentation features on a huge wide-aspect screen while letting you work as comfortably as possible - you might even forget you&amp;#39;re at the office&lt;/p&gt;\r\n', '', '', '', '', ''),
(47, 2, 'HP LP3065', '&lt;p&gt;Stop your co-workers in their tracks with the stunning new 30-inch \r\ndiagonal HP LP3065 Flat Panel Monitor. This flagship monitor features \r\nbest-in-class performance and presentation features on a huge \r\nwide-aspect screen while letting you work as comfortably as possible - \r\nyou might even forget you\'re at the office&lt;br&gt;&lt;/p&gt;', '', '', '', '', ''),
(48, 1, 'iPod Classic', '&lt;div class=&quot;cpt_product_description &quot;&gt;\r\n &lt;div&gt;\r\n  &lt;p&gt;\r\n   &lt;strong&gt;More room to move.&lt;/strong&gt;&lt;/p&gt;\r\n  &lt;p&gt;\r\n   With 80GB or 160GB of storage and up to 40 hours of battery life, the new iPod classic lets you enjoy up to 40,000 songs or up to 200 hours of video or any combination wherever you go.&lt;/p&gt;\r\n  &lt;p&gt;\r\n   &lt;strong&gt;Cover Flow.&lt;/strong&gt;&lt;/p&gt;\r\n  &lt;p&gt;\r\n   Browse through your music collection by flipping through album art. Select an album to turn it over and see the track list.&lt;/p&gt;\r\n  &lt;p&gt;\r\n   &lt;strong&gt;Enhanced interface.&lt;/strong&gt;&lt;/p&gt;\r\n  &lt;p&gt;\r\n   Experience a whole new way to browse and view your music and video.&lt;/p&gt;\r\n  &lt;p&gt;\r\n   &lt;strong&gt;Sleeker design.&lt;/strong&gt;&lt;/p&gt;\r\n  &lt;p&gt;\r\n   Beautiful, durable, and sleeker than ever, iPod classic now features an anodized aluminum and polished stainless steel enclosure with rounded edges.&lt;/p&gt;\r\n &lt;/div&gt;\r\n&lt;/div&gt;\r\n&lt;!-- cpt_container_end --&gt;', '', '', '', '', ''),
(48, 2, 'iPod Classic', '&lt;p&gt;&lt;strong&gt;More room to move.&lt;/strong&gt;\r\n  &lt;/p&gt;&lt;div class=&quot;cpt_product_description &quot;&gt;&lt;div&gt;&lt;p&gt;\r\n   With 80GB or 160GB of storage and up to 40 hours of battery life, the\r\n new iPod classic lets you enjoy up to 40,000 songs or up to 200 hours \r\nof video or any combination wherever you go.&lt;/p&gt;\r\n  &lt;p&gt;\r\n   &lt;strong&gt;Cover Flow.&lt;/strong&gt;&lt;/p&gt;\r\n  &lt;p&gt;\r\n   Browse through your music collection by flipping through album art. Select an album to turn it over and see the track list.&lt;/p&gt;\r\n  &lt;p&gt;\r\n   &lt;strong&gt;Enhanced interface.&lt;/strong&gt;&lt;/p&gt;\r\n  &lt;p&gt;\r\n   Experience a whole new way to browse and view your music and video.&lt;/p&gt;\r\n  &lt;p&gt;\r\n   &lt;strong&gt;Sleeker design.&lt;/strong&gt;&lt;/p&gt;\r\n  &lt;p&gt;\r\n   Beautiful, durable, and sleeker than ever, iPod classic now features \r\nan anodized aluminum and polished stainless steel enclosure with rounded\r\n edges.&lt;/p&gt;\r\n &lt;/div&gt;\r\n&lt;/div&gt;&lt;p&gt;&lt;br&gt;&lt;/p&gt;', '', '', '', '', ''),
(49, 1, 'Samsung Galaxy Tab 10.1', '&lt;p&gt;\r\n Samsung Galaxy Tab 10.1, is the world&amp;rsquo;s thinnest tablet, measuring 8.6 mm thickness, running with Android 3.0 Honeycomb OS on a 1GHz dual-core Tegra 2 processor, similar to its younger brother Samsung Galaxy Tab 8.9.&lt;/p&gt;\r\n&lt;p&gt;\r\n Samsung Galaxy Tab 10.1 gives pure Android 3.0 experience, adding its new TouchWiz UX or TouchWiz 4.0 &amp;ndash; includes a live panel, which lets you to customize with different content, such as your pictures, bookmarks, and social feeds, sporting a 10.1 inches WXGA capacitive touch screen with 1280 x 800 pixels of resolution, equipped with 3 megapixel rear camera with LED flash and a 2 megapixel front camera, HSPA+ connectivity up to 21Mbps, 720p HD video recording capability, 1080p HD playback, DLNA support, Bluetooth 2.1, USB 2.0, gyroscope, Wi-Fi 802.11 a/b/g/n, micro-SD slot, 3.5mm headphone jack, and SIM slot, including the Samsung Stick &amp;ndash; a Bluetooth microphone that can be carried in a pocket like a pen and sound dock with powered subwoofer.&lt;/p&gt;\r\n&lt;p&gt;\r\n Samsung Galaxy Tab 10.1 will come in 16GB / 32GB / 64GB verities and pre-loaded with Social Hub, Reader&amp;rsquo;s Hub, Music Hub and Samsung Mini Apps Tray &amp;ndash; which gives you access to more commonly used apps to help ease multitasking and it is capable of Adobe Flash Player 10.2, powered by 6860mAh battery that gives you 10hours of video-playback time.&amp;nbsp;&amp;auml;&amp;ouml;&lt;/p&gt;\r\n', '', '', '', '', ''),
(49, 2, 'Samsung Galaxy Tab 10.1', '&lt;p&gt;Samsung Galaxy Tab 10.1, is the world’s thinnest tablet, measuring 8.6 \r\nmm thickness, running with Android 3.0 Honeycomb OS on a 1GHz dual-core \r\nTegra 2 processor, similar to its younger brother Samsung Galaxy Tab \r\n8.9.&lt;/p&gt;&lt;p&gt;\r\n&lt;br&gt;&lt;/p&gt;&lt;p&gt;\r\n Samsung Galaxy Tab 10.1 gives pure Android 3.0 experience, adding its \r\nnew TouchWiz UX or TouchWiz 4.0 – includes a live panel, which lets you \r\nto customize with different content, such as your pictures, bookmarks, \r\nand social feeds, sporting a 10.1 inches WXGA capacitive touch screen \r\nwith 1280 x 800 pixels of resolution, equipped with 3 megapixel rear \r\ncamera with LED flash and a 2 megapixel front camera, HSPA+ connectivity\r\n up to 21Mbps, 720p HD video recording capability, 1080p HD playback, \r\nDLNA support, Bluetooth 2.1, USB 2.0, gyroscope, Wi-Fi 802.11 a/b/g/n, \r\nmicro-SD slot, 3.5mm headphone jack, and SIM slot, including the Samsung\r\n Stick – a Bluetooth microphone that can be carried in a pocket like a \r\npen and sound dock with powered subwoofer.&lt;/p&gt;&lt;p&gt;\r\n&lt;br&gt;&lt;/p&gt;&lt;p&gt;\r\n Samsung Galaxy Tab 10.1 will come in 16GB / 32GB / 64GB verities and \r\npre-loaded with Social Hub, Reader’s Hub, Music Hub and Samsung Mini \r\nApps Tray – which gives you access to more commonly used apps to help \r\nease multitasking and it is capable of Adobe Flash Player 10.2, powered \r\nby 6860mAh battery that gives you 10hours of video-playback time.&amp;nbsp;äö&lt;/p&gt;&lt;p&gt;&lt;br&gt;&lt;/p&gt;', '', '', '', '', '');

----------------------------------------------------------

--
-- Table structure for table `oc_product_discount`
--

DROP TABLE IF EXISTS `oc_product_discount`;
CREATE TABLE `oc_product_discount` (
  `product_discount_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `customer_group_id` int(11) NOT NULL,
  `quantity` int(4) NOT NULL DEFAULT '0',
  `priority` int(5) NOT NULL DEFAULT '1',
  `price` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `date_start` date NOT NULL DEFAULT '0000-00-00',
  `date_end` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`product_discount_id`),
  KEY `product_id` (`product_id`),
  KEY `customer_group_id` (`customer_group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_product_discount`
--

INSERT INTO `oc_product_discount` (`product_discount_id`, `product_id`, `customer_group_id`, `quantity`, `priority`, `price`, `date_start`, `date_end`) VALUES
(438, 42, 1, 10, 1, '88.0000', '0000-00-00', '0000-00-00'),
(439, 42, 1, 20, 1, '77.0000', '0000-00-00', '0000-00-00'),
(440, 42, 1, 30, 1, '66.0000', '0000-00-00', '0000-00-00');

----------------------------------------------------------

--
-- Table structure for table `oc_product_filter`
--

DROP TABLE IF EXISTS `oc_product_filter`;
CREATE TABLE `oc_product_filter` (
  `product_id` int(11) NOT NULL,
  `filter_id` int(11) NOT NULL,
  PRIMARY KEY (`product_id`,`filter_id`),
  KEY `product_id` (`product_id`),
  KEY `filter_id` (`filter_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_product_filter`
--


----------------------------------------------------------

--
-- Table structure for table `oc_product_image`
--

DROP TABLE IF EXISTS `oc_product_image`;
CREATE TABLE `oc_product_image` (
  `product_image_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `sort_order` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`product_image_id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_product_image`
--

INSERT INTO `oc_product_image` (`product_id`, `image`, `sort_order`) VALUES
(43, 'catalog/demo/macbook_3.jpg', 0),
(43, 'catalog/demo/macbook_2.jpg', 0),
(43, 'catalog/demo/macbook_4.jpg', 0),
(43, 'catalog/demo/macbook_5.jpg', 0),
(44, 'catalog/demo/macbook_air_3.jpg', 0),
(44, 'catalog/demo/macbook_air_2.jpg', 0),
(44, 'catalog/demo/macbook_air_4.jpg', 0),
(45, 'catalog/demo/macbook_pro_4.jpg', 0),
(45, 'catalog/demo/macbook_pro_3.jpg', 0),
(45, 'catalog/demo/macbook_pro_2.jpg', 0),
(40, 'catalog/demo/iphone_4.jpg', 0),
(40, 'catalog/demo/iphone_3.jpg', 0),
(40, 'catalog/demo/iphone_5.jpg', 0),
(40, 'catalog/demo/iphone_2.jpg', 0),
(40, 'catalog/demo/iphone_6.jpg', 0),
(31, 'catalog/demo/nikon_d300_5.jpg', 0),
(31, 'catalog/demo/nikon_d300_4.jpg', 0),
(31, 'catalog/demo/nikon_d300_2.jpg', 0),
(31, 'catalog/demo/nikon_d300_3.jpg', 0),
(29, 'catalog/demo/palm_treo_pro_2.jpg', 0),
(29, 'catalog/demo/palm_treo_pro_3.jpg', 0),
(48, 'catalog/demo/ipod_classic_2.jpg', 0),
(48, 'catalog/demo/ipod_classic_3.jpg', 0),
(48, 'catalog/demo/ipod_classic_4.jpg', 0),
(46, 'catalog/demo/sony_vaio_3.jpg', 0),
(46, 'catalog/demo/sony_vaio_2.jpg', 0),
(46, 'catalog/demo/sony_vaio_4.jpg', 0),
(46, 'catalog/demo/sony_vaio_5.jpg', 0),
(36, 'catalog/demo/ipod_nano_3.jpg', 0),
(36, 'catalog/demo/ipod_nano_2.jpg', 0),
(36, 'catalog/demo/ipod_nano_4.jpg', 0),
(36, 'catalog/demo/ipod_nano_5.jpg', 0),
(34, 'catalog/demo/ipod_shuffle_3.jpg', 0),
(34, 'catalog/demo/ipod_shuffle_2.jpg', 0),
(34, 'catalog/demo/ipod_shuffle_4.jpg', 0),
(34, 'catalog/demo/ipod_shuffle_5.jpg', 0),
(32, 'catalog/demo/ipod_touch_4.jpg', 0),
(32, 'catalog/demo/ipod_touch_3.jpg', 0),
(32, 'catalog/demo/ipod_touch_2.jpg', 0),
(32, 'catalog/demo/ipod_touch_5.jpg', 0),
(32, 'catalog/demo/ipod_touch_6.jpg', 0),
(32, 'catalog/demo/ipod_touch_7.jpg', 0),
(28, 'catalog/demo/htc_touch_hd_3.jpg', 0),
(28, 'catalog/demo/htc_touch_hd_2.jpg', 0),
(42, 'catalog/demo/canon_eos_5d_2.jpg', 0),
(42, 'catalog/demo/canon_eos_5d_1.jpg', 0),
(42, 'catalog/demo/compaq_presario.jpg', 0),
(42, 'catalog/demo/hp_1.jpg', 0),
(42, 'catalog/demo/canon_logo.jpg', 0),
(47, 'catalog/demo/hp_2.jpg', 0),
(47, 'catalog/demo/hp_3.jpg', 0),
(49, 'catalog/demo/samsung_tab_2.jpg', 0),
(49, 'catalog/demo/samsung_tab_3.jpg', 0),
(49, 'catalog/demo/samsung_tab_4.jpg', 0),
(49, 'catalog/demo/samsung_tab_5.jpg', 0),
(49, 'catalog/demo/samsung_tab_6.jpg', 0),
(49, 'catalog/demo/samsung_tab_7.jpg', 0),
(30, 'catalog/demo/canon_eos_5d_3.jpg', 0),
(30, 'catalog/demo/canon_eos_5d_2.jpg', 0),
(41, 'catalog/demo/imac_2.jpg', 0),
(41, 'catalog/demo/imac_3.jpg', 0);

----------------------------------------------------------

--
-- Table structure for table `oc_product_option`
--

DROP TABLE IF EXISTS `oc_product_option`;
CREATE TABLE `oc_product_option` (
  `product_option_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `option_id` int(11) NOT NULL,
  `value` text NOT NULL,
  `required` tinyint(1) NOT NULL,
  PRIMARY KEY (`product_option_id`),
  KEY `product_id` (`product_id`),
  KEY `option_id` (`option_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_product_option`
--

INSERT INTO `oc_product_option` (`product_option_id`, `product_id`, `option_id`, `value`, `required`) VALUES
(208, 42, 4, 'test', 1),
(209, 42, 6, '', 1),
(217, 42, 5, '', 1),
(218, 42, 1, '', 1),
(219, 42, 8, '2011-02-20', 1),
(220, 42, 10, '2011-02-20 22:25', 1),
(221, 42, 9, '22:25', 1),
(222, 42, 7, '', 1),
(223, 42, 2, '', 1),
(224, 35, 11, '', 1),
(225, 47, 12, '2011-04-22', 1),
(226, 30, 5, '', 1);

----------------------------------------------------------

--
-- Table structure for table `oc_product_option_value`
--

DROP TABLE IF EXISTS `oc_product_option_value`;
CREATE TABLE `oc_product_option_value` (
  `product_option_value_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_option_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `option_id` int(11) NOT NULL,
  `option_value_id` int(11) NOT NULL,
  `quantity` int(3) NOT NULL,
  `subtract` tinyint(1) NOT NULL,
  `price` decimal(15,4) NOT NULL,
  `price_prefix` varchar(1) NOT NULL,
  `points` int(8) NOT NULL,
  `points_prefix` varchar(1) NOT NULL,
  `weight` decimal(15,8) NOT NULL,
  `weight_prefix` varchar(1) NOT NULL,
  PRIMARY KEY (`product_option_value_id`),
  KEY `product_option_id` (`product_option_id`),
  KEY `product_id` (`product_id`),
  KEY `option_id` (`option_id`),
  KEY `option_value_id` (`option_value_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_product_option_value`
--

INSERT INTO `oc_product_option_value` (`product_option_id`, `product_id`, `option_id`, `option_value_id`, `quantity`, `subtract`, `price`, `price_prefix`, `points`, `points_prefix`, `weight`, `weight_prefix`) VALUES
(217, 42, 5, 41, 100, 0, '1.0000', '+', 0, '+', '1.00000000', '+'),
(217, 42, 5, 42, 200, 1, '2.0000', '+', 0, '+', '2.00000000', '+'),
(217, 42, 5, 40, 300, 0, '3.0000', '+', 0, '+', '3.00000000', '+'),
(217, 42, 5, 39, 92, 1, '4.0000', '+', 0, '+', '4.00000000', '+'),
(218, 42, 1, 32, 96, 1, '10.0000', '+', 1, '+', '10.00000000', '+'),
(218, 42, 1, 31, 146, 1, '20.0000', '+', 2, '-', '20.00000000', '+'),
(218, 42, 1, 43, 300, 1, '30.0000', '+', 3, '+', '30.00000000', '+'),
(223, 42, 2, 23, 48, 1, '10.0000', '+', 0, '+', '10.00000000', '+'),
(223, 42, 2, 24, 194, 1, '20.0000', '+', 0, '+', '20.00000000', '+'),
(223, 42, 2, 44, 2696, 1, '30.0000', '+', 0, '+', '30.00000000', '+'),
(223, 42, 2, 45, 3998, 1, '40.0000', '+', 0, '+', '40.00000000', '+'),
(224, 35, 11, 46, 0, 1, '5.0000', '+', 0, '+', '0.00000000', '+'),
(224, 35, 11, 47, 10, 1, '10.0000', '+', 0, '+', '0.00000000', '+'),
(224, 35, 11, 48, 15, 1, '15.0000', '+', 0, '+', '0.00000000', '+'),
(226, 30, 5, 39, 2, 1, '0.0000', '+', 0, '+', '0.00000000', '+'),
(226, 30, 5, 40, 5, 1, '0.0000', '+', 0, '+', '0.00000000', '+');

----------------------------------------------------------

--
-- Table structure for table `oc_product_recurring`
--

DROP TABLE IF EXISTS `oc_product_recurring`;
CREATE TABLE `oc_product_recurring` (
  `product_id` int(11) NOT NULL,
  `recurring_id` int(11) NOT NULL,
  `customer_group_id` int(11) NOT NULL,
  PRIMARY KEY (`product_id`,`recurring_id`,`customer_group_id`),
  KEY `product_id` (`product_id`),
  KEY `recurring_id` (`recurring_id`),
  KEY `customer_group_id` (`customer_group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_product_recurring`
--


----------------------------------------------------------

--
-- Table structure for table `oc_product_related`
--

DROP TABLE IF EXISTS `oc_product_related`;
CREATE TABLE `oc_product_related` (
  `product_id` int(11) NOT NULL,
  `related_id` int(11) NOT NULL,
  PRIMARY KEY (`product_id`,`related_id`),
  KEY `product_id` (`product_id`),
  KEY `related_id` (`related_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_product_related`
--

INSERT INTO `oc_product_related` (`product_id`, `related_id`) VALUES
(40, 42),
(41, 42),
(42, 40),
(42, 41);

----------------------------------------------------------

--
-- Table structure for table `oc_product_related_article`
--

DROP TABLE IF EXISTS `oc_product_related_article`;
CREATE TABLE `oc_product_related_article` (
  `article_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  PRIMARY KEY (`article_id`,`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_product_related_article`
--

INSERT INTO `oc_product_related_article` (`article_id`, `product_id`) VALUES
(120, 30),
(120, 40),
(120, 42),
(123, 40),
(123, 42),
(124, 40),
(125, 30);

----------------------------------------------------------

--
-- Table structure for table `oc_product_related_mn`
--

DROP TABLE IF EXISTS `oc_product_related_mn`;
CREATE TABLE `oc_product_related_mn` (
  `product_id` int(11) NOT NULL,
  `manufacturer_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_product_related_mn`
--

INSERT INTO `oc_product_related_mn` (`product_id`, `manufacturer_id`) VALUES
(30, 9),
(41, 8),
(42, 8),
(47, 7);

----------------------------------------------------------

--
-- Table structure for table `oc_product_related_wb`
--

DROP TABLE IF EXISTS `oc_product_related_wb`;
CREATE TABLE `oc_product_related_wb` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_product_related_wb`
--

INSERT INTO `oc_product_related_wb` (`product_id`, `category_id`) VALUES
(33, 20),
(41, 26),
(41, 27),
(43, 18),
(44, 18),
(45, 18);

----------------------------------------------------------

--
-- Table structure for table `oc_product_reward`
--

DROP TABLE IF EXISTS `oc_product_reward`;
CREATE TABLE `oc_product_reward` (
  `product_reward_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0',
  `customer_group_id` int(11) NOT NULL DEFAULT '0',
  `points` int(8) NOT NULL DEFAULT '0',
  PRIMARY KEY (`product_reward_id`),
  KEY `product_id` (`product_id`),
  KEY `customer_group_id` (`customer_group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_product_reward`
--

INSERT INTO `oc_product_reward` (`product_id`, `customer_group_id`, `points`) VALUES
(43, 1, 600),
(44, 1, 700),
(45, 1, 800),
(40, 1, 0),
(31, 1, 0),
(29, 1, 0),
(48, 1, 0),
(33, 1, 0),
(46, 1, 0),
(36, 1, 0),
(34, 1, 0),
(32, 1, 0),
(28, 1, 400),
(35, 1, 0),
(42, 1, 100),
(47, 1, 300),
(49, 1, 1000),
(30, 1, 200),
(41, 1, 0);

----------------------------------------------------------

--
-- Table structure for table `oc_product_special`
--

DROP TABLE IF EXISTS `oc_product_special`;
CREATE TABLE `oc_product_special` (
  `product_special_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `customer_group_id` int(11) NOT NULL,
  `priority` int(5) NOT NULL DEFAULT '1',
  `price` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `date_start` date NOT NULL DEFAULT '0000-00-00',
  `date_end` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`product_special_id`),
  KEY `product_id` (`product_id`),
  KEY `customer_group_id` (`customer_group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_product_special`
--


----------------------------------------------------------

--
-- Table structure for table `oc_product_to_category`
--

DROP TABLE IF EXISTS `oc_product_to_category`;
CREATE TABLE `oc_product_to_category` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `main_category` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`product_id`,`category_id`),
  KEY `product_id` (`product_id`),
  KEY `category_id` (`category_id`),
  KEY `main_category` (`main_category`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_product_to_category`
--

INSERT INTO `oc_product_to_category` (`product_id`, `category_id`, `main_category`) VALUES
(28, 20, 0),
(28, 24, 1),
(29, 20, 0),
(29, 24, 1),
(30, 20, 0),
(30, 33, 1),
(31, 33, 1),
(32, 34, 1),
(33, 20, 0),
(33, 28, 1),
(34, 34, 1),
(35, 20, 1),
(36, 34, 1),
(40, 20, 0),
(40, 24, 1),
(41, 27, 1),
(42, 20, 0),
(42, 28, 1),
(43, 18, 0),
(43, 20, 0),
(43, 46, 1),
(44, 18, 0),
(44, 20, 0),
(44, 46, 1),
(45, 18, 0),
(45, 46, 1),
(46, 18, 0),
(46, 20, 0),
(46, 45, 1),
(47, 18, 0),
(47, 20, 0),
(47, 45, 1),
(48, 20, 0),
(48, 34, 1),
(49, 57, 1);

----------------------------------------------------------

--
-- Table structure for table `oc_product_to_download`
--

DROP TABLE IF EXISTS `oc_product_to_download`;
CREATE TABLE `oc_product_to_download` (
  `product_id` int(11) NOT NULL,
  `download_id` int(11) NOT NULL,
  PRIMARY KEY (`product_id`,`download_id`),
  KEY `product_id` (`product_id`),
  KEY `download_id` (`download_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_product_to_download`
--


----------------------------------------------------------

--
-- Table structure for table `oc_product_to_layout`
--

DROP TABLE IF EXISTS `oc_product_to_layout`;
CREATE TABLE `oc_product_to_layout` (
  `product_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `layout_id` int(11) NOT NULL,
  PRIMARY KEY (`product_id`,`store_id`),
  KEY `product_id` (`product_id`),
  KEY `store_id` (`store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_product_to_layout`
--


----------------------------------------------------------

--
-- Table structure for table `oc_product_to_store`
--

DROP TABLE IF EXISTS `oc_product_to_store`;
CREATE TABLE `oc_product_to_store` (
  `product_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`product_id`,`store_id`),
  KEY `product_id` (`product_id`),
  KEY `store_id` (`store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_product_to_store`
--

INSERT INTO `oc_product_to_store` (`product_id`, `store_id`) VALUES
(28, 0),
(29, 0),
(30, 0),
(31, 0),
(32, 0),
(33, 0),
(34, 0),
(35, 0),
(36, 0),
(40, 0),
(41, 0),
(42, 0),
(43, 0),
(44, 0),
(45, 0),
(46, 0),
(47, 0),
(48, 0),
(49, 0);

----------------------------------------------------------

--
-- Table structure for table `oc_recurring`
--

DROP TABLE IF EXISTS `oc_recurring`;
CREATE TABLE `oc_recurring` (
  `recurring_id` int(11) NOT NULL AUTO_INCREMENT,
  `price` decimal(10,4) NOT NULL,
  `frequency` enum('day','week','semi_month','month','year') NOT NULL,
  `duration` int(10) unsigned NOT NULL,
  `cycle` int(10) unsigned NOT NULL,
  `trial_status` tinyint(4) NOT NULL,
  `trial_price` decimal(10,4) NOT NULL,
  `trial_frequency` enum('day','week','semi_month','month','year') NOT NULL,
  `trial_duration` int(10) unsigned NOT NULL,
  `trial_cycle` int(10) unsigned NOT NULL,
  `status` tinyint(4) NOT NULL,
  `sort_order` int(11) NOT NULL,
  PRIMARY KEY (`recurring_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_recurring`
--


----------------------------------------------------------

--
-- Table structure for table `oc_recurring_description`
--

DROP TABLE IF EXISTS `oc_recurring_description`;
CREATE TABLE `oc_recurring_description` (
  `recurring_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`recurring_id`,`language_id`),
  KEY `recurring_id` (`recurring_id`),
  KEY `language_id` (`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_recurring_description`
--


----------------------------------------------------------

--
-- Table structure for table `oc_return`
--

DROP TABLE IF EXISTS `oc_return`;
CREATE TABLE `oc_return` (
  `return_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `firstname` varchar(32) NOT NULL,
  `lastname` varchar(32) NOT NULL,
  `email` varchar(96) NOT NULL,
  `telephone` varchar(32) NOT NULL,
  `product` varchar(255) NOT NULL,
  `model` varchar(64) NOT NULL,
  `quantity` int(4) NOT NULL,
  `opened` tinyint(1) NOT NULL,
  `return_reason_id` int(11) NOT NULL,
  `return_action_id` int(11) NOT NULL,
  `return_status_id` int(11) NOT NULL,
  `comment` text,
  `date_ordered` date NOT NULL DEFAULT '0000-00-00',
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`return_id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_return`
--


----------------------------------------------------------

--
-- Table structure for table `oc_return_action`
--

DROP TABLE IF EXISTS `oc_return_action`;
CREATE TABLE `oc_return_action` (
  `return_action_id` int(11) NOT NULL AUTO_INCREMENT,
  `language_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`return_action_id`,`language_id`),
  KEY `return_action_id` (`return_action_id`),
  KEY `language_id` (`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_return_action`
--

INSERT INTO `oc_return_action` (`return_action_id`, `language_id`, `name`) VALUES
(1, 1, 'Возврат'),
(1, 2, 'Повернуто'),
(1, 3, 'Refunded'),
(2, 1, 'Возврат средств'),
(2, 2, 'Повернення коштів'),
(2, 3, 'Credit Issued'),
(3, 1, 'Отправлена замена'),
(3, 2, 'Надіслано заміну'),
(3, 3, 'Replacement Sent');

----------------------------------------------------------

--
-- Table structure for table `oc_return_history`
--

DROP TABLE IF EXISTS `oc_return_history`;
CREATE TABLE `oc_return_history` (
  `return_history_id` int(11) NOT NULL AUTO_INCREMENT,
  `return_id` int(11) NOT NULL,
  `return_status_id` int(11) NOT NULL,
  `notify` tinyint(1) NOT NULL,
  `comment` text NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`return_history_id`),
  KEY `return_id` (`return_id`),
  KEY `return_status_id` (`return_status_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_return_history`
--


----------------------------------------------------------

--
-- Table structure for table `oc_return_reason`
--

DROP TABLE IF EXISTS `oc_return_reason`;
CREATE TABLE `oc_return_reason` (
  `return_reason_id` int(11) NOT NULL AUTO_INCREMENT,
  `language_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(128) NOT NULL,
  PRIMARY KEY (`return_reason_id`,`language_id`),
  KEY `return_reason_id` (`return_reason_id`),
  KEY `language_id` (`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_return_reason`
--

INSERT INTO `oc_return_reason` (`return_reason_id`, `language_id`, `name`) VALUES
(1, 1, 'Получен неисправным (сломанным)'),
(1, 2, 'Отримано пошкодженим'),
(1, 3, 'Dead On Arrival'),
(2, 1, 'Получен не тот (ошибочный) товар'),
(2, 2, 'Отримано не той товар'),
(2, 3, 'Received Wrong Item'),
(3, 1, 'Заказан по ошибке'),
(3, 2, 'Помилкове замовлення'),
(3, 3, 'Order Error'),
(4, 1, 'Неисправен, пожалуйста укажите/приложите подробности'),
(4, 2, 'Несправність, будь ласка, вкажіть деталі'),
(4, 3, 'Faulty, please supply details'),
(5, 1, 'Другое (другая причина), пожалуйста укажите/приложите подробности'),
(5, 2, 'Інше, будь ласка, надайте детальну інформацію'),
(5, 3, 'Other, please supply details');

----------------------------------------------------------

--
-- Table structure for table `oc_return_status`
--

DROP TABLE IF EXISTS `oc_return_status`;
CREATE TABLE `oc_return_status` (
  `return_status_id` int(11) NOT NULL AUTO_INCREMENT,
  `language_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(32) NOT NULL,
  PRIMARY KEY (`return_status_id`,`language_id`),
  KEY `return_status_id` (`return_status_id`),
  KEY `language_id` (`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_return_status`
--

INSERT INTO `oc_return_status` (`return_status_id`, `language_id`, `name`) VALUES
(1, 1, 'В ожидании'),
(1, 2, 'В очікуванні'),
(1, 3, 'Pending'),
(2, 1, 'Ожидание товара'),
(2, 2, 'Очікування товару'),
(2, 3, 'Awaiting Products'),
(3, 1, 'Выполнен'),
(3, 2, 'Виконаний'),
(3, 3, 'Complete');

----------------------------------------------------------

--
-- Table structure for table `oc_review`
--

DROP TABLE IF EXISTS `oc_review`;
CREATE TABLE `oc_review` (
  `review_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `author` varchar(64) NOT NULL,
  `text` text NOT NULL,
  `rating` int(1) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`review_id`),
  KEY `product_id` (`product_id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_review`
--


----------------------------------------------------------

--
-- Table structure for table `oc_review_article`
--

DROP TABLE IF EXISTS `oc_review_article`;
CREATE TABLE `oc_review_article` (
  `review_article_id` int(11) NOT NULL AUTO_INCREMENT,
  `article_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `author` varchar(64) NOT NULL DEFAULT '',
  `text` text NOT NULL,
  `rating` int(1) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `date_added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`review_article_id`),
  KEY `article_id` (`article_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_review_article`
--

INSERT INTO `oc_review_article` (`review_article_id`, `article_id`, `customer_id`, `author`, `text`, `rating`, `status`, `date_added`, `date_modified`) VALUES
(11, 123, 0, 'Василий Покупайкин', 'Спасибо за отличный фото обзор, обязательно в ближайшее время приобрету себе такую тушку и напишу дополнение к Вашей статье.', 5, 1, '2014-04-08 05:59:25', '0000-00-00 00:00:00');

----------------------------------------------------------

--
-- Table structure for table `oc_seo_url`
--

DROP TABLE IF EXISTS `oc_seo_url`;
CREATE TABLE `oc_seo_url` (
  `seo_url_id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `query` varchar(255) NOT NULL,
  `keyword` varchar(255) NOT NULL,
  PRIMARY KEY (`seo_url_id`),
  KEY `store_id` (`store_id`),
  KEY `language_id` (`language_id`),
  KEY `query` (`query`(191)),
  KEY `keyword` (`keyword`(191))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_seo_url`
--

INSERT INTO `oc_seo_url` (`store_id`, `language_id`, `query`, `keyword`) VALUES
(0, 1, 'account/account', 'my-account'),
(0, 1, 'account/address', 'address-book'),
(0, 1, 'account/download', 'downloads'),
(0, 1, 'account/edit', 'edit-account'),
(0, 1, 'account/forgotten', 'forgot-password'),
(0, 1, 'account/login', 'login'),
(0, 1, 'account/logout', 'logout'),
(0, 1, 'account/newsletter', 'newsletter'),
(0, 1, 'account/order', 'order-history'),
(0, 1, 'account/password', 'change-password'),
(0, 1, 'account/register', 'create-account'),
(0, 1, 'account/return', 'returns'),
(0, 1, 'account/return/add', 'add-return'),
(0, 1, 'account/return/insert', 'request-return'),
(0, 1, 'account/reward', 'reward-points'),
(0, 1, 'account/transaction', 'transactions'),
(0, 1, 'account/voucher', 'vouchers'),
(0, 1, 'account/wishlist', 'wishlist'),
(0, 1, 'affiliate/account', 'affiliates'),
(0, 1, 'affiliate/edit', 'edit-affiliate-account'),
(0, 1, 'affiliate/forgotten', 'affiliate-forgot-password'),
(0, 1, 'affiliate/login', 'affiliate-login'),
(0, 1, 'affiliate/logout', 'affiliate-logout'),
(0, 1, 'affiliate/password', 'change-affiliate-password'),
(0, 1, 'affiliate/payment', 'affiliate-payment-options'),
(0, 1, 'affiliate/register', 'create-affiliate-account'),
(0, 1, 'affiliate/tracking', 'affiliate-tracking-code'),
(0, 1, 'affiliate/transaction', 'affiliate-transactions'),
(0, 1, 'category_id=17', 'software'),
(0, 1, 'category_id=18', 'laptop-notebook'),
(0, 1, 'category_id=20', 'desktops'),
(0, 1, 'category_id=24', 'smartphone'),
(0, 1, 'category_id=25', 'component'),
(0, 1, 'category_id=26', 'pc'),
(0, 1, 'category_id=27', 'mac'),
(0, 1, 'category_id=28', 'monitor'),
(0, 1, 'category_id=29', 'mouse'),
(0, 1, 'category_id=30', 'printer'),
(0, 1, 'category_id=31', 'scanner'),
(0, 1, 'category_id=32', 'web-camera'),
(0, 1, 'category_id=33', 'camera'),
(0, 1, 'category_id=34', 'mp3-players'),
(0, 1, 'category_id=35', 'test1'),
(0, 1, 'category_id=36', 'test2'),
(0, 1, 'category_id=37', 'test5'),
(0, 1, 'category_id=38', 'test4'),
(0, 1, 'category_id=39', 'test6'),
(0, 1, 'category_id=40', 'test7'),
(0, 1, 'category_id=41', 'test8'),
(0, 1, 'category_id=42', 'test9'),
(0, 1, 'category_id=43', 'test11'),
(0, 1, 'category_id=44', 'test12'),
(0, 1, 'category_id=45', 'windows'),
(0, 1, 'category_id=46', 'macs'),
(0, 1, 'category_id=47', 'test15'),
(0, 1, 'category_id=48', 'test16'),
(0, 1, 'category_id=49', 'test17'),
(0, 1, 'category_id=50', 'test18'),
(0, 1, 'category_id=51', 'test19'),
(0, 1, 'category_id=52', 'test20'),
(0, 1, 'category_id=53', 'test21'),
(0, 1, 'category_id=54', 'test22'),
(0, 1, 'category_id=55', 'test23'),
(0, 1, 'category_id=56', 'test24'),
(0, 1, 'category_id=57', 'tablets'),
(0, 1, 'category_id=58', 'test25'),
(0, 1, 'checkout/cart', 'cart'),
(0, 1, 'checkout/checkout', 'checkout'),
(0, 1, 'checkout/voucher', 'gift-vouchers'),
(0, 1, 'common/home', ''),
(0, 1, 'information/contact', 'contact-us'),
(0, 1, 'information/sitemap', 'sitemap'),
(0, 1, 'information_id=3', 'ru_privacy'),
(0, 1, 'information_id=4', 'ru_about_us'),
(0, 1, 'information_id=5', 'ru_terms'),
(0, 1, 'information_id=6', 'ru_delivery'),
(0, 1, 'manufacturer_id=10', 'sony'),
(0, 1, 'manufacturer_id=5', 'htc'),
(0, 1, 'manufacturer_id=6', 'palm'),
(0, 1, 'manufacturer_id=7', 'hewlett-packard'),
(0, 1, 'manufacturer_id=8', 'apple'),
(0, 1, 'manufacturer_id=9', 'canon'),
(0, 1, 'product/compare', 'compare-products'),
(0, 1, 'product/manufacturer', 'brands'),
(0, 1, 'product/search', 'search'),
(0, 1, 'product/special', 'specials'),
(0, 1, 'product_id=28', 'htc-touch-hd'),
(0, 1, 'product_id=29', 'palm-treo-pro'),
(0, 1, 'product_id=30', 'canon-eos-5d'),
(0, 1, 'product_id=31', 'nikon-d300'),
(0, 1, 'product_id=32', 'ipod-touch'),
(0, 1, 'product_id=33', 'samsung-syncmaster-941bw'),
(0, 1, 'product_id=34', 'ipod-shuffle'),
(0, 1, 'product_id=35', 'product-8'),
(0, 1, 'product_id=36', 'ipod-nano'),
(0, 1, 'product_id=40', 'iphone'),
(0, 1, 'product_id=41', 'imac'),
(0, 1, 'product_id=42', 'apple_cinema_30'),
(0, 1, 'product_id=43', 'macbook'),
(0, 1, 'product_id=44', 'macbook-air'),
(0, 1, 'product_id=45', 'macbook-pro'),
(0, 1, 'product_id=46', 'sony-vaio'),
(0, 1, 'product_id=47', 'hp-lp3065'),
(0, 1, 'product_id=48', 'ipod-classic'),
(0, 1, 'product_id=49', 'samsung-galaxy-tab-10-1'),
(0, 2, 'account/account', 'uk-my-account'),
(0, 2, 'account/address', 'uk-address-book'),
(0, 2, 'account/download', 'uk-downloads'),
(0, 2, 'account/edit', 'uk-edit-account'),
(0, 2, 'account/forgottuk', 'uk-forgot-password'),
(0, 2, 'account/login', 'uk-login'),
(0, 2, 'account/logout', 'uk-logout'),
(0, 2, 'account/newsletter', 'uk-newsletter'),
(0, 2, 'account/order', 'uk-order-history'),
(0, 2, 'account/password', 'uk-change-password'),
(0, 2, 'account/register', 'uk-create-account'),
(0, 2, 'account/return', 'uk-returns'),
(0, 2, 'account/return/add', 'uk-add-return'),
(0, 2, 'account/return/insert', 'uk-request-return'),
(0, 2, 'account/reward', 'uk-reward-points'),
(0, 2, 'account/transaction', 'uk-transactions'),
(0, 2, 'account/voucher', 'uk-vouchers'),
(0, 2, 'account/wishlist', 'uk-wishlist'),
(0, 2, 'affiliate/account', 'uk-affiliates'),
(0, 2, 'affiliate/edit', 'uk-edit-affiliate-account'),
(0, 2, 'affiliate/forgotten', 'uk-affiliate-forgot-password'),
(0, 2, 'affiliate/login', 'uk-affiliate-login'),
(0, 2, 'affiliate/logout', 'uk-affiliate-logout'),
(0, 2, 'affiliate/password', 'uk-change-affiliate-password'),
(0, 2, 'affiliate/payment', 'uk-affiliate-payment-options'),
(0, 2, 'affiliate/register', 'uk-create-affiliate-account'),
(0, 2, 'affiliate/tracking', 'uk-affiliate-tracking-code'),
(0, 2, 'affiliate/transaction', 'uk-affiliate-transactions'),
(0, 2, 'category_id=17', 'uk_software'),
(0, 2, 'category_id=18', 'uk_laptop-notebook'),
(0, 2, 'category_id=20', 'uk_desktops'),
(0, 2, 'category_id=24', 'uk_smartphone'),
(0, 2, 'category_id=25', 'uk_component'),
(0, 2, 'category_id=26', 'uk_pc'),
(0, 2, 'category_id=27', 'uk_mac'),
(0, 2, 'category_id=28', 'uk_monitor'),
(0, 2, 'category_id=29', 'uk_mouse'),
(0, 2, 'category_id=30', 'uk_printer'),
(0, 2, 'category_id=31', 'uk_scanner'),
(0, 2, 'category_id=32', 'uk_web-camera'),
(0, 2, 'category_id=33', 'uk_camera'),
(0, 2, 'category_id=34', 'uk_mp3-players'),
(0, 2, 'category_id=35', 'uk_test1'),
(0, 2, 'category_id=36', 'uk_test2'),
(0, 2, 'category_id=37', 'uk_test5'),
(0, 2, 'category_id=38', 'uk_test4'),
(0, 2, 'category_id=39', 'uk_test6'),
(0, 2, 'category_id=40', 'uk_test7'),
(0, 2, 'category_id=41', 'uk_test8'),
(0, 2, 'category_id=42', 'uk_test9'),
(0, 2, 'category_id=43', 'uk_test11'),
(0, 2, 'category_id=44', 'uk_test12'),
(0, 2, 'category_id=45', 'uk_windows'),
(0, 2, 'category_id=46', 'uk_macs'),
(0, 2, 'category_id=47', 'uk_test15'),
(0, 2, 'category_id=48', 'uk_test16'),
(0, 2, 'category_id=49', 'uk_test17'),
(0, 2, 'category_id=50', 'uk_test18'),
(0, 2, 'category_id=51', 'uk_test19'),
(0, 2, 'category_id=52', 'uk_test20'),
(0, 2, 'category_id=53', 'uk_test21'),
(0, 2, 'category_id=54', 'uk_test22'),
(0, 2, 'category_id=55', 'uk_test23'),
(0, 2, 'category_id=56', 'uk_test24'),
(0, 2, 'category_id=57', 'uk_tablets'),
(0, 2, 'category_id=58', 'uk_test25'),
(0, 2, 'checkout/cart', 'uk-cart'),
(0, 2, 'checkout/checkout', 'uk-checkout'),
(0, 2, 'checkout/voucher', 'uk-gift-vouchers'),
(0, 2, 'common/home', 'uk'),
(0, 2, 'information/contact', 'uk-contact-us'),
(0, 2, 'information/sitemap', 'uk-sitemap'),
(0, 2, 'information_id=3', 'uk_privacy'),
(0, 2, 'information_id=4', 'uk_about_us'),
(0, 2, 'information_id=5', 'uk_terms'),
(0, 2, 'information_id=6', 'uk_delivery'),
(0, 2, 'manufacturer_id=10', 'uk_sony'),
(0, 2, 'manufacturer_id=5', 'uk_htc'),
(0, 2, 'manufacturer_id=6', 'uk_palm'),
(0, 2, 'manufacturer_id=7', 'uk_hewlett-packard'),
(0, 2, 'manufacturer_id=8', 'uk_apple'),
(0, 2, 'manufacturer_id=9', 'uk_canon'),
(0, 2, 'product/compare', 'uk-compare-products'),
(0, 2, 'product/manufacturer', 'uk-brands'),
(0, 2, 'product/search', 'uk-search'),
(0, 2, 'product/special', 'uk-specials'),
(0, 2, 'product_id=28', 'uk_htc-touch-hd'),
(0, 2, 'product_id=29', 'uk_palm-treo-pro'),
(0, 2, 'product_id=30', 'uk_canon-eos-5d'),
(0, 2, 'product_id=31', 'uk_nikon-d300'),
(0, 2, 'product_id=32', 'uk_ipod-touch'),
(0, 2, 'product_id=33', 'uk_samsung-syncmaster-941bw'),
(0, 2, 'product_id=34', 'uk_ipod-shuffle'),
(0, 2, 'product_id=35', 'uk_product-8'),
(0, 2, 'product_id=36', 'uk_ipod-nano'),
(0, 2, 'product_id=40', 'uk_iphone'),
(0, 2, 'product_id=41', 'uk_imac'),
(0, 2, 'product_id=42', 'uk_apple_cinema_30'),
(0, 2, 'product_id=43', 'uk_macbook'),
(0, 2, 'product_id=44', 'uk_macbook-air'),
(0, 2, 'product_id=45', 'uk_macbook-pro'),
(0, 2, 'product_id=46', 'uk_sony-vaio'),
(0, 2, 'product_id=47', 'uk_hp-lp3065'),
(0, 2, 'product_id=48', 'uk_ipod-classic'),
(0, 2, 'product_id=49', 'uk_samsung-galaxy-tab-10-1');

----------------------------------------------------------

--
-- Table structure for table `oc_session`
--

DROP TABLE IF EXISTS `oc_session`;
CREATE TABLE `oc_session` (
  `session_id` varchar(32) NOT NULL,
  `data` text NOT NULL,
  `expire` datetime NOT NULL,
  PRIMARY KEY (`session_id`),
  KEY `expire` (`expire`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_session`
--


----------------------------------------------------------

--
-- Table structure for table `oc_setting`
--

DROP TABLE IF EXISTS `oc_setting`;
CREATE TABLE `oc_setting` (
  `setting_id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL DEFAULT '0',
  `code` varchar(128) NOT NULL,
  `key` varchar(128) NOT NULL,
  `value` text NOT NULL,
  `serialized` tinyint(1) NOT NULL,
  PRIMARY KEY (`setting_id`),
  KEY `store_id` (`store_id`),
  KEY `code` (`code`),
  KEY `idx_store_key` (`store_id`,`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_setting`
--

INSERT INTO `oc_setting` (`store_id`, `code`, `key`, `value`, `serialized`) VALUES
(0, 'config', 'config_account_id', '3', 0),
(0, 'config', 'config_add_prevnext', '1', 0),
(0, 'config', 'config_address', 'Адрес', 0),
(0, 'config', 'config_admin_language', 'ru-ru', 0),
(0, 'config', 'config_affiliate_approval', '0', 0),
(0, 'config', 'config_affiliate_auto', '0', 0),
(0, 'config', 'config_affiliate_commission', '5', 0),
(0, 'config', 'config_affiliate_group_id', '1', 0),
(0, 'config', 'config_affiliate_id', '0', 0),
(0, 'config', 'config_api_id', '1', 0),
(0, 'config', 'config_canonical_method', '1', 0),
(0, 'config', 'config_canonical_self', '1', 0),
(0, 'config', 'config_captcha', '', 0),
(0, 'config', 'config_captcha_page', '[\"review\",\"return\",\"contact\"]', 1),
(0, 'config', 'config_cart_weight', '1', 0),
(0, 'config', 'config_checkout_guest', '1', 0),
(0, 'config', 'config_checkout_id', '0', 0),
(0, 'config', 'config_comment', '', 0),
(0, 'config', 'config_complete_status', '[\"8\",\"6\"]', 1),
(0, 'config', 'config_compression', '0', 0),
(0, 'config', 'config_country_id', '220', 0),
(0, 'config', 'config_currency', 'UAH', 0),
(0, 'config', 'config_currency_auto', '0', 0),
(0, 'config', 'config_currency_engine', 'nbu', 0),
(0, 'config', 'config_customer_activity', '0', 0),
(0, 'config', 'config_customer_group_id', '1', 0),
(0, 'config', 'config_customer_online', '0', 0),
(0, 'config', 'config_customer_price', '0', 0),
(0, 'config', 'config_customer_search', '0', 0),
(0, 'config', 'config_email', '', 0),
(0, 'config', 'config_encryption', 'AqQygD2rVY2EKWcl4gUmB601EGyqb25Zl7RWQtH7Y6QHfn6HtL0JhkqPz4wOEwBVEX17zLxDIBPvLJfnhE4gg0kJlPiRgFpRZFRpbLXnCR3rkH2XX9lKX17kRvPxlH8YzvXG2z77BT1krrSauNB5umVR6d2B4c9ryzyg1fhdxQ35FUaQVgspdYVbHx2srOFDUeP2cFV2pwsMYcJiakma1w22xcvVirhgaxKex7t0ZVvhqaX4dLHtwr7hBEOsS9TfCyZePu4e83jywKiedT86S3UTHsz7klPllOVb6MsMZ4yEdCOEelfTiNRLQ1crVyjcfvquDjsZ6GKrcYGr2R4jn14gcXpFCgUuqNvpifvzASOqpphGmLAo31ILmZeSdGKgCehxaV3ShTduJvEaE8NrUkLeRSUNRxtorY2wX3N2uJBlPCDvsIm04wO0BihjhV9RdjiNmrteDt0SG56ocWmy52CBcAIldfBJgp0fVMdNZa61PTUdtX8pA07SGTCSu5Ea1jYTCyEIQcfydLwE4I6Fj9GZgpT3xR4ZC67vv9PbhR2CVB6nrWowojk1j4KJTXf3EaKeellIVIB4r3PZP2N073ApYKk79R70YTDDL0iGL9tMI0LnWPJ4iJBHHWx4aoDqatR6fbcJ7CY0ON9CKwbYQVHSVridbMpmMSxirRsubOeECgy2tcM1HafLCOO1tWIBa7VIgtmaa6GcuDi05A24wPidd1o3DZQ6Z691CdJOcFHppoKNp2QhXWQJo1jiDB2ZQLnK3CP4ZbQFQwEMqoNLF5GLjKE87SgjOU1NQwUrslEEdNFN4rzpKG7EI4DltCXtgunp3Zy3iJMVTJlY4TjX8RzUrVH7NUEaxeK8UG1Yn2hAHmmUYROPBQRaEE5x0lFMe09oaySkt7UAjCf66tEmPZNpnJdAUGTIRV26fq6LdARHWLvHbyr5nArJ49ES8J9afsAu8RJbqhef4xZ8w2FGxkwbAsLJky1fuWi2xWHhwb0U1Fh7hvCe2jJawWObu3AV', 0),
(0, 'config', 'config_error_display', '0', 0),
(0, 'config', 'config_error_filename', 'error.log', 0),
(0, 'config', 'config_error_log', '1', 0),
(0, 'config', 'config_fax', '', 0),
(0, 'config', 'config_file_ext_allowed', 'txt\r\npng\r\njpe\r\njpeg\r\njpg\r\ngif\r\nbmp\r\nico\r\ntiff\r\ntif\r\nsvg\r\nsvgz\r\nzip\r\nrar\r\nmsi\r\ncab\r\nmp3\r\nqt\r\nmov\r\npdf\r\npsd\r\nai\r\neps\r\nps\r\ndoc', 0),
(0, 'config', 'config_file_max_size', '20', 0),
(0, 'config', 'config_file_mime_allowed', 'text/plain\r\nimage/png\r\nimage/jpeg\r\nimage/gif\r\nimage/bmp\r\nimage/tiff\r\nimage/svg+xml\r\napplication/zip\r\napplication/x-zip\r\napplication/x-zip-compressed\r\napplication/rar\r\napplication/x-rar\r\napplication/x-rar-compressed\r\napplication/octet-stream\r\naudio/mpeg\r\nvideo/quicktime\r\napplication/pdf', 0),
(0, 'config', 'config_fraud_status_id', '10', 0),
(0, 'config', 'config_geocode', '', 0),
(0, 'config', 'config_icon', 'catalog/cart.png', 0),
(0, 'config', 'config_image', '', 0),
(0, 'config', 'config_invoice_prefix', 'INV-2026-00', 0),
(0, 'config', 'config_language', 'ru-ru', 0),
(0, 'config', 'config_layout_id', '4', 0),
(0, 'config', 'config_length_class_id', '1', 0),
(0, 'config', 'config_limit_admin', '25', 0),
(0, 'config', 'config_limit_autocomplete', '15', 0),
(0, 'config', 'config_limit_filemanager', '16', 0),
(0, 'config', 'config_login_attempts', '5', 0),
(0, 'config', 'config_logo', 'catalog/logo.png', 0),
(0, 'config', 'config_mail_alert', '[\"order\"]', 1),
(0, 'config', 'config_mail_alert_email', '', 0),
(0, 'config', 'config_mail_engine', 'mail', 0),
(0, 'config', 'config_mail_parameter', '', 0),
(0, 'config', 'config_mail_smtp_hostname', '', 0),
(0, 'config', 'config_mail_smtp_password', '', 0),
(0, 'config', 'config_mail_smtp_port', '25', 0),
(0, 'config', 'config_mail_smtp_timeout', '5', 0),
(0, 'config', 'config_mail_smtp_username', '', 0),
(0, 'config', 'config_maintenance', '0', 0),
(0, 'config', 'config_meta_description', 'Мой магазин', 0),
(0, 'config', 'config_meta_keyword', '', 0),
(0, 'config', 'config_meta_title', 'Мой магазин', 0),
(0, 'config', 'config_name', 'Мой магазин', 0),
(0, 'config', 'config_noindex_disallow_params', 'page', 0),
(0, 'config', 'config_noindex_status', '1', 0),
(0, 'config', 'config_open', 'с 10.00 до 18.00', 0),
(0, 'config', 'config_order_status_id', '1', 0),
(0, 'config', 'config_owner', 'Владелец', 0),
(0, 'config', 'config_page_postfix', '', 0),
(0, 'config', 'config_password', '1', 0),
(0, 'config', 'config_processing_status', '[\"1\",\"3\"]', 1),
(0, 'config', 'config_product_count', '0', 0),
(0, 'config', 'config_return_id', '0', 0),
(0, 'config', 'config_return_status_id', '2', 0),
(0, 'config', 'config_review_guest', '1', 0),
(0, 'config', 'config_review_status', '1', 0),
(0, 'config', 'config_robots', 'abot\r\ndbot\r\nebot\r\nhbot\r\nkbot\r\nlbot\r\nmbot\r\nnbot\r\nobot\r\npbot\r\nrbot\r\nsbot\r\ntbot\r\nvbot\r\nybot\r\nzbot\r\nbot.\r\nbot/\r\n_bot\r\n.bot\r\n/bot\r\n-bot\r\n:bot\r\n(bot\r\ncrawl\r\nslurp\r\nspider\r\nseek\r\naccoona\r\nacoon\r\nadressendeutschland\r\nah-ha.com\r\nahoy\r\naltavista\r\nananzi\r\nanthill\r\nappie\r\narachnophilia\r\narale\r\naraneo\r\naranha\r\narchitext\r\naretha\r\narks\r\nasterias\r\natlocal\r\natn\r\natomz\r\naugurfind\r\nbackrub\r\nbannana_bot\r\nbaypup\r\nbdfetch\r\nbig brother\r\nbiglotron\r\nbjaaland\r\nblackwidow\r\nblaiz\r\nblog\r\nblo.\r\nbloodhound\r\nboitho\r\nbooch\r\nbradley\r\nbutterfly\r\ncalif\r\ncassandra\r\nccubee\r\ncfetch\r\ncharlotte\r\nchurl\r\ncienciaficcion\r\ncmc\r\ncollective\r\ncomagent\r\ncombine\r\ncomputingsite\r\ncsci\r\ncurl\r\ncusco\r\ndaumoa\r\ndeepindex\r\ndelorie\r\ndepspid\r\ndeweb\r\ndie blinde kuh\r\ndigger\r\nditto\r\ndmoz\r\ndocomo\r\ndownload express\r\ndtaagent\r\ndwcp\r\nebiness\r\nebingbong\r\ne-collector\r\nejupiter\r\nemacs-w3 search engine\r\nesther\r\nevliya celebi\r\nezresult\r\nfalcon\r\nfelix ide\r\nferret\r\nfetchrover\r\nfido\r\nfindlinks\r\nfireball\r\nfish search\r\nfouineur\r\nfunnelweb\r\ngazz\r\ngcreep\r\ngenieknows\r\ngetterroboplus\r\ngeturl\r\nglx\r\ngoforit\r\ngolem\r\ngrabber\r\ngrapnel\r\ngralon\r\ngriffon\r\ngromit\r\ngrub\r\ngulliver\r\nhamahakki\r\nharvest\r\nhavindex\r\nhelix\r\nheritrix\r\nhku www octopus\r\nhomerweb\r\nhtdig\r\nhtml index\r\nhtml_analyzer\r\nhtmlgobble\r\nhubater\r\nhyper-decontextualizer\r\nia_archiver\r\nibm_planetwide\r\nichiro\r\niconsurf\r\niltrovatore\r\nimage.kapsi.net\r\nimagelock\r\nincywincy\r\nindexer\r\ninfobee\r\ninformant\r\ningrid\r\ninktomisearch.com\r\ninspector web\r\nintelliagent\r\ninternet shinchakubin\r\nip3000\r\niron33\r\nisraeli-search\r\nivia\r\njack\r\njakarta\r\njavabee\r\njetbot\r\njumpstation\r\nkatipo\r\nkdd-explorer\r\nkilroy\r\nknowledge\r\nkototoi\r\nkretrieve\r\nlabelgrabber\r\nlachesis\r\nlarbin\r\nlegs\r\nlibwww\r\nlinkalarm\r\nlink validator\r\nlinkscan\r\nlockon\r\nlwp\r\nlycos\r\nmagpie\r\nmantraagent\r\nmapoftheinternet\r\nmarvin/\r\nmattie\r\nmediafox\r\nmediapartners\r\nmercator\r\nmerzscope\r\nmicrosoft url control\r\nminirank\r\nmiva\r\nmj12\r\nmnogosearch\r\nmoget\r\nmonster\r\nmoose\r\nmotor\r\nmultitext\r\nmuncher\r\nmuscatferret\r\nmwd.search\r\nmyweb\r\nnajdi\r\nnameprotect\r\nnationaldirectory\r\nnazilla\r\nncsa beta\r\nnec-meshexplorer\r\nnederland.zoek\r\nnetcarta webmap engine\r\nnetmechanic\r\nnetresearchserver\r\nnetscoop\r\nnewscan-online\r\nnhse\r\nnokia6682/\r\nnomad\r\nnoyona\r\nnutch\r\nnzexplorer\r\nobjectssearch\r\noccam\r\nomni\r\nopen text\r\nopenfind\r\nopenintelligencedata\r\norb search\r\nosis-project\r\npack rat\r\npageboy\r\npagebull\r\npage_verifier\r\npanscient\r\nparasite\r\npartnersite\r\npatric\r\npear.\r\npegasus\r\nperegrinator\r\npgp key agent\r\nphantom\r\nphpdig\r\npicosearch\r\npiltdownman\r\npimptrain\r\npinpoint\r\npioneer\r\npiranha\r\nplumtreewebaccessor\r\npogodak\r\npoirot\r\npompos\r\npoppelsdorf\r\npoppi\r\npopular iconoclast\r\npsycheclone\r\npublisher\r\npython\r\nrambler\r\nraven search\r\nroach\r\nroad runner\r\nroadhouse\r\nrobbie\r\nrobofox\r\nrobozilla\r\nrules\r\nsalty\r\nsbider\r\nscooter\r\nscoutjet\r\nscrubby\r\nsearch.\r\nsearchprocess\r\nsemanticdiscovery\r\nsenrigan\r\nsg-scout\r\nshai\'hulud\r\nshark\r\nshopwiki\r\nsidewinder\r\nsift\r\nsilk\r\nsimmany\r\nsite searcher\r\nsite valet\r\nsitetech-rover\r\nskymob.com\r\nsleek\r\nsmartwit\r\nsna-\r\nsnappy\r\nsnooper\r\nsohu\r\nspeedfind\r\nsphere\r\nsphider\r\nspinner\r\nspyder\r\nsteeler/\r\nsuke\r\nsuntek\r\nsupersnooper\r\nsurfnomore\r\nsven\r\nsygol\r\nszukacz\r\ntach black widow\r\ntarantula\r\ntempleton\r\n/teoma\r\nt-h-u-n-d-e-r-s-t-o-n-e\r\ntheophrastus\r\ntitan\r\ntitin\r\ntkwww\r\ntoutatis\r\nt-rex\r\ntutorgig\r\ntwiceler\r\ntwisted\r\nucsd\r\nudmsearch\r\nurl check\r\nupdated\r\nvagabondo\r\nvalkyrie\r\nverticrawl\r\nvictoria\r\nvision-search\r\nvolcano\r\nvoyager/\r\nvoyager-hc\r\nw3c_validator\r\nw3m2\r\nw3mir\r\nwalker\r\nwallpaper\r\nwanderer\r\nwauuu\r\nwavefire\r\nweb core\r\nweb hopper\r\nweb wombat\r\nwebbandit\r\nwebcatcher\r\nwebcopy\r\nwebfoot\r\nweblayers\r\nweblinker\r\nweblog monitor\r\nwebmirror\r\nwebmonkey\r\nwebquest\r\nwebreaper\r\nwebsitepulse\r\nwebsnarf\r\nwebstolperer\r\nwebvac\r\nwebwalk\r\nwebwatch\r\nwebwombat\r\nwebzinger\r\nwhizbang\r\nwhowhere\r\nwild ferret\r\nworldlight\r\nwwwc\r\nwwwster\r\nxenu\r\nxget\r\nxift\r\nxirq\r\nyandex\r\nyanga\r\nyeti\r\nyodao\r\nzao\r\nzippp\r\nzyborg', 0),
(0, 'config', 'config_secure', '1', 0),
(0, 'config', 'config_seo_pro', '0', 0),
(0, 'config', 'config_seo_url', '1', 0),
(0, 'config', 'config_seo_url_cache', '0', 0),
(0, 'config', 'config_seo_url_include_path', '0', 0),
(0, 'config', 'config_seopro_addslash', '0', 0),
(0, 'config', 'config_seopro_lowercase', '0', 0),
(0, 'config', 'config_shared', '0', 0),
(0, 'config', 'config_stock_checkout', '0', 0),
(0, 'config', 'config_stock_display', '0', 0),
(0, 'config', 'config_stock_warning', '0', 0),
(0, 'config', 'config_tax', '0', 0),
(0, 'config', 'config_tax_customer', 'shipping', 0),
(0, 'config', 'config_tax_default', 'shipping', 0),
(0, 'config', 'config_telephone', '123456789', 0),
(0, 'config', 'config_theme', 'default', 0),
(0, 'config', 'config_timezone', 'UTC', 0),
(0, 'config', 'config_valide_param_flag', '0', 0),
(0, 'config', 'config_valide_params', 'block\r\nfrommarket\r\ngclid\r\nfbclid\r\nkeyword\r\nlist_type\r\nopenstat\r\nopenstat_service\r\nopenstat_campaign\r\nopenstat_ad\r\nopenstat_source\r\nposition\r\nsource\r\ntracking\r\ntype\r\nyclid\r\nymclid\r\nuri\r\nurltype\r\nutm_source\r\nutm_medium\r\nutm_campaign\r\nutm_term\r\nutm_content', 0),
(0, 'config', 'config_voucher_max', '10', 0),
(0, 'config', 'config_voucher_min', '1', 0),
(0, 'config', 'config_weight_class_id', '1', 0),
(0, 'config', 'config_zone_id', '627', 0),
(0, 'configblog', 'configblog_article_count', '1', 0),
(0, 'configblog', 'configblog_article_description_length', '200', 0),
(0, 'configblog', 'configblog_article_download', '1', 0),
(0, 'configblog', 'configblog_article_limit', '20', 0),
(0, 'configblog', 'configblog_blog_menu', '1', 0),
(0, 'configblog', 'configblog_html_h1', 'Блог для интернет-магазина на OpenCart', 0),
(0, 'configblog', 'configblog_image_article_height', '150', 0),
(0, 'configblog', 'configblog_image_article_width', '150', 0),
(0, 'configblog', 'configblog_image_category_height', '50', 0),
(0, 'configblog', 'configblog_image_category_width', '50', 0),
(0, 'configblog', 'configblog_image_related_height', '200', 0),
(0, 'configblog', 'configblog_image_related_width', '200', 0),
(0, 'configblog', 'configblog_limit_admin', '20', 0),
(0, 'configblog', 'configblog_meta_description', 'Блог для интернет-магазина на OpenCart', 0),
(0, 'configblog', 'configblog_meta_keyword', 'Блог для интернет-магазина на OpenCart', 0),
(0, 'configblog', 'configblog_meta_title', 'Блог для интернет-магазина на OpenCart', 0),
(0, 'configblog', 'configblog_name', '{\"1\":\"\\u0411\\u043b\\u043e\\u0433\",\"2\":\"\\u0411\\u043b\\u043e\\u0433\"}', 1),
(0, 'configblog', 'configblog_review_guest', '1', 0),
(0, 'configblog', 'configblog_review_mail', '1', 0),
(0, 'configblog', 'configblog_review_status', '1', 0),
(0, 'currency_cbr', 'currency_cbr_status', '1', 0),
(0, 'currency_nbu', 'currency_nbu_status', '1', 0),
(0, 'dashboard_activity', 'dashboard_activity_sort_order', '7', 0),
(0, 'dashboard_activity', 'dashboard_activity_status', '1', 0),
(0, 'dashboard_activity', 'dashboard_activity_width', '4', 0),
(0, 'dashboard_chart', 'dashboard_chart_sort_order', '6', 0),
(0, 'dashboard_chart', 'dashboard_chart_status', '1', 0),
(0, 'dashboard_chart', 'dashboard_chart_width', '6', 0),
(0, 'dashboard_chart_by_country_and_region', 'dashboard_chart_by_country_and_region_sort_order', '5', 0),
(0, 'dashboard_chart_by_country_and_region', 'dashboard_chart_by_country_and_region_status', '0', 0),
(0, 'dashboard_chart_by_country_and_region', 'dashboard_chart_by_country_and_region_width', '6', 0),
(0, 'dashboard_customer', 'dashboard_customer_sort_order', '3', 0),
(0, 'dashboard_customer', 'dashboard_customer_status', '1', 0),
(0, 'dashboard_customer', 'dashboard_customer_width', '3', 0),
(0, 'dashboard_map', 'dashboard_map_sort_order', '5', 0),
(0, 'dashboard_map', 'dashboard_map_status', '1', 0),
(0, 'dashboard_map', 'dashboard_map_width', '6', 0),
(0, 'dashboard_online', 'dashboard_online_sort_order', '4', 0),
(0, 'dashboard_online', 'dashboard_online_status', '1', 0),
(0, 'dashboard_online', 'dashboard_online_width', '3', 0),
(0, 'dashboard_order', 'dashboard_order_sort_order', '1', 0),
(0, 'dashboard_order', 'dashboard_order_status', '1', 0),
(0, 'dashboard_order', 'dashboard_order_width', '3', 0),
(0, 'dashboard_recent', 'dashboard_recent_sort_order', '8', 0),
(0, 'dashboard_recent', 'dashboard_recent_status', '1', 0),
(0, 'dashboard_recent', 'dashboard_recent_width', '8', 0),
(0, 'dashboard_sale', 'dashboard_sale_sort_order', '2', 0),
(0, 'dashboard_sale', 'dashboard_sale_status', '1', 0),
(0, 'dashboard_sale', 'dashboard_sale_width', '3', 0),
(0, 'developer', 'developer_sass', '1', 0),
(0, 'developer', 'developer_theme', '1', 0),
(0, 'module_account', 'module_account_status', '1', 0),
(0, 'module_blog_category', 'module_blog_category_status', '1', 0),
(0, 'module_category', 'module_category_status', '1', 0),
(0, 'payment_cod', 'payment_cod_geo_zone_id', '0', 0),
(0, 'payment_cod', 'payment_cod_order_status_id', '1', 0),
(0, 'payment_cod', 'payment_cod_sort_order', '5', 0),
(0, 'payment_cod', 'payment_cod_status', '1', 0),
(0, 'payment_cod', 'payment_cod_total', '0.01', 0),
(0, 'payment_free_checkout', 'payment_free_checkout_order_status_id', '1', 0),
(0, 'payment_free_checkout', 'payment_free_checkout_sort_order', '1', 0),
(0, 'payment_free_checkout', 'payment_free_checkout_status', '1', 0),
(0, 'report_customer_activity', 'report_customer_activity_sort_order', '1', 0),
(0, 'report_customer_activity', 'report_customer_activity_status', '1', 0),
(0, 'report_customer_order', 'report_customer_order_sort_order', '2', 0),
(0, 'report_customer_order', 'report_customer_order_status', '1', 0),
(0, 'report_customer_reward', 'report_customer_reward_sort_order', '3', 0),
(0, 'report_customer_reward', 'report_customer_reward_status', '1', 0),
(0, 'report_customer_search', 'report_customer_search_sort_order', '3', 0),
(0, 'report_customer_search', 'report_customer_search_status', '1', 0),
(0, 'report_customer_transaction', 'report_customer_transaction_status', '1', 0),
(0, 'report_customer_transaction', 'report_customer_transaction_status_sort_order', '4', 0),
(0, 'report_marketing', 'report_marketing_sort_order', '12', 0),
(0, 'report_marketing', 'report_marketing_status', '1', 0),
(0, 'report_product_purchased', 'report_product_purchased_sort_order', '11', 0),
(0, 'report_product_purchased', 'report_product_purchased_status', '1', 0),
(0, 'report_product_viewed', 'report_product_viewed_sort_order', '10', 0),
(0, 'report_product_viewed', 'report_product_viewed_status', '1', 0),
(0, 'report_sale_coupon', 'report_sale_coupon_sort_order', '9', 0),
(0, 'report_sale_coupon', 'report_sale_coupon_status', '1', 0),
(0, 'report_sale_order', 'report_sale_order_sort_order', '8', 0),
(0, 'report_sale_order', 'report_sale_order_status', '1', 0),
(0, 'report_sale_return', 'report_sale_return_sort_order', '7', 0),
(0, 'report_sale_return', 'report_sale_return_status', '1', 0),
(0, 'report_sale_shipping', 'report_sale_shipping_sort_order', '6', 0),
(0, 'report_sale_shipping', 'report_sale_shipping_status', '1', 0),
(0, 'report_sale_tax', 'report_sale_tax_sort_order', '5', 0),
(0, 'report_sale_tax', 'report_sale_tax_status', '1', 0),
(0, 'shipping_flat', 'shipping_flat_cost', '5.00', 0),
(0, 'shipping_flat', 'shipping_flat_geo_zone_id', '0', 0),
(0, 'shipping_flat', 'shipping_flat_sort_order', '1', 0),
(0, 'shipping_flat', 'shipping_flat_status', '1', 0),
(0, 'shipping_flat', 'shipping_flat_tax_class_id', '0', 0),
(0, 'theme_default', 'theme_default_directory', 'default', 0),
(0, 'theme_default', 'theme_default_image_additional_height', '74', 0),
(0, 'theme_default', 'theme_default_image_additional_width', '74', 0),
(0, 'theme_default', 'theme_default_image_cart_height', '47', 0),
(0, 'theme_default', 'theme_default_image_cart_width', '47', 0),
(0, 'theme_default', 'theme_default_image_category_height', '80', 0),
(0, 'theme_default', 'theme_default_image_category_width', '80', 0),
(0, 'theme_default', 'theme_default_image_compare_height', '90', 0),
(0, 'theme_default', 'theme_default_image_compare_width', '90', 0),
(0, 'theme_default', 'theme_default_image_location_height', '50', 0),
(0, 'theme_default', 'theme_default_image_location_width', '268', 0),
(0, 'theme_default', 'theme_default_image_manufacturer_height', '80', 0),
(0, 'theme_default', 'theme_default_image_manufacturer_width', '80', 0),
(0, 'theme_default', 'theme_default_image_popup_height', '500', 0),
(0, 'theme_default', 'theme_default_image_popup_width', '500', 0),
(0, 'theme_default', 'theme_default_image_product_height', '228', 0),
(0, 'theme_default', 'theme_default_image_product_width', '228', 0),
(0, 'theme_default', 'theme_default_image_related_height', '200', 0),
(0, 'theme_default', 'theme_default_image_related_width', '200', 0),
(0, 'theme_default', 'theme_default_image_thumb_height', '228', 0),
(0, 'theme_default', 'theme_default_image_thumb_width', '228', 0),
(0, 'theme_default', 'theme_default_image_wishlist_height', '47', 0),
(0, 'theme_default', 'theme_default_image_wishlist_width', '47', 0),
(0, 'theme_default', 'theme_default_product_description_length', '100', 0),
(0, 'theme_default', 'theme_default_product_limit', '15', 0),
(0, 'theme_default', 'theme_default_status', '1', 0),
(0, 'total_coupon', 'total_coupon_sort_order', '4', 0),
(0, 'total_coupon', 'total_coupon_status', '0', 0),
(0, 'total_credit', 'total_credit_sort_order', '7', 0),
(0, 'total_credit', 'total_credit_status', '0', 0),
(0, 'total_reward', 'total_reward_sort_order', '2', 0),
(0, 'total_reward', 'total_reward_status', '0', 0),
(0, 'total_shipping', 'total_shipping_estimator', '1', 0),
(0, 'total_shipping', 'total_shipping_sort_order', '3', 0),
(0, 'total_shipping', 'total_shipping_status', '1', 0),
(0, 'total_sub_total', 'total_sub_total_sort_order', '1', 0),
(0, 'total_sub_total', 'total_sub_total_status', '1', 0),
(0, 'total_tax', 'total_tax_sort_order', '5', 0),
(0, 'total_tax', 'total_tax_status', '0', 0),
(0, 'total_total', 'total_total_sort_order', '9', 0),
(0, 'total_total', 'total_total_status', '1', 0),
(0, 'total_voucher', 'total_voucher_sort_order', '8', 0),
(0, 'total_voucher', 'total_voucher_status', '0', 0);



----------------------------------------------------------

--
-- Table structure for table `oc_shipping_courier`
--

DROP TABLE IF EXISTS `oc_shipping_courier`;
CREATE TABLE `oc_shipping_courier` (
  `shipping_courier_id` int(11) NOT NULL,
  `shipping_courier_code` varchar(255) NOT NULL DEFAULT '',
  `shipping_courier_name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`shipping_courier_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_shipping_courier`
--


----------------------------------------------------------

--
-- Table structure for table `oc_statistics`
--

DROP TABLE IF EXISTS `oc_statistics`;
CREATE TABLE `oc_statistics` (
  `statistics_id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(64) NOT NULL,
  `value` decimal(15,4) NOT NULL,
  PRIMARY KEY (`statistics_id`),
  KEY `code` (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_statistics`
--

INSERT INTO `oc_statistics` (`statistics_id`, `code`, `value`) VALUES
(1, 'order_sale', '0.0000'),
(2, 'order_processing', '0.0000'),
(3, 'order_complete', '0.0000'),
(4, 'order_other', '0.0000'),
(5, 'returns', '0.0000'),
(6, 'product', '0.0000'),
(7, 'review', '0.0000');

----------------------------------------------------------

--
-- Table structure for table `oc_stock_status`
--

DROP TABLE IF EXISTS `oc_stock_status`;
CREATE TABLE `oc_stock_status` (
  `stock_status_id` int(11) NOT NULL AUTO_INCREMENT,
  `language_id` int(11) NOT NULL,
  `name` varchar(32) NOT NULL,
  PRIMARY KEY (`stock_status_id`,`language_id`),
  KEY `stock_status_id` (`stock_status_id`),
  KEY `language_id` (`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_stock_status`
--

INSERT INTO `oc_stock_status` (`stock_status_id`, `language_id`, `name`) VALUES
(1, 1, 'Распродано'),
(1, 2, 'Закінчився'),
(1, 3, 'Out Of Stock'),
(2, 1, 'Ожидание 2-3 дня'),
(2, 2, 'Очікування 2-3 дня'),
(2, 3, '2-3 Days'),
(3, 1, 'В наличии'),
(3, 2, 'В наявності'),
(3, 3, 'In Stock'),
(4, 1, 'Предзаказ'),
(4, 2, 'Під замовлення'),
(4, 3, 'Pre-Order'),
(5, 1, 'Ожидается'),
(5, 2, 'Очікується'),
(5, 3, 'Expected');

----------------------------------------------------------

--
-- Table structure for table `oc_store`
--

DROP TABLE IF EXISTS `oc_store`;
CREATE TABLE `oc_store` (
  `store_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `url` varchar(255) NOT NULL,
  `ssl` varchar(255) NOT NULL,
  PRIMARY KEY (`store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_store`
--


----------------------------------------------------------

--
-- Table structure for table `oc_tax_class`
--

DROP TABLE IF EXISTS `oc_tax_class`;
CREATE TABLE `oc_tax_class` (
  `tax_class_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(32) NOT NULL,
  `description` varchar(255) NOT NULL,
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`tax_class_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_tax_class`
--


----------------------------------------------------------

--
-- Table structure for table `oc_tax_rate`
--

DROP TABLE IF EXISTS `oc_tax_rate`;
CREATE TABLE `oc_tax_rate` (
  `tax_rate_id` int(11) NOT NULL AUTO_INCREMENT,
  `geo_zone_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(32) NOT NULL,
  `rate` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `type` char(1) NOT NULL,
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`tax_rate_id`),
  KEY `geo_zone_id` (`geo_zone_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_tax_rate`
--


----------------------------------------------------------

--
-- Table structure for table `oc_tax_rate_to_customer_group`
--

DROP TABLE IF EXISTS `oc_tax_rate_to_customer_group`;
CREATE TABLE `oc_tax_rate_to_customer_group` (
  `tax_rate_id` int(11) NOT NULL,
  `customer_group_id` int(11) NOT NULL,
  PRIMARY KEY (`tax_rate_id`,`customer_group_id`),
  KEY `tax_rate_id` (`tax_rate_id`),
  KEY `customer_group_id` (`customer_group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_tax_rate_to_customer_group`
--


----------------------------------------------------------

--
-- Table structure for table `oc_tax_rule`
--

DROP TABLE IF EXISTS `oc_tax_rule`;
CREATE TABLE `oc_tax_rule` (
  `tax_rule_id` int(11) NOT NULL AUTO_INCREMENT,
  `tax_class_id` int(11) NOT NULL,
  `tax_rate_id` int(11) NOT NULL,
  `based` varchar(10) NOT NULL,
  `priority` int(5) NOT NULL DEFAULT '1',
  PRIMARY KEY (`tax_rule_id`),
  KEY `tax_class_id` (`tax_class_id`),
  KEY `tax_rate_id` (`tax_rate_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_tax_rule`
--


----------------------------------------------------------

--
-- Table structure for table `oc_theme`
--

DROP TABLE IF EXISTS `oc_theme`;
CREATE TABLE `oc_theme` (
  `theme_id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL,
  `theme` varchar(64) NOT NULL,
  `route` varchar(64) NOT NULL,
  `code` mediumtext NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`theme_id`),
  KEY `store_id` (`store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_theme`
--


----------------------------------------------------------

--
-- Table structure for table `oc_translation`
--

DROP TABLE IF EXISTS `oc_translation`;
CREATE TABLE `oc_translation` (
  `translation_id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `route` varchar(64) NOT NULL,
  `key` varchar(64) NOT NULL,
  `value` text NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`translation_id`),
  KEY `store_id` (`store_id`),
  KEY `language_id` (`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_translation`
--


----------------------------------------------------------

--
-- Table structure for table `oc_upload`
--

DROP TABLE IF EXISTS `oc_upload`;
CREATE TABLE `oc_upload` (
  `upload_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`upload_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_upload`
--


----------------------------------------------------------

--
-- Table structure for table `oc_user`
--

DROP TABLE IF EXISTS `oc_user`;
CREATE TABLE `oc_user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_group_id` int(11) NOT NULL,
  `username` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `firstname` varchar(32) NOT NULL,
  `lastname` varchar(32) NOT NULL,
  `email` varchar(96) NOT NULL,
  `image` varchar(255) NOT NULL,
  `code` varchar(40) NOT NULL,
  `ip` varchar(40) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`user_id`),
  KEY `user_group_id` (`user_group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_user`
--


----------------------------------------------------------

--
-- Table structure for table `oc_user_group`
--

DROP TABLE IF EXISTS `oc_user_group`;
CREATE TABLE `oc_user_group` (
  `user_group_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `permission` text NOT NULL,
  PRIMARY KEY (`user_group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_user_group`
--

INSERT INTO `oc_user_group` (`name`, `permission`) VALUES
('Administrator', '{\"access\":[\"blog\\/article\",\"blog\\/category\",\"blog\\/review\",\"blog\\/setting\",\"catalog\\/attribute\",\"catalog\\/attribute_bulk\",\"catalog\\/attribute_group\",\"catalog\\/category\",\"catalog\\/download\",\"catalog\\/filter\",\"catalog\\/information\",\"catalog\\/manufacturer\",\"catalog\\/option\",\"catalog\\/product\",\"catalog\\/recurring\",\"catalog\\/review\",\"common\\/column_left\",\"common\\/developer\",\"common\\/filemanager\",\"common\\/profile\",\"common\\/security\",\"customer\\/custom_field\",\"customer\\/customer\",\"customer\\/customer_approval\",\"customer\\/customer_group\",\"design\\/banner\",\"design\\/layout\",\"design\\/seo_url\",\"design\\/theme\",\"design\\/translation\",\"event\\/currency\",\"event\\/language\",\"event\\/statistics\",\"event\\/theme\",\"extension\\/advertise\\/google\",\"extension\\/analytics\\/google\",\"extension\\/captcha\\/basic\",\"extension\\/captcha\\/google\",\"extension\\/currency\\/cbr\",\"extension\\/currency\\/ecb\",\"extension\\/currency\\/nbu\",\"extension\\/currency\\/privatbank\",\"extension\\/dashboard\\/activity\",\"extension\\/dashboard\\/chart\",\"extension\\/dashboard\\/chart_by_country_and_region\",\"extension\\/dashboard\\/customer\",\"extension\\/dashboard\\/map\",\"extension\\/dashboard\\/online\",\"extension\\/dashboard\\/order\",\"extension\\/dashboard\\/recent\",\"extension\\/dashboard\\/sale\",\"extension\\/extension\\/analytics\",\"extension\\/extension\\/captcha\",\"extension\\/extension\\/currency\",\"extension\\/extension\\/dashboard\",\"extension\\/extension\\/feed\",\"extension\\/extension\\/fraud\",\"extension\\/extension\\/menu\",\"extension\\/extension\\/module\",\"extension\\/extension\\/payment\",\"extension\\/extension\\/report\",\"extension\\/extension\\/shipping\",\"extension\\/extension\\/theme\",\"extension\\/extension\\/total\",\"extension\\/feed\\/blog_sitemap\",\"extension\\/feed\\/google_base\",\"extension\\/feed\\/google_sitemap\",\"extension\\/feed\\/unisender\",\"extension\\/feed\\/yandex_market\",\"extension\\/fraud\\/ip\",\"extension\\/module\\/account\",\"extension\\/module\\/banner\",\"extension\\/module\\/bestseller\",\"extension\\/module\\/blog_category\",\"extension\\/module\\/blog_featured\",\"extension\\/module\\/blog_latest\",\"extension\\/module\\/carousel\",\"extension\\/module\\/category\",\"extension\\/module\\/featured\",\"extension\\/module\\/featured_article\",\"extension\\/module\\/featured_product\",\"extension\\/module\\/filter\",\"extension\\/module\\/google_hangouts\",\"extension\\/module\\/html\",\"extension\\/module\\/information\",\"extension\\/module\\/latest\",\"extension\\/module\\/paypal_smart_button\",\"extension\\/module\\/slideshow\",\"extension\\/module\\/special\",\"extension\\/module\\/store\",\"extension\\/payment\\/bank_transfer\",\"extension\\/payment\\/cheque\",\"extension\\/payment\\/cod\",\"extension\\/payment\\/free_checkout\",\"extension\\/payment\\/liqpay\",\"extension\\/payment\\/paypal\",\"extension\\/payment\\/skrill\",\"extension\\/payment\\/twocheckout_api\",\"extension\\/payment\\/twocheckout_cplus\",\"extension\\/payment\\/twocheckout_inline\",\"extension\\/report\\/customer_activity\",\"extension\\/report\\/customer_order\",\"extension\\/report\\/customer_reward\",\"extension\\/report\\/customer_search\",\"extension\\/report\\/customer_transaction\",\"extension\\/report\\/marketing\",\"extension\\/report\\/product_purchased\",\"extension\\/report\\/product_viewed\",\"extension\\/report\\/sale_coupon\",\"extension\\/report\\/sale_order\",\"extension\\/report\\/sale_return\",\"extension\\/report\\/sale_shipping\",\"extension\\/report\\/sale_tax\",\"extension\\/shipping\\/flat\",\"extension\\/shipping\\/free\",\"extension\\/shipping\\/item\",\"extension\\/shipping\\/pickup\",\"extension\\/shipping\\/weight\",\"extension\\/theme\\/default\",\"extension\\/total\\/coupon\",\"extension\\/total\\/credit\",\"extension\\/total\\/handling\",\"extension\\/total\\/low_order_fee\",\"extension\\/total\\/reward\",\"extension\\/total\\/shipping\",\"extension\\/total\\/sub_total\",\"extension\\/total\\/tax\",\"extension\\/total\\/total\",\"extension\\/total\\/voucher\",\"localisation\\/country\",\"localisation\\/currency\",\"localisation\\/geo_zone\",\"localisation\\/language\",\"localisation\\/length_class\",\"localisation\\/location\",\"localisation\\/order_status\",\"localisation\\/return_action\",\"localisation\\/return_reason\",\"localisation\\/return_status\",\"localisation\\/stock_status\",\"localisation\\/tax_class\",\"localisation\\/tax_rate\",\"localisation\\/weight_class\",\"localisation\\/zone\",\"mail\\/affiliate\",\"mail\\/customer\",\"mail\\/forgotten\",\"mail\\/return\",\"mail\\/reward\",\"mail\\/transaction\",\"marketing\\/contact\",\"marketing\\/coupon\",\"marketing\\/marketing\",\"marketplace\\/event\",\"marketplace\\/extension\",\"marketplace\\/install\",\"marketplace\\/installer\",\"marketplace\\/modification\",\"report\\/online\",\"report\\/order_profit\",\"report\\/report\",\"report\\/statistics\",\"sale\\/order\",\"sale\\/recurring\",\"sale\\/return\",\"sale\\/voucher\",\"sale\\/voucher_theme\",\"search\\/search\",\"setting\\/setting\",\"setting\\/store\",\"startup\\/error\",\"startup\\/event\",\"startup\\/login\",\"startup\\/permission\",\"startup\\/router\",\"startup\\/sass\",\"startup\\/startup\",\"tool\\/backup\",\"tool\\/dump_install\",\"tool\\/log\",\"tool\\/svg_preview\",\"tool\\/upload\",\"user\\/api\",\"user\\/user\",\"user\\/user_permission\"],\"modify\":[\"blog\\/article\",\"blog\\/category\",\"blog\\/review\",\"blog\\/setting\",\"catalog\\/attribute\",\"catalog\\/attribute_bulk\",\"catalog\\/attribute_group\",\"catalog\\/category\",\"catalog\\/download\",\"catalog\\/filter\",\"catalog\\/information\",\"catalog\\/manufacturer\",\"catalog\\/option\",\"catalog\\/product\",\"catalog\\/recurring\",\"catalog\\/review\",\"common\\/column_left\",\"common\\/developer\",\"common\\/filemanager\",\"common\\/profile\",\"common\\/security\",\"customer\\/custom_field\",\"customer\\/customer\",\"customer\\/customer_approval\",\"customer\\/customer_group\",\"design\\/banner\",\"design\\/layout\",\"design\\/seo_url\",\"design\\/theme\",\"design\\/translation\",\"event\\/currency\",\"event\\/language\",\"event\\/statistics\",\"event\\/theme\",\"extension\\/advertise\\/google\",\"extension\\/analytics\\/google\",\"extension\\/captcha\\/basic\",\"extension\\/captcha\\/google\",\"extension\\/currency\\/cbr\",\"extension\\/currency\\/ecb\",\"extension\\/currency\\/nbu\",\"extension\\/currency\\/privatbank\",\"extension\\/dashboard\\/activity\",\"extension\\/dashboard\\/chart\",\"extension\\/dashboard\\/chart_by_country_and_region\",\"extension\\/dashboard\\/customer\",\"extension\\/dashboard\\/map\",\"extension\\/dashboard\\/online\",\"extension\\/dashboard\\/order\",\"extension\\/dashboard\\/recent\",\"extension\\/dashboard\\/sale\",\"extension\\/extension\\/analytics\",\"extension\\/extension\\/captcha\",\"extension\\/extension\\/currency\",\"extension\\/extension\\/dashboard\",\"extension\\/extension\\/feed\",\"extension\\/extension\\/fraud\",\"extension\\/extension\\/menu\",\"extension\\/extension\\/module\",\"extension\\/extension\\/payment\",\"extension\\/extension\\/report\",\"extension\\/extension\\/shipping\",\"extension\\/extension\\/theme\",\"extension\\/extension\\/total\",\"extension\\/feed\\/blog_sitemap\",\"extension\\/feed\\/google_base\",\"extension\\/feed\\/google_sitemap\",\"extension\\/feed\\/unisender\",\"extension\\/feed\\/yandex_market\",\"extension\\/fraud\\/ip\",\"extension\\/module\\/account\",\"extension\\/module\\/banner\",\"extension\\/module\\/bestseller\",\"extension\\/module\\/blog_category\",\"extension\\/module\\/blog_featured\",\"extension\\/module\\/blog_latest\",\"extension\\/module\\/carousel\",\"extension\\/module\\/category\",\"extension\\/module\\/featured\",\"extension\\/module\\/featured_article\",\"extension\\/module\\/featured_product\",\"extension\\/module\\/filter\",\"extension\\/module\\/google_hangouts\",\"extension\\/module\\/html\",\"extension\\/module\\/information\",\"extension\\/module\\/latest\",\"extension\\/module\\/paypal_smart_button\",\"extension\\/module\\/slideshow\",\"extension\\/module\\/special\",\"extension\\/module\\/store\",\"extension\\/payment\\/bank_transfer\",\"extension\\/payment\\/cheque\",\"extension\\/payment\\/cod\",\"extension\\/payment\\/free_checkout\",\"extension\\/payment\\/liqpay\",\"extension\\/payment\\/paypal\",\"extension\\/payment\\/skrill\",\"extension\\/payment\\/twocheckout_api\",\"extension\\/payment\\/twocheckout_cplus\",\"extension\\/payment\\/twocheckout_inline\",\"extension\\/report\\/customer_activity\",\"extension\\/report\\/customer_order\",\"extension\\/report\\/customer_reward\",\"extension\\/report\\/customer_search\",\"extension\\/report\\/customer_transaction\",\"extension\\/report\\/marketing\",\"extension\\/report\\/product_purchased\",\"extension\\/report\\/product_viewed\",\"extension\\/report\\/sale_coupon\",\"extension\\/report\\/sale_order\",\"extension\\/report\\/sale_return\",\"extension\\/report\\/sale_shipping\",\"extension\\/report\\/sale_tax\",\"extension\\/shipping\\/flat\",\"extension\\/shipping\\/free\",\"extension\\/shipping\\/item\",\"extension\\/shipping\\/pickup\",\"extension\\/shipping\\/weight\",\"extension\\/theme\\/default\",\"extension\\/total\\/coupon\",\"extension\\/total\\/credit\",\"extension\\/total\\/handling\",\"extension\\/total\\/low_order_fee\",\"extension\\/total\\/reward\",\"extension\\/total\\/shipping\",\"extension\\/total\\/sub_total\",\"extension\\/total\\/tax\",\"extension\\/total\\/total\",\"extension\\/total\\/voucher\",\"localisation\\/country\",\"localisation\\/currency\",\"localisation\\/geo_zone\",\"localisation\\/language\",\"localisation\\/length_class\",\"localisation\\/location\",\"localisation\\/order_status\",\"localisation\\/return_action\",\"localisation\\/return_reason\",\"localisation\\/return_status\",\"localisation\\/stock_status\",\"localisation\\/tax_class\",\"localisation\\/tax_rate\",\"localisation\\/weight_class\",\"localisation\\/zone\",\"mail\\/affiliate\",\"mail\\/customer\",\"mail\\/forgotten\",\"mail\\/return\",\"mail\\/reward\",\"mail\\/transaction\",\"marketing\\/contact\",\"marketing\\/coupon\",\"marketing\\/marketing\",\"marketplace\\/event\",\"marketplace\\/extension\",\"marketplace\\/install\",\"marketplace\\/installer\",\"marketplace\\/modification\",\"report\\/online\",\"report\\/order_profit\",\"report\\/report\",\"report\\/statistics\",\"sale\\/order\",\"sale\\/recurring\",\"sale\\/return\",\"sale\\/voucher\",\"sale\\/voucher_theme\",\"search\\/search\",\"setting\\/setting\",\"setting\\/store\",\"startup\\/error\",\"startup\\/event\",\"startup\\/login\",\"startup\\/permission\",\"startup\\/router\",\"startup\\/sass\",\"startup\\/startup\",\"tool\\/backup\",\"tool\\/dump_install\",\"tool\\/log\",\"tool\\/svg_preview\",\"tool\\/upload\",\"user\\/api\",\"user\\/user\",\"user\\/user_permission\"]}'),
('Demonstration', '');

----------------------------------------------------------

--
-- Table structure for table `oc_voucher`
--

DROP TABLE IF EXISTS `oc_voucher`;
CREATE TABLE `oc_voucher` (
  `voucher_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `code` varchar(10) NOT NULL,
  `from_name` varchar(64) NOT NULL,
  `from_email` varchar(96) NOT NULL,
  `to_name` varchar(64) NOT NULL,
  `to_email` varchar(96) NOT NULL,
  `voucher_theme_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `amount` decimal(15,4) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`voucher_id`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_voucher`
--


----------------------------------------------------------

--
-- Table structure for table `oc_voucher_history`
--

DROP TABLE IF EXISTS `oc_voucher_history`;
CREATE TABLE `oc_voucher_history` (
  `voucher_history_id` int(11) NOT NULL AUTO_INCREMENT,
  `voucher_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `amount` decimal(15,4) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`voucher_history_id`),
  KEY `voucher_id` (`voucher_id`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_voucher_history`
--


----------------------------------------------------------

--
-- Table structure for table `oc_voucher_theme`
--

DROP TABLE IF EXISTS `oc_voucher_theme`;
CREATE TABLE `oc_voucher_theme` (
  `voucher_theme_id` int(11) NOT NULL AUTO_INCREMENT,
  `image` varchar(255) NOT NULL,
  PRIMARY KEY (`voucher_theme_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_voucher_theme`
--

INSERT INTO `oc_voucher_theme` (`voucher_theme_id`, `image`) VALUES
(6, 'catalog/demo/apple_logo.jpg'),
(7, 'catalog/demo/gift-voucher-birthday.jpg'),
(8, 'catalog/demo/canon_eos_5d_2.jpg');

----------------------------------------------------------

--
-- Table structure for table `oc_voucher_theme_description`
--

DROP TABLE IF EXISTS `oc_voucher_theme_description`;
CREATE TABLE `oc_voucher_theme_description` (
  `voucher_theme_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `name` varchar(32) NOT NULL,
  PRIMARY KEY (`voucher_theme_id`,`language_id`),
  KEY `voucher_theme_id` (`voucher_theme_id`),
  KEY `language_id` (`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_voucher_theme_description`
--

INSERT INTO `oc_voucher_theme_description` (`voucher_theme_id`, `language_id`, `name`) VALUES
(1, 1, 'Рождество'),
(1, 2, 'Різдво'),
(1, 3, 'Christmas'),
(2, 1, 'День рождения'),
(2, 2, 'День народження'),
(2, 3, 'Birthday'),
(3, 1, 'Общее'),
(3, 2, 'Загальне'),
(3, 3, 'General');

----------------------------------------------------------

--
-- Table structure for table `oc_weight_class`
--

DROP TABLE IF EXISTS `oc_weight_class`;
CREATE TABLE `oc_weight_class` (
  `weight_class_id` int(11) NOT NULL AUTO_INCREMENT,
  `value` decimal(15,8) NOT NULL DEFAULT '0.00000000',
  PRIMARY KEY (`weight_class_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_weight_class`
--

INSERT INTO `oc_weight_class` (`weight_class_id`, `value`) VALUES
(1, '1000.00000000'),
(2, '1.00000000');

----------------------------------------------------------

--
-- Table structure for table `oc_weight_class_description`
--

DROP TABLE IF EXISTS `oc_weight_class_description`;
CREATE TABLE `oc_weight_class_description` (
  `weight_class_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `title` varchar(32) NOT NULL,
  `unit` varchar(4) NOT NULL,
  PRIMARY KEY (`weight_class_id`,`language_id`),
  KEY `weight_class_id` (`weight_class_id`),
  KEY `language_id` (`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_weight_class_description`
--

INSERT INTO `oc_weight_class_description` (`weight_class_id`, `language_id`, `title`, `unit`) VALUES
(1, 1, 'Грамм', 'гр'),
(1, 2, 'Грам', 'г'),
(1, 3, 'Gram', 'g'),
(2, 1, 'Килограмм', 'кг'),
(2, 2, 'Кілограм', 'кг'),
(2, 3, 'Kilogram', 'kg');

----------------------------------------------------------

--
-- Table structure for table `oc_zone`
--

DROP TABLE IF EXISTS `oc_zone`;
CREATE TABLE `oc_zone` (
  `zone_id` int(11) NOT NULL AUTO_INCREMENT,
  `country_id` int(11) NOT NULL,
  `name` varchar(128) NOT NULL,
  `code` varchar(32) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`zone_id`),
  KEY `country_id` (`country_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_zone`
--

INSERT INTO `oc_zone` (`country_id`, `name`, `code`, `status`) VALUES
(11, 'Арагацотн', 'AGT', 1),
(11, 'Арарат', 'ARR', 1),
(11, 'Армавир', 'ARM', 1),
(11, 'Вайоц Дзор', 'VAY', 1),
(11, 'Гегаркуник', 'GEG', 1),
(11, 'Ереван', 'YER', 1),
(11, 'Котайк', 'KOT', 1),
(11, 'Лори', 'LOR', 1),
(11, 'Сюник', 'SYU', 1),
(11, 'Тавуш', 'TAV', 1),
(11, 'Ширак', 'SHI', 1),
(15, 'Abseron', 'ABS', 1),
(15, 'AgcabAdi', 'AGC', 1),
(15, 'Agdam', 'AGM', 1),
(15, 'Agdas', 'AGS', 1),
(15, 'Agstafa', 'AGA', 1),
(15, 'Agsu', 'AGU', 1),
(15, 'Ali Bayramli', 'AB', 1),
(15, 'Astara', 'AST', 1),
(15, 'BArdA', 'BAR', 1),
(15, 'BabAk', 'BAB', 1),
(15, 'Baki', 'BA', 1),
(15, 'BalakAn', 'BAL', 1),
(15, 'Beylaqan', 'BEY', 1),
(15, 'Bilasuvar', 'BIL', 1),
(15, 'Cabrayil', 'CAB', 1),
(15, 'Calilabab', 'CAL', 1),
(15, 'Culfa', 'CUL', 1),
(15, 'Daskasan', 'DAS', 1),
(15, 'Davaci', 'DAV', 1),
(15, 'Fuzuli', 'FUZ', 1),
(15, 'Gadabay', 'GAD', 1),
(15, 'Ganca', 'GA', 1),
(15, 'Goranboy', 'GOR', 1),
(15, 'Goycay', 'GOY', 1),
(15, 'Haciqabul', 'HAC', 1),
(15, 'Imisli', 'IMI', 1),
(15, 'Ismayilli', 'ISM', 1),
(15, 'Kalbacar', 'KAL', 1),
(15, 'Kurdamir', 'KUR', 1),
(15, 'Lacin', 'LAC', 1),
(15, 'Lankaran', 'LA', 1),
(15, 'Lankaran', 'LAN', 1),
(15, 'Lerik', 'LER', 1),
(15, 'Masalli', 'MAS', 1),
(15, 'Mingacevir', 'MI', 1),
(15, 'Naftalan', 'NA', 1),
(15, 'Naxcivan', 'NX', 1),
(15, 'Neftcala', 'NEF', 1),
(15, 'Oguz', 'OGU', 1),
(15, 'Ordubad', 'ORD', 1),
(15, 'Qabala', 'QAB', 1),
(15, 'Qax', 'QAX', 1),
(15, 'Qazax', 'QAZ', 1),
(15, 'Qobustan', 'QOB', 1),
(15, 'Quba', 'QBA', 1),
(15, 'Qubadli', 'QBI', 1),
(15, 'Qusar', 'QUS', 1),
(15, 'Saatli', 'SAT', 1),
(15, 'Sabirabad', 'SAB', 1),
(15, 'Sadarak', 'SAD', 1),
(15, 'Sahbuz', 'SAH', 1),
(15, 'Saki', 'SA', 1),
(15, 'Saki', 'SAK', 1),
(15, 'Salyan', 'SAL', 1),
(15, 'Samaxi', 'SMI', 1),
(15, 'Samkir', 'SKR', 1),
(15, 'Samux', 'SMX', 1),
(15, 'Sarur', 'SAR', 1),
(15, 'Siyazan', 'SIY', 1),
(15, 'Sumqayit', 'SM', 1),
(15, 'Susa', 'SUS', 1),
(15, 'Susa', 'SS', 1),
(15, 'Tartar', 'TAR', 1),
(15, 'Tovuz', 'TOV', 1),
(15, 'Ucar', 'UCA', 1),
(15, 'Xacmaz', 'XAC', 1),
(15, 'Xankandi', 'XA', 1),
(15, 'Xanlar', 'XAN', 1),
(15, 'Xizi', 'XIZ', 1),
(15, 'Xocali', 'XCI', 1),
(15, 'Xocavand', 'XVD', 1),
(15, 'Yardimli', 'YAR', 1),
(15, 'Yevlax', 'YEV', 1),
(15, 'Zangilan', 'ZAN', 1),
(15, 'Zaqatala', 'ZAQ', 1),
(15, 'Zardab', 'ZAR', 1),
(20, 'Брест', 'BR', 1),
(20, 'Витебск', 'VI', 1),
(20, 'Гомель', 'HO', 1),
(20, 'Гродно', 'HR', 1),
(20, 'Минск', 'HM', 1),
(20, 'Минская область', 'MI', 1),
(20, 'Могилев', 'MA', 1),
(44, 'Anhui', 'AN', 1),
(44, 'Beijing', 'BE', 1),
(44, 'Chongqing', 'CH', 1),
(44, 'Fujian', 'FU', 1),
(44, 'Gansu', 'GA', 1),
(44, 'Guangdong', 'GU', 1),
(44, 'Guangxi', 'GX', 1),
(44, 'Guizhou', 'GZ', 1),
(44, 'Hainan', 'HA', 1),
(44, 'Hebei', 'HB', 1),
(44, 'Heilongjiang', 'HL', 1),
(44, 'Henan', 'HE', 1),
(44, 'Hong Kong', 'HK', 1),
(44, 'Hubei', 'HU', 1),
(44, 'Hunan', 'HN', 1),
(44, 'Inner Mongolia', 'IM', 1),
(44, 'Jiangsu', 'JI', 1),
(44, 'Jiangxi', 'JX', 1),
(44, 'Jilin', 'JL', 1),
(44, 'Liaoning', 'LI', 1),
(44, 'Macau', 'MA', 1),
(44, 'Ningxia', 'NI', 1),
(44, 'Qinghai', 'QH', 1),
(44, 'Shaanxi', 'SH', 1),
(44, 'Shandong', 'SA', 1),
(44, 'Shanghai', 'SG', 1),
(44, 'Shanxi', 'SX', 1),
(44, 'Sichuan', 'SI', 1),
(44, 'Tianjin', 'TI', 1),
(44, 'Xinjiang', 'XI', 1),
(44, 'Yunnan', 'YU', 1),
(44, 'Zhejiang', 'ZH', 1),
(53, 'Bjelovarsko-bilogorska', 'BB', 1),
(53, 'Brodsko-posavska', 'BP', 1),
(53, 'Dubrovačko-neretvanska', 'DN', 1),
(53, 'Grad Zagreb', 'GZ', 1),
(53, 'Istarska', 'IS', 1),
(53, 'Karlovačka', 'KA', 1),
(53, 'Koprivničko-križevačka', 'KK', 1),
(53, 'Krapinsko-zagorska', 'KZ', 1),
(53, 'Ličko-senjska', 'LS', 1),
(53, 'Međimurska', 'ME', 1),
(53, 'Osječko-baranjska', 'OB', 1),
(53, 'Požeško-slavonska', 'PS', 1),
(53, 'Primorsko-goranska', 'PG', 1),
(53, 'Sisačko-moslavačka', 'SM', 1),
(53, 'Splitsko-dalmatinska', 'SD', 1),
(53, 'Varaždinska', 'VA', 1),
(53, 'Virovitičko-podravska', 'VP', 1),
(53, 'Vukovarsko-srijemska', 'VS', 1),
(53, 'Zadarska', 'ZA', 1),
(53, 'Zagrebačka', 'ZG', 1),
(53, 'Šibensko-kninska', 'SK', 1),
(56, 'Jihomoravský', 'B', 1),
(56, 'Jihočeský', 'C', 1),
(56, 'Karlovarský', 'K', 1),
(56, 'Královehradecký', 'H', 1),
(56, 'Liberecký', 'L', 1),
(56, 'Moravskoslezský', 'T', 1),
(56, 'Olomoucký', 'M', 1),
(56, 'Pardubický', 'E', 1),
(56, 'Plzeňský', 'P', 1),
(56, 'Praha', 'A', 1),
(56, 'Středočeský', 'S', 1),
(56, 'Vysočina', 'J', 1),
(56, 'Zlínský', 'Z', 1),
(56, 'Ústecký', 'U', 1),
(67, 'Harjumaa (Tallinn)', 'HA', 1),
(67, 'Hiiumaa (Kardla)', 'HI', 1),
(67, 'Ida-Virumaa (Johvi)', 'IV', 1),
(67, 'Jarvamaa (Paide)', 'JA', 1),
(67, 'Jogevamaa (Jogeva)', 'JO', 1),
(67, 'Laane-Virumaa (Rakvere)', 'LV', 1),
(67, 'Laanemaa (Haapsalu)', 'LA', 1),
(67, 'Parnumaa (Parnu)', 'PA', 1),
(67, 'Polvamaa (Polva)', 'PO', 1),
(67, 'Raplamaa (Rapla)', 'RA', 1),
(67, 'Saaremaa (Kuessaare)', 'SA', 1),
(67, 'Tartumaa (Tartu)', 'TA', 1),
(67, 'Valgamaa (Valga)', 'VA', 1),
(67, 'Viljandimaa (Viljandi)', 'VI', 1),
(67, 'Vorumaa (Voru)', 'VO', 1),
(72, 'Ahvenanmaan lääni', 'AL', 1),
(72, 'Etelä-Suomen lääni', 'ES', 1),
(72, 'Itä-Suomen lääni', 'IS', 1),
(72, 'Lapin lääni', 'LA', 1),
(72, 'Länsi-Suomen lääni', 'LS', 1),
(72, 'Oulun lääni', 'OU', 1),
(80, 'Abkhazia', 'AB', 1),
(80, 'Ajaria', 'AJ', 1),
(80, 'Guria', 'GU', 1),
(80, 'Imereti', 'IM', 1),
(80, 'Kakheti', 'KA', 1),
(80, 'Kvemo Kartli', 'KK', 1),
(80, 'Mtskheta-Mtianeti', 'MM', 1),
(80, 'Racha Lechkhumi and Kvemo Svanet', 'RL', 1),
(80, 'Samegrelo-Zemo Svaneti', 'SZ', 1),
(80, 'Samtskhe-Javakheti', 'SJ', 1),
(80, 'Shida Kartli', 'SK', 1),
(80, 'Tbilisi', 'TB', 1),
(109, 'Абайская область', 'AB', 1),
(109, 'Акмолинская область', 'AM', 1),
(109, 'Актюбинская область', 'AQ', 1),
(109, 'Алматинская область', 'AL', 1),
(109, 'Алматы', 'ALA', 1),
(109, 'Астана', 'AST', 1),
(109, 'Атырауская область', 'AT', 1),
(109, 'Байконур', 'BY', 1),
(109, 'Восточно-Казахстанская область', 'SH', 1),
(109, 'Жамбылская область', 'ZH', 1),
(109, 'Жетысуская область', 'ZE', 1),
(109, 'Западно-Казахстанская область', 'BA', 1),
(109, 'Карагандинская область', 'QA', 1),
(109, 'Костанайская область', 'QO', 1),
(109, 'Кызылординская область', 'QY', 1),
(109, 'Мангистауская область', 'MA', 1),
(109, 'Павлодарская область', 'PA', 1),
(109, 'Северо-Казахстанская область', 'SO', 1),
(109, 'Туркестанская область', 'TU', 1),
(109, 'Улытауская область', 'UL', 1),
(109, 'Шымкент', 'SHY', 1),
(109, 'Южно-Казахстанская область', 'ON', 1),
(115, 'Batken', 'B', 1),
(115, 'Bishkek', 'GB', 1),
(115, 'Chu', 'C', 1),
(115, 'Jalal-Abad', 'J', 1),
(115, 'Naryn', 'N', 1),
(115, 'Osh', 'O', 1),
(115, 'Talas', 'T', 1),
(115, 'Ysyk-Kol', 'Y', 1),
(117, 'Ainaži, Salacgrīvas novads', '0661405', 1),
(117, 'Aizkraukle, Aizkraukles novads', '0320201', 1),
(117, 'Aizkraukles novads', '0320200', 1),
(117, 'Aizpute, Aizputes novads', '0640605', 1),
(117, 'Aizputes novads', '0640600', 1),
(117, 'Aknīste, Aknīstes novads', '0560805', 1),
(117, 'Aknīstes novads', '0560800', 1),
(117, 'Aloja, Alojas novads', '0661007', 1),
(117, 'Alojas novads', '0661000', 1),
(117, 'Alsungas novads', '0624200', 1),
(117, 'Alūksne, Alūksnes novads', '0360201', 1),
(117, 'Alūksnes novads', '0360200', 1),
(117, 'Amatas novads', '0424701', 1),
(117, 'Ape, Apes novads', '0360805', 1),
(117, 'Apes novads', '0360800', 1),
(117, 'Auce, Auces novads', '0460805', 1),
(117, 'Auces novads', '0460800', 1),
(117, 'Babītes novads', '0804900', 1),
(117, 'Baldone, Baldones novads', '0800605', 1),
(117, 'Baldones novads', '0800600', 1),
(117, 'Baloži, Ķekavas novads', '0800807', 1),
(117, 'Baltinavas novads', '0384400', 1),
(117, 'Balvi, Balvu novads', '0380201', 1),
(117, 'Balvu novads', '0380200', 1),
(117, 'Bauska, Bauskas novads', '0400201', 1),
(117, 'Bauskas novads', '0400200', 1),
(117, 'Beverīnas novads', '0964700', 1),
(117, 'Brocēni, Brocēnu novads', '0840605', 1),
(117, 'Brocēnu novads', '0840601', 1),
(117, 'Burtnieku novads', '0967101', 1),
(117, 'Carnikavas novads', '0805200', 1),
(117, 'Cesvaine, Cesvaines novads', '0700807', 1),
(117, 'Cesvaines novads', '0700800', 1),
(117, 'Ciblas novads', '0684901', 1),
(117, 'Cēsis, Cēsu novads', '0420201', 1),
(117, 'Cēsu novads', '0420200', 1),
(117, 'Dagda, Dagdas novads', '0601009', 1),
(117, 'Dagdas novads', '0601000', 1),
(117, 'Daugavpils novads', '0440200', 1),
(117, 'Daugavpils', '0050000', 1),
(117, 'Dobele, Dobeles novads', '0460201', 1),
(117, 'Dobeles novads', '0460200', 1),
(117, 'Dundagas novads', '0885100', 1),
(117, 'Durbe, Durbes novads', '0640807', 1),
(117, 'Durbes novads', '0640801', 1),
(117, 'Engures novads', '0905100', 1),
(117, 'Garkalnes novads', '0806000', 1),
(117, 'Grobiņa, Grobiņas novads', '0641009', 1),
(117, 'Grobiņas novads', '0641000', 1),
(117, 'Gulbene, Gulbenes novads', '0500201', 1),
(117, 'Gulbenes novads', '0500200', 1),
(117, 'Iecavas novads', '0406400', 1),
(117, 'Ikšķile, Ikšķiles novads', '0740605', 1),
(117, 'Ikšķiles novads', '0740600', 1),
(117, 'Ilūkste, Ilūkstes novads', '0440807', 1),
(117, 'Ilūkstes novads', '0440801', 1),
(117, 'Inčukalna novads', '0801800', 1),
(117, 'Jaunjelgava, Jaunjelgavas novads', '0321007', 1),
(117, 'Jaunjelgavas novads', '0321000', 1),
(117, 'Jaunpiebalgas novads', '0425700', 1),
(117, 'Jaunpils novads', '0905700', 1),
(117, 'Jelgava', '0090000', 1),
(117, 'Jelgavas novads', '0540200', 1),
(117, 'Jēkabpils novads', '0560200', 1),
(117, 'Jēkabpils', '0110000', 1),
(117, 'Jūrmala', '0130000', 1),
(117, 'Kalnciems, Jelgavas novads', '0540211', 1),
(117, 'Kandava, Kandavas novads', '0901211', 1),
(117, 'Kandavas novads', '0901201', 1),
(117, 'Kocēnu novads ,bij. Valmieras)', '0960200', 1),
(117, 'Kokneses novads', '0326100', 1),
(117, 'Krimuldas novads', '0806900', 1),
(117, 'Krustpils novads', '0566900', 1),
(117, 'Krāslava, Krāslavas novads', '0600201', 1),
(117, 'Krāslavas novads', '0600202', 1),
(117, 'Kuldīga, Kuldīgas novads', '0620201', 1),
(117, 'Kuldīgas novads', '0620200', 1),
(117, 'Kārsava, Kārsavas novads', '0681009', 1),
(117, 'Kārsavas novads', '0681000', 1),
(117, 'Lielvārde, Lielvārdes novads', '0741413', 1),
(117, 'Lielvārdes novads', '0741401', 1),
(117, 'Liepāja', '0170000', 1),
(117, 'Limbaži, Limbažu novads', '0660201', 1),
(117, 'Limbažu novads', '0660200', 1),
(117, 'Lubāna, Lubānas novads', '0701413', 1),
(117, 'Lubānas novads', '0701400', 1),
(117, 'Ludza, Ludzas novads', '0680201', 1),
(117, 'Ludzas novads', '0680200', 1),
(117, 'Līgatne, Līgatnes novads', '0421211', 1),
(117, 'Līgatnes novads', '0421200', 1),
(117, 'Līvāni, Līvānu novads', '0761211', 1),
(117, 'Līvānu novads', '0761201', 1),
(117, 'Madona, Madonas novads', '0700201', 1),
(117, 'Madonas novads', '0700200', 1),
(117, 'Mazsalaca, Mazsalacas novads', '0961011', 1),
(117, 'Mazsalacas novads', '0961000', 1),
(117, 'Mālpils novads', '0807400', 1),
(117, 'Mārupes novads', '0807600', 1),
(117, 'Mērsraga novads', '0887600', 1),
(117, 'Naukšēnu novads', '0967300', 1),
(117, 'Neretas novads', '0327100', 1),
(117, 'Nīcas novads', '0647900', 1),
(117, 'Ogre, Ogres novads', '0740201', 1),
(117, 'Ogres novads', '0740202', 1),
(117, 'Olaine, Olaines novads', '0801009', 1),
(117, 'Olaines novads', '0801000', 1),
(117, 'Ozolnieku novads', '0546701', 1),
(117, 'Piltene, Ventspils novads', '0980213', 1),
(117, 'Preiļi, Preiļu novads', '0760201', 1),
(117, 'Preiļu novads', '0760202', 1),
(117, 'Priekule, Priekules novads', '0641615', 1),
(117, 'Priekules novads', '0641600', 1),
(117, 'Priekuļu novads', '0427300', 1),
(117, 'Pārgaujas novads', '0427500', 1),
(117, 'Pāvilosta, Pāvilostas novads', '0641413', 1),
(117, 'Pāvilostas novads', '0641401', 1),
(117, 'Pļaviņas, Pļaviņu novads', '0321413', 1),
(117, 'Pļaviņu novads', '0321400', 1),
(117, 'Raunas novads', '0427700', 1),
(117, 'Riebiņu novads', '0766300', 1),
(117, 'Rojas novads', '0888300', 1),
(117, 'Ropažu novads', '0808400', 1),
(117, 'Rucavas novads', '0648500', 1),
(117, 'Rugāju novads', '0387500', 1),
(117, 'Rundāles novads', '0407700', 1),
(117, 'Rēzekne', '0210000', 1),
(117, 'Rēzeknes novads', '0780200', 1),
(117, 'Rīga', '0010000', 1),
(117, 'Rūjiena, Rūjienas novads', '0961615', 1),
(117, 'Rūjienas novads', '0961600', 1),
(117, 'Sabile, Talsu novads', '0880213', 1),
(117, 'Salacgrīva, Salacgrīvas novads', '0661415', 1),
(117, 'Salacgrīvas novads', '0661400', 1),
(117, 'Salas novads', '0568700', 1),
(117, 'Salaspils, Salaspils novads', '0801211', 1),
(117, 'Salaspils novads', '0801200', 1),
(117, 'Saldus, Saldus novads', '0840201', 1),
(117, 'Saldus novads', '0840200', 1),
(117, 'Saulkrasti, Saulkrastu novads', '0801413', 1),
(117, 'Saulkrastu novads', '0801400', 1),
(117, 'Seda, Strenču novads', '0941813', 1),
(117, 'Sigulda, Siguldas novads', '0801615', 1),
(117, 'Siguldas novads', '0801601', 1),
(117, 'Skrunda, Skrundas novads', '0621209', 1),
(117, 'Skrundas novads', '0621200', 1),
(117, 'Skrīveru novads', '0328200', 1),
(117, 'Smiltene, Smiltenes novads', '0941615', 1),
(117, 'Smiltenes novads', '0941600', 1),
(117, 'Staicele, Alojas novads', '0661017', 1),
(117, 'Stende, Talsu novads', '0880215', 1),
(117, 'Stopiņu novads', '0809600', 1),
(117, 'Strenči, Strenču novads', '0941817', 1),
(117, 'Strenču novads', '0941800', 1),
(117, 'Subate, Ilūkstes novads', '0440815', 1),
(117, 'Sējas novads', '0809200', 1),
(117, 'Talsi, Talsu novads', '0880201', 1),
(117, 'Talsu novads', '0880200', 1),
(117, 'Tukuma novads', '0900200', 1),
(117, 'Tukums, Tukuma novads', '0900201', 1),
(117, 'Tērvetes novads', '0468900', 1),
(117, 'Vaiņodes novads', '0649300', 1),
(117, 'Valdemārpils, Talsu novads', '0880217', 1),
(117, 'Valka, Valkas novads', '0940201', 1),
(117, 'Valkas novads', '0940200', 1),
(117, 'Valmiera', '0250000', 1),
(117, 'Vangaži, Inčukalna novads', '0801817', 1),
(117, 'Varakļāni, Varakļānu novads', '0701817', 1),
(117, 'Varakļānu novads', '0701800', 1),
(117, 'Vecpiebalgas novads', '0429300', 1),
(117, 'Vecumnieku novads', '0409500', 1),
(117, 'Ventspils novads', '0980200', 1),
(117, 'Ventspils', '0270000', 1),
(117, 'Viesīte, Viesītes novads', '0561815', 1),
(117, 'Viesītes novads', '0561800', 1),
(117, 'Viļaka, Viļakas novads', '0381615', 1),
(117, 'Viļakas novads', '0381600', 1),
(117, 'Viļāni, Viļānu novads', '0781817', 1),
(117, 'Viļānu novads', '0781800', 1),
(117, 'Vārkavas novads', '0769101', 1),
(117, 'Zilupe, Zilupes novads', '0681817', 1),
(117, 'Zilupes novads', '0681801', 1),
(117, 'Ādažu novads', '0804400', 1),
(117, 'Ērgļu novads', '0705500', 1),
(117, 'Ķeguma novads', '0741001', 1),
(117, 'Ķegums, Ķeguma novads', '0741009', 1),
(117, 'Ķekavas novads', '0800800', 1),
(123, 'Alytus', 'AL', 1),
(123, 'Kaunas', 'KA', 1),
(123, 'Klaipeda', 'KL', 1),
(123, 'Marijampole', 'MA', 1),
(123, 'Panevezys', 'PA', 1),
(123, 'Siauliai', 'SI', 1),
(123, 'Taurage', 'TA', 1),
(123, 'Telsiai', 'TE', 1),
(123, 'Utena', 'UT', 1),
(140, 'Balti', 'BA', 1),
(140, 'Cahul', 'CA', 1),
(140, 'Chisinau', 'CU', 1),
(140, 'Edinet', 'ED', 1),
(140, 'Gagauzia', 'GA', 1),
(140, 'Lapusna', 'LA', 1),
(140, 'Orhei', 'OR', 1),
(140, 'Soroca', 'SO', 1),
(140, 'St‚nga Nistrului', 'SN', 1),
(140, 'Tighina', 'TI', 1),
(140, 'Ungheni', 'UN', 1),
(176, 'Алтайский край', 'ALT', 1),
(176, 'Амурская область', 'AMU', 1),
(176, 'Архангельская область', 'ARK', 1),
(176, 'Астраханская область', 'AST', 1),
(176, 'Белгородская область', 'BEL', 1),
(176, 'Брянская область', 'BRY', 1),
(176, 'Владимирская область', 'VLA', 1),
(176, 'Волгоградская область', 'VGG', 1),
(176, 'Вологодская область', 'VLG', 1),
(176, 'Воронежская область', 'VOR', 1),
(176, 'Еврейская АО', 'YEV', 1),
(176, 'Забайкальский край', 'ZAB', 1),
(176, 'Ивановская область', 'IVA', 1),
(176, 'Иркутская область', 'IRK', 1),
(176, 'Калининградская область', 'KGD', 1),
(176, 'Калужская область', 'KLU', 1),
(176, 'Камчатский край', 'KAM', 1),
(176, 'Карачаево-Черкесия', 'KC', 1),
(176, 'Кемеровская область', 'KEM', 1),
(176, 'Кировская область', 'KIR', 1),
(176, 'Коми-Пермяцкий АО', 'KOP', 1),
(176, 'Корякский АО', 'KOR', 1),
(176, 'Костромская область', 'KOS', 1),
(176, 'Краснодарский край', 'KDA', 1),
(176, 'Красноярский край', 'KYA', 1),
(176, 'Курганская область', 'KGN', 1),
(176, 'Курская область', 'KRS', 1),
(176, 'Ленинградская область', 'LEN', 1),
(176, 'Липецкая область', 'LIP', 1),
(176, 'Магаданская область', 'MAG', 1),
(176, 'Москва', 'MOW', 1),
(176, 'Московская область', 'MOS', 1),
(176, 'Мурманская область', 'MUR', 1),
(176, 'Ненецкий АО', 'NEN', 1),
(176, 'Нижегородская область', 'NIZ', 1),
(176, 'Новгородская область', 'NGR', 1),
(176, 'Новосибирская область', 'NVS', 1),
(176, 'Омская область', 'OMS', 1),
(176, 'Оренбургская область', 'ORE', 1),
(176, 'Орловская область', 'ORL', 1),
(176, 'Пензенская область', 'PNZ', 1),
(176, 'Пермский край', 'PER', 1),
(176, 'Приморский край', 'PRI', 1),
(176, 'Псковская область', 'PSK', 1),
(176, 'Республика  Саха / Якутия', 'SA', 1),
(176, 'Республика Адыгея', 'AD', 1),
(176, 'Республика Алтай', 'AL', 1),
(176, 'Республика Башкортостан', 'BA', 1),
(176, 'Республика Бурятия', 'BU', 1),
(176, 'Республика Дагестан', 'DA', 1),
(176, 'Республика Ингушетия', 'IN', 1),
(176, 'Республика Кабардино-Балкария', 'KB', 1),
(176, 'Республика Калмыкия', 'KL', 1),
(176, 'Республика Карелия', 'KR', 1),
(176, 'Республика Коми', 'KO', 1),
(176, 'Республика Марий Эл', 'ME', 1),
(176, 'Республика Мордовия', 'MO', 1),
(176, 'Республика Северная Осетия', 'SE', 1),
(176, 'Республика Татарстан', 'TA', 1),
(176, 'Республика Тыва', 'TY', 1),
(176, 'Республика Хакасия', 'KK', 1),
(176, 'Ростовская область', 'ROS', 1),
(176, 'Рязанская область', 'RYA', 1),
(176, 'Самарская область', 'SAM', 1),
(176, 'Санкт-Петербург', 'SPE', 1),
(176, 'Саратовская область', 'SAR', 1),
(176, 'Сахалинская область', 'SAK', 1),
(176, 'Свердловская область', 'SVE', 1),
(176, 'Смоленская область', 'SMO', 1),
(176, 'Ставропольский край', 'STA', 1),
(176, 'Таймырский АО', 'TDN', 1),
(176, 'Тамбовская область', 'TAM', 1),
(176, 'Тверская область', 'TVE', 1),
(176, 'Томская область', 'TOM', 1),
(176, 'Тульская область', 'TUL', 1),
(176, 'Тюменская область', 'TYU', 1),
(176, 'Удмуртская Республика', 'UD', 1),
(176, 'Ульяновская область', 'ULY', 1),
(176, 'Хабаровский край', 'KHA', 1),
(176, 'Ханты-Мансийский АО - Югра', 'KHM', 1),
(176, 'Челябинская область', 'CHE', 1),
(176, 'Чеченская Республика', 'CE', 1),
(176, 'Чувашская Республика', 'CU', 1),
(176, 'Чукотский АО', 'CHU', 1),
(176, 'Ямало-Ненецкий АО', 'YAN', 1),
(176, 'Ярославская область', 'YAR', 1),
(189, 'Banskobystrický', 'BA', 1),
(189, 'Bratislavský', 'BR', 1),
(189, 'Košický', 'KO', 1),
(189, 'Nitriansky', 'NI', 1),
(189, 'Prešovský', 'PR', 1),
(189, 'Trenčiansky', 'TC', 1),
(189, 'Trnavský', 'TV', 1),
(189, 'Žilinský', 'ZI', 1),
(190, 'Gorenjska', '9', 1),
(190, 'Goriška', '11', 1),
(190, 'Jugovzhodna Slovenija', '7', 1),
(190, 'Koroška', '3', 1),
(190, 'Notranjsko-kraška', '10', 1),
(190, 'Obalno-kraška', '12', 1),
(190, 'Osrednjeslovenska', '8', 1),
(190, 'Podravska', '2', 1),
(190, 'Pomurska', '1', 1),
(190, 'Savinjska', '4', 1),
(190, 'Spodnjeposavska', '6', 1),
(190, 'Zasavska', '5', 1),
(207, 'Gorno-Badakhstan', 'GB', 1),
(207, 'Khatlon', 'KT', 1),
(207, 'Sughd', 'SU', 1),
(215, 'Adana', 'ADA', 1),
(215, 'Adıyaman', 'ADI', 1),
(215, 'Afyonkarahisar', 'AFY', 1),
(215, 'Aksaray', 'AKS', 1),
(215, 'Amasya', 'AMA', 1),
(215, 'Ankara', 'ANK', 1),
(215, 'Antalya', 'ANT', 1),
(215, 'Ardahan', 'ARD', 1),
(215, 'Artvin', 'ART', 1),
(215, 'Aydın', 'AYI', 1),
(215, 'Ağrı', 'AGR', 1),
(215, 'Balıkesir', 'BAL', 1),
(215, 'Bartın', 'BAR', 1),
(215, 'Batman', 'BAT', 1),
(215, 'Bayburt', 'BAY', 1),
(215, 'Bilecik', 'BIL', 1),
(215, 'Bingöl', 'BIN', 1),
(215, 'Bitlis', 'BIT', 1),
(215, 'Bolu', 'BOL', 1),
(215, 'Burdur', 'BRD', 1),
(215, 'Bursa', 'BRS', 1),
(215, 'Denizli', 'DEN', 1),
(215, 'Diyarbakır', 'DIY', 1),
(215, 'Düzce', 'DUZ', 1),
(215, 'Edirne', 'EDI', 1),
(215, 'Elazığ', 'ELA', 1),
(215, 'Erzincan', 'EZC', 1),
(215, 'Erzurum', 'EZR', 1),
(215, 'Eskişehir', 'ESK', 1),
(215, 'Gaziantep', 'GAZ', 1),
(215, 'Giresun', 'GIR', 1),
(215, 'Gümüşhane', 'GMS', 1),
(215, 'Hakkari', 'HKR', 1),
(215, 'Hatay', 'HTY', 1),
(215, 'Isparta', 'ISP', 1),
(215, 'Iğdır', 'IGD', 1),
(215, 'Kahramanmaraş', 'KAH', 1),
(215, 'Karabük', 'KRB', 1),
(215, 'Karaman', 'KRM', 1),
(215, 'Kars', 'KRS', 1),
(215, 'Kastamonu', 'KAS', 1),
(215, 'Kayseri', 'KAY', 1),
(215, 'Kilis', 'KLS', 1),
(215, 'Kocaeli', 'KOC', 1),
(215, 'Konya', 'KON', 1),
(215, 'Kütahya', 'KUT', 1),
(215, 'Kırklareli', 'KLR', 1),
(215, 'Kırıkkale', 'KRK', 1),
(215, 'Kırşehir', 'KRH', 1),
(215, 'Malatya', 'MAL', 1),
(215, 'Manisa', 'MAN', 1),
(215, 'Mardin', 'MAR', 1),
(215, 'Mersin', 'MER', 1),
(215, 'Muğla', 'MUG', 1),
(215, 'Muş', 'MUS', 1),
(215, 'Nevşehir', 'NEV', 1),
(215, 'Niğde', 'NIG', 1),
(215, 'Ordu', 'ORD', 1),
(215, 'Osmaniye', 'OSM', 1),
(215, 'Rize', 'RIZ', 1),
(215, 'Sakarya', 'SAK', 1),
(215, 'Samsun', 'SAM', 1),
(215, 'Siirt', 'SII', 1),
(215, 'Sinop', 'SIN', 1),
(215, 'Sivas', 'SIV', 1),
(215, 'Tekirdağ', 'TEL', 1),
(215, 'Tokat', 'TOK', 1),
(215, 'Trabzon', 'TRA', 1),
(215, 'Tunceli', 'TUN', 1),
(215, 'Uşak', 'USK', 1),
(215, 'Van', 'VAN', 1),
(215, 'Yalova', 'YAL', 1),
(215, 'Yozgat', 'YOZ', 1),
(215, 'Zonguldak', 'ZON', 1),
(215, 'Çanakkale', 'CKL', 1),
(215, 'Çankırı', 'CKR', 1),
(215, 'Çorum', 'COR', 1),
(215, 'İstanbul', 'IST', 1),
(215, 'İzmir', 'IZM', 1),
(215, 'Şanlıurfa', 'SAN', 1),
(215, 'Şırnak', 'SIR', 1),
(216, 'Ahal Welayaty', 'A', 1),
(216, 'Balkan Welayaty', 'B', 1),
(216, 'Dashhowuz Welayaty', 'D', 1),
(216, 'Lebap Welayaty', 'L', 1),
(216, 'Mary Welayaty', 'M', 1),
(220, 'Винницкая область', '05', 1),
(220, 'Волынская область', '07', 1),
(220, 'Днепропетровская область', '12', 1),
(220, 'Донецкая область', '14', 1),
(220, 'Житомирская область', '18', 1),
(220, 'Закарпатская область', '21', 1),
(220, 'Запорожская область', '23', 1),
(220, 'Ивано-Франковская область', '26', 1),
(220, 'Киев', '30', 1),
(220, 'Киевская область', '32', 1),
(220, 'Кировоградская область', '35', 1),
(220, 'Крым', '43', 0),
(220, 'Луганская область', '09', 1),
(220, 'Львовская область', '46', 1),
(220, 'Николаевская область', '48', 1),
(220, 'Одесская область', '51', 1),
(220, 'Полтавская область', '53', 1),
(220, 'Ровненская область', '56', 1),
(220, 'Севастополь', '40', 0),
(220, 'Сумская область', '59', 1),
(220, 'Тернопольская область', '61', 1),
(220, 'Харьковская область', '63', 1),
(220, 'Херсонская область', '65', 1),
(220, 'Хмельницкая область', '68', 1),
(220, 'Черкасская область', '71', 1),
(220, 'Черниговская область', '74', 1),
(220, 'Черновицкая область', '77', 1),
(226, 'Andijon', 'AN', 1),
(226, 'Buxoro', 'BU', 1),
(226, 'Farg\'ona', 'FA', 1),
(226, 'Jizzax', 'JI', 1),
(226, 'Namangan', 'NG', 1),
(226, 'Navoiy', 'NW', 1),
(226, 'Qashqadaryo', 'QA', 1),
(226, 'Qoraqalpog\'iston Republikasi', 'QR', 1),
(226, 'Samarqand', 'SA', 1),
(226, 'Sirdaryo', 'SI', 1),
(226, 'Surxondaryo', 'SU', 1),
(226, 'Toshkent City', 'TK', 1),
(226, 'Toshkent Region', 'TO', 1),
(226, 'Xorazm', 'XO', 1),
(243, 'Belgrade', '00', 1),
(243, 'Bor', '14', 1),
(243, 'Braničevo', '11', 1),
(243, 'Central Banat', '02', 1),
(243, 'Jablanica', '23', 1),
(243, 'Kolubara', '09', 1),
(243, 'Mačva', '08', 1),
(243, 'Moravica', '17', 1),
(243, 'Nišava', '20', 1),
(243, 'North Banat', '03', 1),
(243, 'North Bačka', '01', 1),
(243, 'Pirot', '22', 1),
(243, 'Podunavlje', '10', 1),
(243, 'Pomoravlje', '13', 1),
(243, 'Pčinja', '24', 1),
(243, 'Rasina', '19', 1),
(243, 'Raška', '18', 1),
(243, 'South Banat', '04', 1),
(243, 'South Bačka', '06', 1),
(243, 'Srem', '07', 1),
(243, 'Toplica', '21', 1),
(243, 'West Bačka', '05', 1),
(243, 'Zaječar', '15', 1),
(243, 'Zlatibor', '16', 1),
(243, 'Šumadija', '12', 1);

----------------------------------------------------------

--
-- Table structure for table `oc_zone_to_geo_zone`
--

DROP TABLE IF EXISTS `oc_zone_to_geo_zone`;
CREATE TABLE `oc_zone_to_geo_zone` (
  `zone_to_geo_zone_id` int(11) NOT NULL AUTO_INCREMENT,
  `country_id` int(11) NOT NULL,
  `zone_id` int(11) NOT NULL DEFAULT '0',
  `geo_zone_id` int(11) NOT NULL,
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`zone_to_geo_zone_id`),
  KEY `country_id` (`country_id`),
  KEY `zone_id` (`zone_id`),
  KEY `geo_zone_id` (`geo_zone_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

----------------------------------------------------------

--
-- Dumping data for table `oc_zone_to_geo_zone`
--


----------------------------------------------------------
