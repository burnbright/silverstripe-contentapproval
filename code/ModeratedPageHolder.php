<?php
class ModeratedPageHolder extends Page{

	static $singular_name = "Moderated Page Holder";
	static $plural_name = "Moderated Page Holders";

	static $default_child = "ModeratedPage";

	static $db = array(
		'ItemPlural' => 'Varchar',
		'ItemSingular' => 'Varchar',
		'AllowExpiry' => 'Boolean',
		'ArticlesPerPage' => 'Int',
		'AllowAttachments' => 'Boolean',
		'SubmitContent' => 'HTMLText'

		//show in menus option for new pages

	);

	static $has_one = array(
		//atachments upload directory
		'UploadDirectory' => 'Folder',
		'UploadParent' => 'SiteTree'
	);

	static $many_many = array(
		'Moderators' => 'Member'
	);

	static $has_many = array();

	static $defaults = array(
		'AllowExpiry' => true,
		'AllowAttachements' => true
	);

	static $icon = array(
		"approvecontent/images/holder", "file"
	);

	function getCMSFields(){
		$fields = parent::getCMSFields();

		$fields->addFieldToTab('Root.Content.ModeratedPages',new TextField('ItemSingular','Name of single item:'));
		$fields->addFieldToTab('Root.Content.ModeratedPages',new TextField('ItemPlural','Name of plural items:'));
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
		$fields->addFieldToTab('Root.Content.ModeratedPages',new TreeDropdownField("UploadDirectoryID","Upload attachments to","Folder"));
		$fields->addFieldToTab('Root.Content.ModeratedPages',new TreeDropdownField("UploadParentID","Page for new uploads to become children of","SiteTree"));
		return $fields;
	}

	function getItemName(){
		return ($this->ItemSingular) ? $this->ItemSingular : singleton("ModeratedPage")->i18n_singular_name();
	}

}

class ModeratedPageHolder_Controller extends Page_Controller{

	static $allowed_actions = array(
		'index',
		'rss',
		'submit',
		'SubmitForm',
		'approve'
	);

	function init(){
		parent::init();
	}

	function index(){
		$label = trim(sprintf(_t("ModeratedPage.SUBMITBUTTON","Submit %s"),$this->getItemName()));
		return array(
			'Form' => "<a href=\"".$this->Link("submit")."\" class=\"submitarticlelink\">$label</a>"
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
				'Content' => $name.' has been approved. <a href="'.$this->article->Link().'">view</a>'
			);
		}
		Director::redirect($this->Link());
		return false;
	}

	function submit(){
		return array(
			'ApprovedPages' => false,
			'Content' => ($this->SubmitContent) ? $this->SubmitContent : false,
			'Form' => $this->SubmitForm(),
			'Title' => "Submit"
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
			$filefield = new NoSaveFileField('Attachment','Attachment')
		);

		if($this->AllowExpiry){
			$fields->insertAfter($expiryfield = new PopupDateTimeField('Expires','Expiry date'),'Content');
		}

		if(!Controller::CurrentMember()){
			$fields->insertAfter(new EmailField('Email'),'Title');
		}

		if(ClassInfo::exists('VariableGroupField')){ //use variable group field for multiple attachments
			$fields->removeByName('Attachment');
			$filefield = new NoSaveFileField('Attachment','Attachment'); //this is necessary because....?
			$vgf = new VariableGroupField('Attachments',0, //there needs to be at least one set for the form to be the correct enctype
				$filefield
			);

			$vgf->setAddRemoveLabels('Add Attachment','Remove');
			$vgf->setLoadingImageURL('variablegroupfield/images/ajax-loader.gif');
			$vgf->writeOnSave(false);
			$vgf->generateFields();
			$fields->push($vgf);
		}

		//TODO: spam protection field here

		$this->data()->extend('updateFields', $fields);

		$actions = new FieldSet(
			new FormAction('post', trim(sprintf(_t("ModeratedPage.SUBMITBUTTON","Submit %s"),$this->getItemName())))
		);

		$validator = new RequiredFields('Title','Content');

		if(!Controller::CurrentMember()){
			$validator->addRequiredField('Email');
		}

		$form = new FileEncodedForm($this,'SubmitForm',$fields,$actions,$validator); //a custom form that always sets the enctype to "multipart/form-data" so that files upload properly, if added

		$protector = SpamProtectorManager::update_form($form, 'SpamProtection');

		$this->data()->extend('updateSubmitForm',$form);
		return $form;
	}

	function post($data,$form){

		//save content into new Content
		$mpage = new ModeratedPage();
		$mpage->writeToStage(''); //get an id to save components properly
		$form->saveInto($mpage); //TODO: this is attempting to save one of the vgf image fields into the article

		$path = 'Uploads';

		if($this->UploadDirectory()->exists()){
			$path = str_replace("assets/","",$this->UploadDirectory()->getRelativePath());
		}

		//save all file attachments into assets
		if(isset($_FILES)){
			foreach($_FILES as $key => $info){
				$upload = new Upload();
				$file = new File();
				$upload->loadIntoFile($_FILES[$key], $file, $path);
				if(!$upload->isError())
					$mpage->Attachments()->add($file);
			}
		}

		$mpage->Approved = $mpage->canApprove(Member::currentUser()); //TODO: allow articles submitted by moderators to be approved immediately
		if($member = Controller::CurrentMember()){
			$mpage->SubmitterID = $member->ID;
		}

		if($this->UploadParent()->exists()){
			$mpage->ParentID = $this->UploadParentID;
		}else{
			$mpage->ParentID = $this->ID;
		}
		$mpage->OriginID = $this->ID;
		$mpage->writeToStage('');

		$this->extend('updatepost',$form,$data,$mpage);

		if($mpage->Approved){
			$mpage->publish('Stage','Live');
			Director::redirect($mpage->Link());
			return false;
		}

		//send email to moderators
		$body = $mpage->customise(array(
			'Holder' => $this
		))->renderWith('ApprovalNeeded');
		$moderators = $this->Moderators();
		$moderatoremails = ($moderators->Count() > 0) ? $moderators->map('ID','Email') : array(Email::getAdminEmail());
		$subject = sprintf(_t("ModeratedPage.APPROVALEMAILSUBJECT".'%s - %s Approval Needed'),$this->Title,$this->getItemName());
		$email = new Email(Email::getAdminEmail(),implode($moderatoremails,","),$subject,$body);
		$result = $email->send();

		$form->sessionMessage('Your content has been submitted for approval','good');//set form session message
		Director::redirectBack();
		return false;
	}

	function ApprovedPages(){
		$filter = "\"ParentID\" = $this->ID OR \"OriginID\" = $this->ID";
		return ModeratedPage::get($filter);
	}

	function rss() {
		$events = ModeratedPage::get("\"ParentID\" = $this->ID OR \"OriginID\" = $this->ID", 'Created DESC', 20);
		$rss = new RSSFeed($events, $this->Link(), $this->Title, "", "Title", "Content");
		$rss->outputToBrowser();
		die();
	}

}