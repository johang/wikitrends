package wikitrends.iterators;

import java.io.FileNotFoundException;
import java.io.IOException;
import java.io.InputStream;

import wikitrends.records.ExtractRecord;

public class RightExtractIterator extends ExtractIterator {
	public RightExtractIterator(InputStream i) throws FileNotFoundException,
			IOException {
		super(i);
	}

	@Override
	protected ExtractRecord filter(ExtractRecord r) {
		return r;
	}

	@Override
	protected ExtractRecord parse(String s) {
		String[] p = s.split(" ");

		if (p.length != 3)
			return null;

		try {
			return new ExtractRecord(p[0], p[1], 0, Integer.parseInt(p[2]));
		} catch (NumberFormatException e) {
			return null;
		}
	}
}
