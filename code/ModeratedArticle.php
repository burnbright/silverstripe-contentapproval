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
		'ArticleHolder' => 'ModeratedArticleHolder',
		'Attachment' => 'File'
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
	
	function PreviewLink(){
		return false;
	}
	
	function preview(){
		//TODO: check moderator or ADMIN
		
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
