<IfModule mod_rewrite.c>
    
    # Negotiation
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    # Mod security
    <IfModule mod_security.c>
        SecFilterScanPOST Off
    </IfModule>

    # page speed
    <IfModule pagespeed_module>
        ModPagespeed on
        ModPagespeedEnableFilters rewrite_css,combine_css
        ModPagespeedEnableFilters recompress_images
        ModPagespeedEnableFilters convert_png_to_jpeg,convert_jpeg_to_webp
        ModPagespeedEnableFilters collapse_whitespace,remove_comments
    </IfModule>

    #Enable gzip compression for resources
    <ifModule mod_gzip.c>
        mod_gzip_on Yes
        mod_gzip_dechunk Yes
        mod_gzip_item_include file .(html?|txt|css|js|php|pl)$
        mod_gzip_item_include handler ^application/x-httpd-php
        mod_gzip_item_include mime ^application/javascript$
        mod_gzip_item_include mime ^application/x-javascript$
        mod_gzip_item_include mime ^text/.*
        mod_gzip_item_exclude mime ^image/.*
        mod_gzip_item_exclude rspheader ^Content-Encoding:.*gzip.*
    </ifModule>

    # Compress resources
    <IfModule mod_deflate.c>
        SetOutputFilter DEFLATE
        AddOutputFilterByType DEFLATE text/html
        AddOutputFilterByType DEFLATE application/xhtml+xml
        AddOutputFilterByType DEFLATE text/css
        AddOutputFilterByType DEFLATE text/javascript
        AddOutputFilterByType DEFLATE text/xml
        AddOutputFilterByType DEFLATE text/plain
        AddOutputFilterByType DEFLATE image/x-icon
        AddOutputFilterByType DEFLATE image/svg+xml
        AddOutputFilterByType DEFLATE application/rss+xml
        AddOutputFilterByType DEFLATE application/javascript
        AddOutputFilterByType DEFLATE application/x-javascript
        AddOutputFilterByType DEFLATE application/xml
        AddOutputFilterByType DEFLATE application/x-font
        AddOutputFilterByType DEFLATE application/x-font-truetype
        AddOutputFilterByType DEFLATE application/x-font-ttf
        AddOutputFilterByType DEFLATE application/x-font-otf
        AddOutputFilterByType DEFLATE application/x-font-opentype
        AddOutputFilterByType DEFLATE application/vnd.ms-fontobject
        AddOutputFilterByType DEFLATE font/ttf
        AddOutputFilterByType DEFLATE font/otf
        AddOutputFilterByType DEFLATE font/opentype
        BrowserMatch ^Mozilla/4 gzip-only-text/html
        BrowserMatch ^Mozilla/4\.0[678] no-gzip
        BrowserMatch \bMSIE !no-gzip !gzip-only-text/html
        Header append Vary User-Agent env=!dont-vary
    </IfModule>

    #access plus 1 month | 1 year | 5 seconds | 2 days
    <ifModule mod_expires.c>
        ExpiresActive On
        ExpiresByType text/html "access plus 600 seconds"
        ExpiresByType application/xhtml+xml "access plus 600 seconds"
        ExpiresByType image/gif "access 1 year"
        ExpiresByType image/png "access 1 year"
        ExpiresByType image/jpg "access 1 year"
        ExpiresByType image/jpeg "access 1 year"
        ExpiresByType image/x-icon "access 1 year"
        ExpiresByType image/svg+xml "access plus 1 year"
        ExpiresByType audio/ogg "access plus 1 year"
        ExpiresByType video/mp4 "access plus 1 year"
        ExpiresByType video/ogg "access plus 1 year"
        ExpiresByType video/webm "access plus 1 year"
        ExpiresByType application/atom+xml "access plus 1 hour"
        ExpiresByType application/rss+xml "access plus 1 hour"
        ExpiresByType application/pdf "access plus 1 month"
        ExpiresByType text/css "access 1 month"
        ExpiresByType application/javascript "access 1 month"
        ExpiresByType text/x-javascript "access 1 month"
        ExpiresByType text/x-component "access plus 1 month"
        ExpiresByType application/x-shockwave-flash "access 1 month"
        ExpiresByType font/opentype "access plus 1 month"
        ExpiresByType application/vnd.ms-fontobject "access plus 1 month"
        ExpiresByType application/x-font-ttf "access plus 1 month"
        ExpiresByType application/font-woff "access plus 1 month"
        ExpiresByType application/font-woff2 "access plus 1 month"
        ExpiresDefault "access plus 30 days"
    </ifModule>

    # BEGIN Cache-Control Headers
    # public | private | must-revalidate
    # Header set Cache-Control "private, must-revalidate"
    # Header set Cache-Control "max-age=2592000, private"
    <IfModule mod_headers.c>
        Header set Connection keep-alive
        <filesMatch ".(js|css|xml|gz|html|php)$">
            Header append Vary: Accept-Encoding
        </filesMatch>
        <filesmatch "\.(ico|flv|gif|swf|eot|woff|otf|ttf|svg)$">
            Header set Cache-Control "max-age=31536000, public"
        </filesmatch>
        <filesmatch "\.(jpg|jpeg|png)$">
            Header set Cache-Control "max-age=31536000, public" 
        </filesmatch>
        <filesmatch "\.(css)$">
            Header set Cache-Control "max-age=2592000, private" 
        </filesmatch>
        <filesmatch "\.(js)$">
            Header set Cache-Control "max-age=2592000, private"
        </filesmatch>
        <filesMatch "\.(x?html?|php)$">
            Header set Cache-Control "max-age=2592000, public"
        </filesMatch>
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    
</IfModule>
