<?php
class ModeratedArticle extends DataObject{
	
	static $db = array(
		'Title' => 'Varchar',
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
		'Created' => 'Date',
		'Email' => 'Submitter Email',		
		'Expires' => 'Expires',
		'YesNoApproved' => "Approved"
	);
	
	static $searchable_fields = array(
		'Title'
	);
	
	static $default_sort = 'Created DESC';

	//Hack for editing articles in cms
	//TODO: make this better / proper
	function getCMSFieldsForPopup(){
		echo "<h1><a href=\"".Director::absoluteBaseURL()."admin/articlemoderation/ModeratedArticle/".$this->ID."/edit\" target=\"_top\">edit</a></h1>";
		die();
	}
	
	function getCMSFields(){
		$fields = parent::getCMSFields();
		$attachments = new TreeMultiselectField('Attachments','Attachments','File');
		$fields->addFieldToTab('Root.Attachments',$attachments);
		return $fields;
	}

	function Link(){
		if($this->ArticleHolderID){
			return $this->ArticleHolder()->Link().'show/'.$this->ID."/";
		}
		return false;
	}
	
	function ApproveLink(){
		return $this->ArticleHolder()->Link().'approve/'.$this->ID."/";
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
?>
