<?php
class ModeratedArticleHolder extends Page{
	
	static $db = array(
		'ItemPlural' => 'Varchar',
		'ItemSingular' => 'Varchar',
		'AllowExpiry' => 'Boolean',
		'ArticlesPerPage' => 'Int'
	);
	
	static $many_many = array(
		'Moderators' => 'Member'
	);
	
	static $has_many = array(
		'Articles' => 'ModeratedArticle'
	);
	
	static $defaults = array(
		'AllowExpiry' => true
	);
	
	//TODO: submit content permissions - who can submit?? (eg: members, group, anyone)
	//TODO: moderate permissions - who may moderate?
	
	function getCMSFields(){
		$fields = parent::getCMSFields();
		
		$fields->addFieldToTab('Root.Content.Main',new TextField('ItemSingular','Name of single item:'));
		$fields->addFieldToTab('Root.Content.Main',new TextField('ItemPlural','Name of plural items:'));
		
		$source = DataObject::get('Member',"","Surname");
		
		if(ClassInfo::exists('AsmselectField')){
			$mods = $this->owner->Moderators();
			$msf = new AsmselectField('Moderators','Moderators',$source->map('ID','Name'),$mods->map('ID','ID'));
			
		}else{
			$msf = new ManyManyComplexTableField(
				$this,
			    "Moderators",
			    "Member", 
			    array(
			    	'FirstName' => 'First Name',
			    	'Surname' => 'Surname'
			    )
			);
		}

		$fields->addFieldToTab('Root.Moderators',$msf);
		
		$summaryfields = singleton('ModeratedArticle')->summaryFields();
		
		$content = new ComplexTableField($this,'Articles','ModeratedArticle',$summaryfields,'getCMSFieldsForPopup',"",'Approved,Title');
		$fields->addFieldToTab('Root.Content.SubmittedArticles',$content);
		$fields->addFieldToTab('Root.Content.SubmittedArticles',new CheckboxField('AllowExpiry','Include expiry date option'));
		$fields->addFieldToTab('Root.Content.SubmittedArticles',new NumericField('ArticlesPerPage','ArticlesPerPage'));
		
		if($content && $name = $this->ItemSingular)
			$content->setAddTitle("add $name");		
		
		return $fields;
	}
	
	function Articles($limit = ''){
		return ModeratedArticle::get('ArticleHolderID = '.$this->ID,"Created DESC",$limit);
	}
	
}

class ModeratedArticleHolder_Controller extends Page_Controller{
	
	//TODO: add allowed actions
	
	protected $article = null; //TODO: possibly start using modelfrontend?
	
	protected $itemname = "";
	
	static $allowed_actions = array(
		'index',
		'show',
		'rss',
		'submit',
		'SubmitForm',
		'approve'
	);
	
	function init(){
		parent::init();
		if(is_numeric(Director::urlParam('ID'))){
			$id = Director::urlParam('ID');
			$this->article = ModeratedArticle::get_by_id($id);
		}
		$this->itemname = ($this->ItemSingular) ? " ".$this->ItemSingular : "";
	}
	
	function index(){
		
		return array(
			'Form' => '<a href="'.$this->Link("submit").'" class="submitarticlelink">Submit'.$this->itemname.'</a>'
		);
	}
	
	function show(){
		if($article = $this->article){
				return array(
					'Title' => $article->Title,
					'Content' => $article
				);
		}
		Director::redirect($this->Link());
		return array();
	}
	
	/**
	 * Action for approving content.
	 */
	function approve(){
		if($this->article && $this->article->canApprove(Controller::CurrentMember())){
			$this->article->approve();
			
			$name = ($this->ItemSingular) ? $this->ItemSingluar : "Article";
			return array(
				'Title' => $this->article->Title." - Approved",
				'Content' => $name.' has been approved. <a href="'.$this->article->Link().'">view</a>',
				'Articles' => false
			);
		}
		Director::redirect($this->Link());
		return false;
	}
	
