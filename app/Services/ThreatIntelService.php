<?php

namespace App\Services;

class ThreatIntelService
{
    /**
     * Known Threat Actors & IP Geolocation Mapping
     */
    protected static array $knownIps = [
        '185.220.101.45' => [
            'country' => 'Netherlands',
            'country_code' => 'NL',
            'flag' => '🇳🇱',
            'asn' => 'AS60729 (Zwiebelfreunde e.V.)',
            'threat_tag' => 'TOR_EXIT_NODE',
            'risk_score' => 98,
        ],
        '45.154.255.89' => [
            'country' => 'Russia',
            'country_code' => 'RU',
            'flag' => '🇷🇺',
            'asn' => 'AS208043 (HostRoyale Ltd)',
            'threat_tag' => 'BULLETPROOF_HOST',
            'risk_score' => 94,
        ],
        '194.26.29.112' => [
            'country' => 'Seychelles',
            'country_code' => 'SC',
            'flag' => '🇸🇨',
            'asn' => 'AS44050 (Chang Way Tech)',
            'threat_tag' => 'BOTNET_SCANNER',
            'risk_score' => 89,
        ],
        '91.240.118.17' => [
            'country' => 'Bulgaria',
            'country_code' => 'BG',
            'flag' => '🇧🇬',
            'asn' => 'AS49367 (S3 Global Ltd)',
            'threat_tag' => 'EXPLOIT_ORIGIN',
            'risk_score' => 85,
        ],
        '198.51.100.84' => [
            'country' => 'United States',
            'country_code' => 'US',
            'flag' => '🇺🇸',
            'asn' => 'AS15169 (Google Cloud)',
            'threat_tag' => 'SUSPICIOUS_PROXY',
            'risk_score' => 76,
        ],
        '103.145.13.22' => [
            'country' => 'Indonesia',
            'country_code' => 'ID',
            'flag' => '🇮🇩',
            'asn' => 'AS136052 (PT Telkom Akses)',
            'threat_tag' => 'REGIONAL_VPN',
            'risk_score' => 25,
        ],
        '203.0.113.88' => [
            'country' => 'Germany',
            'country_code' => 'DE',
            'flag' => '🇩🇪',
            'asn' => 'AS24940 (Hetzner Online)',
            'threat_tag' => 'HOSTILE_SCANNER',
            'risk_score' => 88,
        ],
    ];

    /**
     * Lookup Threat Intelligence and Geolocation for an IP address.
     */
    public static function lookup(?string $ip): array
    {
        if (empty($ip)) {
            return [
                'country' => 'Unknown',
                'country_code' => 'XX',
                'flag' => '🌐',
                'asn' => 'Unclassified Endpoint',
                'threat_tag' => 'UNKNOWN',
                'risk_score' => 0,
            ];
        }

        // 1. Check if Private / Local Subnet
        if (self::isPrivateIp($ip)) {
            return [
                'country' => 'Internal LAN',
                'country_code' => 'CORP',
                'flag' => '🏢',
                'asn' => 'Corporate Intranet Node',
                'threat_tag' => 'INTERNAL_HOST',
                'risk_score' => 5,
            ];
        }

        // 2. Check Known Threat Intel Repository
        if (isset(self::$knownIps[$ip])) {
            return self::$knownIps[$ip];
        }

        // 3. Deterministic Fallback based on IP hash for realistic simulation
        $hash = crc32($ip);
        $countries = [
            ['country' => 'United States', 'code' => 'US', 'flag' => '🇺🇸', 'asn' => 'AS16509 (Amazon AWS)', 'tag' => 'CLOUD_HOST', 'score' => 45],
            ['country' => 'China', 'code' => 'CN', 'flag' => '🇨🇳', 'asn' => 'AS4134 (Chinanet)', 'tag' => 'PORT_SCANNER', 'score' => 82],
            ['country' => 'Germany', 'code' => 'DE', 'flag' => '🇩🇪', 'asn' => 'AS24940 (Hetzner)', 'tag' => 'HOSTING_GATEWAY', 'score' => 50],
            ['country' => 'Russia', 'code' => 'RU', 'flag' => '🇷🇺', 'asn' => 'AS12389 (Rostelecom)', 'tag' => 'ANOMALOUS_SRC', 'score' => 86],
            ['country' => 'Singapore', 'code' => 'SG', 'flag' => '🇸🇬', 'asn' => 'AS45102 (Alibaba Cloud)', 'tag' => 'CLOUD_PROXY', 'score' => 60],
            ['country' => 'Indonesia', 'code' => 'ID', 'flag' => '🇮🇩', 'asn' => 'AS7713 (Telkom Indonesia)', 'tag' => 'REMOTE_CLIENT', 'score' => 20],
        ];

        $choice = $countries[abs($hash) % count($countries)];

        return [
            'country' => $choice['country'],
            'country_code' => $choice['code'],
            'flag' => $choice['flag'],
            'asn' => $choice['asn'],
            'threat_tag' => $choice['tag'],
            'risk_score' => $choice['score'],
        ];
    }

    protected static function isPrivateIp(string $ip): bool
    {
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false;
    }
}
