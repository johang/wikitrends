package wikitrends.top;

import java.util.Comparator;
import java.util.Iterator;
import java.util.Set;
import java.util.TreeSet;

public class Top<T> implements Iterable<T> {
	private final int size;

	private final TreeSet<T> items;

	public Top(int n, Comparator<T> c) {
		size = n;
		items = new TreeSet<T>(c);
	}

	public void offer(T t) {
		items.add(t);

		while (items.size() > size) {
			items.pollFirst();
		}
	}

	public Set<T> getList() {
		return items.descendingSet();
	}

	@Override
	public Iterator<T> iterator() {
		return items.descendingIterator();
	}
}
