{*<!--
/************************************************************************************
** The contents of this file are subject to the mycrm CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  mycrm CRM Open Source
* The Initial Developer of the Original Code is mycrm.
* Portions created by mycrm are Copyright (C) mycrm.
* All Rights Reserved.
*************************************************************************************/
-->*}
{strip}
	{include file="Header.tpl"|vtemplate_path:$MODULE}
	<div class="container-fluid page-container">
		<div class="row-fluid">
			<div class="span6">
				<div class="logo">
					<img src="{vimage_path('vt1.png')}" alt="Mycrm Logo"/>
				</div>
			</div>
			<div class="span6">
				<div class="head pull-right">
					<h3> {vtranslate('LBL_MIGRATION_WIZARD', $MODULE)}</h3>
				</div>
			</div>
		</div>
		<div class="row-fluid main-container">
			<div class="span12 inner-container">
					<div class="row-fluid">
						<div class="span10">
							<h4> {vtranslate('LBL_MIGRATION_COMPLETED', $MODULE)} </h4> 
						</div>
					</div>
					<hr>
					<div class="row-fluid">
						<div class="span4 welcome-image">
							<img src="{vimage_path('migration_screen.png')}" alt="Mycrm Logo"/>
						</div>
						<div class="span1"></div>
						<div class="span6">
							<br><br>
							<h5>{vtranslate('LBL_MIGRATION_COMPLETED_SUCCESSFULLY', $MODULE)}  </h5><br><br>
								{vtranslate('LBL_RELEASE_NOTES', $MODULE)}<br>
								{vtranslate('LBL_CRM_DOCUMENTATION', $MODULE)}<br>
								{vtranslate('LBL_TALK_TO_US_AT_FORUMS', $MODULE)}<br>
								{vtranslate('LBL_DISCUSS_WITH_US_AT_BLOGS', $MODULE)}<br><br>
								Connect with us &nbsp;&nbsp;
								<a href="https://www.facebook.com/mycrm" target="_blank"><img src="{vimage_path('facebook.png')}"></a> 
	                            &nbsp;&nbsp;<a href="https://twitter.com/mycrmcrm" target="_blank"><img src="{vimage_path('twitter.png')}"></a> 
	                            &nbsp;&nbsp;<a href="//www.mycrm.com/products/crm/privacy_policy.html" target="_blank"><img src="{vimage_path('linkedin.png')}"></a> 
								<br><br>
							<div class="button-container">
								<input type="button" onclick="window.location.href='index.php'" class="btn btn-large btn-primary" value="{vtranslate('Finish', $MODULE)}"/>
							</div>
					</div>
				</div>
			</div>
		</div>
		</div>
	</div>