<?php
class ModeratedArticle extends DataObject{

	static $singular_name = "moderated content";
	static $plural_name = "moderated content";

	static $db = array(
		'Title' => 'Varchar(255)',
		'Content' => 'HTMLText',
		'SubmitterEmail' => 'Varchar',
		'Approved' => 'Boolean',
		'Expires' => 'SSDatetime'
	);

	static $has_one = array(
		'Submitter' => 'Member',
		'ArticleHolder' => 'ModeratedArticleHolder'
	);

	static $many_many = array(
		'Attachments' => 'File'
	);

	static $casting = array(
		'YesNoApproved' => 'Varchar',
		'Email' => 'Varchar'
	);

	static $summary_fields = array(
		'Title' => "Title",
		//'ArticleHolderID' => 'Parent',
		'Created' => 'Date',
		'Email' => 'Submitter Email',
		'Expires' => 'Expires',
		'YesNoApproved' => "Approved"
	);

	static $searchable_fields = array(
		'Title',
		//'ArticleHolderID' => array('title' => 'Parent page')
	);

	static $default_sort = 'Created DESC';

	/**
	 * Removes the need to check for expired / approved
	 */
	static function get($filter = "", $sort = "",$limit = 5){
		if($filter != "") $filter = " AND $filter";
		return DataObject::get("ModeratedArticle", "(Approved = TRUE) AND ((Expires > NOW()) OR (Expires IS NULL))$filter", $sort, "", $limit);
	}

	static function get_by_id($id){
		return DataObject::get_one('ModeratedArticle',"ID = $id AND (Approved = TRUE) AND ((Expires > NOW()) OR (Expires IS NULL))");
	}

	//Hack for editing articles in cms
	//TODO: make this better / proper
	function getCMSFieldsForPopup(){
		$link = Director::absoluteBaseURL().'admin/'.ModeratedArticleAdmin::$url_segment."/ModeratedArticle/".$this->ID."/edit";
		echo "<h1><a href=\"$link\" target=\"_top\">edit</a></h1>";
		die();
	}

	function getCMSFields(){
		$fields = parent::getCMSFields();
		$attachments = new TreeMultiselectField('Attachments','Attachments','File');
		$fields->addFieldToTab('Root.Attachments',$attachments);
		return $fields;
	}

	function Link($action = 'show'){
		if($this->ArticleHolderID){
			return $this->ArticleHolder()->Link($action).'/'.$this->ID."/";
		}
		return false;
	}

	function ApproveLink(){
		return $this->Link('approve');
	}

	function approve(){
		$this->Approved = true;
		$this->write();
	}

	/**
	 * Check that member can approve this article.
	 */
	function canApprove(Member $member){
		if(!$member) return false;
		if(Permission::checkMember($member,"ADMIN")) return true;
		if($holder = $this->ArticleHolder())
			return $holder->Moderators()->Count() > 0 && $holder->Moderators()->containsIDs(array(Controller::CurrentMember()->ID));
		return false;
	}

	function PreviewLink(){
		return false;
	}

	function preview(){
		//TODO: finish me
		return Permission::check('ADMIN');
	}

	function isModerator($member = null){
		if(!$member) return false;
		if(Permission::check('ADMIN','any',$member)) return true;
		if($this->ArticleHolderID) return false;
		if($this->ArticleHolder()->Moderators()->containsIDs(array($member->ID)))
		return false;
	}

	function YesNoApproved(){
		return ($this->Approved)? "Yes" : "No";
	}

	function getEmail(){
		if($this->SubmitterID)
			$this->Submitter()->Email;
		return $this->SubmitterEmail;
	}

}