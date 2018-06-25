#!/bin/bash
# author: mmcclintock@wbez.org
# date: 2017.07.26
# Send simple messages via SMS using CPM-SMS Notifier
# https://github.com/wbez/cpm-sms-notifications

# read configuration file or exit
cd "$(dirname "$0")"
if [ -e ".sms-settings.cfg" ]; then
	source .sms-settings.cfg
else
	echo ".sms-settings.cfg not found. Creating it."
cat <<EOF > .sms-settings.cfg
TOKEN=
EOF
	exit 1
fi

# check for token
if [ -z "$TOKEN" ]; then
     echo "TOKEN is not set in .sms-settings.cfg."
     exit 0
fi

# check for recipient phone number
if [ -z "$1" ]; then
     echo "Please supply a phone number, e.g. 7088059999"
  else
  # make sure first argument is an integer
	if ! [ $1 -ge 0 2>/dev/null ]; then
	   echo "ERROR: $1 is not a number. Please phone number without punctuation e.g. 7088059999"
	   exit 0
	fi
  	 RECIPIENT=$1
fi

# check for message
if [ -z "$2" ]; then
     echo "Please supply a message. Quote strings with spaces or special characters."
     exit 0
  else
  	 MESSAGE=$2
fi

# echo to the user
echo "Sending SMS...
	Recipient:" $RECIPIENT "
	Message:" $MESSAGE 

# send to admintools
/usr/bin/curl -X POST -F "token=$TOKEN" -F "recipients=$RECIPIENT" -F "message=$MESSAGE" http://admintools.wbez.org/sms/

