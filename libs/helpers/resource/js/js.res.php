<?php
/**
 * MaiaFW - Copyright (c) Marcus Maia (http://marcusmaia.com.br)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author     Marcus Maia (contato@marcusmaia.com.br)
 * @copyright  Copyright (c) Marcus Maia (http://marcusmaia.com.br)
 * @link       http://maiafw.marcusmaia.com.br MaiaFW
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 */

$jsContents = str_replace( '\\', '\\\\', $jsContents );
$jsContents = str_replace( '\'', '\\\'', $jsContents );
echo <<<EOF
<?php
\$lastModified = filemtime(__FILE__);
\$etagFile = md5_file(__FILE__);
\$ifModifiedSince = ( isset( \$_SERVER['HTTP_IF_MODIFIED_SINCE'] ) ? \$_SERVER['HTTP_IF_MODIFIED_SINCE'] : false );

\$etagHeader = ( isset( \$_SERVER['HTTP_IF_NONE_MATCH'] ) ? trim( \$_SERVER['HTTP_IF_NONE_MATCH'] ) : false );

\$expireTime = 31536000;

header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', \$lastModified ) . ' GMT' );
header( 'Etag: ' . \$etagFile );
header( 'Cache-Control: public' );
header( 'Content-type: text/javascript;' );
header('Expires: '.gmdate('D, d M Y H:i:s', time() + \$expireTime).' GMT');

//check if page has changed. If not, send 304 and exit
if ( \$ifModifiedSince == \$lastModified || \$etagHeader == \$etagFile ) {
       header( 'HTTP/1.1 304 Not Modified' );
       exit ();
} else {
	header( 'HTTP/1.1 200 Ok' );
}

echo '
$jsContents;

';

EOF;
