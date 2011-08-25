<?php
class ModeratedArticleAdmin extends ModelAdmin{

	static $url_segment = 'articlemoderation';
	static $menu_title = 'Articles';
	static $menu_priority = 7;

	static $managed_models = array(
		'ModeratedArticle'
	);

}
?>
