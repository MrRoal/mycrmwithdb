{*<!--
/*********************************************************************************
** The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *
 ********************************************************************************/
-->*}

<button name="next" class="create btn"
	   onclick="location.href='index.php?module={$FOR_MODULE}&view=Import&return_module={$FOR_MODULE}&return_action=index'" ><strong>{'LBL_IMPORT_MORE'|@vtranslate:$MODULE}</strong></button>
&nbsp;&nbsp;
<button name="next" class="cancel btn"
		onclick="return window.open('index.php?module={$MODULE}&for_module={$FOR_MODULE}&view=List&start=1&foruser={$OWNER_ID}','test','width=700,height=650,resizable=1,scrollbars=0,top=150,left=200');"><strong>{'LBL_VIEW_LAST_IMPORTED_RECORDS'|@vtranslate:$MODULE}</strong></button>
&nbsp;&nbsp;
{if $MERGE_ENABLED eq '0'}
<button name="next" class="delete btn"
		onclick="location.href='index.php?module={$FOR_MODULE}&view=Import&mode=undoImport&foruser={$OWNER_ID}'"><strong>{'LBL_UNDO_LAST_IMPORT'|@vtranslate:$MODULE}</strong></button>
&nbsp;&nbsp;
{/if}
<button name="cancel" class="edit btn btn-success"
		onclick="location.href='index.php?module={$FOR_MODULE}&view=List'"><strong>{'LBL_FINISH_BUTTON_LABEL'|@vtranslate:$MODULE}</strong></button>