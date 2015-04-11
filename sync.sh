#!/bin/bash
HOST='112.124.123.222'
USER='xxxxxx'
PASS='xxxxxx'
TARGETFOLDER='/jdb'
SOURCEFOLDER=$PWD

echo $(date)

lftp -e "
open $HOST
user $USER $PASS
lcd $SOURCEFOLDER
mirror  --reverse \
	--delete \
	--exclude-glob *.sh \
	--exclude-glob .* \
	--exclude-glob debug.* \
	--exclude-glob .git/ \
	--verbose $SOURCEFOLDER $TARGETFOLDER
bye
"

echo $(date)

