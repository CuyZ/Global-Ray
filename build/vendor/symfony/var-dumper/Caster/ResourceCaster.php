<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RayGlobalScoped\Symfony\Component\VarDumper\Caster;

use RayGlobalScoped\Symfony\Component\VarDumper\Cloner\Stub;
/**
 * Casts common resource types to array representation.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 *
 * @final
 */
class ResourceCaster
{
    /**
     * @param \CurlHandle|resource $h
     *
     * @return array
     */
    public static function castCurl($h, array $a, \RayGlobalScoped\Symfony\Component\VarDumper\Cloner\Stub $stub, bool $isNested)
    {
        return \curl_getinfo($h);
    }
    public static function castDba($dba, array $a, \RayGlobalScoped\Symfony\Component\VarDumper\Cloner\Stub $stub, bool $isNested)
    {
        $list = \dba_list();
        $a['file'] = $list[(int) $dba];
        return $a;
    }
    public static function castProcess($process, array $a, \RayGlobalScoped\Symfony\Component\VarDumper\Cloner\Stub $stub, bool $isNested)
    {
        return \proc_get_status($process);
    }
    public static function castStream($stream, array $a, \RayGlobalScoped\Symfony\Component\VarDumper\Cloner\Stub $stub, bool $isNested)
    {
        $a = \stream_get_meta_data($stream) + static::castStreamContext($stream, $a, $stub, $isNested);
        if (isset($a['uri'])) {
            $a['uri'] = new \RayGlobalScoped\Symfony\Component\VarDumper\Caster\LinkStub($a['uri']);
        }
        return $a;
    }
    public static function castStreamContext($stream, array $a, \RayGlobalScoped\Symfony\Component\VarDumper\Cloner\Stub $stub, bool $isNested)
    {
        return @\stream_context_get_params($stream) ?: $a;
    }
    public static function castGd($gd, array $a, \RayGlobalScoped\Symfony\Component\VarDumper\Cloner\Stub $stub, $isNested)
    {
        $a['size'] = \imagesx($gd) . 'x' . \imagesy($gd);
        $a['trueColor'] = \imageistruecolor($gd);
        return $a;
    }
    public static function castMysqlLink($h, array $a, \RayGlobalScoped\Symfony\Component\VarDumper\Cloner\Stub $stub, bool $isNested)
    {
        $a['host'] = \mysql_get_host_info($h);
        $a['protocol'] = \mysql_get_proto_info($h);
        $a['server'] = \mysql_get_server_info($h);
        return $a;
    }
    public static function castOpensslX509($h, array $a, \RayGlobalScoped\Symfony\Component\VarDumper\Cloner\Stub $stub, bool $isNested)
    {
        $stub->cut = -1;
        $info = \openssl_x509_parse($h, \false);
        $pin = \openssl_pkey_get_public($h);
        $pin = \openssl_pkey_get_details($pin)['key'];
        $pin = \array_slice(\explode("\n", $pin), 1, -2);
        $pin = \base64_decode(\implode('', $pin));
        $pin = \base64_encode(\hash('sha256', $pin, \true));
        $a += ['subject' => new \RayGlobalScoped\Symfony\Component\VarDumper\Caster\EnumStub(\array_intersect_key($info['subject'], ['organizationName' => \true, 'commonName' => \true])), 'issuer' => new \RayGlobalScoped\Symfony\Component\VarDumper\Caster\EnumStub(\array_intersect_key($info['issuer'], ['organizationName' => \true, 'commonName' => \true])), 'expiry' => new \RayGlobalScoped\Symfony\Component\VarDumper\Caster\ConstStub(\date(\DateTime::ISO8601, $info['validTo_time_t']), $info['validTo_time_t']), 'fingerprint' => new \RayGlobalScoped\Symfony\Component\VarDumper\Caster\EnumStub(['md5' => new \RayGlobalScoped\Symfony\Component\VarDumper\Caster\ConstStub(\wordwrap(\strtoupper(\openssl_x509_fingerprint($h, 'md5')), 2, ':', \true)), 'sha1' => new \RayGlobalScoped\Symfony\Component\VarDumper\Caster\ConstStub(\wordwrap(\strtoupper(\openssl_x509_fingerprint($h, 'sha1')), 2, ':', \true)), 'sha256' => new \RayGlobalScoped\Symfony\Component\VarDumper\Caster\ConstStub(\wordwrap(\strtoupper(\openssl_x509_fingerprint($h, 'sha256')), 2, ':', \true)), 'pin-sha256' => new \RayGlobalScoped\Symfony\Component\VarDumper\Caster\ConstStub($pin)])];
        return $a;
    }
}
