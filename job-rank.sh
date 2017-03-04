#!/bin/bash

# wikitrends
# merge and rank
# copyright (c) johan gunnarsson 2012 (johang@toolserver.org)

set -e
set -o pipefail

. ~/projects/wikitrends/env.sh
. $1/input.$SGE_TASK_ID

mkdir -p $TMP/$N/{up,down,top}

if [ -e $WIKITRENDS_FLAGS/pending-$N ]
then
  ls -r1 $WIKITRENDS_EXTRACTS | optimize.py 0 $((1*N)) > $TMP/$N/a
  ls -r1 $WIKITRENDS_EXTRACTS | optimize.py $((1*N)) $((3*N)) > $TMP/$N/b

  java -Xms32m -Xmx128m wikitrends.main.Rank \
    $TMP/$N/a \
    $TMP/$N/b \
    $TMP/$N/up \
    $TMP/$N/down \
    $TMP/$N/top

  for T in $TMP/$N/{up,down,top}/*
  do
    php \
      -d display_errors=On \
      -d display_startup_errors=On \
      $WIKITRENDS_ROOT/render/render.php $T
  done

  rm $WIKITRENDS_FLAGS/pending-$N
fi

[ ! -s "$SGE_STDOUT_PATH" ] && rm -f $SGE_STDOUT_PATH
[ ! -s "$SGE_STDERR_PATH" ] && rm -f $SGE_STDERR_PATH
