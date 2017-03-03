#!/usr/bin/python3

# wikitrends
# queue unmerged extracts
# copyright (c) johan gunnarsson 2012 (johang@toolserver.org)

import os, os.path, re, datetime, sys

EXTRACTS = os.environ["WIKITRENDS_EXTRACTS"]
BUCKETS = os.environ["WIKITRENDS_BUCKETS"]
QUEUES = os.environ["WIKITRENDS_QUEUES"]

def link(source, target):
	try:
		os.makedirs(os.path.dirname(target))
	except OSError:
		pass

	try:
		os.symlink(
			os.path.relpath(source, os.path.dirname(target)),
			target)
	except OSError:
		pass

def queue(f):
	d = datetime.datetime.strptime(f, "pageviews-%Y%m%d-%H%M%S.gz")

	ef = os.path.join(EXTRACTS, "%s")
	bqf = os.path.join(BUCKETS, "%s", "queue", "%s")
	bq = os.path.join(BUCKETS, "%s")
	qf = os.path.join(QUEUES, "%s")

	link(ef % f, bqf % (d.strftime("y%Y-m%m-d%d"), f))
	link(ef % f, bqf % (d.strftime("y%Y-w%U"), f))
	link(ef % f, bqf % (d.strftime("y%Y-m%m"), f))
	link(bq % d.strftime("y%Y-m%m-d%d"), qf % d.strftime("y%Y-m%m-d%d"))
	link(bq % d.strftime("y%Y-w%U"), qf % d.strftime("y%Y-w%U"))
	link(bq % d.strftime("y%Y-m%m"), qf % d.strftime("y%Y-m%m"))

if __name__ == "__main__":
	for f in sys.argv[1:]:
		if os.path.exists(os.path.join(EXTRACTS, os.path.basename(f))):
			queue(os.path.basename(f))
