<?php
class ModeratedArticleAdmin extends ModelAdmin{
	
	static $url_segment = 'articlemoderation';
	static $menu_title = 'Articles';
	
	static $managed_models = array(
		'ModeratedArticle'
	);

	
}
?>
