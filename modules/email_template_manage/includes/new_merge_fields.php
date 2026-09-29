<?php


hooks()->add_filter('project_merge_fields', function( $fields , $args )
{

    $project_id = $args['id'];

    $project_status = '';

    if ( !empty( $args['project']->status ) )
    {

        $status_data = get_project_status_by_id( $args['project']->status );

        if ( !empty( $status_data[ 'name' ] ) )
            $project_status = $status_data[ 'name' ];

    }

    if ( empty( $project_status ) )
    {
        $project = get_instance()->db->select('status')->from(db_prefix().'projects')->where('id',$project_id)->get()->row();

        if ( !empty( $project->status ) )
        {

            $status_data = get_project_status_by_id( $project->status );

            if ( !empty( $status_data[ 'name' ] ) )
                $project_status = $status_data[ 'name' ];

        }

    }

    $fields['{project_id}']     = $project_id;
    $fields['{project_status}'] = $project_status;

    return $fields;

} , 10 , 2 );


hooks()->add_filter('proposal_merge_fields', function( $fields , $args )
{

    $proposal_id = $args['id'];

    $proposal_status = '';

    if ( !empty( $args['proposal']->status ) )
    {

        $proposal_status = format_proposal_status( $args['proposal']->status , '' , false );

    }

    $fields['{proposal_status}'] = $proposal_status;

    return $fields;

} , 10 , 2 );



hooks()->add_filter('staff_merge_fields', function( $fields , $args )
{

    $phonenumber = "";


    if( !empty( $args['staff']->phonenumber ) )
        $phonenumber = $args['staff']->phonenumber;


    $fields['{staff_phonenumber}'] = $phonenumber;

    return $fields;

} , 10 , 2 );



hooks()->add_filter('task_merge_fields', function( $fields , $args )
{

    $task_id = $args['id'];


    $task_checklist_current = '';
    $task_checklist_all = '';
    $task_checklist_finished = '';
    $task_checklist_unfinished = '';

    $check_lists = get_instance()->db->select('finished, description')->from(db_prefix().'task_checklist_items')->where('taskid',$task_id)->get()->result();

    if ( !empty( $check_lists ) )
    {

        foreach ( $check_lists as $check_list )
        {

            $task_checklist_all .= '<li>'.$check_list->description.'</li>';

            if ( $check_list->finished == 1 )
                $task_checklist_finished .= '<li>'.$check_list->description.'</li>';
            else
                $task_checklist_unfinished .= '<li>'.$check_list->description.'</li>';

        }

        if ( !empty( $task_checklist_all ) )
            $task_checklist_all = '<ul>'.$task_checklist_all.'</ul>';

        if ( !empty( $task_checklist_finished ) )
            $task_checklist_finished = '<ul>'.$task_checklist_finished.'</ul>';

        if ( !empty( $task_checklist_unfinished ) )
            $task_checklist_unfinished = '<ul>'.$task_checklist_unfinished.'</ul>';

    }


    $fields['{task_checklist_current}']     = $task_checklist_current;
    $fields['{task_checklist_all}']         = $task_checklist_all;
    $fields['{task_checklist_finished}']    = $task_checklist_finished;
    $fields['{task_checklist_unfinished}']  = $task_checklist_unfinished;


    return $fields;

} , 10 , 2 );

