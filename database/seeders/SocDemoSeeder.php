<?php

namespace Database\Seeders;

use App\Models\DetectionRule;
use App\Models\LogSource;
use App\Models\NormalizedEvent;
use App\Models\RawLog;
use App\Models\SecurityAlert;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SocDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Log Sources
        $sources = [
            [
                'name' => 'Edge Perimeter Firewall (Palo Alto)',
                'ip_address' => '192.168.1.1',
                'type' => 'syslog',
                'status' => 'active',
            ],
            [
                'name' => 'Primary Web Cluster (Nginx)',
                'ip_address' => '10.0.1.50',
                'type' => 'file',
                'status' => 'active',
            ],
            [
                'name' => 'Bastion & SSH Gateway',
                'ip_address' => '10.0.1.20',
                'type' => 'syslog',
                'status' => 'active',
            ],
            [
                'name' => 'Internal Core Database (PostgreSQL)',
                'ip_address' => '10.0.2.15',
                'type' => 'api',
                'status' => 'active',
            ],
            [
                'name' => 'Remote Worker VPN Gateway',
                'ip_address' => '192.168.100.1',
                'type' => 'syslog',
                'status' => 'active',
            ],
            [
                'name' => 'Legacy Staging Host',
                'ip_address' => '10.0.3.99',
                'type' => 'file',
                'status' => 'inactive',
            ],
        ];

        $sourceModels = [];
        foreach ($sources as $source) {
            $sourceModels[] = LogSource::create($source);
        }

        // 2. Detection Rules
        $rules = [
            [
                'name' => 'SSH Brute Force Authentication Flooding',
                'condition_type' => 'threshold_exceeded',
                'threshold' => 5,
                'time_window_minutes' => 5,
                'severity' => 'high',
            ],
            [
                'name' => 'Remote Code Execution / Traversal (.env / shell probe)',
                'condition_type' => 'pattern_match',
                'threshold' => 1,
                'time_window_minutes' => 1,
                'severity' => 'critical',
            ],
            [
                'name' => 'Direct Root Login Attempt over SSH',
                'condition_type' => 'single_event',
                'threshold' => 1,
                'time_window_minutes' => 1,
                'severity' => 'critical',
            ],
            [
                'name' => 'High Frequency 403 Forbidden Access Scan',
                'condition_type' => 'threshold_exceeded',
                'threshold' => 10,
                'time_window_minutes' => 2,
                'severity' => 'medium',
            ],
            [
                'name' => 'Suspicious Outbound Port Sweep',
                'condition_type' => 'volume_spike',
                'threshold' => 30,
                'time_window_minutes' => 10,
                'severity' => 'medium',
            ],
            [
                'name' => 'Anomalous Privilege Escalation Execution',
                'condition_type' => 'pattern_match',
                'threshold' => 1,
                'time_window_minutes' => 1,
                'severity' => 'critical',
            ],
        ];

        $ruleModels = [];
        foreach ($rules as $rule) {
            $ruleModels[] = DetectionRule::create($rule);
        }

        // 3. Security Alerts
        $alerts = [
            [
                'rule_id' => $ruleModels[1]->id, // Remote Code Execution
                'source_ip' => '185.220.101.45',
                'severity' => 'critical',
                'status' => 'open',
                'triggered_at' => CarbonImmutable::now()->subMinutes(12),
            ],
            [
                'rule_id' => $ruleModels[0]->id, // SSH Brute Force
                'source_ip' => '45.154.255.89',
                'severity' => 'high',
                'status' => 'open',
                'triggered_at' => CarbonImmutable::now()->subMinutes(34),
            ],
            [
                'rule_id' => $ruleModels[2]->id, // Root Login SSH
                'source_ip' => '194.26.29.112',
                'severity' => 'critical',
                'status' => 'open',
                'triggered_at' => CarbonImmutable::now()->subHours(1)->subMinutes(8),
            ],
            [
                'rule_id' => $ruleModels[3]->id, // 403 Scan
                'source_ip' => '103.145.13.22',
                'severity' => 'medium',
                'status' => 'acknowledged',
                'triggered_at' => CarbonImmutable::now()->subHours(2)->subMinutes(15),
            ],
            [
                'rule_id' => $ruleModels[4]->id, // Port Sweep
                'source_ip' => '91.240.118.17',
                'severity' => 'medium',
                'status' => 'open',
                'triggered_at' => CarbonImmutable::now()->subHours(3)->subMinutes(40),
            ],
            [
                'rule_id' => $ruleModels[0]->id, // SSH Brute Force
                'source_ip' => '198.51.100.84',
                'severity' => 'high',
                'status' => 'resolved',
                'triggered_at' => CarbonImmutable::now()->subHours(5)->subMinutes(20),
            ],
            [
                'rule_id' => $ruleModels[5]->id, // Priv Escalation
                'source_ip' => '10.0.1.88',
                'severity' => 'critical',
                'status' => 'acknowledged',
                'triggered_at' => CarbonImmutable::now()->subHours(7)->subMinutes(5),
            ],
            [
                'rule_id' => $ruleModels[3]->id, // 403 Scan
                'source_ip' => '185.220.101.45',
                'severity' => 'medium',
                'status' => 'resolved',
                'triggered_at' => CarbonImmutable::now()->subHours(11)->subMinutes(12),
            ],
        ];

        foreach ($alerts as $alert) {
            SecurityAlert::create($alert);
        }

        // 4. Normalized Events & Raw Logs
        $sampleEvents = [
            [
                'source_idx' => 1,
                'source_ip' => '185.220.101.45',
                'dest_ip' => '10.0.1.50',
                'user' => null,
                'category' => 'web',
                'action' => 'GET /.env',
                'outcome' => 'failure',
                'severity' => 'critical',
                'message' => '185.220.101.45 - - [29/Oct/2026:13:30:15 +0000] "GET /.env HTTP/1.1" 404 196 "-" "curl/7.68.0"',
                'sub_minutes' => 5,
                'parsed' => ['path' => '/.env', 'status' => 404, 'http_method' => 'GET', 'threat' => 'Sensitive Environment File Disclosure Attempt'],
            ],
            [
                'source_idx' => 2,
                'source_ip' => '45.154.255.89',
                'dest_ip' => '10.0.1.20',
                'user' => 'root',
                'category' => 'authentication',
                'action' => 'ssh_login',
                'outcome' => 'failure',
                'severity' => 'high',
                'message' => 'Failed password for invalid user root from 45.154.255.89 port 42812 ssh2',
                'sub_minutes' => 9,
                'parsed' => ['service' => 'sshd', 'auth_type' => 'password', 'reason' => 'invalid_user'],
            ],
            [
                'source_idx' => 1,
                'source_ip' => '185.220.101.45',
                'dest_ip' => '10.0.1.50',
                'user' => null,
                'category' => 'web',
                'action' => 'POST /api/v1/auth/login',
                'outcome' => 'failure',
                'severity' => 'medium',
                'message' => '185.220.101.45 - - [29/Oct/2026:13:35:10 +0000] "POST /api/v1/auth/login HTTP/1.1" 401 84 "-" "Mozilla/5.0"',
                'sub_minutes' => 15,
                'parsed' => ['path' => '/api/v1/auth/login', 'status' => 401, 'http_method' => 'POST'],
            ],
            [
                'source_idx' => 0,
                'source_ip' => '91.240.118.17',
                'dest_ip' => '192.168.1.1',
                'user' => null,
                'category' => 'network',
                'action' => 'SYN_FLOOD_DROP',
                'outcome' => 'blocked',
                'severity' => 'high',
                'message' => '<134>Oct 29 13:20:00 PA-5220 TRAFFIC: DROP rule=Inbound-Drop src=91.240.118.17 dst=192.168.1.1 proto=TCP dport=445',
                'sub_minutes' => 22,
                'parsed' => ['protocol' => 'TCP', 'target_port' => 445, 'action' => 'DROP'],
            ],
            [
                'source_idx' => 2,
                'source_ip' => '45.154.255.89',
                'dest_ip' => '10.0.1.20',
                'user' => 'admin',
                'category' => 'authentication',
                'action' => 'ssh_login',
                'outcome' => 'failure',
                'severity' => 'high',
                'message' => 'Failed password for admin from 45.154.255.89 port 42818 ssh2',
                'sub_minutes' => 31,
                'parsed' => ['service' => 'sshd', 'auth_type' => 'password'],
            ],
            [
                'source_idx' => 1,
                'source_ip' => '194.26.29.112',
                'dest_ip' => '10.0.1.50',
                'user' => null,
                'category' => 'web',
                'action' => 'GET /wp-admin/setup-config.php',
                'outcome' => 'failure',
                'severity' => 'medium',
                'message' => '194.26.29.112 - - [29/Oct/2026:13:02:40 +0000] "GET /wp-admin/setup-config.php HTTP/1.1" 404 153 "-" "Nikto/2.1.6"',
                'sub_minutes' => 48,
                'parsed' => ['scanner' => 'Nikto', 'status' => 404],
            ],
            [
                'source_idx' => 4,
                'source_ip' => '103.145.13.22',
                'dest_ip' => '192.168.100.1',
                'user' => 'johndoe',
                'category' => 'authentication',
                'action' => 'vpn_handshake',
                'outcome' => 'success',
                'severity' => 'info',
                'message' => 'VPN gateway TLS handshake established from 103.145.13.22 for user johndoe via AES-256-GCM',
                'sub_minutes' => 65,
                'parsed' => ['cipher' => 'AES-256-GCM', 'tunnel_ip' => '10.8.0.4'],
            ],
            [
                'source_idx' => 1,
                'source_ip' => '185.220.101.45',
                'dest_ip' => '10.0.1.50',
                'user' => null,
                'category' => 'web',
                'action' => 'GET /api/v1/users?id=1%20UNION%20SELECT%20NULL,password',
                'outcome' => 'failure',
                'severity' => 'critical',
                'message' => '185.220.101.45 - - [29/Oct/2026:12:15:33 +0000] "GET /api/v1/users?id=1%20UNION%20SELECT HTTP/1.1" 500 524 "-" "sqlmap/1.5.2"',
                'sub_minutes' => 95,
                'parsed' => ['attack_vector' => 'SQLi', 'waf_triggered' => true],
            ],
            [
                'source_idx' => 3,
                'source_ip' => '10.0.1.50',
                'dest_ip' => '10.0.2.15',
                'user' => 'soc_app',
                'category' => 'database',
                'action' => 'QUERY_SELECT_AUDIT',
                'outcome' => 'success',
                'severity' => 'low',
                'message' => 'PostgreSQL [pid 9014]: [soc_app] LOG: duration: 4.12 ms statement: SELECT * FROM security_alerts WHERE status = "open"',
                'sub_minutes' => 120,
                'parsed' => ['duration_ms' => 4.12],
            ],
            [
                'source_idx' => 0,
                'source_ip' => '194.26.29.112',
                'dest_ip' => '192.168.1.1',
                'user' => null,
                'category' => 'network',
                'action' => 'PORT_SCAN_TCP',
                'outcome' => 'blocked',
                'severity' => 'medium',
                'message' => '<134>Oct 29 11:30:12 PA-5220 THREAT: Port Scan detected from 194.26.29.112 against internal subnet 10.0.1.0/24',
                'sub_minutes' => 150,
                'parsed' => ['ports_probed' => [21, 22, 23, 80, 443, 3306, 8080]],
            ],
            [
                'source_idx' => 2,
                'source_ip' => '10.0.1.105',
                'dest_ip' => '10.0.1.20',
                'user' => 'lead_analyst',
                'category' => 'authentication',
                'action' => 'ssh_login',
                'outcome' => 'success',
                'severity' => 'info',
                'message' => 'Accepted publickey for lead_analyst from 10.0.1.105 port 55122 ssh2: RSA SHA256:7uKm...Wq',
                'sub_minutes' => 180,
                'parsed' => ['auth_method' => 'publickey'],
            ],
            [
                'source_idx' => 1,
                'source_ip' => '198.51.100.84',
                'dest_ip' => '10.0.1.50',
                'user' => null,
                'category' => 'web',
                'action' => 'GET /phpmyadmin/index.php',
                'outcome' => 'failure',
                'severity' => 'high',
                'message' => '198.51.100.84 - - [29/Oct/2026:10:14:02 +0000] "GET /phpmyadmin/index.php HTTP/1.1" 403 273 "-" "Masscan/1.3"',
                'sub_minutes' => 240,
                'parsed' => ['target' => 'Database Web Admin', 'status' => 403],
            ],
            [
                'source_idx' => 0,
                'source_ip' => '185.220.101.45',
                'dest_ip' => '10.0.1.20',
                'user' => 'root',
                'category' => 'authentication',
                'action' => 'ssh_login',
                'outcome' => 'failure',
                'severity' => 'critical',
                'message' => 'Failed password for invalid user root from 185.220.101.45 port 59910 ssh2',
                'sub_minutes' => 310,
                'parsed' => ['target_user' => 'root'],
            ],
            [
                'source_idx' => 1,
                'source_ip' => '103.145.13.22',
                'dest_ip' => '10.0.1.50',
                'user' => null,
                'category' => 'web',
                'action' => 'GET /static/dashboard.js',
                'outcome' => 'success',
                'severity' => 'info',
                'message' => '103.145.13.22 - - [29/Oct/2026:08:22:15 +0000] "GET /static/dashboard.js HTTP/1.1" 200 48201 "https://aegis.corp/" "Mozilla/5.0"',
                'sub_minutes' => 420,
                'parsed' => ['status' => 200, 'size' => 48201],
            ],
            [
                'source_idx' => 2,
                'source_ip' => '194.26.29.112',
                'dest_ip' => '10.0.1.20',
                'user' => 'ubnt',
                'category' => 'authentication',
                'action' => 'ssh_login',
                'outcome' => 'failure',
                'severity' => 'high',
                'message' => 'Failed password for invalid user ubnt from 194.26.29.112 port 38112 ssh2',
                'sub_minutes' => 540,
                'parsed' => ['dictionary_attack' => true],
            ],
            [
                'source_idx' => 1,
                'source_ip' => '45.154.255.89',
                'dest_ip' => '10.0.1.50',
                'user' => null,
                'category' => 'web',
                'action' => 'GET /cgi-bin/test.cgi',
                'outcome' => 'failure',
                'severity' => 'high',
                'message' => '45.154.255.89 - - [29/Oct/2026:04:12:08 +0000] "GET /cgi-bin/test.cgi HTTP/1.1" 404 180 "-" "Shellshock-Scanner"',
                'sub_minutes' => 720,
                'parsed' => ['vector' => 'CGI exploit probe'],
            ],
            [
                'source_idx' => 0,
                'source_ip' => '91.240.118.17',
                'dest_ip' => '192.168.1.1',
                'user' => null,
                'category' => 'network',
                'action' => 'ICMP_SWEEP_DROP',
                'outcome' => 'blocked',
                'severity' => 'low',
                'message' => '<134>Oct 29 02:11:15 PA-5220 TRAFFIC: ICMP Flood rate-limit applied for 91.240.118.17',
                'sub_minutes' => 840,
                'parsed' => ['icmp_type' => 'echo_request'],
            ],
            [
                'source_idx' => 1,
                'source_ip' => '185.220.101.45',
                'dest_ip' => '10.0.1.50',
                'user' => null,
                'category' => 'web',
                'action' => 'GET /actuator/health',
                'outcome' => 'failure',
                'severity' => 'medium',
                'message' => '185.220.101.45 - - [28/Oct/2026:22:10:44 +0000] "GET /actuator/health HTTP/1.1" 404 142 "-" "Spring-Probe"',
                'sub_minutes' => 1100,
                'parsed' => ['spring_boot_probe' => true],
            ],
            [
                'source_idx' => 2,
                'source_ip' => '198.51.100.84',
                'dest_ip' => '10.0.1.20',
                'user' => 'support',
                'category' => 'authentication',
                'action' => 'ssh_login',
                'outcome' => 'failure',
                'severity' => 'high',
                'message' => 'Failed password for support from 198.51.100.84 port 49102 ssh2',
                'sub_minutes' => 1250,
                'parsed' => ['credential_stuffing' => true],
            ],
            [
                'source_idx' => 4,
                'source_ip' => '103.145.13.22',
                'dest_ip' => '192.168.100.1',
                'user' => 'alice_sec',
                'category' => 'authentication',
                'action' => 'vpn_handshake',
                'outcome' => 'success',
                'severity' => 'info',
                'message' => 'VPN gateway TLS handshake established from 103.145.13.22 for user alice_sec via AES-256-GCM',
                'sub_minutes' => 1380,
                'parsed' => ['tunnel_ip' => '10.8.0.5'],
            ],
        ];

        // Let's generate a batch of background logs across 24 hours to give realistic trend charts
        $now = CarbonImmutable::now();
        foreach ($sampleEvents as $ev) {
            $receivedAt = $now->subMinutes($ev['sub_minutes']);
            $source = $sourceModels[$ev['source_idx']];

            $rawLog = RawLog::create([
                'log_source_id' => $source->id,
                'raw_message' => $ev['message'],
                'checksum' => hash('sha256', $ev['message'].$receivedAt->timestamp),
                'received_at' => $receivedAt,
            ]);

            NormalizedEvent::create([
                'raw_log_id' => $rawLog->id,
                'timestamp' => $receivedAt,
                'source_ip' => $ev['source_ip'],
                'destination_ip' => $ev['dest_ip'],
                'username' => $ev['user'],
                'event_category' => $ev['category'],
                'event_action' => $ev['action'],
                'event_outcome' => $ev['outcome'],
                'severity' => $ev['severity'],
                'parsed_data' => $ev['parsed'],
            ]);
        }

        // Additional random distribution of events across past 24 hours for nice charts
        $attackerIps = ['185.220.101.45', '45.154.255.89', '194.26.29.112', '91.240.118.17', '198.51.100.84'];
        $categories = ['web', 'authentication', 'network', 'system'];
        $severities = ['info', 'low', 'medium', 'high', 'critical'];

        for ($h = 24; $h >= 0; $h--) {
            // Generate 2-5 background events per hour
            $eventsThisHour = rand(2, 6);
            for ($k = 0; $k < $eventsThisHour; $k++) {
                $eventTime = $now->subHours($h)->subMinutes(rand(1, 55));
                $source = $sourceModels[rand(0, 4)];
                $ip = $attackerIps[array_rand($attackerIps)];
                $cat = $categories[array_rand($categories)];
                $sev = $severities[rand(0, 4)];
                $outcome = in_array($sev, ['high', 'critical'], true) ? 'failure' : 'success';

                $msg = sprintf(
                    '[%s] %s event recorded from %s targeting internal node. Action: %s_%s',
                    $eventTime->toIso8601String(),
                    strtoupper($sev),
                    $ip,
                    $cat,
                    $outcome
                );

                $rawLog = RawLog::create([
                    'log_source_id' => $source->id,
                    'raw_message' => $msg,
                    'checksum' => hash('sha256', $msg.$eventTime->timestamp.rand()),
                    'received_at' => $eventTime,
                ]);

                NormalizedEvent::create([
                    'raw_log_id' => $rawLog->id,
                    'timestamp' => $eventTime,
                    'source_ip' => $ip,
                    'destination_ip' => '10.0.1.'.rand(10, 99),
                    'username' => rand(0, 1) ? 'user_'.rand(100, 999) : null,
                    'event_category' => $cat,
                    'event_action' => $cat.'_activity',
                    'event_outcome' => $outcome,
                    'severity' => $sev,
                    'parsed_data' => ['simulated' => true, 'batch' => 'soc_demo'],
                ]);
            }
        }
    }
}
