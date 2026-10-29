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
            'flag' => 'NL',
            'icon' => 'fa-solid fa-user-secret text-rose-400',
            'asn' => 'AS60729 (Zwiebelfreunde e.V.)',
            'threat_tag' => 'TOR_EXIT_NODE',
            'risk_score' => 98,
        ],
        '45.154.255.89' => [
            'country' => 'Russia',
            'country_code' => 'RU',
            'flag' => 'RU',
            'icon' => 'fa-solid fa-server text-rose-400',
            'asn' => 'AS208043 (HostRoyale Ltd)',
            'threat_tag' => 'BULLETPROOF_HOST',
            'risk_score' => 94,
        ],
        '194.26.29.112' => [
            'country' => 'Seychelles',
            'country_code' => 'SC',
            'flag' => 'SC',
            'icon' => 'fa-solid fa-robot text-orange-400',
            'asn' => 'AS44050 (Chang Way Tech)',
            'threat_tag' => 'BOTNET_SCANNER',
            'risk_score' => 89,
        ],
        '91.240.118.17' => [
            'country' => 'Bulgaria',
            'country_code' => 'BG',
            'flag' => 'BG',
            'icon' => 'fa-solid fa-bug text-rose-400',
            'asn' => 'AS49367 (S3 Global Ltd)',
            'threat_tag' => 'EXPLOIT_ORIGIN',
            'risk_score' => 85,
        ],
        '198.51.100.84' => [
            'country' => 'United States',
            'country_code' => 'US',
            'flag' => 'US',
            'icon' => 'fa-solid fa-network-wired text-amber-400',
            'asn' => 'AS15169 (Google Cloud)',
            'threat_tag' => 'SUSPICIOUS_PROXY',
            'risk_score' => 76,
        ],
        '103.145.13.22' => [
            'country' => 'Indonesia',
            'country_code' => 'ID',
            'flag' => 'ID',
            'icon' => 'fa-solid fa-shield-halved text-cyan-400',
            'asn' => 'AS136052 (PT Telkom Akses)',
            'threat_tag' => 'REGIONAL_VPN',
            'risk_score' => 25,
        ],
        '203.0.113.88' => [
            'country' => 'Germany',
            'country_code' => 'DE',
            'flag' => 'DE',
            'icon' => 'fa-solid fa-radar text-amber-400',
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
                'country' => __('Unknown'),
                'country_code' => 'XX',
                'flag' => 'XX',
                'icon' => 'fa-solid fa-globe text-zinc-500',
                'asn' => __('Unclassified Endpoint'),
                'threat_tag' => __('UNKNOWN'),
                'risk_score' => 0,
            ];
        }

        // 1. Check if Private / Local Subnet
        if (self::isPrivateIp($ip)) {
            return [
                'country' => __('Internal LAN'),
                'country_code' => 'CORP',
                'flag' => 'CORP',
                'icon' => 'fa-solid fa-building-shield text-emerald-400',
                'asn' => __('Corporate Intranet Node'),
                'threat_tag' => 'INTERNAL_HOST',
                'risk_score' => 5,
            ];
        }

        // 2. Check Known Threat Intel Repository
        if (isset(self::$knownIps[$ip])) {
            $data = self::$knownIps[$ip];
            $data['country'] = __($data['country']);
            return $data;
        }

        // 3. Deterministic Fallback based on IP hash for realistic simulation
        $hash = crc32($ip);
        $countries = [
            ['country' => 'United States', 'code' => 'US', 'icon' => 'fa-solid fa-cloud text-cyan-400', 'asn' => 'AS16509 (Amazon AWS)', 'tag' => 'CLOUD_HOST', 'score' => 45],
            ['country' => 'China', 'code' => 'CN', 'icon' => 'fa-solid fa-radar text-amber-400', 'asn' => 'AS4134 (Chinanet)', 'tag' => 'PORT_SCANNER', 'score' => 82],
            ['country' => 'Germany', 'code' => 'DE', 'icon' => 'fa-solid fa-server text-zinc-400', 'asn' => 'AS24940 (Hetzner)', 'tag' => 'HOSTING_GATEWAY', 'score' => 50],
            ['country' => 'Russia', 'code' => 'RU', 'icon' => 'fa-solid fa-shield-virus text-rose-400', 'asn' => 'AS12389 (Rostelecom)', 'tag' => 'ANOMALOUS_SRC', 'score' => 86],
            ['country' => 'Singapore', 'code' => 'SG', 'icon' => 'fa-solid fa-network-wired text-cyan-400', 'asn' => 'AS45102 (Alibaba Cloud)', 'tag' => 'CLOUD_PROXY', 'score' => 60],
            ['country' => 'Indonesia', 'code' => 'ID', 'icon' => 'fa-solid fa-desktop text-emerald-400', 'asn' => 'AS7713 (Telkom Indonesia)', 'tag' => 'REMOTE_CLIENT', 'score' => 20],
        ];

        $choice = $countries[abs($hash) % count($countries)];

        return [
            'country' => __($choice['country']),
            'country_code' => $choice['code'],
            'flag' => $choice['code'],
            'icon' => $choice['icon'],
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
