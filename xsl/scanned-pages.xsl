<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns:xlink="http://www.w3.org/1999/xlink" version="1.0">
    <xsl:output encoding="utf-8" indent="yes" method="html" version="1.0" />
    
	<xsl:param name="work" />      
	
    <xsl:include href="shared.xsl" />
    
    <xsl:template match="/">
    	 <xsl:apply-templates select="//front" />
    	 <xsl:apply-templates select="//body" />
    	 <xsl:apply-templates select="//back" />
    </xsl:template>
    
     <xsl:template match="body">    
            <!-- page images -->
            <xsl:if test="//supplementary-material/graphic">
                <!--
					<h2>Full text</h2><p><xsl:text>
							Full text is available as a scanned copy of the original print version. 
							Get a printable copy (PDF file) of the 
						</xsl:text><a><xsl:attribute name="href"><xsl:text>https://archive.org/download/biostor-</xsl:text><xsl:value-of select="//article-id[@pub-id-type=&amp;quot;biostor&amp;quot;]"/><xsl:text>/biostor-</xsl:text><xsl:value-of select="//article-id[@pub-id-type=&amp;quot;biostor&amp;quot;]"/><xsl:text>.pdf</xsl:text></xsl:attribute><xsl:text>complete article</xsl:text></a><xsl:text>.</xsl:text><xsl:if test="//back"><xsl:text>Links are also available for </xsl:text><a href="#reference-sec">Selected References</a><xsl:text>.</xsl:text></xsl:if></p>
					-->
				
                <div style="background-color:rgb(192,192,192);height:800px;overflow-y:auto;text-align:center;">
                    <xsl:apply-templates select="//supplementary-material/graphic" />
                </div>
            </xsl:if>
    </xsl:template>

    <!-- scanned page -->
    
    <!-- suppress thumbnails -->
     <xsl:template match="//supplementary-material/graphic[@xlink:role='thumbnail']">
     </xsl:template>

	<!-- full page images -->
    <xsl:template match="//supplementary-material/graphic[@xlink:role='image']">
        <div style="padding:4px;">
        	<a>
        		<xsl:attribute name="name">
        			<xsl:text>page=</xsl:text>
        			<xsl:value-of select="@xlink:title" />
        		</xsl:attribute>
        	</a>
        
                <img style="border:1px solid rgb(128,128,128);background-color:white;width:90%;">
                    <xsl:attribute name="src">
                        <xsl:value-of select="@xlink:href" />
                    </xsl:attribute>
                </img>
            <!-- Page name -->
            <div style="text-align:center;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                <xsl:value-of select="@xlink:title" />
            </div>
        </div>
    </xsl:template>
    
    <xsl:template match="back">
    </xsl:template>

</xsl:stylesheet>