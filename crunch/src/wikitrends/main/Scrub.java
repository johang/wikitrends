package wikitrends.main;

import java.io.BufferedInputStream;
import java.io.FileNotFoundException;
import java.io.IOException;

import wikitrends.iterators.ScrubIterator;
import wikitrends.records.ExtractRecord;

public class Scrub {
	public static void main(String[] args) {
		try {
			final ScrubIterator e = new ScrubIterator(new BufferedInputStream(
					System.in, 16 * 1024));

			for (ExtractRecord r : e) {
				System.out.println(r.language + " " + r.page + " " + r.left);
			}
		} catch (FileNotFoundException e) {
			e.printStackTrace();
		} catch (IOException e) {
			e.printStackTrace();
		}
	}
}
