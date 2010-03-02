<?php
class ModeratedArticleHolder extends Page{
	
	static $db = array(
		'ItemPlural' => 'Varchar',
		'ItemSingular' => 'Varchar'
	);
	
	static $many_many = array(
		'Moderators' => 'Member'
	);
	
	static $has_many = array(
		'Articles' => 'ModeratedArticle'
	);
	
	//TODO: submit content permissions - who can submit?? (eg: members, group, anyone)
	//TODO: moderate permissions - who may moderate?
	
	function getCMSFields(){
		$fields = parent::getCMSFields();
		
		$fields->addFieldToTab('Root.Content.Main',new TextField('ItemSingular','Name of single item:'));
		$fields->addFieldToTab('Root.Content.Main',new TextField('ItemPlural','Name of plural items:'));
		
		$source = DataObject::get('Member',"","Surname");
		$msf = new CheckboxSetField( //Not native to SS
		    "Moderators",
		    "Moderators", 
		    $source->toDropdownMap('ID','Name')
		);

		$fields->addFieldToTab('Root.Moderators',$msf);
		
		$summaryfields = singleton('ModeratedArticle')->summaryFields();
		
		$content = new ComplexTableField(null,'Articles','ModeratedArticle',$summaryfields,null,"",'Approved,Title');
		$fields->addFieldToTab('Root.Content.SubmittedArticles',$content);		
		
		return $fields;
	}
	
	function Articles(){
		return DataObject::get('ModeratedArticle','Approved = TRUE && ArticleHolderID = '.$this->ID);
	}
	
}

class ModeratedArticleHolder_Controller extends Page_Controller{
	
	protected $article = null;
	
	protected $itemname = "";
	
	//TODO: RSS feed of approved articles
	function init(){
		parent::init();
		if(is_numeric(Director::urlParam('ID'))){
			$id = Director::urlParam('ID');
			$this->article = DataObject::get_by_id('ModeratedArticle',$id);
		}
		$this->itemname = ($this->ItemSingular) ? " ".$this->ItemSingular : "";
	}
	
	function index(){
		
		return array(
			'Form' => '<a href="'.$this->Link()."submit".'">Submit'.$this->itemname.'</a>'
		);
	}
	
	function show(){
		if($article = $this->article){
				return array(
					'Title' => $article->Title,
					'Content' => $article
				);
		}			
		return array();
	}
	
	function approve(){
		if($this->article 
		&& $this->Moderators()->Count() > 0
		&& Controller::CurrentMember()
		&& $this->Moderators()->containsIDs(array(Controller::CurrentMember()->ID))){
			
			$this->article->approve();
			return array(
				'Title' => $this->article->Title." - Approved",
				'Content' => 'Article has been approved. <a href="'.$this->article->Link().'">view'.$this->itemname.'</a>',
				'Articles' => false
			);
		}			
		return array();
	}
	
	
	function submit(){
		return array(
			'Articles' => false,
			'Form' => $this->SubmitForm(),
			'Title' => "Submit".$this->itemname
		);
	}

	function SubmitForm(){
		HtmlEditorConfig::set_active('ModeratedArticleConfig');
		$config = HtmlEditorConfig::get('ModeratedArticleConfig');
		$config->disablePlugins('table');
		$config->disablePlugins('paste');
		$config->disablePlugins('../../tinymce_advcode');
		$config->setButtonsForLine(2);
		
		$fields = new FieldSet(			
			new TextField('Title'),
			new HtmlEditorField('Content','Content',15),
			new DateField('Expires','Expiry date (dd/mm/yyyy)'),
			$filefield = new FileField('Attachment','Attachment')
		);
		
		//$filefield->setAllowedExtensions(array('doc','pdf','txt','docx','jpg','gif',''));
		
		if(!Controller::CurrentMember()){
			$fields->push(new EmailField('Email'));
		}
		$this->extend('updateFields', $fields);
		
		$actions = new FieldSet(
			new FormAction('post', "Submit".$this->itemname),
			new LiteralField('cancel', "<a href='".$this->Link()."'>Go back</a>")
		);
		$form = new Form($this,'SubmitForm',$fields,$actions);
		return $form;		
	}
	
	function post($data,$form){
		
		//save content into new Content
		$article = new ModeratedArticle();
		$form->saveInto($article);
		$article->Approved = false;
		if($member = Controller::CurrentMember()){
			$article->SubmitterID = $member->ID;
		}
		$article->ArticleHolderID = $this->ID;
		$article->write();
		
		//send email to moderators
		$moderators = $this->Moderators();
		if($moderators->Count() > 0){
			
			$body = $article->customise(array(
				'Holder' => $this
			))->renderWith('ApprovalNeeded'); 
			
			$moderatoremails = $moderators->map('ID','Email');
			$email = new Email(Email::getAdminEmail(),implode($moderatoremails,","),$this->Title.' - Article Approval Needed',$body);
			$result = $email->send();
		}
		//set form session message
		$form->sessionMessage('Your content has been submitted for approval','good');	
		
		Director::redirectBack();
		return false;
	}
	
	
	function rss() {
		$events = DataObject::get('ModeratedArticle', 'Approved = TRUE AND ArticleHolderID = ' . $this->ID, 'Created DESC', '', 20);
		if($events) {
			$rss = new RSSFeed($events, $this->Link(), $this->Title, "", "Title", "Content");
			$rss->outputToBrowser();
		}
		return array();
	}

	
}
?>
