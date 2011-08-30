<?php
class FileEncodedForm extends Form{

	/**
	 * Force file encoding.
	 */
	function FormEncType() {
		return "multipart/form-data";
	}
}
?>
