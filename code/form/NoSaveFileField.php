<?php

class NoSaveFileField extends FileField{

	/**
	 * Blocks save into...so that it can be handled elsewhere.
	 */
	public function saveInto(DataObject $record) {
		if(!isset($_FILES[$this->name])) return false;
	}

}

?>
