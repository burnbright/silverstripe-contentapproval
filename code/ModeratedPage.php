<?php

class ModeratedPage extends Page{

	static $singular_name = "Moderated Page";
	static $plural_name = "Moderated Pages";

	static $default_parent = "ModeratedPageHolder";

	static $db = array(
		'SubmitterEmail' => 'Varchar',
		'Approved' => 'Boolean',
		'Expires' => 'SSDatetime'
	);
	static $has_one = array(
		'Submitter' => 'Member',
		'Origin' => 'ModeratedPageHolder'
	);
	static $has_many = array(

	);
	static $many_many = array(
		'Attachments' => 'File'
	);
	static $belongs_many_many = array();

	static $casting = array(
		'YesNoApproved' => 'Varchar',
		'Email' => 'Varchar'
	);
	static $icon = array(
		"approvecontent/images/page", "file"
	);

	static $extensions = array();
	static $searchable_fields = array(
		'Title' => "Title",
		'Created' => 'Date',
		'Email' => 'Submitter Email',
		'Expires' => 'Expires',
		'YesNoApproved' => "Approved"
	);

	static $default_sort = 'Created DESC';

	static $defaults = array(
		'ShowInMenus' => false
	);

	function getCMSFields(){
		$fields = parent::getCMSFields();

		if($this->Submitter()->exists()){
			$source = DataObject::get("Member");
			$fields->addFieldToTab("Root.Content.Main", new DropdownField("SubmitterID","Submitted by",$source->toDropdownMap()),"Content");
		}else{
			$fields->addFieldToTab('Root.Content.Main', new EmailField("SubmitterEmail","Submitter email"),"Content");
		}
		$fields->addFieldToTab("Root.Content.Main",new CheckboxField("Approved"),"Content");
		$fields->addFieldToTab("Root.Content.Main",$dtfield = $this->dbObject('Expires')->scaffoldFormField('Expiry date'),"Content");

		$datefield = $dtfield->getDateField()->setConfig('showcalendar',true);
		$timefield = $dtfield->getTimeField()->setConfig('showdropdown',true);

		$attachments = new TreeMultiselectField('Attachments','Attachments','File');
		$fields->addFieldToTab('Root.Content.Attachments',$attachments);

		$fields->addFieldToTab('Root.Content.Main',new TreeDropdownField("OriginID","Origin","SiteTree"));
		return $fields;
	}

	/**
	 * Removes the need to check for expired / approved
	 */
	static function get($filter = "", $sort = "",$limit = 5){
		if($filter != "") $filter = " AND $filter";
		return DataObject::get("ModeratedPage", "(Approved = 1) AND ((Expires > NOW()) OR (Expires IS NULL))$filter", $sort, "", $limit);
	}

	static function get_unapproved(){
		return DataObject::get("ModeratedPage","\"Approved\" = 0");
	}

	/**
	* Check that member can approve this article.
	*/
	function canApprove(Member $member){
		if(!$member) return false;
		if(Permission::checkMember($member,"ADMIN")) return true;
		if($holder = $this->getParent())
			return $holder->Moderators()->Count() > 0 && $holder->Moderators()->containsIDs(array(Controller::CurrentMember()->ID));
		return false;
	}

	function canView(){
		if(Member::currentUserID() && Member::currentUser()->isAdmin())
			return parent::canView();
		return ($this->Approved && strtotime($this->Expires) > time());
	}

}

class ModeratedPage_Controller extends Page_Controller{

	function init(){
		if($this->isPublished() && $this->Expire && strtotime($this->Expire) < time()){
			$this->doUnpublish();
			Director::redirect($this->Parent()->Link());
		}elseif(!$this->canView()){
			Director::redirect($this->Parent()->Link());
		}
		parent::init();
	}

}