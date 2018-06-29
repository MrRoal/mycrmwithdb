#/bin/sh
#*********************************************************************************
# The contents of this file are subject to the mycrm CRM Public License Version 1.0
# ("License"); You may not use this file except in compliance with the License
# The Original Code is:  mycrm CRM Open Source
# The Initial Developer of the Original Code is mycrm.
# Portions created by mycrm are Copyright (C) mycrm.
# All Rights Reserved.
#
# ********************************************************************************
setVariables()
{
	wdir=`pwd`
	echo 'copying the migrator_backup_connection file to the migrator_connection file'
	cp -f ../apache/htdocs/mycrmCRM/migrator_backup_connection.php ../apache/htdocs/mycrmCRM/migrator_connection.php
        chmod 777 ../apache/htdocs/mycrmCRM/migrator_connection.php
	export diffmac=0
}

checkInstallDir()
{
	bindir=$1
	retval=1
	if [ -d ${bindir} ]
	then
		if [ -f ${bindir}/startmyCrm.sh -a -f ${bindir}/stopmyCrm.sh ]
		then
			retval=0
		else
			echo "No such file ${bindir}/startmycrm.sh ${bindir}/stopmycrm.sh."
			echo "Invalid mycrm 4.0.1 directory specified"
		fi
	else
		echo "No such Directory: $bindir"
	fi
	return ${retval}	
}

getmycrm4_0_1_installdir()
{
	cancontinue=false
	
	while [ $cancontinue != "true" ]
	do
			local flag=0
		while [ $flag -eq 0 ]
		do
			echo ""
			echo "Is 4.0.1 mysql installed in the same machine as 4.2 GA? (Y/N)"
			echo ""

			read RESPONSE
			case $RESPONSE in
				[nN]|[nN][oO])
		echo "**********************"
		echo "**********************"
		echo "**********************"
        	echo "Please ensure that the mysql server instance for 4.0.1 is running "
		echo "**********************"
		echo "**********************"
		echo "**********************"
					
					while [ "$macname" = "" ]
					do
						echo "Please enter the machine name"
						read macname
					done
						
					while [ "$macport" = "" ]
					do
						echo "Please enter the mysql Port Number"
						read macport
					done

					while [ "${username}" = "" ]
					do
							echo "Please enter the mysql User Name"
							read username
					done
					
							echo "Please enter the mysql Password"
							read passwd
				 
					while [ "${apache_port_4_2}" = "" ]
					do
						echo ''
					         echo "Specify the apache port of the mycrm CRM 4.2"
			        	         read apache_port_4_2
		       			done

					getRemoteMySQLDump $macname $macport $username $passwd
					export diffmac=1
					flag=1
					cancontinue=true
					;;

				[yY]|[yY][eE][sS])
						echo ""
						flag=1
						;;

				*)	echo "invalid option"					
					;;
			esac
		done

		if [ ${diffmac} -eq 0 ]
		then
			res=0
			
			while [ "$dir_4_0" = "" ] || [ $res != 0 ]
			do
				echo ""
				echo "Specify the absolute path of the bin directory of the mycrm CRM 4.0.1"
				echo "(For example /home/test/mycrmCRM4_0_1/bin):"
		 		read dir_4_0
				if [ "${dir_4_0}" = "" ]
				then
					echo "Kindly enter a valid value please!"
				fi
		
				checkInstallDir ${dir_4_0}
				res=$?
				##res stores the result of the previous op
				cancontinue=true
			done
		fi
	done
 	
		echo ""
	echo 'The 4.0.1 mycrm CRM installation directory is' $dir_4_0
}

