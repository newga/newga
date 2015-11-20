<?php

class RSS extends CApplicationComponent
{
	public $items;
	
	public function __construct()
	{
		Yii::import("application.extensions.rss.rss_fetch", true);
	}
	
	public function getRss($feed)
	{
		if(!strlen($feed))
		{
			// Always use http protocol
			$feed = '';
		}
		
		/*
		Array
		(
			[title] => 
			[description] => 
			[link] => 
			[guid] => 
			[pubdate] => 
			[summary] => 
			[date_timestamp] => 
		)
		*/
		
		$this->items = array();
		$numberOfItems = 10;
		
		
		
		$rssFeed = fetch_rss($feed);
		
		if(sizeof($rssFeed->items))
		{
			$this->items = array_slice($rssFeed->items, 0, $numberOfItems);
		}
	}
}


?>