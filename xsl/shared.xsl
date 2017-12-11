<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns:xlink="http://www.w3.org/1999/xlink" version="1.0">

	<!-- shared -->
	
	<!-- string replace -->    
    <xsl:variable name="smallcase" select="'abcdefghijklmnopqrstuvwxyz'" />
    <xsl:variable name="uppercase" select="'ABCDEFGHIJKLMNOPQRSTUVWXYZ'" />
    <!-- from http://aspn.activestate.com/ASPN/Cookbook/XSLT/Recipe/65426 -->
    <!-- reusable replace-string function -->
    <xsl:template name="replace-string">
        <xsl:param name="text"/>
        <xsl:param name="from"/>
        <xsl:param name="to"/>
        <xsl:choose>
            <xsl:when test="contains($text, $from)">
                <xsl:variable name="before" select="substring-before($text, $from)"/>
                <xsl:variable name="after" select="substring-after($text, $from)"/>
                <xsl:variable name="prefix" select="concat($before, $to)"/>
                <xsl:value-of select="$before"/>
                <xsl:value-of select="$to"/>
                <xsl:call-template name="replace-string">
                    <xsl:with-param name="text" select="$after"/>
                    <xsl:with-param name="from" select="$from"/>
                    <xsl:with-param name="to" select="$to"/>
                </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="$text"/>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    
    <!-- summarise article metadata -->
     <xsl:template match="front">
        <div>
        
        
            <div>
                <xsl:value-of select="journal-meta/journal-title-group/journal-title" />
                <xsl:text> </xsl:text>
                <xsl:if test="article-meta/pub-date/day">
                    <xsl:value-of select="article-meta/pub-date/day" />
                    <xsl:text> </xsl:text>
                </xsl:if>
                <xsl:if test="article-meta/pub-date/month">
                    <xsl:choose>
                        <xsl:when test="article-meta/pub-date/month = 1">
                            <xsl:text>January</xsl:text>
                        </xsl:when>
                        <xsl:when test="article-meta/pub-date/month = 2">
                            <xsl:text>February</xsl:text>
                        </xsl:when>
                        <xsl:when test="article-meta/pub-date/month = 3">
                            <xsl:text>March</xsl:text>
                        </xsl:when>
                        <xsl:when test="article-meta/pub-date/month = 4">
                            <xsl:text>April</xsl:text>
                        </xsl:when>
                        <xsl:when test="article-meta/pub-date/month = 5">
                            <xsl:text>May</xsl:text>
                        </xsl:when>
                        <xsl:when test="article-meta/pub-date/month = 6">
                            <xsl:text>June</xsl:text>
                        </xsl:when>
                        <xsl:when test="article-meta/pub-date/month = 7">
                            <xsl:text>July</xsl:text>
                        </xsl:when>
                        <xsl:when test="article-meta/pub-date/month = 8">
                            <xsl:text>August</xsl:text>
                        </xsl:when>
                        <xsl:when test="article-meta/pub-date/month = 9">
                            <xsl:text>September</xsl:text>
                        </xsl:when>
                        <xsl:when test="article-meta/pub-date/month = 10">
                            <xsl:text>October</xsl:text>
                        </xsl:when>
                        <xsl:when test="article-meta/pub-date/month = 11">
                            <xsl:text>November</xsl:text>
                        </xsl:when>
                        <xsl:when test="article-meta/pub-date/month = 12">
                            <xsl:text>December</xsl:text>
                        </xsl:when>
                    </xsl:choose>
                    <xsl:text> </xsl:text>
                </xsl:if>
                <xsl:if test="article-meta/pub-date/year">
                    <xsl:value-of select="article-meta/pub-date/year" />
                </xsl:if>
                <xsl:text> </xsl:text>
                <xsl:value-of select="article-meta/volume" />
                <xsl:if test="article-meta/issue">
                    <xsl:text>(</xsl:text>
                    <xsl:value-of select="article-meta/issue" />
                    <xsl:text>)</xsl:text>
                </xsl:if>
                <!-- pagination -->
                <xsl:if test="article-meta/fpage">
                    <xsl:text>:</xsl:text>
                    <xsl:value-of select="article-meta/fpage" />
                    <xsl:if test="article-meta/lpage">
                        <xsl:text>-</xsl:text>
                        <xsl:value-of select="article-meta/lpage" />
                    </xsl:if>
                </xsl:if>
                <!-- article number -->
                <xsl:if test="article-meta/article-id[@pub-id-type='publisher-id']">
                    <xsl:text> </xsl:text>
                    <xsl:value-of select="article-meta/article-id[@pub-id-type='publisher-id']" />
                </xsl:if>
            </div>
            
            <span style="font-size:1.8em;font-weight:bold;">
                <!-- https://stackoverflow.com/questions/701723/rendering-html-tags-from-within-cdata-tag-in-xsl -->
                <xsl:value-of select="//article-title" disable-output-escaping="yes" />
            </span>
            
            
            
            <xsl:apply-templates select="//contrib-group" />
			<!-- comment out as we will do this elswhere in the web page
            <ul>
                <xsl:apply-templates select="//article-id" />
                <xsl:apply-templates select="//self-uri[@content-type='lsid']" />
            </ul>
            -->
        </div>
    </xsl:template>    
    
    <!-- abstract -->
    <xsl:template match="abstract">
        <p style="font-size:1em;border-top:1px solid rgb(128,128,128);border-bottom:1px solid rgb(128,128,128);margin-top:10px;">
            <xsl:if test="@xml:lang">
                <xsl:text>[</xsl:text>
                <xsl:value-of select="@xml:lang" />
                <xsl:text>]</xsl:text>
            </xsl:if>
            <xsl:value-of select="." />
        </p>
    </xsl:template>
    
    <!-- identifiers -->
    <xsl:template match="article-id">
        <xsl:choose>
            <xsl:when test="@pub-id-type='doi'">
                <li>
                    <xsl:text>DOI:</xsl:text>
                    <xsl:value-of select="." />
                </li>
            </xsl:when>
            <xsl:when test="@pub-id-type='pmid'">
                <li>
                    <xsl:text>PMID:</xsl:text>
                    <xsl:value-of select="." />
                </li>
            </xsl:when>
            <xsl:when test="@pub-id-type='pmc'">
                <li>
                    <xsl:value-of select="." />
                </li>
            </xsl:when>
            <xsl:otherwise />
        </xsl:choose>

    </xsl:template>
    
    
    <!-- ZooBank LSID for article -->
    <xsl:template match="//self-uri[@content-type='lsid']">
        <li>
            <xsl:value-of select="." />
        </li>
    </xsl:template>
    
    
    <!-- authors -->
    <xsl:template match="//contrib-group">
        <div class="authors">
            <xsl:apply-templates select="contrib" />
        </div>
    </xsl:template>
    
    <!-- contributors -->
    <xsl:template match="contrib">
        <xsl:if test="@contrib-type='author'">
        <!--
            <xsl:if test="position() != 1">
                <xsl:text>, </xsl:text>
            </xsl:if>
        -->
			<xsl:if test="position() != 1">
                <xsl:text> </xsl:text>
            </xsl:if>            
        
        	<button type="button" class="btn btn-default btn-sm">
            <xsl:apply-templates select="name" />
            <xsl:apply-templates select="contrib-id" />
            </button>
        </xsl:if>
    </xsl:template>
    
    <!-- person's name -->
    <xsl:template match="name">
        <xsl:if test="string-name">
            <xsl:value-of select="string-name" />
        </xsl:if>
        <xsl:if test="given-names">
            <xsl:value-of select="given-names" />
            <xsl:text> </xsl:text>
        </xsl:if>
        <xsl:if test="surname">
            <xsl:value-of select="surname" />
        </xsl:if>
    </xsl:template>
    
    <!-- contrib-id e.g. ORCID -->
    <xsl:template match="contrib-id">
        <xsl:if test="@contrib-id-type = 'orcid'">
        	<xsl:text> </xsl:text>
           <a>
           		<xsl:attribute name="href">
            		<xsl:value-of select="." />
            	</xsl:attribute>
            	<img src="images/orcid_24x24.gif" />
        	</a>
        </xsl:if>
    </xsl:template>
    
    <!-- figure -->
    <xsl:template match="fig">
        <xsl:apply-templates select="graphic" />
    </xsl:template>
    
    <!-- figure or table -->
    <xsl:template match="graphic">
        <div style="float:left;padding:20px;">
            <a>
                <xsl:attribute name="href">
                    <xsl:value-of select="@xlink:href" />
                </xsl:attribute>
                <img height="100">
                    <xsl:attribute name="src">
                        <xsl:value-of select="@xlink:href" />
                    </xsl:attribute>
                </img>
            </a>
        </div>
    </xsl:template>
    
        
    <!-- back -->
    <xsl:template match="back">
        <xsl:apply-templates select="ref-list" />
    </xsl:template>
    
    <!-- reference list -->
    <xsl:template match="ref-list">
        <h2 id="reference-sec">Selected references</h2>
        <ol>
            <xsl:apply-templates select="ref" />
        </ol>
    </xsl:template>
    
    <!-- one reference -->
    <xsl:template match="ref">
        <li style="padding:4px;">
            <xsl:apply-templates select="mixed-citation" />
        </li>
    </xsl:template>
        
    <!-- mixed-citation -->
    <xsl:template match="mixed-citation">
        <!-- may be just unstructured text or full of tags -->
        <xsl:choose>
            <xsl:when test="article-title and volume">
                <xsl:value-of select="person-group/string-name" />
                <xsl:text>(</xsl:text>
                <xsl:value-of select="year" />
                <xsl:text>)</xsl:text>
                <b>
                    <xsl:value-of select="article-title" />
                </b>
                <xsl:text> </xsl:text>
                <xsl:value-of select="source" />
                <xsl:text> </xsl:text>
                <xsl:value-of select="volume" />
                <xsl:text>:</xsl:text>
                <xsl:value-of select="fpage" />
                <!-- <xsl:text>-</xsl:text><xsl:value-of select="lpage" /> -->
                <!-- OpenURL -->
                <xsl:text> </xsl:text>
                <a>
                    <xsl:attribute name="class">Z3988</xsl:attribute>
                    <xsl:attribute name="title"></xsl:attribute>
                    <xsl:attribute name="href">
                        <xsl:text>http://direct.biostor.org/openurl?</xsl:text>
                        <xsl:text>ctx_ver=Z39.88-2004&amp;rft_val_fmt=info:ofi/fmt:kev:mtx:journal</xsl:text>
                        <!-- referring entity (i.e., this article -->
                        <!-- <xsl:text>&amp;rfe_id=info:doi/</xsl:text><xsl:value-of select="//article-meta/article-id[@pub-id-type='doi']"/> -->
                        <!-- authors -->
                        <xsl:for-each select="person-group">
                            <xsl:if test="@person-group-type='author'">
                                <!-- first author -->
                                <xsl:text>&amp;rft.aulast=</xsl:text>
                                <xsl:value-of select="name[1]/surname"/>
                                <xsl:text>&amp;rft.aufirst=</xsl:text>
                                <xsl:value-of select="name[1]/given-names"/>
                                <!-- all authors -->
                                <xsl:for-each select="name">
                                    <xsl:text>&amp;rft.au=</xsl:text>
                                    <xsl:value-of select="given-names"/>
                                    <xsl:text>+</xsl:text>
                                    <xsl:value-of select="surname"/>
                                </xsl:for-each>
                            </xsl:if>
                        </xsl:for-each>
                        <!-- article title -->
                        <xsl:text>&amp;rft.atitle=</xsl:text>
                        <xsl:call-template name="replace-string">
                            <xsl:with-param name="text" select="article-title"/>
                            <xsl:with-param name="from" select="' '"/>
                            <xsl:with-param name="to" select="'+'"/>
                        </xsl:call-template>
                        <!-- journal title -->
                        <xsl:text>&amp;rft.jtitle=</xsl:text>
                        <xsl:call-template name="replace-string">
                            <xsl:with-param name="text" select="source"/>
                            <xsl:with-param name="from" select="' '"/>
                            <xsl:with-param name="to" select="'+'"/>
                        </xsl:call-template>
                        <!-- sometimes volume field contains issue -->
                        <xsl:choose>
                            <xsl:when test="contains(volume, ')')">
                                <xsl:variable name="after" select="substring-after(volume, '(')"/>
                                <xsl:variable name="issue" select="substring-before($after, ')')"/>
                                <xsl:variable name="before" select="substring-before(volume, '(')"/>
                                <xsl:text>&amp;rft.volume=</xsl:text>
                                <xsl:value-of select="$before"/>
                                <xsl:text>&amp;rft.issue=</xsl:text>
                                <xsl:value-of select="$issue"/>
                            </xsl:when>
                            <xsl:otherwise>
                                <xsl:text>&amp;rft.volume=</xsl:text>
                                <xsl:value-of select="volume"/>
                            </xsl:otherwise>
                        </xsl:choose>
                        <xsl:text>&amp;rft.spage=</xsl:text>
                        <xsl:value-of select="fpage"/>
                        <xsl:text>&amp;rft.epage=</xsl:text>
                        <xsl:value-of select="lpage"/>
                        <xsl:text>&amp;rft.date=</xsl:text>
                        <xsl:value-of select="year"/>
                    </xsl:attribute>
                    <xsl:text>OpenURL</xsl:text>
                </a>
            </xsl:when>
            <xsl:when test="source and publisher-name and publisher-loc">
                <b>
                    <xsl:value-of select="source" />
                </b>
                <xsl:text>, </xsl:text>
                <xsl:value-of select="publisher-name" />
                <xsl:text>, </xsl:text>
                <xsl:value-of select="publisher-loc" />
            </xsl:when>
            <xsl:when test="chapter-title and source and size">
                <b>
                    <xsl:value-of select="chapter-title" />
                </b>
                <xsl:value-of select="source" />
                <xsl:text>, </xsl:text>
                <xsl:value-of select="size" />
            </xsl:when>
            <xsl:otherwise>
                <!-- need to be careful as if citation is simply a DOI we 
             will get it to appear twice.  -->
                <xsl:value-of select="."/>
            </xsl:otherwise>
        </xsl:choose>
        <xsl:apply-templates select="ext-link" />
    </xsl:template>
    
    <xsl:template match="ext-link">
        <xsl:variable name="uri" select="@xlink:href" />
        <xsl:if test="contains($uri, 'doi.org/')">
            <xsl:text>DOI:</xsl:text>
            <a>
                <xsl:attribute name="href">
                    <xsl:value-of select="$uri" />
                </xsl:attribute>
                <xsl:value-of select="substring-after($uri, 'doi.org/')" />
            </a>
        </xsl:if>
    </xsl:template>
    
</xsl:stylesheet>