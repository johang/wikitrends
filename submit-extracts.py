#!/usr/bin/python3

# wikitrends
# submit extract jobs
# copyright (c) johan gunnarsson 2012 (johang@toolserver.org)

from datetime import timedelta, date

import os, os.path, datetime, tempfile, subprocess, sys, re, urllib.request

JOBS = os.environ["WIKITRENDS_JOBS"]
EXTRACTS = os.environ["WIKITRENDS_EXTRACTS"]
LOGS = os.environ["WIKITRENDS_LOGS"]
ROOT = os.environ["WIKITRENDS_ROOT"]

TASKS_PER_JOB = 24

#
# Submit jobs to SGE
#
def submit(directory, logs, dryrun, wait):
	if len(logs) == 0:
		return

	for i, d in enumerate(logs[-TASKS_PER_JOB:], start=1):
		with open(os.path.join(directory, "input.%d" % (i)), "w") as f:
			f.write("URI=\"%s\"\n" % (d))

	command = [
		"qsub",
		"-N", "EXTRACT-OR-MERGE",
		"-l", "h_vmem=2G",
		"-q", "task",
		"-tc", "6",
		"-t", "1-%d" % len(logs[-TASKS_PER_JOB:]),
		"-r", "yes",
		"-b", "y",
		"-sync", "yes" if wait else "no",
		"-o", LOGS,
		"-e", LOGS,
		os.path.join(ROOT, "job-extract.sh"), directory]

	if not dryrun:
		subprocess.call(command)
	else:
		print(" ".join(command))

#
# Download and parse a list of logs
#
def get_logs(u):
	try:
		return set(map(
			lambda f: "%s/%s" % (u, f.group(0)),
			re.finditer(
				"pageviews\-(\d{4})(\d{2})(\d{2})\-(\d+)\.gz",
				str(urllib.request.urlopen(u).read()))))
	except:
		return set()

#
# Find out which index URL a given that belongs to
#
def get_indexes(age):
	return set(map(
		lambda d: "http://%s/other/pageviews/%04d/%04d-%02d" % (
			"dumps.wikimedia.org", d.year, d.year, d.month),
		[date.today() - timedelta(days=i) for i in range(age)]))

#
# Fetch available and unextracted logs and submit as jobs to SGE.
#
if __name__ == "__main__":
	submit(
		tempfile.mkdtemp(
			prefix="extracts.",
			dir=JOBS),
		sorted(filter(
			lambda log: not os.path.exists(os.path.join(
				EXTRACTS,
				os.path.basename(log))),
			[l for i in get_indexes(60) for l in get_logs(i)])),
		"--dry-run" in sys.argv,
		"--wait" in sys.argv)
