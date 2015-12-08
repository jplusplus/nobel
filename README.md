Installation
============

 * Install Composer: `curl -sS https://getcomposer.org/installer | php`
 * Run `php composer.phar install`
 * `copy settings.default.php settings.php` and make any modifications needed to settings.php

End points
==========

 * list.php
     - See [demo/lists.php](demo/lists.php) and [demo/ui.php](demo/ui.php)
 * gallery.php
     - See [demo/gallery.php](demo/gallery.php)

Gallery
=======
_See [demo/gallery.php](demo/gallery.php) for how to include a gallery._

The gallery widget shows images from Wikimedia Commons, related to a specific laureate. The images are picked from article illustrations on selected Wikipedias. There will be irrelevant images showing up in some cases. These can be manually removed by adding them to the blacklist array `$gImageBlacklist` in [settings.php](settings.default.php).

Wikipedia editions can be added or removed in `$gImageSourceWPEditions` in [settings.php](settings.default.php). By default we include *large* Wikipedias with a *big number of contributors*. Size alone is not a good measure, as those figures are misleading for editions with high bot activity (e.g. [Swedish](https://sv.wikipedia.org/wiki/Portal:Huvudsida) or [Cebuano](https://ceb.wikipedia.org/wiki/Unang_Panid)). Missing from the list is Russian, despite its big size and high user activity. The Russian Wikipedia community is quite fond of using genre images for navigation templates, which will increase the number of irrelevant images shown, and thus require a large blacklist.
Not included by default, but worth contemplating, are French (`fr`), Swedish (`sv`), and Japanese (`ja`) Wikipedia, given the large number of laureates from France, Switzerland, Sweden and Japan.

Looking up images involves a number of steps: Finding the corresponding DbPedia uri from the nobelprize.org linked data API, Finding the corresponding English Wikipedia article from DbPedia, finding the corresponding articles on different Wikipedia edititions from the Wikidata API, and finally fetching all images from all articles, finding their Wikimedia Commons thumbnails, and filtering out obviously irrelevant, blacklisted and duplicated content. Caching is crucial, as these requests will block page rendering.

Image captions consist of an English image description, and an image credit/license text if (and only if) required. Very long descriptions are truncated. If descriptions look weird, or contain text in other languages, it is most likely because the images does not use the right templates at [Wikimedia Commons](https://commons.wikimedia.org). Such problems should be fixed there. The Wikimedia Commons community currently use the [`{{Information}}`](https://commons.wikimedia.org/wiki/Template:Information) template to make sure images have machine readable descriptions. While not strictly nescessary, using the Information template (or [some other template with similar functionality](https://commons.wikimedia.org/wiki/Commons:Machine-readable_data)) is the simplest way to ensure that image data is parsed correctly by our image galleries.

A quick note on the image selection criteria: Using Wikipedia article illustrations generally gave a better result for most laureates, compared to other methods we tried (using fautured images from a Wikimedia Commons category, and using images from Wikimedia Commons pages), but it does return some irrelevant images for quite a few laureates, escpecially those who are more well known in another capacity than that as a Nobel prize laureate (e.g. Henry Kissinger). In such cases, simply keep adding images that feel out of place to `$gImageBlacklist` in [settings.php](settings.default.php).

List widget
===========
_See [demo/lists.php](demo/lists.php) for how to include a list widget._

The list widget shows a top list of the most popular laureates, optionally filtered by _gender_, _birth place_, or _award_, in any combination. Popularity can be based on either nobelprize.org page views (as returned by the [local toplist API](http://www.nobelprize.org/nobel_prizes/popular_api.php)), or by a weighted average of page views at a number of Wikipedia editions.

By default, the Wikipedia popularity is based on the editions of the world's largest languages. You can add or remove editions, or change their weights, in the `$gStatsWPEditions` array in [settings.php](settings.default.php).

Estimating the size of a language is notoriously difficult, and the situation is made even more complex when we start mapping languages to Wikipedia editions. Javanese, for instance, is generally regarded as one of the worlds 10 largest languages, but the Javanese Wikipedia is very, very small, with most Javanese speakers using the Indonesian Wikipedia. Some languages are divided among multiple Wikipedias (such as Arabic, with a separate Wikipedia in Egyptian Arabic). Some Wikipedias are more accessible than others to their main target audience, like the Chinese Wikipedia (occasionally blocked in mainland China). By default, we use the following editions for page view statistics: Chinese (`zh`), including script varieties (zh-hans, zh-tw, etc), but excluding Minnan, Yue (Cantonese), Mindong, Wu, Hakka, and Gan; English, not including simplified English or Scots; Spanish; Hindi, not including Urdu; and Arabic, excluding Egyptian Arabic. Portugese would probably be the next language to add, if the list were ti be expanded.

Wikipedia statistics are fetched one editions at a time, making the first rendering of an uncached list _very_ slow. Caching is crucial, as these requests will block page rendering. Fetching statistics involves a number of steps: Finding the corresponding DbPedia uri from the nobelprize.org linked data API, Finding the corresponding English Wikipedia article from DbPedia, finding the corresponding articles on different Wikipedia edititions from the Wikidata API, and fetching their page views statistics from Wikimedia statistics API.


List ui
=======