<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:s="http://www.sitemaps.org/schemas/sitemap/0.9"
    xmlns:xhtml="http://www.w3.org/1999/xhtml"
    xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
    exclude-result-prefixes="s xhtml image">
  <xsl:output method="html" encoding="UTF-8" indent="yes"/>
  <xsl:template match="/">
    <html lang="en">
      <head>
        <meta charset="UTF-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <title>NovaCart XML Sitemap</title>
        <style>
          *{box-sizing:border-box}
          body{margin:0;background:#f4f6f8;color:#263449;font:15px/1.5 system-ui,-apple-system,"Segoe UI",sans-serif}
          main{max-width:1280px;margin:32px auto;padding:0 20px}
          header{background:#fff;border:1px solid #dce2ea;border-radius:10px;padding:24px;margin-bottom:20px}
          h1{font-size:26px;margin:0 0 6px;color:#18283d}
          p{margin:6px 0;color:#536276}
          a{color:#155aaf;text-decoration:none;overflow-wrap:anywhere}
          a:hover{text-decoration:underline}
          a:focus-visible{outline:2px solid #155aaf;outline-offset:3px}
          .back{display:inline-block;margin-bottom:12px}
          .count{font-weight:600;color:#263449}
          .table{overflow:auto;border:1px solid #dce2ea;border-radius:10px;background:#fff}
          table{width:100%;border-collapse:collapse;text-align:left}
          th,td{padding:12px 16px;border-bottom:1px solid #e3e8ee;vertical-align:top}
          th{background:#e9eef5;color:#263449;font-size:13px;white-space:nowrap}
          tbody tr:nth-child(even){background:#f8fafc}
          tbody tr:hover{background:#edf4fc}
          tbody tr:last-child td{border-bottom:0}
          .number,.date,.language,.images{white-space:nowrap;font-variant-numeric:tabular-nums}
          .number,.images{text-align:right;color:#536276}
          .language{font-size:13px}
          .empty{padding:18px}
          @media(max-width:600px){main{margin:14px auto;padding:0 10px}header{padding:16px}h1{font-size:22px}th,td{padding:9px}}
        </style>
      </head>
      <body>
        <main>
          <header>
            <xsl:if test="s:urlset"><a class="back" href="sitemap.xml">&#8592; Sitemap index</a></xsl:if>
            <h1>NovaCart XML Sitemap</h1>
            <xsl:choose>
              <xsl:when test="s:sitemapindex">
                <p class="count">Sitemaps: <xsl:value-of select="count(s:sitemapindex/s:sitemap)"/></p>
                <p>Select a sitemap to browse its URLs.</p>
              </xsl:when>
              <xsl:otherwise>
                <p class="count">URLs: <xsl:value-of select="count(s:urlset/s:url)"/></p>
                <p>Page addresses, available languages and last modification dates.</p>
              </xsl:otherwise>
            </xsl:choose>
            <p>This XML file is intended for search engines. This view is provided for visitors.</p>
          </header>
          <div class="table">
            <xsl:choose>
              <xsl:when test="s:sitemapindex">
                <table>
                  <thead><tr><th>#</th><th>Sitemap URL</th><th>Last modified</th></tr></thead>
                  <tbody>
                    <xsl:for-each select="s:sitemapindex/s:sitemap">
                      <tr><td class="number"><xsl:value-of select="position()"/></td><td><a href="{s:loc}"><xsl:value-of select="s:loc"/></a></td><td class="date"><xsl:call-template name="date"/></td></tr>
                    </xsl:for-each>
                  </tbody>
                </table>
              </xsl:when>
              <xsl:otherwise>
                <table>
                  <thead><tr><th>#</th><th>Page URL</th><th>Language</th><th>Last modified</th><th>Images</th></tr></thead>
                  <tbody>
                    <xsl:for-each select="s:urlset/s:url">
                      <xsl:variable name="loc" select="s:loc"/>
                      <tr>
                        <td class="number"><xsl:value-of select="position()"/></td>
                        <td><a href="{s:loc}"><xsl:value-of select="s:loc"/></a></td>
                        <td class="language">
                          <xsl:choose>
                            <xsl:when test="xhtml:link[@href=$loc and @hreflang!='x-default']"><xsl:value-of select="xhtml:link[@href=$loc and @hreflang!='x-default'][1]/@hreflang"/></xsl:when>
                            <xsl:otherwise>&#8212;</xsl:otherwise>
                          </xsl:choose>
                        </td>
                        <td class="date"><xsl:call-template name="date"/></td>
                        <td class="images"><xsl:value-of select="count(image:image)"/></td>
                      </tr>
                    </xsl:for-each>
                  </tbody>
                </table>
                <xsl:if test="not(s:urlset/s:url)"><p class="empty">No URLs in this sitemap.</p></xsl:if>
              </xsl:otherwise>
            </xsl:choose>
          </div>
        </main>
      </body>
    </html>
  </xsl:template>
  <xsl:template name="date">
    <xsl:choose>
      <xsl:when test="s:lastmod"><xsl:value-of select="substring(s:lastmod,1,10)"/></xsl:when>
      <xsl:otherwise>&#8212;</xsl:otherwise>
    </xsl:choose>
  </xsl:template>
</xsl:stylesheet>
