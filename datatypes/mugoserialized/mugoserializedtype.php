<?php

class MugoSerializedType extends eZDataType
{
    const DATA_TYPE_STRING = "mugoserialized";
    const COLS_FIELD = 'data_int1';
    const COLS_VARIABLE = '_eztext_cols_';
	const DATA_FIELD = 'data_text';
    
    function __construct()
    {
        $this->eZDataType( self::DATA_TYPE_STRING, ezpI18n::tr( 'kernel/classes/datatypes', "Serialized content", 'Datatype name' ),
                           array( 'serialize_supported' => true,
                                  'object_serialize_map' => array( 'data_text' => 'text' ) ) );
    }

    /*!
     Set class attribute value for template version
    */
    function initializeClassAttribute( $classAttribute )
    {
        if ( $classAttribute->attribute( self::COLS_FIELD ) == null )
            $classAttribute->setAttribute( self::COLS_FIELD, 10 );
        $classAttribute->store();
    }

    /*!
     Sets the default value.
    */
    function initializeObjectAttribute( $contentObjectAttribute, $currentVersion, $originalContentObjectAttribute )
    {
        if ( $currentVersion != false )
        {
            $dataText = $originalContentObjectAttribute->attribute( self::DATA_FIELD );
            $contentObjectAttribute->setAttribute( self::DATA_FIELD, $dataText );
        }
        $contentClassAttribute = $contentObjectAttribute->contentClassAttribute();
        if ( $contentClassAttribute->attribute( self::COLS_FIELD ) == 0 )
        {
            $contentClassAttribute->setAttribute( self::COLS_FIELD, 10 );
            $contentClassAttribute->store();
        }
    }

    /*!
     Validates the input and returns true if the input was
     valid for this datatype.
    */
    function validateObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        return eZInputValidator::STATE_ACCEPTED;
    }

    /*!
     Fetches the http post var string input and stores it in the data instance.
    */
    function fetchObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        if ( $http->hasPostVariable( $base . "_data_text_" . $contentObjectAttribute->attribute( "id" ) ) )
        {
            $data = $http->postVariable( $base . "_data_text_" . $contentObjectAttribute->attribute( "id" ) );
            $contentObjectAttribute->setAttribute( self::DATA_FIELD, serialize( $data ) );
            return true;
        }
        return false;
    }

    /*!
     Store the content.
    */
    function storeObjectAttribute( $attribute )
    {
    }

    /*!
     Simple string insertion is supported.
    */
    function isSimpleStringInsertionSupported()
    {
        return true;
    }

    /*!
     Inserts the string \a $string in the \c 'data_text' database field.
    */
    function insertSimpleString( $object, $objectVersion, $objectLanguage,
                                 $objectAttribute, $string,
                                 &$result )
    {
        $result = array( 'errors' => array(),
                         'require_storage' => true );
        $objectAttribute->setContent( $string );
        $objectAttribute->setAttribute( self::DATA_FIELD, serialize( $string ) );
        return true;
    }

    /*!
     Returns the content.
    */
    function objectAttributeContent( $contentObjectAttribute )
    {
        return unserialize( $contentObjectAttribute->attribute( self::DATA_FIELD ) );
    }

    function fetchClassAttributeHTTPInput( $http, $base, $classAttribute )
    {
        $column = $base . self::COLS_VARIABLE . $classAttribute->attribute( 'id' );
        if ( $http->hasPostVariable( $column ) )
        {
            $columnValue = $http->postVariable( $column );
            $classAttribute->setAttribute( self::COLS_FIELD,  $columnValue );
            return true;
        }
        return false;
    }

    /*!
     Returns the meta data used for storing search indeces.
    */
    function metaData( $contentObjectAttribute )
    {
        return null;
    }

    /*!
     \return string representation of an contentobjectattribute data for simplified export

    */
    function toString( $contentObjectAttribute )
    {
        return $contentObjectAttribute->attribute( self::DATA_FIELD );
    }

    function fromString( $contentObjectAttribute, $string )
    {
        return $contentObjectAttribute->setAttribute( self::DATA_FIELD, $string );
    }

    function hasObjectAttributeContent( $contentObjectAttribute )
    {
        return unserialize( $contentObjectAttribute->attribute( self::DATA_FIELD ) ) ? true : false;
    }

    /*!
     Returns the text.
    */
    function title( $data_instance, $name = null )
    {
        return $data_instance->attribute( self::DATA_FIELD );
    }

    function isIndexable()
    {
        return false;
    }

    function isInformationCollector()
    {
        return false;
    }

    function serializeContentClassAttribute( $classAttribute, $attributeNode, $attributeParametersNode )
    {
        $dom = $attributeParametersNode->ownerDocument;
        $textColumns = $classAttribute->attribute( self::COLS_FIELD );

        $textColumnCountNode = $dom->createElement( 'text-column-count' );
        $textColumnCountNode->appendChild( $dom->createTextNode( $textColumns ) );
        $attributeParametersNode->appendChild( $textColumnCountNode );
    }

    function unserializeContentClassAttribute( $classAttribute, $attributeNode, $attributeParametersNode )
    {
        $textColumns = $attributeParametersNode->getElementsByTagName( 'text-column-count' )->item( 0 )->textContent;
        $classAttribute->setAttribute( self::COLS_FIELD, $textColumns );
    }

    /*
    function diff( $old, $new, $options = false )
    {
        $diff = new eZDiff();
        $diff->setDiffEngineType( $diff->engineType( 'text' ) );
        $diff->initDiffEngine();
        $diffObject = $diff->diff( $old->content(), $new->content() );
        return $diffObject;
    }
    */

    //??
    function supportsBatchInitializeObjectAttribute()
    {
        return true;
    }
}

eZDataType::register( MugoSerializedType::DATA_TYPE_STRING, "MugoSerializedType" );

?>
