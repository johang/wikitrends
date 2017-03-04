#!/bin/bash

# wikitrends
# merge a bucket
# copyright (c) johan gunnarsson 2012 (johang@toolserver.org)

set -e
set -o pipefail

. ~/projects/wikitrends/env.sh
. $1/input.$SGE_TASK_ID

FILE=`mktemp --tmpdir=$WIKITRENDS_TMP merge-XXXXXX.gz`

java -Xms32m -Xmx128m wikitrends.main.Merge \
	$TMP/merge.gz \
	$BUCKET/merge.gz \
	$BUCKET/queue/*

cp --no-preserve mode $TMP/merge.gz $FILE
sync
gzip -t $FILE
chmod 644 $FILE
ln -f $FILE $BUCKET/merge.gz
rm $FILE
mkdir -p $BUCKET/extracts
mv $BUCKET/queue/* $BUCKET/extracts
rm $WIKITRENDS_QUEUES/`basename $BUCKET`
touch $WIKITRENDS_FLAGS/pending-{24,168,720}

[ ! -s "$SGE_STDOUT_PATH" ] && rm -f $SGE_STDOUT_PATH
[ ! -s "$SGE_STDERR_PATH" ] && rm -f $SGE_STDERR_PATH
