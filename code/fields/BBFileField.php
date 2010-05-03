<?php

class BBFileField extends FileField{
	
	public function saveInto(DataObject $record) {
		if(!isset($_FILES[$this->name])) return false;
		
		//TODO: if $record->class name is decendant of File, then save into that
		
		if($this->relationAutoSetting) {
			// assume that the file is connected via a has-one
			$hasOnes = $record->has_one($this->name);
			// try to create a file matching the relation
			$file = (is_string($hasOnes)) ? Object::create($hasOnes) : new File(); 
		} else {
			$file = new File();
		}
		
		$this->upload->setAllowedExtensions($this->allowedExtensions);
		$this->upload->setAllowedMaxFileSize($this->allowedMaxFileSize);
		$this->upload->loadIntoFile($_FILES[$this->name], $file, $this->folderName);
		if($this->upload->isError()) return false;
		
		$file = $this->upload->getFile();
		
		if($this->relationAutoSetting) {
			if(!$hasOnes) return false;
			
			// save to record
			$record->{$this->name . 'ID'} = $file->ID;
		}
	}
	
}

?>
