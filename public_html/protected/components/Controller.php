<?php
/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 */
class Controller extends CController
{
	public $siteSettings;
	/**
	 * @var string the default layout for the controller view. Defaults to '//layouts/column1',
	 * meaning using a single column layout. See 'protected/views/layouts/column1.php'.
	 */
	public $layout='//layouts/admin';
	/**
	 * @var array context menu items. This property will be assigned to {@link CMenu::items}.
	 */
	public $menu=array();
	/**
	 * @var array the breadcrumbs of the current page. The value of this property will
	 * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
	 * for more details on how to specify this property.
	 */
	public $breadcrumbs=array();
	/**
	 * @var some project pages have custom CSS sheets in the CSS folder
	 */
	public $projectStyle;
	
	
	/**
	* This is method is fired when the Yii application starts up.
	*/
	
	public $User;
	public $debug = false;
	public $inAccession = false;
	
	public function init()
	{
		/*
		if($error=Yii::app()->errorHandler->error)
		{
			$this->render('//site/error', array('error' => $error));exit();
		}
		*/

		// put config into params
		$Configs = Config::model()->findAll(array('index' => 'key'));
		if(!isset($Configs['host']) || $_SERVER['HTTP_HOST'] !== $Configs['host']['value']){
			// put the latest host into the database.

			if(!isset($Configs['host'])){
				$Configs['host'] = new Config;
				$Configs['host']->key = 'host';
			}

			$Configs['host']->value = $_SERVER['HTTP_HOST'];
			$Configs['host']->save();

			if(!isset($Configs['https'])){
				$Configs['https'] = new Config;
				$Configs['https']->key = 'https';
			}

			$Configs['https']->value = (int)isset($_SERVER['HTTPS']);
			$Configs['https']->save();
		}


		foreach($Configs as $setting){
			Yii::app()->params[$setting['key']] = $setting['value'];
		}

		//check for encyption key passphrase being set
		EncryptFile::checkPassphrase();

		
	}	

}