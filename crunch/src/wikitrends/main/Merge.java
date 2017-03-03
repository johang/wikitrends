package wikitrends.main;

import java.io.BufferedInputStream;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.PrintStream;
import java.util.ArrayList;
import java.util.List;
import java.util.zip.GZIPInputStream;
import java.util.zip.GZIPOutputStream;

import wikitrends.iterators.ExtractIterator;
import wikitrends.iterators.LeftExtractIterator;
import wikitrends.iterators.MergeIterator;
import wikitrends.iterators.SumIterator;
import wikitrends.records.ExtractRecord;

public class Merge {
	public static void main(String[] args) throws IOException {
		File x = new File(args[0]);

		x.setReadable(true, false);

		PrintStream o = new PrintStream(new GZIPOutputStream(
				new FileOutputStream(x)));

		List<ExtractIterator> files = new ArrayList<ExtractIterator>();

		for (int i = 1; i < args.length; i++) {
			try {
				files.add(new LeftExtractIterator(new BufferedInputStream(
						new GZIPInputStream(new FileInputStream(args[i]),
								16 * 1024), 16 * 1024)));
			} catch (FileNotFoundException e) {
				// Can happen. Ignore.
			}
		}

		SumIterator e = new SumIterator(new MergeIterator(files));

		for (ExtractRecord r : e) {
			o.println(r.language + " " + r.page + " " + r.left);
		}

		o.close();
	}
}