getmycrm4_0_1_data()
{
	scrfile=${dir_4_0}/startmyCrm.sh

	while [ "${mysql_host_name_4_0}" = "" ]
	do
		echo ""
		echo "Specify the host name of the mycrm CRM 4.0.1 mysql server"
	        read mysql_host_name_4_0
	done

	while [ "${apache_port_4_0_1}" = "" ]
	do
		echo ''
        	echo "Specify the apache port of the mycrm CRM 4.2"
	        read apache_port_4_0_1
	done
	
	mysql_dir=`grep "mysql_dir=" ${scrfile} | cut -d "=" -f2 | cut -d "'" -f2`
	echo 'mysql dir is ' $mysql_dir

	mysql_username=`grep "mysql_username=" ${scrfile}  | cut -d "=" -f2 | cut -d "'" -f2`
	echo 'mysql username is ' $mysql_username

	mysql_password=`grep "mysql_password=" ${scrfile} | cut -d "=" -f2 | cut -d "'" -f2`
	echo 'mysql password is ' $mysql_password

	mysql_port=`grep "mysql_port=" ${scrfile} | cut -d "=" -f2 | cut -d "'" -f2`
	echo 'mysql port is ' $mysql_port

	mysql_socket=`grep "mysql_socket=" ${scrfile} | cut -d "=" -f2 | cut -d "'" -f2`
	echo $mysql_socket

	mysql_bundled=`grep "mysql_bundled=" ${scrfile} | cut -d "=" -f2 | cut -d "'" -f2`
	echo $mysql_bundled
	
	pwd
	src_file_4_0_1=./startmyCrm.sh
	mysql_dir_4_0_1=`grep "mysql_dir=" ${src_file_4_0_1} | cut -d "=" -f2 | cut -d "'" -f2`
	echo $mysql_dir_4_0_1

	finAndReplace ../apache/htdocs/mycrmCRM/migrator_connection.php MYSQLHOSTNAME ${mysql_host_name_4_0}
        finAndReplace ../apache/htdocs/mycrmCRM/migrator_connection.php MYSQLUSERNAME ${mysql_username}
        finAndReplace ../apache/htdocs/mycrmCRM/migrator_connection.php MYSQLPASSWORD ${mysql_password}
        finAndReplace ../apache/htdocs/mycrmCRM/migrator_connection.php MYSQLPORT ${mysql_port}
        chmod 777 ../apache/htdocs/mycrmCRM/migrator_connection.php
}

finAndReplace()
{
        fileName=$1
        var=$2
        val=$3

        tmpFile=${fileName}.$$
        sed -e "s@${var}@${val}@g" ${fileName} > ${tmpFile}
        mv -f ${tmpFile} ${fileName}

}

ismycrm_MySQL_Running()
{
	version=$1
	retval=1
	echo "select 1" | ${mysql_dir}/bin/mysql --user=${mysql_username} --password=${mysql_password}  --port=${mysql_port} --socket=${mysql_socket} > /dev/null

	exit_status=$?

	if [ $exit_status -eq 0 ]
	then
        	echo " "
        	echo "The mycrm CRM $version MySQL server is running"
        	echo " "
		retval=0
	fi

	return ${retval}
}

checkInput()
{
	checkcharlower=$1
	checkcharupper=$2
	correctkey=false
	
	while [ "${correctkey}" != "true" ]
	do
		read char
		if [ "$char" != "${checkcharlower}" -a "$char" != "${checkcharupper}" ]
		then
			echo "Press ${checkcharlower} (or) ${checkcharupper}"
		else
			correctkey=true	
		fi
	done

}
promptAndCheckMySQL()
{
	version=$1
	echo ""
	echo "**************************************************"
	echo "Mysql server in the directory $mysql_dir is not running at port $mysql_port."
	echo "Start the mycrm CRM $version mysql server and then Press C to contiunue"
	echo "**************************************************"
	echo ""
	checkInput "c" "C"
}




