#!/bin/bash
HOST='112.124.123.89'
USER='suofeiyavip'
PASS='sogal5911'
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

