package wikitrends.iterators;

import java.util.Iterator;
import java.util.List;
import java.util.PriorityQueue;

import wikitrends.records.ExtractRecord;

public class MergeIterator implements Iterator<ExtractRecord> {
	private final PriorityQueue<ExtractIterator> queue;

	public MergeIterator(List<ExtractIterator> extractors) {
		queue = new PriorityQueue<ExtractIterator>();

		for (ExtractIterator i : extractors) {
			if (i.hasNext())
				queue.add(i);
		}
	}

	@Override
	public boolean hasNext() {
		return queue.peek() != null;
	}

	@Override
	public ExtractRecord next() {
		ExtractIterator e = queue.poll();

		ExtractRecord r = e.next();

		if (e.hasNext())
			queue.add(e);

		return r;
	}

	@Override
	public void remove() {
		/* NOOP */
	}
}
