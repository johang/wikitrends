package wikitrends.records;

public class ExtractRecord implements Comparable<ExtractRecord> {
	public final String language, page;

	public int left, right;

	public ExtractRecord(String l, String p, int hl) {
		language = l;
		page = p;
		left = hl;
	}

	public ExtractRecord(String l, String p, int hl, int hr) {
		language = l;
		page = p;
		left = hl;
		right = hr;
	}

	public double getScore(int lefts, int rights) {
		return Math.abs((left / lefts) - (right / rights))
				* Math.log(((left / lefts) + 1.0d) / ((right / rights) + 1.0d));
	}

	public void add(ExtractRecord r) {
		if (!samePageAndLanguage(r))
			throw new IllegalArgumentException("Not same language and page.");

		left += r.left;
		right += r.right;
	}

	public boolean samePageAndLanguage(ExtractRecord o) {
		return language.equals(o.language) && page.equals(o.page);
	}

	@Override
	public boolean equals(Object o) {
		if (!(o instanceof ExtractRecord))
			return false;

		final ExtractRecord r = (ExtractRecord) o;

		return language.equals(r.language) && page.equals(r.page)
				&& left == r.left && right == r.right;
	}

	@Override
	public int compareTo(ExtractRecord r) {
		if (!language.equals(r.language))
			return language.compareTo(r.language);

		if (!page.equals(r.page))
			return page.compareTo(r.page);

		return 0;
	}
}
