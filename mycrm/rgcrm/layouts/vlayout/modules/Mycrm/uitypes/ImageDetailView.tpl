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
{foreach key=ITER item=IMAGE_INFO from=$RECORD->getImageDetails()}
	{if !empty($IMAGE_INFO.path) && !empty({$IMAGE_INFO.orgname})}
		<img src="{$IMAGE_INFO.path}_{$IMAGE_INFO.orgname}" width="150" height="80">
	{/if}
{/foreach}