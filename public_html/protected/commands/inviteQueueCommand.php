<?php

class inviteQueueCommand extends CConsoleCommand
{
    public function run($args)
    {
	    exit();

        date_default_timezone_set("Europe/London");

        print "Getting campaigns to process \n";

        $CampaignCollection = Campaign::model()->with('query')->findAll(array(
           "condition" => "processing = 0 AND status = :status AND invite = 1",
           "params" => array(":status" => Campaign::STATUS_QUEUED)
        ));

        print count($CampaignCollection) . ' campaigns to process' . "\n";

        $campaignIDs = [];

        foreach($CampaignCollection as $Campaign)
        {
            $campaignIDs[] = $Campaign->id;
            print "Will process ".$Campaign->id." \n";
        }

        $command = Yii::app()->db->createCommand();
        $command->update('campaign', array("processing" => 1), array('in', 'id', $campaignIDs));

        foreach($CampaignCollection as $Campaign)
        {
            $InviteEmail = new InviteEmail;
            $InviteEmail->send($Campaign);

            $Campaign->processing = 0;
            $Campaign->save(true, array("processing"));

            print "Completed processing ".$Campaign->id." \n";
            $Campaign->refresh();
            print "Status of campaign was ".$Campaign->getStatusText() . "\n\n";
        }

    }
}

?>