getRemoteMySQLDump()
{
machine_name=$1
m_port=$2
m_uname=$3
m_passwd=$4

echo ''
echo ''
echo '###########################################################'
echo '###########################################################'
echo 'replacing the values in the migrator_connection.php file '
echo '###########################################################'
echo '###########################################################'
echo ''
echo ''

finAndReplace ../apache/htdocs/mycrmCRM/migrator_connection.php MYSQLHOSTNAME ${machine_name}
finAndReplace ../apache/htdocs/mycrmCRM/migrator_connection.php MYSQLUSERNAME ${m_uname}
finAndReplace ../apache/htdocs/mycrmCRM/migrator_connection.php MYSQLPASSWORD ${m_passwd}
finAndReplace ../apache/htdocs/mycrmCRM/migrator_connection.php MYSQLPORT ${m_port}
chmod 777 ../apache/htdocs/mycrmCRM/migrator_connection.php

scrfile=./startmyCrm.sh
mysql_dir=`grep "mysql_dir=" ${scrfile} | cut -d "=" -f2 | cut -d "'" -f2`
#take the dump of the 4.0.1 mysql
echo 'set FOREIGN_KEY_CHECKS=0;' > mycrm4_0_1_dump.txt
${mysql_dir}/bin/mysqldump -u $m_uname -h $machine_name --port=$m_port --password=$m_passwd mycrmcrm4_0_1 >> mycrm4_0_1_dump.txt

if [ $? -eq 0 ]
then
	echo 'Data dump taken successfully in mycrm_4_0_1_dump.txt'
else
	echo 'Unable to take the database dump. mycrmcrm database may be corrupted'
	exit
fi
#this should be the 4.0.1 mysql, so create the bkup database in it 
${mysql_dir}/bin/mysql -h $machine_name --user=$m_uname --password=$m_passwd --port=$m_port -e "create database mycrmcrm_4_0_1_bkp"
#dump the 4.0.1 dump into the bkup database
${mysql_dir}/bin/mysql -h $machine_name --user=$m_uname --password=$m_passwd --port=$m_port mycrmcrm_4_0_1_bkp < mycrm4_0_1_dump.txt












wget http://localhost:${apache_port_4_2}/Migrate.php

echo 'set FOREIGN_KEY_CHECKS=0;' > migrated_mycrm_4_2_dump.txt

#dump the migrated bkup database to a file

echo 'about to take the dump of the bkup file and put into the migrated_dump.txt file'

 ${mysql_dir}/bin/mysqldump -h $machine_name --user=$m_uname --password=$m_passwd --port=$m_port mycrmcrm_4_0_1_bkp >> migrated_mycrm_4_2_dump.txt

echo 'about to drop the database mycrmcrm_4_0_1_bkp'

 #${mysql_dir}/bin/mysql -h $machine_name --user=$m_uname --password=$m_passwd --port=$m_port -e "drop database mycrmcrm_4_0_1_bkp"

}
#end of getRemoteMySQLDump


startMySQL()
{
	version=$1
	if [ "$mysql_bundled" == "false" ]
	then
		mysql_running=false
		while [ "${mysql_running}" != "true" ]
		do
			promptAndCheckMySQL $version
			ismycrm_MySQL_Running $version
			if [ $? = 0 ]
			then	
				mysql_running=true
			fi
		done
	else
		mysql_running=false
		while [ "${mysql_running}" != "true" ]
		do
			echo "mycrm CRM $version Mysql Server is not running."
			echo "Going to start the  mycrm CRM $version mysql server at port $mysql_port"
			${mysql_dir}/bin/mysqld_safe --basedir=$mysql_dir --datadir=$mysql_dir/data --socket=$mysql_socket --tmpdir=$mysql_dir/tmp --user=root --port=$mysql_port --default-table-type=INNODB > /dev/null &
			sleep 8
			ismycrm_MySQL_Running $version
			if [ $? -ne 0 ]
			then
				echo ""
				echo "*****************************************"
				echo "Unable to start mycrm CRM $version mysql server."
				echo "Check whether the port $mysql_port is free."
				echo "Free the port and press c to continue"
				echo "*****************************************"
				echo ""
				checkInput "c" "C"		
			else
				mysql_running=true
			fi
		done
	fi

}

getdump4_0_1_db()
{
	echo "********************************************"
	echo "Taking the dump of mycrm CRM 4.0.1 database"
	echo "*******************************************"
	echo 'set FOREIGN_KEY_CHECKS=0;' > mycrm_4_0_1_dump.txt		
	${mysql_dir_4_0_1}/bin/mysqldump --user=$mysql_username --password=$mysql_password --port=$mysql_port --socket=$mysql_socket mycrmcrm4_0_1 >> mycrm_4_0_1_dump.txt
	if [ $? -eq 0 ]
	then
		echo 'Data dump taken successfully in mycrm_4_0_1_dump.txt'
	else
		echo 'Unable to take the database dump. mycrmcrm database may be corrupted'
		exit
	fi
		
	${mysql_dir}/bin/mysql --user=$mysql_username --password=$mysql_password --port=$mysql_port --socket=$mysql_socket -e "create database mycrmcrm_4_0_1_bkp" 
	${mysql_dir}/bin/mysql --user=$mysql_username --password=$mysql_password --port=$mysql_port --socket=$mysql_socket mycrmcrm_4_0_1_bkp < mycrm_4_0_1_dump.txt
	wget http://localhost:${apache_port_4_0_1}/Migrate.php
	#${mysql_dir}/bin/mysql --user=$mysql_username --password=$mysql_password --port=$mysql_port --socket=$mysql_socket mycrmcrm_4_0_bkp < migrate_4_0to4_0_1.sql
	echo 'set FOREIGN_KEY_CHECKS=0;' > migrated_mycrm_4_2_dump.txt
	${mysql_dir_4_0_1}/bin/mysqldump --user=$mysql_username --password=$mysql_password --port=$mysql_port --socket=$mysql_socket mycrmcrm_4_0_1_bkp >> migrated_mycrm_4_2_dump.txt
	${mysql_dir}/bin/mysql --user=$mysql_username --password=$mysql_password --port=$mysql_port --socket=$mysql_socket -e "drop database mycrmcrm_4_0_1_bkp"

}


