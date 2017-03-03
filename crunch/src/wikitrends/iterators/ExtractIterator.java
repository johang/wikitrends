package wikitrends.iterators;

import java.io.BufferedReader;
import java.io.FileNotFoundException;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.util.Iterator;

import wikitrends.records.ExtractRecord;

public abstract class ExtractIterator implements Iterator<ExtractRecord>,
		Iterable<ExtractRecord>, Comparable<ExtractIterator> {
	private ExtractRecord next;

	private BufferedReader reader;

	public ExtractIterator(InputStream i) throws FileNotFoundException,
			IOException {
		// Construct a reader
		reader = new BufferedReader(new InputStreamReader(i));

		// Prefetch first receord
		next = readNextRecord();
	}

	private ExtractRecord readNextRecord() throws IOException {
		ExtractRecord r = null;

		do {
			String l = reader.readLine();

			if (l == null)
				return null; /* EOF */

			r = filter(parse(l));
		} while (r == null);

		return r;
	}

	protected abstract ExtractRecord filter(ExtractRecord r);

	protected abstract ExtractRecord parse(String s);

	@Override
	public boolean hasNext() {
		return next != null;
	}

	@Override
	public ExtractRecord next() {
		ExtractRecord r = next;

		try {
			next = readNextRecord();
		} catch (IOException e) {
			throw new RuntimeException(e);
		}

		return r;
	}

	@Override
	public void remove() {
		/* NOOP */
	}

	@Override
	public Iterator<ExtractRecord> iterator() {
		return this;
	}

	@Override
	public int compareTo(ExtractIterator i) {
		return next.compareTo(i.next);
	}
}
