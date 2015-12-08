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

Looking up images involves a number of steps: Finding the corresponding DbPedia uri from the nobelprize.org linked data API, Finding the corresponding English Wikipedia article from DbPedia, finding the corresponding articles on differens Wikipedia edititions from the Wikidata API, and finally fetching all images from all articles, finding their Wikimedia Commons thumbnails, and filtering out obviously irrelevant, blacklisted and duplicated content. Caching is crucial, as these requests will block page rendering.

A quick note on the image selection criteria: Using Wikipedia article illustrations generally gave a better result for most laureates, compared to other methods we tried (using fautured images from a Wikimedia Commons category, and using images from Wikimedia Commons pages), but it will give some irrelevant images for some laureates, escpecially those who are more well known in another capacity than that as a Nobel prize laureate (e.g. Henry Kissinger). In such case, simply keep adding images that feel out of place to `$gImageBlacklist` in [settings.php](settings.default.php).