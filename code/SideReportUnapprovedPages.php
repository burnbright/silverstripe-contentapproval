<?php

class SideReport_UnapprovedPages extends SS_Report {
	function title() {
		return _t('ModeratedPage.UNAPPROVEDPAGES',"Unapproved pages");
	}
	function group() {
		return _t('ModeratedPage.REPORTGROUPTITLE', "Moderated Pages");
	}
	function sort() {
		return 200;
	}
	function sourceRecords($params = null) {
		return ModeratedPage::get_unapproved();
	}
	function columns() {
		return array(
			"Title" => array(
				"title" => "Title",
				"link" => true,
		),
		);
	}
}