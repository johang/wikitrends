package wikitrends.top;

import java.util.Comparator;

import wikitrends.records.ExtractRecord;

public class RightExtractComparator implements Comparator<ExtractRecord> {
	@Override
	public int compare(ExtractRecord o1, ExtractRecord o2) {
		return o1.right - o2.right;
	}
}
