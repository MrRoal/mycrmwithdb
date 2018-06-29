<?php
/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

function GetRelatedList($module,$relatedmodule,$focus,$query,$button,$returnset,$id='',$edit_val='',$del_val='',$skipActions=false) {
	return array( 'query' => $query , 'entries' => array() );
}

/**
 * Function that returns Activity History Query
 * @return <String>
 */
function GetHistory($parentmodule,$query,$id){
    return array('query' => $query);
}
?>
