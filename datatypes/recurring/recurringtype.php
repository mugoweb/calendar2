<?php

class RecurringType extends MugoSerializedType
{
    const DATA_TYPE_STRING = "recurring";
    
    function __construct()
    {
        $this->eZDataType( self::DATA_TYPE_STRING, ezpI18n::tr( 'kernel/classes/datatypes', "Recurring Events", 'Datatype name' ),
                           array( 'serialize_supported' => true,
                                  'object_serialize_map' => array( 'data_text' => 'text' ) ) );
    }


    /*!
     Fetches the http post var string input and stores it in the data instance.
    */
	function fetchObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
	{
		$scope = $base . "_serialize_" . $contentObjectAttribute->attribute( "id" );
		
		$contentObjectAttribute->setAttribute( self::DATA_FIELD, serialize( $_REQUEST[ $scope ] ) );
        
		return true;
    }
    
}

eZDataType::register( RecurringType::DATA_TYPE_STRING, 'RecurringType' );

?>
