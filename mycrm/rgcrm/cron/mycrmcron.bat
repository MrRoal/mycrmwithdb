@echo OFF
REM #*********************************************************************************
REM # The contents of this file are subject to the mycrm CRM Public License Version 1.0
REM # ("License"); You may not use this file except in compliance with the License
REM # The Original Code is:  mycrm CRM Open Source
REM # The Initial Developer of the Original Code is mycrm.
REM # Portions created by mycrm are Copyright (C) mycrm.
REM # All Rights Reserved.
REM #
REM # ********************************************************************************

set MYCRMCRM_ROOTDIR="C:\Program Files\mycrmcrm5\apache\htdocs\mycrmCRM"
set PHP_EXE="C:\Program Files\mycrmcrm5\php\php.exe"

cd /D %MYCRMCRM_ROOTDIR%

%PHP_EXE% -f mycrmcron.php 
