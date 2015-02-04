MantisBT_Sitemap
====================

This script generates and outputs a sitemap (in XML format) for [MantisBT](https://www.mantisbt.org/)  installations.

### Installation ###
The first step is to simply place the ``sitemap.php`` inside the folder where MantisBT is installed. No changes should need to be made to ``sitemap.php`` in order for it to work properly. The next step is to setup a rewrite rule so that requests for ``sitemap.xml`` will be redirected to ``sitemap.php``. 

The following is a rewrite rule the most popular web servers. This should be at the top of the rewrite rules in the ``.htaccess`` file.

    RewriteRule sitemap\.xml sitemap.php [L]

You should now be able to view the sitemap by navigating to something like ``http://example.com/sitemap.xml``
	
### Notes ###
 - By default, MantisBT sets the meta value of ``robots`` to ``noindex,follow`` on some of the pages. This causes search engines to not cache the page but links will be followed.
 - MantisBT implements RSS feeds which are located at ``http://example.com/issues_rss.php`` and ``http://example.com/news_rss.php``. These can also be submitted to Google's and Bing's Webmaster Tools.

### License ###

MantisBT_Sitemap is licensed under the [GNU Lesser General Public License](http://www.gnu.org/copyleft/lesser.html).

### Show Your Support ###

Little Apps relies on people like you to keep our software running. If you would like to show your support for MantisBT_Sitemap, then you can [make a donation](http://www.little-apps.com/?donate) using PayPal, Payza or Bitcoins. Please note that any amount helps (even just $1).

