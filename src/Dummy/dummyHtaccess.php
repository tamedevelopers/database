<IfModule mod_rewrite.c>
    # Start Engine
    RewriteEngine On
    
    # Define additional rules below
    # 
    
    # Cache control headers
    <IfModule mod_headers.c>
        <filesMatch ".(js|css|xml|gz|html|php)$">
            Header append Vary: Accept-Encoding
        </filesMatch>
        <filesMatch "\.(ico|flv|gif|swf|eot|woff|otf|ttf|svg)$">
            Header set Cache-Control "max-age=31536000, public"
        </filesMatch>
        <filesMatch "\.(jpg|jpeg|png)$">
            Header set Cache-Control "max-age=31536000, public"
        </filesMatch>
        <filesMatch "\.(css)$">
            Header set Cache-Control "max-age=2592000, public"
        </filesMatch>
        <filesMatch "\.(js)$">
            Header set Cache-Control "max-age=2592000, public"
        </filesMatch>
        <filesMatch "\.(x?html?)$">
            Header set Cache-Control "public, must-revalidate"
        </filesMatch>
        <filesMatch "\.(x?php)$">
            Header set Cache-Control "private, must-revalidate"
        </filesMatch>
    </IfModule>
    
    # Mod security
    <IfModule mod_security.c>
        SecFilterScanPOST Off
    </IfModule>
    
    # Mod Speling
    <IfModule mod_speling.c>
        CheckCaseOnly On
        CheckSpelling On
    </IfModule>
</IfModule>

<IfModule mod_expires.c>
    # Set expiration for different file types
    ExpiresActive On
    ExpiresByType text/html "access plus 600 seconds"
    ExpiresByType application/xhtml+xml "access plus 600 seconds"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/x-icon "access plus 1 year"
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
</IfModule>

<IfModule mod_gzip.c>
    # Enable gzip compression for resources
    mod_gzip_on Yes
    mod_gzip_dechunk Yes
    mod_gzip_item_include file .(html?|txt|css|js|php|pl)$
    mod_gzip_item_include handler ^application/x-httpd-php
    mod_gzip_item_include mime ^application/javascript$
    mod_gzip_item_include mime ^application/x-javascript$
    mod_gzip_item_include mime ^text/.*
    mod_gzip_item_exclude mime ^image/.*
    mod_gzip_item_exclude rspheader ^Content-Encoding:.*gzip.*
</IfModule>

<IfModule pagespeed_module>
    # Page Speed
    ModPagespeed on
    ModPagespeedEnableFilters rewrite_css,combine_css
    ModPagespeedEnableFilters recompress_images
    ModPagespeedEnableFilters convert_png_to_jpeg,convert_jpeg_to_webp
    ModPagespeedEnableFilters collapse_whitespace,remove_comments
</IfModule>
