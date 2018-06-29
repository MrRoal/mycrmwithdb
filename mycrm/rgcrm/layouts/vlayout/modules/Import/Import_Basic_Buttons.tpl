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

<button type="submit" name="next"  class="btn btn-success"
		onclick="return ImportJs.uploadAndParse();"><strong>{'LBL_NEXT_BUTTON_LABEL'|@vtranslate:$MODULE}</strong></button>
&nbsp;&nbsp;
<a name="cancel" class="cursorPointer cancelLink" value="{'LBL_CANCEL'|@vtranslate:$MODULE}" onclick="location.href='index.php?module={$FOR_MODULE}&view=List'">
		{'LBL_CANCEL'|@vtranslate:$MODULE}
</a>