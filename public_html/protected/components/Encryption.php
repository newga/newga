<?php


class Encryption
{
	protected $key = 'PzJRQR8cpNePnkDPiQ4nvuWzvXtiovDp';
	
	protected $iv = null;
	
	protected $cipher = MCRYPT_RIJNDAEL_256;
	
	protected $mode = MCRYPT_MODE_ECB;
	
	/*
	 *	USAGE:
	 *
	 *	$Encryption = new Encryption;
	 *	
	 *	$encrypted = $Encryption->encrypt('string');
	 *	
	 *	print $Encryption->decrypt($encrypted);
	 *	
	 *	
	 */
	
	public function __construct()
	{
		$this->iv = mcrypt_create_iv(mcrypt_get_iv_size($this->cipher, $this->mode), MCRYPT_RAND);
	}
	
	public function encrypt($text)
	{
		return trim(base64_encode(mcrypt_encrypt($this->cipher, $this->key, $text, $this->mode, $this->iv)));
	}

	public function decrypt($text)
	{
		return trim(mcrypt_decrypt($this->cipher, $this->key, base64_decode($text), $this->mode, $this->iv));
	}
	
	public function strtohex($x)
	{
		$s='';
		foreach (str_split($x) as $c) $s.=sprintf("%02X",ord($c));
		return($s);
	} 
}
?>