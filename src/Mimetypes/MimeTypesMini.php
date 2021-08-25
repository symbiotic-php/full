<?php


namespace Dissonance\Mimetypes;
/**
 * abbreviating standard names
 */
const A = 'application/';
const I = 'image/';
const T = 'text/';

/**
 * Для отдачи файлов ресурсов нет необходимости описывать все 2000 расширений
 *
 */
class MimeTypesMini
{

    protected static $mime_types = [

        'txt' => T . 'plain',
        'htm' => T . 'html',
        'html' => T . 'html',
        'php' => T . 'html',
        'css' => T . 'css',
        'js' => A . 'javascript',
        'json' => A . 'json',
        'jsonld' => A . 'ld+json',
        'xml' => A . 'xml',
        'swf' => A . 'x-shockwave-flash',
        'flv' => 'video/x-flv',
        'csv' => T . 'csv',

        // images
        'png' => I . 'png',
        'jpe' => I . 'jpeg',
        'jpeg' => I . 'jpeg',
        'jpg' => I . 'jpeg',
        'gif' => I . 'gif',
        'bmp' => I . 'bmp',
        'ico' => I . 'vnd.microsoft.icon',
        'tiff' => I . 'tiff',
        'tif' => I . 'tiff',
        'svg' => I . 'svg+xml',
        'svgz' => I . 'svg+xml',

        // archives
        'zip' => A . 'zip',
        'rar' => A . 'x-rar-compressed',
        'exe' => A . 'x-msdownload',
        'msi' => A . 'x-msdownload',
        'cab' => A . 'vnd.ms-cab-compressed',
        'tar.gz' => A . 'x-compressed-tar',

        // audio/video
        'mp3' => 'audio/mpeg',
        'qt' => 'video/quicktime',
        'mov' => 'video/quicktime',
        'mp4' => 'video/mp4',

        // adobe
        'pdf' => A . 'pdf',
        'psd' => I . 'vnd.adobe.photoshop',
        'ai' => A . 'postscript',
        'eps' => A . 'postscript',
        'ps' => A . 'postscript',

        // ms office
        'doc' => A . 'msword',
        'rtf' => A . 'rtf',
        'xls' => A . 'vnd.ms-excel',
        'ppt' => A . 'vnd.ms-powerpoint',

        // open office
        'odt' => A . 'vnd.oasis.opendocument.text',
        'ods' => A . 'vnd.oasis.opendocument.spreadsheet',
    ];

    public function getExtensionsPattern(array $extensions)
    {
        $pattern = '';
        foreach ($extensions as $v) {
            $pattern .= preg_quote($v, '/') . '|';
        }

        return trim($pattern, '|');
    }

    /**
     * @param string $path
     * @param array|null $allowed_extensions
     */
    public function findExtension(string $path, array $allowed_extensions = null)
    {
        if (!$allowed_extensions) {
            $allowed_extensions = array_keys(static::$mime_types);
        }
        usort($allowed_extensions, function ($a, $b) {
            return substr_count($a, '.') <=> substr_count($b, '.');
        });

        return preg_match('/(' . $this->getExtensionsPattern($allowed_extensions) . ')$/i', $path, $m) ? $m[1] : false;
    }

    /**
     * @param string $path
     * @return string | null если найден
     */
    public function getMimeType(string $path): ?string
    {
        $ext = $this->findExtension($path);
        return ($ext && isset(static::$mime_types[$ext])) ? static::$mime_types[$ext] : null;
    }


}
