# Sitemap PRO routing and presentation

The main index is /sitemap.xml. Child maps use /sitemap-home.xml and
/sitemap-{type}-{part}.xml. Existing index.php?route=extension/feed/google_sitemap
URLs (including sitemap_type and sitemap_part) remain supported.

Apache: the bundled upload/.htaccess contains explicit rules. Existing custom
rewrite configurations forwarding unknown paths as _route_ are also supported
by the catalog SEO startup controller.

Nginx: forward sitemap.xml and sitemap-*.xml to the storefront front controller
with _route_ set to the requested filename if the existing generic SEO rule
does not already do this. Serve sitemap.xsl directly as a static XML stylesheet
from the store root (application/xml or text/xsl). Preserve the store subdirectory
when applicable.

The XSL stylesheet is presentation only: XML content, namespaces and hreflang
remain available to search engines. Browsers without XSLT support can use the
raw XML source. The stylesheet is self-contained and uses no external assets.
