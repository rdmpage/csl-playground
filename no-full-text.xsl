<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns:xlink="http://www.w3.org/1999/xlink" version="1.0">
    <xsl:output encoding="utf-8" indent="yes" method="html" version="1.0" />
    
	<xsl:param name="work" />    
	
	<xsl:include href="shared.xsl" />	

    <xsl:template match="/">
    	 <xsl:apply-templates select="//front" />
    	 
        <xsl:if test="//front/article-meta/abstract">
            <xsl:apply-templates select="//front/article-meta/abstract" />
        </xsl:if>
    	 	 
    	 <xsl:apply-templates select="//body" />
    	 <xsl:apply-templates select="//back" />
    </xsl:template>
    
     <xsl:template match="body">    
            <!-- page image thumbnails -->
            <xsl:if test="//supplementary-material/graphic">
					<p>
						
							<a>
								<xsl:attribute name="href">
									<xsl:text>work/</xsl:text>
									<xsl:value-of select="$work" />
									<xsl:text>/full</xsl:text>
								</xsl:attribute>
							<xsl:text>Full text is available.</xsl:text>
							</a>
						
						<!--
						<a>
							<xsl:attribute name="href">
							<xsl:text>https://archive.org/download/biostor-</xsl:text>
							<xsl:value-of select="//article-id[@pub-id-type=&amp;quot;biostor&amp;quot;]"/><xsl:text>/biostor-</xsl:text><xsl:value-of select="//article-id[@pub-id-type=&amp;quot;biostor&amp;quot;]"/>
							<xsl:text>.pdf</xsl:text>
							</xsl:attribute>
							<xsl:text>complete article</xsl:text>
						</a>
						
						<xsl:text>.</xsl:text> 
						<xsl:if test="//back/ref-list">
							<xsl:text>Links are also available for </xsl:text>
							<a href="#reference-sec">Selected References</a>
							<xsl:text>.</xsl:text>
						</xsl:if> -->
					</p>
					
                <div>
                    <xsl:apply-templates select="//supplementary-material/graphic" />
                </div>
            </xsl:if>
            
            <!-- extracted images -->
            <xsl:if test="//floats-group">
                <div style="clear:both;" />
                <h2>Images in this article</h2>
                <!-- <p>Figures and tables extracted from ABBYY OCR XML.</p> -->
                <div>
                    <xsl:apply-templates select="//fig" />
                </div>
                <div style="clear:both;" />
            </xsl:if>    
    </xsl:template>
    
    <!-- scanned page -->
    <!-- thumbnails -->
    <xsl:template match="//supplementary-material/graphic[@xlink:role='thumbnail']">
        <div style="float:left;padding:20px;">
            <!-- link to HTML -->
            <a>
                <xsl:attribute name="href">
                    <xsl:text>work/</xsl:text>
                    <xsl:value-of select="$work" />
                    <xsl:text>/full</xsl:text>
                    <xsl:text>#page=</xsl:text>
                    <xsl:value-of select="@xlink:title" />
                </xsl:attribute>
                <img style="border:1px solid rgb(192,192,192);" height="140">
                    <xsl:attribute name="src">
                        <!--
					<xsl:text>Med_Hist_1985_Jan_29(1)_1-32/</xsl:text><xsl:value-of select="substring-before(@xlink:href, '.tif')" /><xsl:text>.gif</xsl:text>
				-->
                        <xsl:value-of select="@xlink:href" />
                    </xsl:attribute>
                </img>
            </a>
            <!-- Page name -->
            <div style="text-align:center;width:100px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                <xsl:value-of select="@xlink:title" />
            </div>
        </div>
    </xsl:template>
    
    <!-- suppress full page images -->
     <xsl:template match="//supplementary-material/graphic[@xlink:role='image']">
     </xsl:template>
    

</xsl:stylesheet>