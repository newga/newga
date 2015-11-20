<span>
    <?php
    switch($Question->option_id)
    {

        // Venue
        case QueryQuestion::OPTION_VENUE:
            $Venue = Venue::model()->findByPk($query_option);
            print $Venue->title;
        break;

        // Organisation
        case QueryQuestion::OPTION_ORGANISATION:
            $Organisation = Organisation::model()->findByPk($query_option);
            print $Organisation->title;
        break;


        // Invite
        case QueryQuestion::OPTION_INVITE:
            $InviteQuery = Query::model()->findByPk($query_option);
            print $InviteQuery->name;
        break;


        // Culture Segment
        case QueryQuestion::OPTION_CS:

            $CultureSegment = CultureSegment::model()->findByPk($query_option);
            print $CultureSegment->name;
        break;


        // Artforms
        case QueryQuestion::OPTION_ARTFORM:
            $Artforms = Artform::model()->findByPk($query_option);
            print $Artform->title;

        // Level of engagement
        case QueryQuestion::OPTION_LOE:

            $LevelsOfEngagement = QueryQuestion::model()->levelsOfEngagement();
            foreach ($LevelsOfEngagement as $LOEid => $LOEName)
            {
                if ($query_option == $LOEid) print $LOEName;
            }

        break;


        // Campaign
        case QueryQuestion::OPTION_CAMPAIGN:

            $Campaign = Campaign::model()->findByPk($query_option);
            print $Campaign->name;

        break;

    }
?>
</span>