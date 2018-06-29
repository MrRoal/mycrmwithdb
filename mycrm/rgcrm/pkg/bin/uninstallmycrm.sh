#*********************************************************************************
# The contents of this file are subject to the mycrm CRM Public License Version 1.0
# ("License"); You may not use this file except in compliance with the License
# The Original Code is:  mycrm CRM Open Source
# The Initial Developer of the Original Code is mycrm.
# Portions created by mycrm are Copyright (C) mycrm.
# All Rights Reserved.
#
# ********************************************************************************

INS_DIR="../.."
WRKDIR=`pwd`
PREV_DIR=".."

APACHE_STATUS=`cat startmyCrm.sh | grep ^apache_bundled | cut -d "=" -f2 | cut -d "'" -f2`
cd ${INS_DIR}
cd ${PREV_DIR}
if [ ${APACHE_STATUS} == "false" ]
then
	diff conf/httpd.conf conf/mycrm_conf/mycrmcrm-5.4.0/httpd.conf > /dev/null;
	if [ $? -eq 0 ]
	then
		cp conf/mycrmCRMBackup/mycrmcrm-5.4.0/httpd.mycrm.crm.conf conf/httpd.conf
		echo "The httpd.conf file successfully reverted"
	else
		echo "The httpd.conf file under apache/conf has been edited since installation. Hence the uninstallation will not revert the httpd.conf file. The original httpd.conf file is present in <apache home>/conf/mycrmCRMBackup/mycrmcrm-5.4.0/httpd.mycrm.crm.conf. Kindly revert the same manually"
	fi

	diff modules/libphp4.so modules/mycrm_modules/mycrmcrm-5.4.0/libphp4.so > /dev/null;
	if [ $? -eq 0 ]
        then
		cp modules/mycrmCRMBackup/mycrmcrm-5.4.0/libphp4.mycrm.crm.so modules/libphp4.so
		echo "The libphp4.so file successfully reverted"
	else
		echo "The libphp4.so file under apache/modules has been edited since installation. Hence the uninstallation will not revert the libphp4.so file. The original libphp4.so file is present in <apache home>/modules/mycrmCRMBackup/mycrmcrm-5.4.0/libphp4.mycrm.crm.so. Kindly revert the same manually"
	fi

	cd -

	if [ -d $PWD/mycrmcrm-5.4.0 ]; then
		echo "Uninstalling mycrmCRM from the system..."
		rm -rf ../conf/mycrm_conf/mycrmcrm-5.4.0
		rm -rf ../modules/mycrm_modules/mycrmcrm-5.4.0
		rm -rf mycrmcrm-5.4.0
		echo "Uninstallation of mycrmCRM completed"
		cd ${HOME}
	fi

else
	cd -
	if [ -d $PWD/mycrmcrm-5.4.0 ]; then
                echo "Uninstalling mycrmCRM from the system..."
		rm -rf ../conf/mycrm_conf/mycrmcrm-5.4.0
                rm -rf ../modules/mycrm_modules/mycrmcrm-5.4.0
                rm -rf mycrmcrm-5.4.0
                echo "Uninstallation of mycrmCRM completed"
                cd ${HOME}
        fi
fi
