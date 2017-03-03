#
# SGE setup
#

#
# Wikitrends setup
#

ROOT="/data/project/wikitrends/projects/wikitrends"
STORAGE="/data/project/wikitrends/data"
HTML="/data/project/wikitrends/public_html"

export WIKITRENDS_ROOT="$ROOT"
export WIKITRENDS_LOGS="$STORAGE/logs"
export WIKITRENDS_BUCKETS="$STORAGE/buckets"
export WIKITRENDS_EXTRACTS="$STORAGE/extracts"
export WIKITRENDS_JOBS="$STORAGE/jobs"
export WIKITRENDS_QUEUES="$STORAGE/queues"
export WIKITRENDS_TMP="$STORAGE/tmp"
export WIKITRENDS_FLAGS="$STORAGE/flags"
export WIKITRENDS_HTML="$HTML"

export PATH="$ROOT:$PATH:$SGE_BINARY_PATH"
export CLASSPATH="$ROOT/crunch/dist/crunch.jar"
export TMP

#
# Make sure everything exists
#

mkdir -p $WIKITRENDS_LOGS
mkdir -p $WIKITRENDS_BUCKETS
mkdir -p $WIKITRENDS_EXTRACTS
mkdir -p $WIKITRENDS_JOBS
mkdir -p $WIKITRENDS_QUEUES
mkdir -p $WIKITRENDS_TMP
mkdir -p $WIKITRENDS_FLAGS
mkdir -p $WIKITRENDS_HTML

#
# Step into root directory.
#

cd $ROOT

trap "{ hostname; echo $TMP; du -hs $TMP; df -h; exit 1; }" ERR
