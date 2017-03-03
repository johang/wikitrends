#!/usr/bin/python3

# wikitrends
# optimize buckets
# copyright (c) johan gunnarsson 2012 (johang@toolserver.org)

import sys, os.path, datetime

from os.path import join

EXTRACTS = os.environ["WIKITRENDS_EXTRACTS"]
BUCKETS = os.environ["WIKITRENDS_BUCKETS"]

#
# Find all extracts in a bucket.
#
def bucket(bucket):
	return frozenset([
		join(EXTRACTS, e)
		for e in os.listdir(join(bucket, "extracts"))
		if e.endswith(".gz")])

#
# Find all bucket which extracts is part of.
#
def candidates(extract):
	date = datetime.datetime.strptime(
		os.path.basename(extract),
		"pageviews-%Y%m%d-%H%M%S.gz")

	return [
		(join(BUCKETS, b, "merge.gz"), bucket(join(BUCKETS, b)))
		for b in (
			date.strftime("y%Y-m%m-d%d"),
			date.strftime("y%Y-w%U"),
			date.strftime("y%Y-m%m"))
		if os.path.exists(join(BUCKETS, b))]

#
# Optimize a set of extract files.
#
def optimize(files):
	tree = sorted(
		[b for a in map(candidates, set(files)) for b in a],
		key = lambda i: len(i[1]),
		reverse = True)

	for n, es in tree:
		if len(es) > 0 and es.issubset(files):
			files = (files - es) | set([n])

	return sorted(files)

#
# Read a set of extract filenames from stdin and print the optimized set.
#
if __name__ == "__main__":
	a = int(sys.argv[1])
	b = int(sys.argv[2])

	files = optimize(set([
		join(EXTRACTS, os.path.basename(x.strip()))
		for x in sys.stdin][a:b]))

	for f in files:
		print(f)
