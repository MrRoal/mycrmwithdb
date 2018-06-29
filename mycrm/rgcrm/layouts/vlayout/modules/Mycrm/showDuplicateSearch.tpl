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
{strip}
<div class='modelContainer'>
	<div class="modal-header contentsBackground">
        <button data-dismiss="modal" class="close" title="{vtranslate('LBL_CLOSE')}">&times;</button>
		<h3>{vtranslate('LBL_MERGING_CRITERIA_SELECTION', $MODULE)}</h3>
	</div>
	<form class="form-horizontal" id="findDuplicate" action="index.php" method="POST">
		<input type='hidden' name='module' value='{$MODULE}' />
		<input type='hidden' name='view' value='FindDuplicates' />
		<br>
		<div class="control-group">
			<span class="control-label">
				{vtranslate('LBL_AVAILABLE_FIELDS', $MODULE)}
			</span>
			<div class="controls">
				<div class="row-fluid">
					<span class="span10" style="max-width: 200px;">
						<select id="fieldList" class="select2 row-fluid" multiple="true" name="fields[]"
							data-validation-engine="validate[required]">
							{foreach from=$FIELDS item=FIELD}
								{if $FIELD->isViewableInDetailView()}
									<option value="{$FIELD->getName()}">{vtranslate($FIELD->get('label'), $MODULE)}</option>
								{/if}
							{/foreach}
						</select>
					</span>
				</div>
				<div class="row-fluid">
					<label><input type="checkbox" name="ignoreEmpty" checked /><span class="alignMiddle">&nbsp;{vtranslate('LBL_IGNORE_EMPTY_VALUES', $MODULE)}</span></label
				</div>
				<br><br>
			</div>
		</div>
		<div class="modal-footer">
			<div class="pull-right cancelLinkContainer">
				<a class="cancelLink" type="reset" data-dismiss="modal" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
			</div>
			<button class="btn btn-success" type="submit" disabled="true">
				<strong>{vtranslate('LBL_FIND_DUPLICATES', $MODULE)}</strong>
			</button>
		</div>
	</form>
</div>
{/strip}