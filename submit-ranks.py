#!/usr/bin/python3

# wikitrends
# submit rank jobs
# copyright (c) johan gunnarsson 2012 (johang@toolserver.org)

import os, os.path, datetime, tempfile, subprocess, sys

JOBS = os.environ["WIKITRENDS_JOBS"]
LOGS = os.environ["WIKITRENDS_LOGS"]
ROOT = os.environ["WIKITRENDS_ROOT"]

def schedule(directory, intervals, dryrun, wait):
	if len(intervals) < 1:
		sys.exit("no intervals to rank")

	for i, d in enumerate(intervals, start=1):
		with open(os.path.join(directory, "input.%d" % (i)), "w") as f:
			f.write("N=\"%d\"\n" % (d))

	commands = [
		"qsub",
		"-N", "RANK-AND-RENDER",
		"-l", "release=trusty",
		"-l", "h_vmem=2G",
		"-l", "hostname=!tools-exec-01",
		"-q", "task",
		"-t", "1-%d" % len(intervals),
		"-r", "yes",
		"-b", "y",
		"-sync", "yes" if wait else "no",
		"-o", LOGS,
		"-e", LOGS,
		os.path.join(ROOT, "job-rank.sh"), directory]

	if not dryrun:
		subprocess.call(commands)
	else:
		print(" ".join(commands))

if __name__ == "__main__":
	now = datetime.datetime.now()

	i720 = [720] if now.hour % 24 == 0 else []
	i168 = [168] if now.hour % 6 == 0 else []
	i24 = [24] if now.hour % 1 == 0 else []

	schedule(
		tempfile.mkdtemp(
			prefix="rank.",
			dir=JOBS),
		i24 + i168 + i720,
		"--dry-run" in sys.argv,
		"--wait" in sys.argv)
