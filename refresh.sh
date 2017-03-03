#!/bin/bash

# wikitrends
# start a wikitrends refresh
# copyright (c) johan gunnarsson 2012 (johang@toolserver.org)

set -e
set -o pipefail

. ~/projects/wikitrends/env.sh

launch.sh
