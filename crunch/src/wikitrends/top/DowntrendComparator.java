package wikitrends.top;

import java.util.Comparator;

import wikitrends.records.ExtractRecord;

public class DowntrendComparator implements Comparator<ExtractRecord> {
	private final int ls, rs;

	public DowntrendComparator(int lefts, int rights) {
		ls = lefts;
		rs = rights;
	}

	@Override
	public int compare(ExtractRecord o1, ExtractRecord o2) {
		return -Double.compare(o1.getScore(ls, rs), o2.getScore(ls, rs));
	}
}
