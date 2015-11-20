<?php
/**
 * EncryptFile class file.
 * Set of functions to do normal file stuff but encrypted.
 * The $_passphrase variable is taken from a file in root called .passphrase which is .gitignored
 */


class EncryptFile 
{


	static private $_files;

	private $_name;
	private $_tempName;
	private $_type;
	private $_size;
	private $_error;
	private $_passphrase;
	private $_fp;

	
	
	public function __construct()
	{
		$this->getPassphrase();
	}


	private function getPassphrase() {
		if (self::checkPassphrase()) {
			$file = Yii::app()->basePath .'/../../.passphrase';
			$_passphrase = file_get_contents($file);
		}
	}

	/**
	* Checks if there is a .passphrase file in root
	* It then creates one if there isn't
	*/
	public static function checkPassphrase() {

		$file = Yii::app()->basePath .'/../../.passphrase';
		
		if (file_exists($file)) {

			$p = file_get_contents($file);

			if (strlen($p) < 31) {
				throw new CException('Set passphrase needs to be at least 32 characters');
			}
			return true;
		}
		else {
			
			throw new CException('Unable to find .passphrase file in root');
			return;
			//removed auto generation below
			/*
			substr(md5(rand()),0,32);

			*/

		}
	}



	/**
	* Loads a single file from $_POST into class
	*/
	public function setPostFile($model,$name) {


		if (isset($_FILES[get_class($model)])) {
			
			$this->_files = $_FILES[get_class($model)];
			$this->_name = $this->_files['name'][$name];
			$this->_tempName = $this->_files['tmp_name'][$name];
			$this->_type = $this->_files['type'][$name];
			$this->_size = $this->_files['size'][$name];
			$this->_error = $this->_files['error'][$name];
			return true;
		}
		else return;
	}

	/**
	 * String output.
	 * This is PHP magic method that returns string representation of an object.
	 * The implementation here returns the uploaded file's name.
	 * @return string the string representation of the object
	 */
	public function __toString()
	{
		return $this->_name;
	}

	/**
	 * Saves and encrypts the uploaded file.
	 * Note: this method uses php's move_uploaded_file() method. As such, if the target file ($file) 
	 * already exists it is overwritten.
	 */
	public function saveAsEncrypted($file)
	{
		// print_r(mcrypt_list_algorithms());
		// print_r(stream_get_filters());
		
		
		if($this->_size)
		{
		
			// Turn a human readable passphrase
			// into a reproducible iv/key pair
			 
			$iv = substr(md5("\x1B\x3C\x58".$this->_passphrase, true), 0, 8);
			$key = substr(md5("\x2D\xFC\xD8".$this->_passphrase, true) .
			md5("\x2D\xFC\xD9".$this->_passphrase, true), 0, 24);
			$opts = array('iv' => $iv, 'key' => $key, 'mode' => 'stream');
			 
			// Open the file
			$fp = fopen($file, 'wb');
			 
			// Add the Mcrypt stream filter
			// Check we have an encryption algorithm set
			if (!isset(Yii::app()->params['encryptionAlgorithm'])) {

				throw new CException('No yii param set for encryptionAlgorithm');
			}
			stream_filter_append($fp, Yii::app()->params['encryptionAlgorithm'], STREAM_FILTER_WRITE, $opts);
			 
			// Wrote some contents to the file
			fwrite($fp,  file_get_contents($this->_tempName));



		}
		else
			return false;
	}

	/**
	* Open a file from path ready for reading, add encnyption string return file pointer
	*
	*/
	public function fileOpen($path) {
		// Turn a human readable passphrase
		// into a reproducible iv/key pair
		 
		$iv = substr(md5("\x1B\x3C\x58".$this->_passphrase, true), 0, 8);
		$key = substr(md5("\x2D\xFC\xD8".$this->_passphrase, true) .
		md5("\x2D\xFC\xD9".$this->_passphrase, true), 0, 24);
		$opts = array('iv' => $iv, 'key' => $key, 'mode' => 'stream');
		 
		// Open the file
		$fp = fopen($path, 'rb');
		 
		// Add the Mcrypt stream filter
		// Check we have an encryption algorithm set
		if (!isset(Yii::app()->params['decryptionAlgorithm'])) {

			throw new CException('No yii param set for decryptionAlgorithm');
		}

		stream_filter_append($fp, Yii::app()->params['decryptionAlgorithm'], STREAM_FILTER_READ, $opts);

		return $fp;
	}

	/**
	 * @return string the original name of the file being uploaded
	 */
	public function getName()
	{
		return $this->_name;
	}

	/**
	 * @return string the path of the uploaded file on the server.
	 * Note, this is a temporary file which will be automatically deleted by PHP
	 * after the current request is processed.
	 */
	public function getTempName()
	{
		return $this->_tempName;
	}

	/**
	 * @return string the MIME-type of the uploaded file (such as "image/gif").
	 * Since this MIME type is not checked on the server side, do not take this value for granted.
	 * Instead, use {@link CFileHelper::getMimeType} to determine the exact MIME type.
	 */
	public function getType()
	{
		return $this->_type;
	}

	/**
	 * @return integer the actual size of the uploaded file in bytes
	 */
	public function getSize()
	{
		return $this->_size;
	}

	/**
	 * Returns an error code describing the status of this file uploading.
	 * @return integer the error code
	 * @see http://www.php.net/manual/en/features.file-upload.errors.php
	 */
	public function getError()
	{
		return $this->_error;
	}

	/**
	 * @return boolean whether there is an error with the uploaded file.
	 * Check {@link error} for detailed error code information.
	 */
	public function getHasError()
	{
		return $this->_error!=UPLOAD_ERR_OK;
	}

	/**
	 * @return string the file extension name for {@link name}.
	 * The extension name does not include the dot character. An empty string
	 * is returned if {@link name} does not have an extension name.
	 */
	public function getExtensionName()
	{
		if(($pos=strrpos($this->_name,'.'))!==false)
			return (string)substr($this->_name,$pos+1);
		else
			return '';
	}
}
