<?php

/**
* This class contains generic, site-wide functions
*
* Call a method with Yii::app()->functions->methodName();
*/



class Functions extends CApplicationComponent
{
	
	public function makeHash($len = 15)
	{
		$base = '23456789ABCDEFGHKLMNPQRSTWXYZabcdefghjkmnpqrstwxyz23456789';
		
		$max = strlen($base)-1;
		
		$hash = '';
		
		mt_srand((double)microtime()*1000000);
		
		while(strlen($hash)<$len)
		{
			$hash .= $base{mt_rand(0,$max)};
		}
		
		return $hash;
	}
	
	public function makeUrl($str)
	{
		$str = preg_replace("@&amp;@", "and", trim($str));
		$str = preg_replace("@&@", "and", $str);
		
		$str = preg_replace("@'@", "", $str);
		$str = strtolower(preg_replace("@[^a-zA-Z0-9-]@", "-", $str));
		
		$str = preg_replace("@-{2,}@", "-", $str);
		
		if(preg_match("@-$@", $str))
		{
			$str = substr($str, 0, -1);
		}
		
		if(preg_match("@^-@", $str))
		{
			$str = substr($str, 1);
		}
		
		return trim($str);
	}
	
	
	
	public function fileUploadErrors($file)
	{
		$error_text = true;
		define("UPLOAD_ERR_EMPTY",5);
		
		if($file['size'] == 0 && $file['error'] == 0)
		{
			$file['error'] = 5;
		}
		
		$upload_errors = array(
			UPLOAD_ERR_OK 			=> "No errors.",
			UPLOAD_ERR_INI_SIZE 	=> "The file is larger than the maximum file upload size (" . ini_get('upload_max_filesize') . "b)",
			UPLOAD_ERR_FORM_SIZE 	=> "Larger than form MAX_FILE_SIZE.",
			UPLOAD_ERR_PARTIAL 		=> "Partial upload.",
			UPLOAD_ERR_NO_FILE 		=> "No file was chosen",
			UPLOAD_ERR_NO_TMP_DIR 	=> "No temporary directory.",
			UPLOAD_ERR_CANT_WRITE 	=> "Can't write to disk.",
			UPLOAD_ERR_EXTENSION 	=> "File upload stopped by extension.",
			UPLOAD_ERR_EMPTY 		=> "File is empty." // add this to avoid an offset
		);
		
		return ($error_text) ? $upload_errors[$file['error']] : $file['error'] ; 
	}



	function superentities( $str ){

		$str2 = '';
		// get rid of existing entities else double-escape
		$str = html_entity_decode(stripslashes($str),ENT_QUOTES,'UTF-8');
		$ar = preg_split('/(?<!^)(?!$)/u', $str );  // return array of every multi-byte character
		foreach ($ar as $c){
			$o = ord($c);
			if ($o !== 10 &&
				(
					(strlen($c) > 1) || /* multi-byte [unicode] */
					($o <32 || $o > 126) || /* <- control / latin weirdos -> */
					($o >33 && $o < 40) ||/* quotes + ambersand */
					($o >59 && $o < 63) /* html */
				)
			) {
				// convert to numeric entity
				$c = mb_encode_numericentity($c,array (0x0, 0xffff, 0, 0xffff), 'UTF-8');
			}

		$str2 .= $c;

		}

		return $str2;
	}


}