<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9">

    <xsl:template match="/">
        <html lang="fr">
            <head>
                <meta charset="UTF-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
                <title>Plan du site - EnergyDash</title>
                <link rel="stylesheet" href="assets/css/SiteMap.css"/>
            </head>
            <body>
                <div class="container">
                    <header>
                        <h1>
                            <span style="font-size: 3rem;">🗺️</span>
                            Plan du site
                        </h1>
                        <p class="subtitle">EnergyDash - Toutes les pages disponibles</p>
                    </header>

                    <div class="stats">
                        <div class="stat-card">
                            <div class="stat-number">
                                <xsl:value-of select="count(sitemap:urlset/sitemap:url)"/>
                            </div>
                            <div class="stat-label">Pages totales</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number">
                                <xsl:value-of select="substring(//sitemap:lastmod[1], 1, 10)"/>
                            </div>
                            <div class="stat-label">Dernière mise à jour</div>
                        </div>
                    </div>

                    <div class="pages-grid">
                        <xsl:for-each select="sitemap:urlset/sitemap:url">
                            <div class="page-card">
                                <div class="page-title">
                                    <xsl:value-of select="sitemap:title"/>
                                </div>

                                <a class="page-url" href="{sitemap:loc}">
                                    <xsl:value-of select="sitemap:loc"/>
                                    <span>→</span>
                                </a>

                                <p class="page-description">
                                    <xsl:value-of select="sitemap:description"/>
                                </p>

                                <div class="page-meta">
                                    <xsl:choose>
                                        <xsl:when test="sitemap:priority &gt;= 0.8">
                                            <span class="badge priority-high">
                                                ⭐ Priorité: <xsl:value-of select="sitemap:priority"/>
                                            </span>
                                        </xsl:when>
                                        <xsl:when test="sitemap:priority &gt;= 0.5">
                                            <span class="badge priority-medium">
                                                ⚡ Priorité: <xsl:value-of select="sitemap:priority"/>
                                            </span>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <span class="badge priority-low">
                                                📌 Priorité: <xsl:value-of select="sitemap:priority"/>
                                            </span>
                                        </xsl:otherwise>
                                    </xsl:choose>

                                    <span class="badge freq-badge">
                                        🔄 <xsl:value-of select="sitemap:changefreq"/>
                                    </span>

                                    <span class="badge date-badge">
                                        📅 <xsl:value-of select="substring(sitemap:lastmod, 1, 10)"/>
                                    </span>
                                </div>
                            </div>
                        </xsl:for-each>
                    </div>

                    <footer>
                        <p>Sitemap XML généré automatiquement • EnergyDash © 2025</p>
                        <p style="margin-top: 10px; font-size: 0.8rem;">
                            Pour les moteurs de recherche : Ce fichier est au format XML standard
                        </p>
                    </footer>
                </div>
            </body>
        </html>
    </xsl:template>

</xsl:stylesheet>