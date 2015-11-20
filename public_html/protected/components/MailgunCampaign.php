<?php

class MailgunCampaign extends MailgunApi
{
	public function __construct($domain, $key)
	{
		//$this->_domain = $domain;
		parent::__construct($domain, $key);
	}

	public function createCampaign($domain, $params)
	{
		//add prefix
		$params['id'] = Yii::app()->params['campaignPrefix'] . $params['id'];
		return $this->_performRequest('POST', $this->_url . $domain . '/campaigns', null, $params);
	}
	
	public function getCampaign($domain, $id)
	{
		return $this->_performRequest('GET', $this->_url . $domain . '/campaigns/' . Yii::app()->params['campaignPrefix'] . (int)$id);
	}
	
	public function getCampaignOpensByRecipient($domain, $id)
	{
		//returns first 100 records
		return $this->_performRequest('GET', $this->_url . $domain . '/campaigns/' . Yii::app()->params['campaignPrefix'] . (int)$id . '/opens?groupby=recipient');
	}


	public function getCampaignOpensByRecipientCount($domain, $id)
	{
		//returns just the count
		return $this->_performRequest('GET', $this->_url . $domain . '/campaigns/' . Yii::app()->params['campaignPrefix'] . (int)$id . '/opens?groupby=recipient&count=1');
	}

	public function validateEmailAddress($address)
	{
		// pass it along to the multiple version below
		return $this->_performRequest('GET', $this->_url . 'address/validate?address=' . $address);
	}

	public function validateEmailAddresses($addresses)
	{
		// validate one or more comma separated email addresses
		return $this->_performRequest('GET', $this->_url . 'address/parse?addresses=' . (implode(',', $addresses)));
	}

	public function getOpensByCampaign($domain, $id, $page = 1)
	{
		//returns first 100 records at a time
		return $this->_performRequest('GET', $this->_url . $domain . '/campaigns/' . Yii::app()->params['campaignPrefix'] . (int)$id . '/events?limit=100&page=' . $page . '&event=opened');
	}

	public function getBouncesByCampaign($domain, $id, $page = 1)
	{
		//returns first 100 records at a time
		return $this->_performRequest('GET', $this->_url . $domain . '/campaigns/' . Yii::app()->params['campaignPrefix'] . (int)$id . '/events?limit=100&page=' . $page . '&event=bounced');
	}

}

?>