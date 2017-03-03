#!/bin/bash

# wikitrends
# start a wikitrends refresh
# copyright (c) johan gunnarsson 2012 (johang@toolserver.org)

set -e
set -o pipefail

. ~/projects/wikitrends/env.sh

date
submit-extracts.py --wait
date
submit-merges.py --wait
date
submit-ranks.py --wait
date
echo "Purging data"
find ~/data/jobs -mtime +14 -delete
find ~/data/logs -mtime +14 -delete
find ~/data/tmp -mtime +14 -delete
find ~/data/extracts -mtime +120 -delete
date
#echo "Purging cache"
#mysql \
#  --defaults-file=$HOME/replica.my.cnf \
#  -h tools-db \
#  -e "DELETE FROM cache WHERE t < DATE_SUB(NOW(), INTERVAL 14 DAY);" \
#  s51388__cache
#date
