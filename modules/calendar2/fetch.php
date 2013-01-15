<?php 
$tpl    = eZTemplate::factory();
$module = $Params['Module'];

$filter = array();
$filter[ 'calendar_node_id' ] = (int) $_REQUEST[ 'calendar_node_id' ];
$filter[ 'start' ]            = (int) $_REQUEST[ 'start' ];  // should default to beginning of month
$filter[ 'end' ]              = (int) $_REQUEST[ 'end' ]; // should default to end of month
// more security if we would check for a maximal time spawn ( end - start )

// categories
$filter[ 'categories' ] = array();

if( $_REQUEST[ 'categories' ] )
{
    $cats = explode( '-', $_REQUEST[ 'categories' ] );
    
    foreach( $cats as $cat )
    {
        if( (int)$cat )
        {
            $filter[ 'categories' ][] = $cat;
        }
    }
}

$result = calendar2EventHandler::fetch( $filter );

$return = array();

foreach( $result as $e => $entry )
{
    foreach( $entry as $f => $value )
    {
        switch( $f )
        {            
            case 'all_day':
            {
                $return[ $e ][ 'allDay' ] = ( $value == 1 );
            }
            break;
            
            case 'node_id':
            {
                $node = eZContentObjectTreeNode::fetch( $value );
                if( $node )
                {
                    $url = $node->attribute( 'url_alias' );
                    eZURI::transformURI( $url );
                }
                else
                {
                    $url = '/';
                }

                $return[ $e ][ 'url' ] = $url;
            }
            break;
            
            case 'categories':
            {
                if( $value )
                {
                    $categories = explode( ',', $value );
                    
                    foreach( $categories as $category )
                    {
                        $return[ $e ][ 'className' ][] = 'calendar2category-' . $category;
                    }
                }    
            }
            case 'start':
            case 'end':
            {
                // Return an ISO 8601 formatted date
                $return[$e][$f] = date( 'c', $value );
            }
            break;
            case 'title':
            {
                $return[ $e ][ $f ] = $value;
            }
            break;
            
        }
    }
}

// use a template for caching and url handling - maybe also for calendar content parsing
echo json_encode( $return );

eZExecution::cleanExit();
?>