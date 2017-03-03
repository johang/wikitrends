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
import wikitrends.iterators.RightExtractIterator;
import wikitrends.iterators.SumIterator;
import wikitrends.records.ExtractRecord;
import wikitrends.top.DowntrendComparator;
import wikitrends.top.LeftExtractComparator;
import wikitrends.top.Top;
import wikitrends.top.UptrendComparator;

public class Rank {
	public static void main(String[] args) throws FileNotFoundException,
			IOException {
		final List<ExtractIterator> files = new ArrayList<ExtractIterator>();

		final List<File> lefts = new ArrayList<File>();
		final List<File> rights = new ArrayList<File>();

		final BufferedReader flefts = new BufferedReader(
				new FileReader(args[0]));
		final BufferedReader frights = new BufferedReader(new FileReader(
				args[1]));

		for (String f = flefts.readLine(); f != null; f = flefts.readLine()) {
			lefts.add(new File(f));
		}

		for (String f = frights.readLine(); f != null; f = frights.readLine()) {
			rights.add(new File(f));
		}

		flefts.close();
		frights.close();

		for (File f : lefts) {
			files.add(new LeftExtractIterator(new BufferedInputStream(
					new GZIPInputStream(new FileInputStream(f), 16 * 1024),
					16 * 1024)));
		}

		for (File f : rights) {
			files.add(new RightExtractIterator(new BufferedInputStream(
					new GZIPInputStream(new FileInputStream(f), 16 * 1024),
					16 * 1024)));
		}

		final SumIterator e = new SumIterator(new MergeIterator(files));

		final Map<String, Top<ExtractRecord>> uptrends = new HashMap<String, Top<ExtractRecord>>();
		final Map<String, Top<ExtractRecord>> downtrends = new HashMap<String, Top<ExtractRecord>>();
		final Map<String, Top<ExtractRecord>> top = new HashMap<String, Top<ExtractRecord>>();

		for (ExtractRecord r : e) {
			Top<ExtractRecord> up = uptrends.get(r.language);

			if (up == null)
				uptrends.put(r.language, up = new Top<ExtractRecord>(1000,
						new UptrendComparator(lefts.size(), rights.size())));

			up.offer(r);

			Top<ExtractRecord> down = downtrends.get(r.language);

			if (down == null)
				downtrends.put(r.language, down = new Top<ExtractRecord>(1000,
						new DowntrendComparator(lefts.size(), rights.size())));

			down.offer(r);

			Top<ExtractRecord> hit = top.get(r.language);

			if (hit == null)
				top.put(r.language, hit = new Top<ExtractRecord>(1000,
						new LeftExtractComparator()));

			hit.offer(r);
		}

		for (Entry<String, Top<ExtractRecord>> entry : uptrends.entrySet()) {
			writeToFile(entry.getValue(), new File(args[2], entry.getKey()),
					lefts.size(), rights.size());
		}

		for (Entry<String, Top<ExtractRecord>> entry : downtrends.entrySet()) {
			writeToFile(entry.getValue(), new File(args[3], entry.getKey()),
					lefts.size(), rights.size());
		}

		for (Entry<String, Top<ExtractRecord>> entry : top.entrySet()) {
			writeToFile(entry.getValue(), new File(args[4], entry.getKey()),
					lefts.size(), rights.size());
		}
	}

	public static void writeToFile(Top<ExtractRecord> top, File file, int L,
			int R) throws FileNotFoundException {
		PrintStream f = new PrintStream(file);

		for (ExtractRecord item : top) {
			if (L > R)
				f.println(item.language + " " + item.page + " "
						+ (R * item.left / L) + " " + item.right + " "
						+ item.getScore(L, R));
			else if (R > L)
				f.println(item.language + " " + item.page + " " + item.left
						+ " " + (L * item.right / R) + " "
						+ item.getScore(L, R));
			else
				f.println(item.language + " " + item.page + " " + item.left
						+ " " + item.right + " " + item.getScore(L, R));
		}

		f.close();
	}
}
