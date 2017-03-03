package wikitrends.iterators;

import java.util.Iterator;

import wikitrends.records.ExtractRecord;

public class SumIterator implements Iterator<ExtractRecord>,
		Iterable<ExtractRecord> {
	private Iterator<ExtractRecord> source;

	private ExtractRecord head;

	public SumIterator(Iterator<ExtractRecord> i) {
		source = i;
	}

	@Override
	public boolean hasNext() {
		return head != null || source.hasNext();
	}

	@Override
	public ExtractRecord next() {
		ExtractRecord key = (head != null) ? head : source.next();

		head = null;

		while (source.hasNext()) {
			head = source.next();

			if (!head.samePageAndLanguage(key))
				break;

			key.add(head);
		}

		return key;
	}

	@Override
	public void remove() {
		/* NOOP */
	}

	@Override
	public Iterator<ExtractRecord> iterator() {
		return this;
	}
}
