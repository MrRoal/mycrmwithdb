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
<div class='listViewPageDiv'>
	<div class="row-fluid  listViewActionsDiv">
		<span class="btn-toolbar span4">
			<span class="btn-group listViewMassActions">
				{foreach item=LISTVIEW_BASICACTION from=$LISTVIEW_LINKS}
					<span class="btn-group">
						<button id="{$MODULE}_listView_basicAction_{Mycrm_Util_Helper::replaceSpaceWithUnderScores($LISTVIEW_BASICACTION->getLabel())}" class="btn btn-danger" {if stripos($LISTVIEW_BASICACTION->getUrl(), 'javascript:')===0} onclick='{$LISTVIEW_BASICACTION->getUrl()|substr:strlen("javascript:")};'{else} onclick='window.location.href="{$LISTVIEW_BASICACTION->getUrl()}"'{/if}><strong>{vtranslate($LISTVIEW_BASICACTION->getLabel(), $MODULE)}</strong></button>
					</span>
				{/foreach}
			</span>
		</span>
		<span class='span4'><div class="textAlignCenter"><h3 style='margin-top:2px'>{vtranslate('LBL_DUPLICATE')}  {vtranslate($MODULE, $MODULE)}</h3></div></span>
		<span class="span4 btn-toolbar">
			{include file='ListViewActions.tpl'|@vtemplate_path}
		</span>
	</div>
	<div id="listViewContents" class="listViewContentDiv">
