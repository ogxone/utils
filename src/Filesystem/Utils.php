<?php
/**
 * Created by PhpStorm.
 * User: Оля
 * Date: 24.04.2015
 * Time: 13:21
 */

namespace Ogxone\Utils\Filesystem;


/**
 * Class Utils
 * @package Ogxone\Utils\Filesystem
 */
class Utils
{
    /**
     * @param $dir
     * @param array $exceptions
     * @param bool $rmSource
     */
    public static function recursiveRmDir($dir, Array $exceptions = [], $rmSource = false)
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $dir,
                \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($iterator as $filename => $fileInfo) {
            if (in_array($filename, $exceptions)) {
                continue;
            }
            if ($fileInfo->isDir()) {
                self::rmdir($filename);
            } else {
                self::unlink($filename);
            }
        }
        if ($rmSource) {
            self::rmdir($dir);
        }
    }

    /**
     * @param $dir
     * @param $toDir
     * @param $version
     * @throws \UnexpectedValueException
     */
    public static function recursiveCopyDir($dir, $toDir, $version = false)
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $dir,
                \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST);
        /**@var $fileInfo \SplFileInfo */
        foreach ($iterator as $filename => $fileInfo) {
            if (!$fileInfo->isDir()) {
                $toPath = $toDir . str_replace($dir, '', $fileInfo->getPath());
                if (!is_dir($toPath)) {
                    mkdir($toPath, 0777, true);
                }
                self::copyFile($fileInfo->getRealPath(), $toPath . '/' . $fileInfo->getFilename(), $version);
            }
        }
    }

    /**
     * @param $dir
     * @param array $exceptions
     */
    public static function clearDir($dir, Array $exceptions = [])
    {
        if (!is_dir($dir)) {
            throw new \UnexpectedValueException(sprintf(
                'File %s is not a directory', $dir
            ));
        }
        foreach (new \DirectoryIterator($dir) as $fname => $finfo) {
            if ($finfo->isDot()) {
                continue;
            }
            /**@var $finfo \SplFileInfo */
            if ($finfo->isDot() && in_array($fname, $exceptions)) {
                continue;
            }
            if ($finfo->isDir()) {
                self::recursiveRmDir($finfo->getPathname(), $exceptions);
            } else {
                self::unlink($finfo->getPathname());
            }
        }
    }

    /**
     * @param $path
     * @throws \UnexpectedValueException
     */
    public static function unlink($path)
    {
        if (file_exists($path)) {
            if (is_writable($path)) {
                if (!@unlink($path)) {
                    throw new \BadMethodCallException(sprintf(
                        'Failed to delete file `%s`. Check your permission',
                        $path
                    ));
                }
            } else {
                throw new \UnexpectedValueException(sprintf(
                    'File %s is not writable. Please check your permissions', $path
                ));
            }
        } else {
            throw new \UnexpectedValueException(sprintf('File %s is not exists', $path));
        }
    }

    /**
     * @param $path
     * @param int $mode
     * @param \array[] ...$options
     * @throws \Exception
     */
    public static function mkdir($path, $mode = 0755, Array ...$options)
    {
        // resolve options
        $forceNew = $options['force_new'] ?? false;
        $recursive = $options['recursive'] ?? true;

        if (is_dir($path)) {
            if ($forceNew) {
                throw new \UnexpectedValueException(sprintf(
                    'Directory `%s` is already exists',
                    $path
                ));
            }
            // directory already exists
            return;
        }
        // validate that path is not a file
        if (is_file($path)) {
            throw new \UnexpectedValueException(sprintf(
                'Include file path `%s` supposed to be a folder, file given',
                $path
            ));
        }
        // try to create dir
        if (false === @mkdir($path, $mode, $recursive)) {
            throw new \Exception(sprintf(
                'Failed to create dir `%s`. Check your permissions',
                $path
            ));
        }
    }

    /**
     * @param $path
     * @throws \UnexpectedValueException
     */
    public static function rmdir($path)
    {
        if (file_exists($path)) {
            if (is_writable($path) && is_dir($path)) {
                rmdir($path);
            } else {
                throw new \UnexpectedValueException(sprintf(
                    'Directory %s is not writable or not a dir', $path
                ));
            }
        } else {
            throw new \UnexpectedValueException(sprintf('File %s is not exists', $path));
        }
    }

    /**
     * @param $dir
     * @param $fileName
     * @param string $data
     * @param bool $gzip
     * @param int $dirMode
     * @param int $fileMode
     * @throws \Exception
     */
    public static function putFile($dir, $fileName, $data = '', $gzip = false, $dirMode = 0755, $fileMode = 0775)
    {
        if (file_exists($dir)) {
            if (!is_writable($dir)) {
                throw new \UnexpectedValueException(sprintf(
                    'Directory %s is not writable or not a dir', $dir
                ));
            }
        } else {
            if (false === @mkdir($dir, $dirMode, true)) {
                throw new \UnexpectedValueException(sprintf(
                    'Failed to create dir `%s` with permissions `%o`. Check your permissions',
                    $dir,
                    $dirMode
                ));
            }
        }

        if ($gzip) {
            if (($gz = gzopen($path = $dir . '/' . $fileName, 'wb9')) === false) {
                throw new \Exception(sprintf('Failed to write to the file %s. check your permissions', $path));
            }
            gzwrite($gz, $data);
            gzclose($gz);
        } else {
            if (@file_put_contents($path = $dir . '/' . $fileName, $data) === false) {
                throw new \Exception(sprintf('Failed to write to the file %s. check your permissions', $path));
            }

            if (false === @chmod($path, $fileMode)) {
                throw new \UnexpectedValueException(sprintf(
                    'Failed to change mode for path `%s` to `%o`. Check your permissions',
                    $path,
                    $fileMode
                ));
            };
        }
    }

    /**
     * @param $fromChunks
     * @param $toFile
     * @param $chunks
     * @param $partName
     * @param $unlink
     * @throws \Exception
     */
    public static function writeFileFromChunks($fromChunks, $toFile, $chunks = 1, $partName = 'part', $unlink = true)
    {
        $dir = dirname($toFile);
        if (file_exists($dir)) {
            if (!is_writable($dir)) {
                throw new \UnexpectedValueException(sprintf(
                    'Directory %s is not writable or not a dir', $dir
                ));
            }
        } else {
            mkdir($dir, 0777, true);
        }

        if (($fp = fopen($toFile, 'w')) !== false) {
            for ($i = 1; $i <= $chunks; $i++) {
                fwrite($fp, file_get_contents($fromChunks . $partName . $i));
                if ($unlink) {
                    @unlink($fromChunks . $partName . $i);
                }
            }
            fclose($fp);
        } else {
            throw new \Exception(sprintf('Failed to write to the file %s. check your permissions', $toFile));
        }
    }

    /**
     * @param $dir
     * @param $fileName
     * @param $code
     * @param $prefix
     * @throws \Exception
     */
    public static function saveAsCode($dir, $fileName, $code, $prefix = 'return')
    {
        if (file_exists($dir)) {
            if (!is_writable($dir)) {
                throw new \UnexpectedValueException(sprintf(
                    'Directory %s is not writable or not a dir', $dir
                ));
            }
        } else {
            mkdir($dir, 0777, true);
        }

        $code = var_export($code, true);
        $data = "<?php \n" . $prefix . ' ' . $code . ';';
        if (file_put_contents($path = $dir . '/' . $fileName, $data) === false) {
            throw new \Exception(sprintf('Failed to write to the file %s. check your permissions', $path));
        }
    }

    /**
     * @param $filePath
     * @return string
     * @throws \UnexpectedValueException
     */
    public static function getFile($filePath)
    {
        if (file_exists($filePath)) {
            if (!is_readable($filePath)) {
                throw new \UnexpectedValueException(sprintf(
                    'File %s is not readable', $filePath
                ));
            } else {
                return file_get_contents($filePath);
            }
        }
        throw new \UnexpectedValueException(sprintf('Failed to read from file %s', $filePath));
    }

    /**
     * @param $filePath
     * @return string
     * @throws \UnexpectedValueException
     */
    public static function returnAsCode($filePath)
    {
        if (file_exists($filePath)) {
            if (!is_readable($filePath)) {
                throw new \UnexpectedValueException(sprintf(
                    'File %s is not readable', $filePath
                ));
            } else {
                return include $filePath;
            }
        }
        throw new \UnexpectedValueException(sprintf('Failed to read from file %s', $filePath));
    }

    /**
     * @param $from
     * @param $to
     * @param $version
     * @throws \Exception
     */
    public static function copyFile($from, $to, $version = false)
    {
        if (file_exists($from)) {
            if (!is_writable(pathinfo($from)['dirname']) || !is_writable(pathinfo($to)['dirname'])) {
                throw new \UnexpectedValueException(sprintf(
                    'Directory %s or %s is not writable or not a dir', pathinfo($from)['dirname'], pathinfo($to)['dirname']
                ));
            }
        } else {
            throw new \UnexpectedValueException(sprintf(
                'File %s is not exists', $from
            ));
        }

        if ($version) {
            $pathinfo = pathinfo($to);
            $to = $pathinfo['dirname'] . '/' . $pathinfo['filename'] . '_v' . md5(file_get_contents($from)) . '.' . $pathinfo['extension'];
        }

        if (!copy($from, $to)) {
            throw new \RuntimeException(sprintf('Failed to write to the file %s. check your permissions', $to));
        }
    }

    /**
     * @param $from
     * @param $to
     * @param $version
     * @throws \Exception
     */
    public static function moveFile($from, $to, $version = false)
    {
        if (file_exists($from)) {
            if (!is_writable(pathinfo($from)['dirname']) || !is_writable(pathinfo($to)['dirname'])) {
                throw new \UnexpectedValueException(sprintf(
                    'Directory %s or %s is not writable or not a dir', pathinfo($from)['dirname'], pathinfo($to)['dirname']
                ));
            }
        } else {
            throw new \UnexpectedValueException(sprintf(
                'File %s is not exists', $from
            ));
        }

        if ($version) {
            $pathinfo = pathinfo($to);
            $to = $pathinfo['dirname'] . '/' . $pathinfo['filename'] . '_v' . md5(file_get_contents($from)) . '.' . $pathinfo['extension'];
        }

        if (!rename($from, $to)) {
            throw new \RuntimeException(sprintf('Failed to write to the file %s. check your permissions', $to));
        }
    }


    /**
     * @param $source
     * @param $dest
     * @param bool $allowOverwrite
     */
    public static function copyRecursive($source, $dest, $allowOverwrite = true)
    {
        if (!is_writable($source)) {
            throw new \RuntimeException(sprintf(
                'Source file %s is no writable . check your permissions', $source, $dest
            ));
        }
        if (file_exists($dest) && !$allowOverwrite) {
            throw new \RuntimeException(sprintf(
                'Dest file %s already exists', $source, $dest
            ));
        }
        if (is_dir($source)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );
            foreach ($iterator as $file) {
                if ($file->isDir()) {
                    if (!mkdir($dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName())) {
                        throw new \RuntimeException(sprintf(
                            'Failed to write from %s to the file %s. check your permissions', $source, $dest
                        ));
                    }
                } else {
                    if (!copy($file, $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName())) {
                        throw new \RuntimeException(sprintf(
                            'Failed to write from %s to the file %s. check your permissions', $source, $dest
                        ));
                    }
                }
            }
        } else {
            if (!copy($source, $dest)) {
                throw new \RuntimeException(sprintf(
                    'Failed to write from %s to the file %s. check your permissions', $source, $dest
                ));
            }

        }
    }

    /**
     * @param $dir
     * @param $needle
     * @param array $files
     * @return array
     */
    public static function searchDir($dir, $needle, Array &$files)
    {
        $i = new \FilesystemIterator($dir);
        foreach ($i as $file) {
            if ($file->getBasename() == $needle) {
                $files[] = $file->getPathName();
            } else if ($file->isDir()) {
                self::searchDir($file->getPathName(), $needle, $files);
            }
        }
        return $files;
    }

    /**
     * Creates a new directory.
     *
     * This method is similar to the PHP `mkdir()` function except that
     * it uses `chmod()` to set the permission of the created directory
     * in order to avoid the impact of the `umask` setting.
     *
     * @param string $path path of the directory to be created.
     * @param integer $mode the permission to be set for the created directory.
     * @param boolean $recursive whether to create parent directories if they do not exist.
     * @return bool whether the directory is created successfully
     * @throws \Exception
     */
    public static function createDirectory($path, $mode = 0775, $recursive = true)
    {
        if (is_dir($path)) {
            return true;
        }
        $parentDir = dirname($path);
        // recurse if parent dir does not exist and we are not at the root of the file system.
        if ($recursive && !is_dir($parentDir) && $parentDir !== $path) {
            static::createDirectory($parentDir, $mode, true);
        }
        try {
            if (!mkdir($path, $mode)) {
                return false;
            }
        } catch (\Exception $e) {
            if (!is_dir($path)) {// https://github.com/yiisoft/yii2/issues/9288
                throw new \Exception("Failed to create directory \"$path\": " . $e->getMessage(), $e->getCode(), $e);
            }
        }
        try {
            return chmod($path, $mode);
        } catch (\Exception $e) {
            throw new \Exception("Failed to change permissions for directory \"$path\": " . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param $entityId
     * @return string
     */
    public static function getPathById($entityId)
    {
        $k = 100000;
        $group = '';
        do {
            $gr = floor($entityId / $k) * $k;
            $group .= $gr . '/';
            $k /= 10;
        } while ($k > 99);

        $group .= $entityId . '/';

        return $group;
    }

} 