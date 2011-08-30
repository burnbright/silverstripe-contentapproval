<?php
class ModeratedArticleAdmin extends ModelAdmin{

	static $url_segment = 'moderatedcontent';
	static $menu_title = 'Moderated Content';
	static $menu_priority = 7;

	static $managed_models = array(
		'ModeratedArticle'
	);

}
?>