stopmycrm4_0_1MySQL()
{
	echo "Shutting down the mycrm CRM 4.0.1 mysql server"
       	${mysql_dir}/bin/mysqladmin --user=$mysql_username --password=$mysql_password --port=$mysql_port --socket=$mysql_socket shutdown
       	echo "mycrm CRM 4.0.1 MySQL server shutdown"
}

getmycrm4_2data()
{
	echo 'in get mycrm 4_2 data '
	scrfile=./startmyCrm.sh
	
		echo ""
	mysql_dir=`grep "mysql_dir=" ${scrfile} | cut -d "=" -f2 | cut -d "'" -f2`
	echo "4.2 dir is$mysql_dir"

		echo ""
	mysql_username=`grep "mysql_username=" ${scrfile}  | cut -d "=" -f2 | cut -d "'" -f2`
	echo "4.2 user name is $mysql_username"

	mysql_password=`grep "mysql_password=" ${scrfile} | cut -d "=" -f2 | cut -d "'" -f2`
		echo ""
	echo "4.2 password is $mysql_password"

		echo ""
	mysql_port=`grep "mysql_port=" ${scrfile} | cut -d "=" -f2 | cut -d "'" -f2`
	echo "4.2 port is $mysql_port"

		echo ""
	mysql_socket=`grep "mysql_socket=" ${scrfile} | cut -d "=" -f2 | cut -d "'" -f2`
	echo "4.2 socket is $mysql_socket"

		echo ""
	mysql_bundled=`grep "mysql_bundled=" ${scrfile} | cut -d "=" -f2 | cut -d "'" -f2`
	echo "4.2 bundled status is $mysql_bundled"
}

dumpinto4_2db()
{
       	${mysql_dir}/bin/mysql --user=$mysql_username --password=$mysql_password --port=$mysql_port --socket=$mysql_socket -e "drop database mycrmcrm4_2"
       	${mysql_dir}/bin/mysql --user=$mysql_username --password=$mysql_password --port=$mysql_port --socket=$mysql_socket -e "create database if not exists mycrmcrm4_2"
	${mysql_dir}/bin/mysql  --user=$mysql_username --password=$mysql_password --port=$mysql_port --socket=$mysql_socket mycrmcrm4_2 --force < migrated_mycrm_4_2_dump.txt  2> migrate_log.txt
	
	if [ $? -eq 0 ]
	then
		echo 'myCrm CRM 4.0.1 Data successfully migrated into mycrm CRM 4.2 database mycrmcrm4_2'
	else
		echo 'Unable to dump data into the mycrm CRM 4.2 database mycrmcrm4_2. Check the migrate_log.txt in the $wdir directory'
		exit
	fi
}

main()
{
	setVariables $*
	getmycrm4_0_1_installdir

	if [ ${diffmac} -eq 0 ]
	then
		getmycrm4_0_1_data
		ismycrm_MySQL_Running 4_0_1

		if [ $? != 0 ]
		then
			startMySQL 4_0_1
		fi
		getdump4_0_1_db
		if [ "$mysql_bundled" == "true" ]
		then
			stopmycrm4_0_1MySQL
		fi	
	fi

	getmycrm4_2data
	ismycrm_MySQL_Running 4_2
	if [ $? != 0 ]
	then
		startMySQL 4_2
	fi
	dumpinto4_2db
	 	
}

main $*
