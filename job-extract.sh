#!/bin/bash

# wikitrends
# filter a dump file
# copyright (c) johan gunnarsson 2012 (johang@toolserver.org)

set -e
set -o pipefail

. ~/projects/wikitrends/env.sh
. $1/input.$SGE_TASK_ID

FILE=`mktemp --tmpdir=$WIKITRENDS_TMP extract-XXXXXX.gz`

case $URI in
	http://*)
		wget -q -O - $URI |
		gzip -c -d |
		java -Xms32m -Xmx128m wikitrends.main.Scrub |
		LC_ALL=C sort -T $TMP |
		gzip -c > $TMP/extract.gz
		;;
	*)
		gzip -c -d $URI |
		java -Xms32m -Xmx128m wikitrends.main.Scrub |
		LC_ALL=C sort -T $TMP |
		gzip -c > $TMP/extract.gz
		;;
esac

cp --no-preserve mode $TMP/extract.gz $FILE
sync
gzip -t $FILE
chmod 644 $FILE
ln $FILE $WIKITRENDS_EXTRACTS/`basename $URI`
rm $FILE
queue.py `basename $URI`

[ ! -s "$SGE_STDOUT_PATH" ] && rm -f $SGE_STDOUT_PATH
[ ! -s "$SGE_STDERR_PATH" ] && rm -f $SGE_STDERR_PATH
