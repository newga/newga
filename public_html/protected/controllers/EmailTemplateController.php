<?php

class EmailTemplateController extends Controller
{
	
	public $layout = '/layouts/admin';
	
	
	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			
			array('allow',
				'actions'=>array('create','view'),
				'expression' => 'Yii::app()->controller->accessFilter()'
			),
			

			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}



	public static function deleteDir($dirPath) {

		if (!file_exists($dirPath)) return true;
		    if (!is_dir($dirPath)) return unlink($dirPath);
		    foreach (scandir($dirPath) as $item) {
		        if ($item == '.' || $item == '..') continue;
		        if (!self::deleteDir($dirPath.DIRECTORY_SEPARATOR.$item)) return false;
		    }
		return rmdir($dirPath);
	}

	public function accessFilter()
	{
		// is the data base dirty and do they have access?

		return (int)Yii::app()->user->role >=  User::ROLE_ORGANISATION;
	}


	public function actionCreate($group_id)
	{

		$this->layout = '/layouts/vanilla';

		$EmailTemplate = new EmailTemplate;


		$CampaignGroup = CampaignGroup::model()->with(array(
			'campaign' => array(
				'with' => array(
					'outcomes' => array(
						'index' => 'id'
					)
				)
			)
		))->findByPK($group_id);

		if (!$CampaignGroup) {
			throw new CHttpException(404, 'Unable to find campaign group.');
		}

		$EmailTemplate->campaign_group_id = $CampaignGroup->id;

		if(isset($_POST['EmailTemplate']))
		{

			$EmailTemplate->attributes=$_POST['EmailTemplate'];
			$EmailTemplate->file=CUploadedFile::getInstance($EmailTemplate,'file');

			if($EmailTemplate->validate())
			{
				//Email validates, lets unzip and check the contents
				$EmailTemplate->folder = uniqid();

				//create folder in templates
				$path = dirname(Yii::app()->request->scriptFile) . '/templates/' . $EmailTemplate->folder;
				mkdir($path);

				//error currently is false, if it become true
				//we destory the folder we just created
				$error = false;
				
				//unzip file to the path
				$zip = new ZipArchive;
				$res = $zip->open($EmailTemplate->file->tempName);


				//Valid zip file
				if ($res === TRUE)
				{
		
					//unzip to folder
					$zip->extractTo($path);
					$zip->close();

					//loop through files in the folder
					$files = array();
					
					foreach (scandir($path) as $file)
					{
						if ('.' === $file) continue;
						if ('..' === $file) continue;
						if ('__MACOSX' === $file) continue;
						
						$files[] = $file;
					}
					
					//check that template.html is in the array
					if (!in_array('template.html', $files))
					{
						$error = true;
						$EmailTemplate->addError('file', 'The zip file does not contain template.html or it contains sub folders.');
					}
					else
					{
						//remove template.html from image array
						$EmailTemplate->images =  json_encode(array_diff($files, array('template.html','__MACOSX')));

						//check template.html for a unsubscribe tag
						$EmailTemplate->html = file_get_contents($path . '/template.html');
						if (stripos($EmailTemplate->html , '%recipient.insider_unsubscribe%') === false)
						{
							$error = true;
							$EmailTemplate->addError('file', 'The template.html file does not have an unsubscribe tag: "%recipient.insider_unsubscribe%"');
						}

						//check template html for remote images
						if ((stripos($EmailTemplate->html , 'src="http') > 0) || (stripos($EmailTemplate->html , "src='http") > 0))
						{
							$error = true;
							$EmailTemplate->addError('file', 'The template.html file contains one or more images that are externally referenced using a http link. They should be local images within your zip file such as: " &lt;img src=\'logo.png\' /&gt;"');
						}

						//check for referenced images within EmailTemplate
						preg_match_all('/< *img[^>]*src *= *["\']?([^"\']*)/i', $EmailTemplate->html, $matches);
						
						foreach ($matches[1] as $match) {
							if (!in_array($match, $files))
							{
								$error = true;
								$EmailTemplate->addError('file', 'The zip file does not contain an image called ' . $match . ' which is referenced in the HTML.' );
							}
						}


						// check for incorrect outcome references
						//check for referenced images within EmailTemplate
						preg_match_all("/%recipient.outcome_(\d+)%/", $EmailTemplate->html, $outcomes);

						foreach($outcomes[1] as $key => $match)
						{
							if(!in_array($match, array_keys($CampaignGroup->campaign->outcomes)))
							{
								$error = true;
								$EmailTemplate->addError('file', 'The template contains a reference to the outcome "' . $outcomes[0][$key] . '" which does not belong to this campaign.' );
							}
						}
					}

					//delete folder and contents if there is an error
					if ($error)
					{
						//delete files
						$this->deleteDir($path);
						$EmailTemplate->unsetAttributes();
					}
					else
					{
						//Delete existing templates - protection to make sure users hasn't reposted form 
						EmailTemplate::model()->deleteAll(array('condition'=>'campaign_group_id = '.$CampaignGroup->id));  


						$EmailTemplate->save();

						// Redirect to view
						$this->redirect(array('emailTemplate/view', 'template_id' => $EmailTemplate->id, 'campaign_id'=>$CampaignGroup->campaign_id,'group_id'=>$CampaignGroup->id));

					}


				}
				//Invalid zip file
				else
				{
					 $EmailTemplate->addError('file', 'The file you uploaded was not a valid zip file');
				}

			}
		}


		$this->breadcrumbs=array(
			'Campaigns' => array('campaign/index'),
			$CampaignGroup->campaign->name => array('campaign/createUpdate', 'id' => $CampaignGroup->campaign_id),
			'Group ' . $CampaignGroup->name => array('campaignGroup/update', 'id' => $CampaignGroup->id, 'campaign_id' => $CampaignGroup->campaign_id),
			'Upload Email Template',
		);

		$this->render('create', array(
			'EmailTemplate'=>$EmailTemplate,
			'Outcomes' => $CampaignGroup->campaign->outcomes,
		));

	}

	public function actionView($template_id,$group_id,$campaign_id)
	{

		$this->layout = '/layouts/vanilla';

		$fromEmailDomain = Yii::app()->params['insiderEmailDomain'];
		$testEmailInvalid = false;
		$EmailTemplate = EmailTemplate::model()->findByPK($template_id);

		if (is_null($EmailTemplate)) {
			throw new CHttpException(404, 'Unable to find template.');
		}

		$CampaignGroup = CampaignGroup::model()->with(array('campaign'))->findByPK($group_id);

		if (is_null($CampaignGroup))
		{
			throw new CHttpException(404, 'Unable to find campaign group.');
		}

		if (isset($_POST['delete']))
		{

			$path = dirname(Yii::app()->request->scriptFile) . '/templates/' . $EmailTemplate->folder;

			$this->deleteDir($path);

			$EmailTemplate->delete();
			$this->redirect(array('emailTemplate/create', 'campaign_id' => $campaign_id, 'group_id'=> $group_id));

		}

		//send a test email
		if (isset($_POST['EmailTemplate']['email_test_recipient']) && $_POST['EmailTemplate']['email_test_recipient'])
		{

			$insiderEmailDomain = 'abc123.mailgun.org';

			$mailgunApiVerify = new MailgunCampaign($fromEmailDomain, Yii::app()->params['mailgun']['public-key']);

			try {

				$emails = explode(',', $_POST['EmailTemplate']['email_test_recipient']);

				foreach ($emails as $key => $email) {

					$emails[$key] = trim($email);
					$validEmailResponse = $mailgunApiVerify->validateEmailAddress($emails[$key]);

					if($validEmailResponse['is_valid'] != true){
						unset($emails[$key]);
					}
				}

				if(sizeof($emails)){

					// send it
					foreach ($emails as $key => $email) {

						$mailgunApi = new MailgunCampaign($fromEmailDomain, Yii::app()->params['mailgun']['key']);
						$message = $mailgunApi->newMessage();
						$message->setFrom('email@' . $fromEmailDomain, 'Application Name');

						$message->addTo($email);

						$html = $EmailTemplate->exampleEmail;
						$html = preg_replace("@%recipient.warehouse_id%@", '000', $html);

						foreach($CampaignGroup->campaign->outcomes as $Outcome)
						{
							if(strlen($Outcome->url))
							{
								$html = preg_replace("@%recipient.outcome_" . $Outcome->id . "%@", $Outcome->url, $html);
							}
						}

						$message->setHtml($html);

						$message->setSubject($CampaignGroup->subject);
						$message->send();
					}

					Yii::app()->user->setFlash('success', "Emails sent to " . implode(', ', $emails) . '.');
				}
				else
				{
					Yii::app()->user->setFlash('danger', 'No valid email addresses found to send to');
				}

				$this->refresh();

			}
			catch(Exception $e)
			{
				var_dump($e); exit;
				$testEmailInvalid = true;
			}

		}

		

		//check template for first_name
		if (stripos($EmailTemplate->html , '%recipient.first_name%') === false)
		{
			$EmailTemplate->addNotice('Are you aware that the template.html file does not have a recipient first name tag?: "%recipient.first_name%');
		}

		//check template for last_name
		if (stripos($EmailTemplate->html , '%recipient.last_name%') === false)
		{
			$EmailTemplate->addNotice('Are you aware that the template.html file does not have a recipient last name tag?: "%recipient.last_name%');
		}

		//check we have an email subject
		if (!strlen($CampaignGroup->subject))
		{
			$EmailTemplate->addNotice('You have not set an email subject for this group.');

		}

		//check for outcomes in the template
		preg_match_all("/%recipient.outcome_(\d+)%/", $EmailTemplate->html, $outcomes);

		if (!sizeof($outcomes[0]))
		{
			$EmailTemplate->addNotice('Are you aware there are no outcome tags in the template?');
		}
		else
		{
			foreach ($outcomes[0] as $tag)
			{
				$EmailTemplate->addNotice('We found the following tag in your template: ' . $tag);
			}
		}


		$this->breadcrumbs=array(
			'Campaigns' => array('campaign/index'),
			$CampaignGroup->campaign->name => array('campaign/createUpdate', 'id' => $CampaignGroup->campaign_id),
			'Group ' . $CampaignGroup->name => array('campaignGroup/update', 'id' => $CampaignGroup->id, 'campaign_id' => $CampaignGroup->campaign_id),
			'View Email Template',
		);


		$this->render('view', array(
			'EmailTemplate'=>$EmailTemplate,
			'subject' => $CampaignGroup->subject,
			'has_ran' => $CampaignGroup->campaign->hasBeenRun,
			'testEmailInvalid' => $testEmailInvalid,

		));



	}


}

?>