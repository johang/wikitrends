#!/usr/bin/python3

# wikitrends
# submit extract jobs
# copyright (c) johan gunnarsson 2012 (johang@toolserver.org)

import os, os.path, datetime, tempfile, subprocess, sys

JOBS = os.environ["WIKITRENDS_JOBS"]
QUEUES = os.environ["WIKITRENDS_QUEUES"]
LOGS = os.environ["WIKITRENDS_LOGS"]
ROOT = os.environ["WIKITRENDS_ROOT"]

TASKS_PER_JOB = 100

def schedule(directory, dumps, fake, wait):
	if len(dumps) == 0:
		return

	for i, d in enumerate(dumps[:TASKS_PER_JOB], start=1):
		with open(os.path.join(directory, "input.%d" % (i)), "w") as f:
			f.write("BUCKET=\"%s\"\n" % (d))

	command = [
		"qsub",
		"-N", "EXTRACT-OR-MERGE",
		"-l", "release=trusty",
		"-l", "h_vmem=2G",
		"-q", "task",
		"-tc", "2",
		"-t", "1-%d" % len(dumps[:TASKS_PER_JOB]),
		"-r", "yes",
		"-b", "y",
		"-sync", "yes" if wait else "no",
		"-o", LOGS,
		"-e", LOGS,
		os.path.join(ROOT, "job-merge.sh"), directory]

	if not fake:
		subprocess.call(command)
	else:
		print(" ".join(command))

if __name__ == "__main__":
	schedule(
		tempfile.mkdtemp(
			prefix="merges.",
			dir=JOBS),
		[os.path.join(QUEUES, x) for x in os.listdir(QUEUES)],
		"--dry-run" in sys.argv,
		"--wait" in sys.argv)
