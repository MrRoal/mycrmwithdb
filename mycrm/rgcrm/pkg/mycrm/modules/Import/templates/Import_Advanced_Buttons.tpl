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

<button type="submit" name="import" id="importButton" class="crmButton big edit btn btn-success"
		><strong>{'LBL_IMPORT_BUTTON_LABEL'|@vtranslate:$MODULE}</strong></button>
&nbsp;&nbsp;
<a type="button" name="cancel" value="{'LBL_CANCEL'|@vtranslate:$MODULE}" class="cursorPointer cancelLink" onclick="window.history.back()">
	{'LBL_CANCEL'|@vtranslate:$MODULE}
</a>