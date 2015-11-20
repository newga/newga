<?php

interface MailgunObject
{
    /**
     * @return array POST-data for Mailgun API request
     */
    public function getPostData();
}

?>