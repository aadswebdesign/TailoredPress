<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-4-2022
 * Time: 07:07
 */
declare(strict_types=1);
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Psr7;
use TP_Core\Libs\PHP\Libs\HTTP_Message\UriInterface;
if(ABSPATH){
    final class UriResolver{
        public static function removeDotSegments(string $path): string{
            static $_segment;
            if ($path === '' || $path === '/') return $path;
            $results = [];
            $segments = explode('/', $path);
            foreach ($segments as $segment) {
                $_segment = $segment;
                if ($_segment === '..') array_pop($results);
                elseif ($_segment !== '.')$results[] = $_segment;
            }
            $newPath = implode('/', $results);
            if ($path[0] === '/' && (!isset($newPath[0]) || $newPath[0] !== '/'))
                $newPath = '/' . $newPath;
            elseif ($newPath !== '' && ($_segment === '.' || $_segment === '..'))
                $newPath .= '/';
            return $newPath;
        }
        public static function resolve(UriInterface $base, UriInterface $rel): UriInterface{
            if ((string) $rel === '')return $base;
            if ($rel->getScheme() !== '')
                return $rel->withPath(self::removeDotSegments($rel->getPath()));
            if ($rel->getAuthority() !== '') {
                $targetAuthority = $rel->getAuthority();
                $targetPath = self::removeDotSegments($rel->getPath());
                $targetQuery = $rel->getQuery();
            } else {
                $targetAuthority = $base->getAuthority();
                if ($rel->getPath() === '') {
                    $targetPath = $base->getPath();
                    $targetQuery = $rel->getQuery() !== '' ? $rel->getQuery() : $base->getQuery();
                } else {
                    if ($rel->getPath()[0] === '/')
                        $targetPath = $rel->getPath();
                    else {
                        /** @noinspection NestedPositiveIfStatementsInspection */
                        if ($targetAuthority !== '' && $base->getPath() === '')
                            $targetPath = '/' . $rel->getPath();
                        else {
                            $lastSlashPos = strrpos($base->getPath(), '/');
                            if ($lastSlashPos === false)
                                $targetPath = $rel->getPath();
                            else $targetPath = substr($base->getPath(), 0, $lastSlashPos + 1) . $rel->getPath();
                        }
                    }
                    $targetPath = self::removeDotSegments($targetPath);
                    $targetQuery = $rel->getQuery();
                }
            }
            return new Uri(Uri::composeComponents(
                $base->getScheme(),
                $targetAuthority,
                $targetPath,
                $targetQuery,
                $rel->getFragment()
            ));
        }
        public static function relativize(UriInterface $base, UriInterface $target): UriInterface {
            if ($target->getScheme() !== '' &&
                ($base->getScheme() !== $target->getScheme() || ($target->getAuthority() === '' && $base->getAuthority() !== ''))
            ) return $target;
            if (Uri::isRelativePathReference($target))
                return $target;
            if ($target->getAuthority() !== '' && $base->getAuthority() !== $target->getAuthority())
                return $target->withScheme('');
            /** @noinspection PhpUndefinedMethodInspection */
            $emptyPathUri = $target->withScheme('')->withPath('')->withUserInfo('')->withPort(null)->withHost('');
            if ($base->getPath() !== $target->getPath())
                /** @noinspection PhpUndefinedMethodInspection */
                return $emptyPathUri->withPath(self::getRelativePath($base, $target));
            if ($base->getQuery() === $target->getQuery())
                /** @noinspection PhpUndefinedMethodInspection */
                return $emptyPathUri->withQuery('');
            if ($target->getQuery() === '') {
                $segments = explode('/', $target->getPath());
                /** @var string $lastSegment */
                $lastSegment = end($segments);
                /** @noinspection PhpUndefinedMethodInspection */
                return $emptyPathUri->withPath($lastSegment === '' ? './' : $lastSegment);
            }
            return $emptyPathUri;
        }
        private static function getRelativePath(UriInterface $base, UriInterface $target): string{
            $sourceSegments = explode('/', $base->getPath());
            $targetSegments = explode('/', $target->getPath());
            array_pop($sourceSegments);
            $targetLastSegment = array_pop($targetSegments);
            foreach ($sourceSegments as $i => $segment) {
                if (isset($targetSegments[$i]) && $segment === $targetSegments[$i])
                    unset($sourceSegments[$i], $targetSegments[$i]);
                else break;
            }
            $targetSegments[] = $targetLastSegment;
            $relativePath = str_repeat('../', count($sourceSegments)) . implode('/', $targetSegments);
            if ('' === $relativePath || false !== strpos(explode('/', $relativePath, 2)[0], ':'))
                $relativePath = "./$relativePath";
            elseif ('/' === $relativePath[0]) {
                if ($base->getAuthority() !== '' && $base->getPath() === '')
                    $relativePath = ".$relativePath";
                else $relativePath = "./$relativePath";
            }
            return $relativePath;
        }
        private function __construct(){}// cannot be instantiated
    }
}else die;