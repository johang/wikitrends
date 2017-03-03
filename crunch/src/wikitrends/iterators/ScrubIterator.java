package wikitrends.iterators;

import java.io.FileNotFoundException;
import java.io.IOException;
import java.io.InputStream;
import java.io.UnsupportedEncodingException;
import java.net.URLDecoder;
import java.net.URLEncoder;

import wikitrends.records.ExtractRecord;

public class ScrubIterator extends ExtractIterator {
	private final static char[] BAD = new char[] { '#', '<', '>', '[', ']',
			'|', '{', '}' };

	public ScrubIterator(final InputStream i) throws FileNotFoundException,
			IOException {
		super(i);
	}

	@Override
	protected ExtractRecord filter(final ExtractRecord r) {
		// Null

		if (r == null)
			return null;

		// Filter on <5 hits

		if (r.left < 5)
			return null;

		// Decode

		final String decoded;

		try {
			decoded = URLDecoder.decode(r.page, "UTF-8");
		} catch (IllegalArgumentException e) {
			return null;
		} catch (UnsupportedEncodingException e) {
			return null;
		}

		// Throw away empty strings

		if (decoded.length() == 0)
			return null;

		// Filter invalid titles

		if (Character.isLowerCase(decoded.charAt(0)))
			return null;

		for (char c : BAD) {
			if (decoded.indexOf(c) != -1)
				return null;
		}

		// Filter trailing slashes

		int slashIndex = decoded.length();

		while (slashIndex > 0 && decoded.charAt(slashIndex - 1) == '/') {
			slashIndex--;
		}

		// Encode

		final String encoded;

		try {
			encoded = URLEncoder.encode(decoded.substring(0, slashIndex),
					"UTF-8");
		} catch (IllegalArgumentException e) {
			return null;
		} catch (UnsupportedEncodingException e) {
			return null;
		}

		// Throw away empty strings

		if (encoded.length() == 0)
			return null;

		// Return

		return new ExtractRecord(r.language, encoded, r.left);
	}

	@Override
	protected ExtractRecord parse(final String s) {
		final String[] p = s.trim().split(" ");

		if (p.length != 4)
			return null;

		try {
			return new ExtractRecord(p[0], p[1], Integer.parseInt(p[2]), 0);
		} catch (NumberFormatException e) {
			return null;
		}
	}
}
