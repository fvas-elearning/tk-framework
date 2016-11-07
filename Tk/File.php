<?php
namespace Tk;

/**
 * Tools for dealing with filesystem data
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class File
{


    /**
     * Returns true if given $path is an absolute path.
     *
     * @param $pathname
     * @return bool true if path is absolute.
     */
    static public function isAbsolute($pathname)
    {
        return !empty($pathname) && ($pathname[0] === '/' || preg_match('/^[A-Z]:\\\\/i', $pathname) || substr($pathname, 0, 2) == '\\\\');
    }

    /**
     * Get the bytes from a string like 40M, 10T, 100K
     *
     * @param string $str
     * @return int
     */
    static public function string2Bytes($str)
    {
        $sUnit = substr($str, -1);
        $iSize = (int)substr($str, 0, -1);
        switch (strtoupper($sUnit)) {
            case 'Y' :
                $iSize *= 1024; // Yotta
            case 'Z' :
                $iSize *= 1024; // Zetta
            case 'E' :
                $iSize *= 1024; // Exa
            case 'P' :
                $iSize *= 1024; // Peta
            case 'T' :
                $iSize *= 1024; // Tera
            case 'G' :
                $iSize *= 1024; // Giga
            case 'M' :
                $iSize *= 1024; // Mega
            case 'K' :
                $iSize *= 1024; // kilo
        }
        return $iSize;
    }

    /**
     * Convert a value from bytes to a human readable value
     *
     * @param int $bytes
     * @return string
     * @author http://php-pdb.sourceforge.net/samples/viewSource.php?file=twister.php
     */
    static public function bytes2String($bytes, $round = 2)
    {
        $tags = array('b', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $index = 0;
        while ($bytes > 999 && isset($tags[$index + 1])) {
            $bytes /= 1024;
            $index++;
        }
        $rounder = 1;
        if ($bytes < 10) {
            $rounder *= 10;
        }
        if ($bytes < 100) {
            $rounder *= 10;
        }
        $bytes *= $rounder;
        settype($bytes, 'integer');
        $bytes /= $rounder;
        if ($round > 0) {
            $bytes = round($bytes, $round);
            return  sprintf('%.'.$round.'f %s', $bytes, $tags[$index]);
        } else {
            return  sprintf('%s %s', $bytes, $tags[$index]);
        }
    }

    /**
     * The trouble is the sum of the byte sizes of the files in your directories
     * is not equal to the amount of disk space consumed, as andudi points out.
     * A 1-byte file occupies 4096 bytes of disk space if the block size is 4096.
     * Couldn't understand why andudi did $s["blksize"]*$s["blocks"]/8.
     * Could only be because $s["blocks"] counts the number of 512-byte disk
     * blocks not the number of $s["blksize"] blocks, so it may as well
     * just be $s["blocks"]*512. Furthermore none of the dirspace suggestions allow
     * for the fact that directories are also files and that they also consume disk
     * space. The following code dskspace addresses all these issues and can also
     * be used to return the disk space consumed by a single non-directory file.
     * It will return much larger numbers than you would have been seeing with
     * any of the other suggestions but I think they are much more realistic
     *
     * @param string $path
     * @return int
     */
    static public function diskSpace($path)
    {
        if (is_dir($path)) {
            $s = stat($path);
        }
        //$space = $s["blocks"] * 512;  // Does not work value $s["blocks"] = -1 allways
        if (!isset($s['size'])) {
            return 0;
        }
        $space = $s["size"];
        if (is_dir($path) && is_readable($path)) {
            $dh = opendir($path);
            while (($file = readdir($dh)) !== false) {
                if ($file != "." and $file != "..") {
                    $space += self::diskSpace($path . "/" . $file);
                }
            }
            closedir($dh);
        }
        return $space;
    }

    /**
     * Returns file extension for this pathname.
     *
     * A the last period ('.') in the pathname is used to delimit the file
     * extension. If the pathname does not have a file extension an empty string is returned.
     * EG: 'mp3', 'php', ...
     *
     * @param $path
     * @return string
     */
    static public function getExtension($path)
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * This function returns the maxumim download size allowed in bytes
     * To Change this modify the php.ini file or use:
     * <code>
     *   ini_set('post_max_size');
     *   ini_set('upload_max_filesize')
     * </code>
     *
     * @return int
     */
    static public function getMaxUploadSize()
    {
        $maxPost = self::string2Bytes(ini_get('post_max_size'));
        $maxUpload = self::string2Bytes(ini_get('upload_max_filesize'));
        if ($maxPost < $maxUpload) {
            return $maxPost;
        }
        return $maxUpload;
    }

    /**
     * Recursively delete all files and directories from the given path
     *
     * @param string $path
     * @return bool
     */
    static public function rmdir($path)
    {
        if (is_file($path)) {
            if (is_writable($path)) {
                if (@unlink($path)) {
                    return true;
                }
            }
            return false;
        }
        if (is_dir($path)) {
            if (is_writeable($path)) {
                foreach (new \DirectoryIterator($path) as $_res) {
                    if ($_res->isDot()) {
                        unset($_res);
                        continue;
                    }
                    if ($_res->isFile()) {
                        self::rmdir($_res->getPathName());
                    } elseif ($_res->isDir()) {
                        self::rmdir($_res->getRealPath());
                    }
                    unset($_res);
                }
                if (@rmdir($path)) {
                    return true;
                }
            }
            return false;
        }
    }
    
    /**
     * Get the mime type of a file based on its extension
     *
     * @param string $filename
     * @return string
     * @package Tk
     */
    static public function getMimeType($filename)
    {
        $mime_types = array('txt' => 'text/plain', 'htm' => 'text/html', 'html' => 'text/html', 'php' => 'text/html', 'css' => 'text/css', 'js' => 'application/javascript', 'json' => 'application/json', 'xml' => 'application/xml', 'swf' => 'application/x-shockwave-flash', 'flv' => 'video/x-flv',

            // images
            'png' => 'image/png', 'jpe' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'jpg' => 'image/jpeg', 'gif' => 'image/gif', 'bmp' => 'image/bmp', 'ico' => 'image/vnd.microsoft.icon', 'tiff' => 'image/tiff', 'tif' => 'image/tiff', 'svg' => 'image/svg+xml', 'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip', 'rar' => 'application/x-rar-compressed', 'exe' => 'application/x-msdownload', 'msi' => 'application/x-msdownload', 'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg', 'qt' => 'video/quicktime', 'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf', 'psd' => 'image/vnd.adobe.photoshop', 'ai' => 'application/postscript', 'eps' => 'application/postscript', 'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword', 'rtf' => 'application/rtf', 'xls' => 'application/vnd.ms-excel', 'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text', 'ods' => 'application/vnd.oasis.opendocument.spreadsheet');
        $extArr = explode('.', $filename);
        $ext = strtolower(array_pop($extArr));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        } elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        } else {
            return 'application/octet-stream';
        }
    }

}