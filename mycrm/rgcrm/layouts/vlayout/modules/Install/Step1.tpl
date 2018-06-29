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

<div class="row-fluid main-container">
	<div class="inner-container">
		<div class="row-fluid">
			<div class="span10">
				<h4>{vtranslate('LBL_WELCOME', 'Install')}</h4>
			</div>
			<div class="span2">
				<a href="https://wiki.mycrm.com/mycrm6/" target="_blank" class="pull-right">
					<img src="{'help.png'|vimage_path}" alt="Help-Icon"/>
				</a>
			</div>
		</div>
		<hr>

		<form class="form-horizontal" name="step1" method="post" action="index.php">
			<input type=hidden name="module" value="Install" />
			<input type=hidden name="view" value="Index" />
			<input type=hidden name="mode" value="Step2" />
			<div class="row-fluid">
				<div class="span4 welcome-image">
					<img src="{'wizard_screen.png'|vimage_path}" alt="Mycrm Logo"/>
				</div>
				<div class="span8">
					<div class="welcome-div">
						<h3>{vtranslate('LBL_WELCOME_TO_MYCRM6_SETUP_WIZARD', 'Install')}</h3>
						{vtranslate('LBL_MYCRM6_SETUP_WIZARD_DESCRIPTION','Install')}
					</div>
					{if $LANGUAGES|@count > 1}
					<div>
						<label>{vtranslate('LBL_CHOOSE_LANGUAGE', 'Install')}
							<select name="lang" id="lang">
							{foreach key=header item=language from=$LANGUAGES}
								<option value="{$header}" {if $header eq $CURRENT_LANGUAGE}selected{/if}>{vtranslate("$language",'Install')}</option>
							{/foreach}
							</select>
						</label>
					</div>
					{/if}
				</div>
			</div>
			<div class="row-fluid">
				<div class="button-container">
					<input type="submit" class="btn btn-large btn-primary" value="{vtranslate('LBL_INSTALL_BUTTON','Install')}"/>
				</div>
			</div>
		</form>
	</div>
</div>