	function submit(){
		$submititem = ($this->itemname) ? " ".$this->itemname : "";
		return array(
			'Articles' => false,
			'Form' => $this->SubmitForm(),
			'Title' => "Submit".$submititem
		);
	}

	function SubmitForm(){
		HtmlEditorConfig::set_active('ModeratedArticleConfig');
		$config = HtmlEditorConfig::get('ModeratedArticleConfig');
		$config->disablePlugins('table');
		$config->disablePlugins('paste');
		$config->enablePlugins('tabfocus'); //get tabbing working
		$config->setOption('tab_focus',':prev,:next');
		$config->disablePlugins('../../tinymce_advcode');
		$config->setButtonsForLine(2);
		
		$fields = new FieldSet(			
			new TextField('Title'),
			new HtmlEditorField('Content','Content',15),
			$filefield = new BBFileField('Attachment','Attachment')
		);
		
		if($this->AllowExpiry)
			$fields->insertAfter(new PopupDateTimeField('Expires','Expiry date'),'Content');
		
		if(!Controller::CurrentMember()){
			$fields->insertAfter(new EmailField('Email'),'Title');
		}
		
		if(ClassInfo::exists('VariableGroupField')){ //use variable group field for multiple attachments
			$fields->removeByName('Attachment');
			
			$vgf = new VariableGroupField('Attachments',0, //there needs to be at least one set for the form to be the correct enctype
				new BBFileField('Attachment','Attachment')
			);
			$vgf->setAddRemoveLabels('Add Attachment','Remove');
			$vgf->setLoadingImageURL('variablegroupfield/images/ajax-loader.gif');
			$vgf->writeOnSave(false);
			$vgf->generateFields();
			$fields->push($vgf);
		}
		
		$this->data()->extend('updateFields', $fields);
		
		$actions = new FieldSet(
			new FormAction('post', "Submit".$this->itemname)
		);
		
		$validator = new RequiredFields('Title','Content');
		
		if(!Controller::CurrentMember()){
			$validator->addRequiredField('Email');
		}
		
		$form = new ModeratedArticleSubmitForm($this,'SubmitForm',$fields,$actions,$validator); //a custom form that always sets the enctype to "multipart/form-data" so that files upload properly, if added
		$this->data()->extend('updateSubmitForm',$form);
		return $form;
	}
	
	function post($data,$form){
				
		//save content into new Content
		$article = new ModeratedArticle();
		$article->write(); //get an id to save components properly
		$form->saveInto($article); //TODO: this is attempting to save one of the vgf image fields into the article 
		
		//save all file attachments into assets
		if(isset($_FILES)){
			foreach($_FILES as $key => $info){
				$upload = new Upload();
				$file = new File();
				$upload->loadIntoFile($_FILES[$key], $file, 'Uploads');
				if(!$upload->isError()) 
					$article->Attachments()->add($file);
			}
		}
		
		$article->Approved = $article->canApprove(Member::currentUser()); //TODO: allow articles submitted by moderators to be approved immediately
		if($member = Controller::CurrentMember()){
			$article->SubmitterID = $member->ID;
		}
		$article->ArticleHolderID = $this->ID;
		$article->write();
		
		$this->extend('updatepost',$form,$data,$article);
		
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
	}
	
	function Articles() {
		$perpage = ($this->ArticlesPerPage) ? $this->ArticlesPerPage : 10 ;
		if(!isset($_GET['start']) || !is_numeric($_GET['start']) || (int)$_GET['start'] < 1) $_GET['start'] = 0;
		$SQL_start = (int)$_GET['start'];
		$doSet = DataObject::get(
			$callerClass = "ModeratedArticle",
			$filter = "`ArticleHolderID` = '".$this->ID."' AND Approved = TRUE",
			$sort = "",
			$join = "",
			$limit = "{$SQL_start},$perpage"
		);
		if($doSet && $this->ArticlesPerPage)
			$doSet->setPageLength($this->ArticlesPerPage);
		return $doSet ? $doSet : false;
	}
	
}
?>
