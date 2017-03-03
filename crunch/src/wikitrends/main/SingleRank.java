package wikitrends.main;

import java.io.BufferedInputStream;
import java.io.BufferedReader;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.FileReader;
import java.io.IOException;
import java.io.PrintStream;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.Map.Entry;
import java.util.zip.GZIPInputStream;

import wikitrends.iterators.ExtractIterator;
import wikitrends.iterators.LeftExtractIterator;
import wikitrends.iterators.MergeIterator;
import wikitrends.iterators.SumIterator;
import wikitrends.records.ExtractRecord;
import wikitrends.top.LeftExtractComparator;
import wikitrends.top.Top;

public class SingleRank {
	public static void main(String[] args) throws FileNotFoundException,
			IOException {
		final List<ExtractIterator> files = new ArrayList<ExtractIterator>();

		final List<File> lefts = new ArrayList<File>();

		final BufferedReader flefts = new BufferedReader(
				new FileReader(args[0]));

		for (String f = flefts.readLine(); f != null; f = flefts.readLine()) {
			lefts.add(new File(f));
		}

		flefts.close();

		for (File f : lefts) {
			files.add(new LeftExtractIterator(new BufferedInputStream(
					new GZIPInputStream(new FileInputStream(f), 16 * 1024),
					16 * 1024)));
		}

		final SumIterator e = new SumIterator(new MergeIterator(files));

		final Map<String, Top<ExtractRecord>> top = new HashMap<String, Top<ExtractRecord>>();

		for (ExtractRecord r : e) {
			Top<ExtractRecord> hit = top.get(r.language);

			if (hit == null)
				top.put(r.language, hit = new Top<ExtractRecord>(1000,
						new LeftExtractComparator()));

			hit.offer(r);
		}

		for (Entry<String, Top<ExtractRecord>> entry : top.entrySet()) {
			writeToFile(entry.getValue(), new File(args[1], entry.getKey()));
		}
	}

	public static void writeToFile(Top<ExtractRecord> top, File file)
			throws FileNotFoundException {
		PrintStream f = new PrintStream(file);

		for (ExtractRecord item : top) {
			f.println(item.language + " " + item.page + " " + item.left);
		}

		f.close();
	}
}
