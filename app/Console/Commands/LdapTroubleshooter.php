<?php

namespace App\Console\Commands;

use App\Models\Ldap;
use App\Models\Setting;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;

/**
 * Check if a given ip is in a network
 *
 * @param  string  $ip  IP to check in IPV4 format eg. 127.0.0.1
 * @param  string  $range  IP/CIDR netmask eg. 127.0.0.0/24, also 127.0.0.1 is accepted and /32 assumed
 * @return bool true if the ip is in this range / false if not.
 */
function ip_in_range($ip, $range)
{
    if (strpos($range, '/') == false) {
        $range .= '/32';
    }
    // $range is in IP/CIDR format eg 127.0.0.1/24
    [$range, $netmask] = explode('/', $range, 2);
    $range_decimal = ip2long($range);
    $ip_decimal = ip2long($ip);
    $wildcard_decimal = pow(2, (32 - $netmask)) - 1;
    $netmask_decimal = ~$wildcard_decimal;

    return  ($ip_decimal & $netmask_decimal) == ($range_decimal & $netmask_decimal);
}
// NOTE - this function was shamelessly stolen from this gist: https://gist.github.com/tott/7684443

/**
 * Ensure LDAP filters are parentheses-wrapped
 */
function parenthesized_filter($filter)
{
    if (substr($filter, 0, 1) == '(') {
        return $filter;
    } else {
        return '('.$filter.')';
    }

}

class LdapTroubleshooter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ldap:troubleshoot
                            {--ldap-search : Output an ldapsearch command-line for testing your LDAP config}
                            {--force : Skip the interactive yes/no prompt for confirmation}
                            {--debug : Include debugging output (verbose)}
                            {--trace : Include extremely verbose LDAP trace output}
                            {--timeout=15 : Timeout for LDAP Bind operations}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Chạy các kiểm tra LDAP không phá hủy để hỗ trợ xác định cấu hình LDAP phù hợp cho môi trường hiện tại.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Output something *only* if debug is enabled
     *
     * @return void
     */
    public function debugout($string)
    {
        if ($this->option('debug')) {
            $this->line($string);
        }
    }

    /**
     * Clean the results from ldap_get_entries into something useful
     *
     * @param  array  $array
     * @return array
     */
    public function ldap_results_cleaner($array)
    {
        $cleaned = [];
        for ($i = 0; $i < $array['count']; $i++) {
            $row = $array[$i];
            $clean_row = [];
            foreach ($row as $key => $val) {
                $this->debugout('Key is: '.$key);
                if ($key == 'count' || is_int($key) || $key == 'dn') {
                    $this->debugout(" and we're gonna skip it\n");

                    continue;
                }
                $this->debugout(" And that seems fine.\n");
                if (array_key_exists('count', $val)) {
                    if ($val['count'] == 1) {
                        $clean_row[$key] = $val[0];
                    } else {
                        unset($val['count']); // these counts are annoying
                        $elements = [];
                        foreach ($val as $entry) {
                            if (isset($ldap_constants[$entry])) {
                                $elements[] = $ldap_constants[$entry];
                            } else {
                                $elements[] = $entry;
                            }
                        }
                        $clean_row[$key] = $elements;
                    }
                } else {
                    $clean_row[$key] = $val;
                }
            }
            $cleaned[$i] = $clean_row;
        }

        return $cleaned;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->option('trace')) {
            ldap_set_option(null, LDAP_OPT_DEBUG_LEVEL, 7);
        }

        $settings = Setting::getSettings();
        $this->settings = $settings;
        if ($this->option('ldap-search')) {
            if (! $this->option('force')) {
                $confirmation = $this->confirm('CẢNH BÁO: Lệnh này sẽ hiển thị mật khẩu LDAP trên terminal. Bạn có chắc muốn tiếp tục?');
                if (! $confirmation) {
                    $this->error('ĐANG HỦY');
                    exit(-1);
                }
            }
            $output = [];
            if ($settings->ldap_server_cert_ignore) {
                $this->line('# Ignoring server certificate validity');
                $output[] = 'LDAPTLS_REQCERT=never';
            }
            if ($settings->ldap_client_tls_cert && $settings->ldap_client_tls_key) {
                $this->line('# Adding LDAP Client Certificate and Key');
                $output[] = 'LDAPTLS_CERT=storage/ldap_client_tls.cert';
                $output[] = 'LDAPTLS_KEY=storage/ldap_client_tls.key';
            }
            $output[] = 'ldapsearch';
            $output[] = '-H '.$settings->ldap_server;
            $output[] = '-x';
            $output[] = '-b '.escapeshellarg($settings->ldap_basedn);
            $output[] = '-D '.escapeshellarg($settings->ldap_uname);

            try {
                $w = Crypt::Decrypt($settings->ldap_pword);
            } catch (Exception $e) {
                $this->warn('Không giải mã được mật khẩu. Thường là do chưa đặt mật khẩu LDAP hoặc APP_KEY đã thay đổi sau lần lưu mật khẩu LDAP gần nhất. Đang hủy.');
                exit(0);
            }

            $output[] = '-w '.escapeshellarg($w);
            $output[] = escapeshellarg(parenthesized_filter($settings->ldap_filter));
            if ($settings->ldap_tls) {
                $this->line('# adding STARTTLS option');
                $output[] = '-Z';
            }
            $output[] = '-v';
            $this->line("\n");
            $this->line(implode(" \\\n", $output));
            exit(0);
        }

        // PHP Version check for warning
        $php_version = phpversion();
        [$major, $minor, $patch] = explode('.', $php_version);
        if (
            $major < 8 ||
            ($major == 8 && $minor < 3) ||
            ($major == 8 && $minor == 3 && $patch < 21) ||
            ($major == 8 && $minor == 4 && $patch < 7)
        ) {
            $this->warn("PHP Version: $php_version WARNING - Versions before 8.3.21 or 8.4.7 will return INCONSISTENT results!");
            if (! $this->confirm('Bạn có chắc muốn tiếp tục?')) {
                $this->warn('ĐANG HỦY');
                exit(-1);
            }
        }

        if (! $this->option('force')) {
            $confirmation = $this->confirm('CẢNH BÁO: Lệnh này sẽ thử kết nối nhiều lần tới máy chủ LDAP. Bạn có chắc muốn tiếp tục?');
            if (! $confirmation) {
                $this->error('ĐANG HỦY');
                exit(-1);
            }
        }
        // $this->line(print_r($settings,true));
        $this->line('BƯỚC 1: Kiểm tra cấu hình');
        if (! $settings->ldap_enabled) {
            $this->error("CẢNH BÁO: Cấu hình LDAP của HSB-IT chưa được bật. Điều này có thể bình thường nếu bạn vẫn đang dò cấu hình.");
        }

        $ldap_conn = false;
        try {
            $ldap_conn = ldap_connect($settings->ldap_server);
        } catch (Exception $e) {
            $this->error("CẢNH BÁO: Bắt được ngoại lệ khi chạy 'ldap_connect()' - ".$e->getMessage().'. Hệ thống sẽ thử tự suy đoán.');
        }

        if (! $ldap_conn) {
            $this->error('CẢNH BÁO: Cấu hình máy chủ LDAP: '.$settings->ldap_server.' cannot be parsed. Hệ thống sẽ thử tự suy đoán.');
            // exit(-1);
        }
        // since we never use $ldap_conn again, we don't have to ldap_unbind() it (it's not even connected, tbh - that only happens at bind-time)

        $parsed = parse_url($settings->ldap_server);

        if (@$parsed['scheme'] != 'ldap' && @$parsed['scheme'] != 'ldaps') {
            $this->error("CẢNH BÁO: Scheme LDAP '".@$parsed['scheme']."' có thể chưa đúng; thông thường nên là ldap hoặc ldaps");
        }

        if (! @$parsed['host']) {
            $this->error('LỖI: Không xác định được hostname hoặc IP từ LDAP URL: '.$settings->ldap_server.'. ĐANG HỦY.');
            exit(-1);
        } else {
            $this->info('Determined LDAP hostname to be: '.$parsed['host']);
        }

        $raw_ips = [];

        if (inet_pton($parsed['host']) !== false) {
            $this->line($parsed['host'].' already looks like an address; skipping DNS lookup');
            $raw_ips[] = $parsed['host'];
        } else {
            $this->line('Performing DNS lookup of: '.$parsed['host']);
            $ips = dns_get_record($parsed['host']);

            // $this->info("Host IP is: ".print_r($ips,true));

            if (! $ips || count($ips) == 0) {
                $this->error('LỖI: Tra cứu DNS cho host: '.$parsed['host'].' thất bại. ĐANG HỦY.');
                exit(-1);
            }
            $this->debugout("IP's? ".print_r($ips, true));
            foreach ($ips as $ip) {
                if (! isset($ip['ip'])) {
                    continue;
                }
                $raw_ips[] = $ip['ip'];
            }
        }
        foreach ($raw_ips as $ip) {
            if ($ip == '127.0.0.1') {
                $this->error('CẢNH BÁO: Đang dùng IP localhost làm máy chủ LDAP. Trường hợp này thường không đúng.');
            }
            if (ip_in_range($ip, '10.0.0.0/8') || ip_in_range($ip, '192.168.0.0/16') || ip_in_range($ip, '172.16.0.0/12')) {
                $this->error('CẢNH BÁO: Đang dùng địa chỉ riêng RFC1918 cho máy chủ LDAP. Có thể đúng, nhưng sẽ có vấn đề nếu HSB-IT không chạy trong mạng riêng của bạn.');
            }
        }

        $this->line('STAGE 2: Checking basic network connectivity');
        $ports = [636, 389];
        if (@$parsed['port'] && ! in_array($parsed['port'], $ports)) {
            $ports[] = $parsed['port'];
        }

        $open_ports = [];
        foreach ($ports as $port) {
            $errno = 0;
            $errstr = '';
            $timeout = 30.0;
            $result = '';
            $this->line('Attempting to connect to port: '.$port." - may take up to $timeout seconds");
            try {
                $result = fsockopen($parsed['host'], $port, $errno, $errstr, 30.0);
            } catch (Exception $e) {
                $this->error('Exception: '.$e->getMessage());
            }
            if ($result) {
                $this->info('Thành công!');
                $open_ports[] = $port;
            } else {
                $this->error("CẢNH BÁO: Không thể kết nối tới cổng: $port - $errstr ($errno)");
            }
        }

        if (count($open_ports) == 0) {
            $this->error('ERROR - no open ports. ĐANG HỦY.');
            exit(-1);
        }

        $this->line('BƯỚC 3: Xác định thuật toán mã hóa nếu có');

        $ldap_urls = []; // [url, cert-check?, start_tls?]
        $pretty_ldap_urls = [];
        foreach ($open_ports as $port) {
            $this->line("Đang thử TLS trước cho cổng $port");
            $ldap_url = 'ldaps://'.$parsed['host'].":$port";
            if ($this->test_anonymous_bind($ldap_url)) {
                $this->info("Anonymous bind succesful to $ldap_url!");
                $ldap_urls[] = [$ldap_url, true, false];
                $pretty_ldap_urls[] = [$ldap_url, 'enabled', 'n/a (no)'];

                continue; // TODO - lots of copypasta in these if(test_anonymous_bind()) routines...
            } else {
                $this->error("CẢNH BÁO: Bind thất bại tới $ldap_url - đang thử lại không kiểm tra chứng chỉ.");
            }

            if ($this->test_anonymous_bind($ldap_url, false)) {
                $this->info("Anonymous bind successful to $ldap_url with certificate-checks disabled");
                $ldap_urls[] = [$ldap_url, false, false];
                $pretty_ldap_urls[] = [$ldap_url, 'DISABLED', 'n/a (no)'];

                continue;
            } else {
                $this->error("CẢNH BÁO: Bind thất bại tới $ldap_url khi đã tắt kiểm tra chứng chỉ. Đang thử không mã hóa với STARTTLS.");
            }

            // now switching to ldap:// URL's from ldaps://
            $ldap_url = 'ldap://'.$parsed['host'].":$port";

            if ($this->test_anonymous_bind($ldap_url, true, true)) {
                $this->info("Plain connection to $ldap_url with STARTTLS succesful!");
                $ldap_urls[] = [$ldap_url, true, true];
                $pretty_ldap_urls[] = [$ldap_url, 'enabled', 'STARTTLS ENABLED'];

                continue;
            } else {
                $this->error("CẢNH BÁO: Bind thất bại tới $ldap_url khi đã bật STARTTLS. Đang thử lại không kiểm tra chứng chỉ.");
            }

            if ($this->test_anonymous_bind($ldap_url, false, true)) {
                $this->info("Plain connection to $ldap_url with STARTTLS and cert checks *disabled* successful!");
                $ldap_urls[] = [$ldap_url, false, true];
                $pretty_ldap_urls[] = [$ldap_url, 'DISABLED', 'STARTTLS ENABLED'];

                continue;
            } else {
                $this->error("CẢNH BÁO: Bind thất bại tới $ldap_url khi đã bật STARTTLS và tắt kiểm tra chứng chỉ. Đang thử lại không dùng STARTTLS.");
            }

            if ($this->test_anonymous_bind($ldap_url)) {
                $this->info("Plain connection to $ldap_url succesful!");
                $ldap_urls[] = [$ldap_url, true, false];
                $pretty_ldap_urls[] = [$ldap_url, 'n/a', 'starttls disabled'];

                continue;
            } else {
                $this->error("CẢNH BÁO: Bind thất bại tới $ldap_url. Bỏ qua cổng $port");
            }
        }

        $this->debugout(print_r($ldap_urls, true));

        if (count($ldap_urls) > 0) {
            $this->debugout("Tìm thấy LDAP URL hoạt động: ");
            foreach ($ldap_urls as $ldap_url) { // TODO maybe do this với tài khoản a $this->table() instead?
                $this->debugout('LDAP URL: '.$ldap_url[0]);
                $this->debugout($ldap_url[0].($ldap_url[1] ? ' certificate checks enabled' : ' certificate checks disabled').($ldap_url[2] ? ' STARTTLS Enabled ' : ' STARTTLS Disabled'));
            }
            $this->table(['URL', 'Cert Checks?', 'STARTTLS?'], $pretty_ldap_urls);
        } else {
            $this->error("ERROR - no valid LDAP URL's available - ĐANG HỦY");
            exit(1);
        }

        $this->line('BƯỚC 4: Kiểm tra bind quản trị cho đồng bộ LDAP');
        foreach ($ldap_urls as $ldap_url) {
            try {
                $w = Crypt::Decrypt($settings->ldap_pword);
            } catch (Exception $e) {
                $this->warn('Không giải mã được mật khẩu. Thường là do chưa đặt mật khẩu LDAP hoặc APP_KEY đã thay đổi sau lần lưu mật khẩu LDAP gần nhất. Đang hủy.');
                exit(0);
            }
            $this->test_authed_bind($ldap_url[0], $ldap_url[1], $ldap_url[2], $settings->ldap_uname, $w);
        }

        $this->line('BƯỚC 5: Kiểm tra BaseDN');
        // grab all LDAP_ constants and fill up a reversed array mapping from weird LDAP dotted-strings to (Constant Name)
        $all_defined_constants = get_defined_constants();
        $ldap_constants = [];
        foreach ($all_defined_constants as $key => $val) {
            if (starts_with($key, 'LDAP_') && is_string($val)) {
                $ldap_constants[$val] = $key; // INVERT the meaning here!
            }
        }
        $this->debugout('LDAP constants are: '.print_r($ldap_constants, true));

        foreach ($ldap_urls as $ldap_url) {
            try {
                $w = Crypt::Decrypt($settings->ldap_pword);
            } catch (Exception $e) {
                $this->warn('Không giải mã được mật khẩu. Thường là do chưa đặt mật khẩu LDAP hoặc APP_KEY đã thay đổi sau lần lưu mật khẩu LDAP gần nhất. Đang hủy.');
                exit(0);
            }

            if ($this->test_informational_bind($ldap_url[0], $ldap_url[1], $ldap_url[2], $settings->ldap_uname, $w, $settings)) {
                $this->info('Bind lấy thông tin thành công!');
            } else {
                $this->error('Unable to get information from bind.');
            }
        }

        $this->line('BƯỚC 6: Kiểm tra đăng nhập LDAP vào HSB-IT');
        foreach ($ldap_urls as $ldap_url) {
            $this->line('Bắt đầu xác thực tới '.$ldap_url[0]);
            while (true) {
                $with_tls = $ldap_url[1] ? 'có' : 'không có';
                $with_startssl = $ldap_url[2] ? 'có dùng' : 'không dùng';
                if (! $this->confirm('Bạn có muốn thử xác thực tới thư mục này: '.$ldap_url[0]." $with_tls TLS và $with_startssl STARTSSL?")) {
                    break;
                }
                $username = $this->ask('Tên đăng nhập');
                $password = $this->secret('Mật khẩu');
                $results = $this->test_authed_bind($ldap_url[0], $ldap_url[1], $ldap_url[2], $username, $password); // FIXME - should do some other stuff here, maybe with the concatenating or something? maybe? and/or should put up some results?
                if ($results) {
                    $this->info('Xác thực thành công với '.$username);
                } else {
                    $this->error('Unable to authenticate with '.$username);
                }
            }
        }

        $this->info('LDAP TROUBLESHOOTING COMPLETE!');
    }

    public function connect_to_ldap($ldap_url, $check_cert, $start_tls)
    {
        if ($check_cert) {
            $this->line('we *ARE* checking certs');
            Ldap::ignoreCertificates(false);

        } else {
            $this->line('we are IGNORING certs');
            Ldap::ignoreCertificates(true);
        }
        $lconn = ldap_connect($ldap_url);
        ldap_set_option($lconn, LDAP_OPT_PROTOCOL_VERSION, 3); // should we 'test' different protocol versions here? Does anyone even use anything other than LDAPv3?
        // no - it's formally deprecated: https://tools.ietf.org/html/rfc3494
        if ($this->settings->ldap_client_tls_cert && $this->settings->ldap_client_tls_key) {
            // client-side TLS certificate support for LDAP (Google Secure LDAP)
            putenv('LDAPTLS_CERT=storage/ldap_client_tls.cert');
            putenv('LDAPTLS_KEY=storage/ldap_client_tls.key');
        }
        if ($start_tls) {
            if (! ldap_start_tls($lconn)) {
                $this->error('CẢNH BÁO: Không thể khởi động TLS.');

                return false;
            }
        }
        if (! $lconn) {
            $this->error('CẢNH BÁO: Không tạo được chuỗi kết nối - đang dùng: '.$ldap_url);

            return false;
        }
        $net = ldap_set_option($lconn, LDAP_OPT_NETWORK_TIMEOUT, $this->option('timeout'));
        $time = ldap_set_option($lconn, LDAP_OPT_TIMELIMIT, $this->option('timeout'));
        if (! $net || ! $time) {
            $this->error('Unable to set timeouts!');
        }

        return $lconn;
    }

    public function test_anonymous_bind($ldap_url, $check_cert = true, $start_tls = false)
    {
        return $this->timed_boolean_execute(function () use ($ldap_url, $check_cert, $start_tls) {
            try {
                $lconn = $this->connect_to_ldap($ldap_url, $check_cert, $start_tls);
                $this->line('Attempting to bind now, this can take a while if we mess it up');
                $bind_results = ldap_bind($lconn);
                $this->line('Bind results are: '.$bind_results.' which translate into boolean: '.(bool) $bind_results);
                ldap_close($lconn);

                return (bool) $bind_results;
            } catch (Exception $e) {
                $this->error('CẢNH BÁO: Bắt được ngoại lệ khi bind - '.$e->getMessage());

                return false;
            }
        });
    }

    public function test_authed_bind($ldap_url, $check_cert, $start_tls, $username, $password)
    {
        return $this->timed_boolean_execute(function () use ($ldap_url, $check_cert, $start_tls, $username, $password) {
            try {
                $lconn = $this->connect_to_ldap($ldap_url, $check_cert, $start_tls);
                $bind_results = ldap_bind($lconn, $username, $password);
                ldap_close($lconn);
                if (! $bind_results) {
                    $this->error("CẢNH BÁO: Bind thất bại tới $ldap_url với tài khoản $username");

                    return false;
                } else {
                    $this->info("THÀNH CÔNG - Bind được tới $ldap_url với tài khoản $username");

                    return (bool) $lconn;
                }
            } catch (Exception $e) {
                $this->error("CẢNH BÁO: Bắt được ngoại lệ khi bind xác thực tới $username - ".$e->getMessage());

                return false;
            }
        });
    }

    public function test_informational_bind($ldap_url, $check_cert, $start_tls, $username, $password, $settings)
    {
        return $this->timed_boolean_execute(function () use ($ldap_url, $check_cert, $start_tls, $username, $password, $settings) {
            try { // TODO - copypasta'ed from test_authed_bind
                $conn = $this->connect_to_ldap($ldap_url, $check_cert, $start_tls);
                $bind_results = ldap_bind($conn, $username, $password);
                if (! $bind_results) {
                    $this->error("CẢNH BÁO: Bind thất bại tới $ldap_url với tài khoản $username");

                    return false;
                }
                $this->info("THÀNH CÔNG - Bind được tới $ldap_url với tài khoản $username");
                $cleaned_results = [];
                try {
                    // This _may_ only work for Active Directory?
                    $result = ldap_read($conn, '', '(objectClass=*)'/* , ['supportedControl'] */);
                    $results = ldap_get_entries($conn, $result);
                    $cleaned_results = $this->ldap_results_cleaner($results);
                    // $this->line(print_r($cleaned_results,true));
                    $default_naming_contexts = $cleaned_results[0]['namingcontexts'];
                    $this->info('Default Naming Contexts:');
                    $this->info(implode(', ', $default_naming_contexts));
                    // okay, great - now how do we display those results? I have no idea.
                } catch (Exception $e) {
                    $this->error("Unable to get base naming contexts - here's what we *did* get:");
                    $this->line(print_r($cleaned_results, true));
                }
                // I don't see why this throws an Exception for Google LDAP, but I guess we ought to try and catch it?
                $this->debugout("I guess we're trying to do the ldap search here, but sometimes it takes too long?");
                $this->debugout('Base DN is: '.$settings->ldap_basedn.' and filter is: '.parenthesized_filter($settings->ldap_filter));
                $search_results = ldap_search($conn, $settings->ldap_basedn, parenthesized_filter($settings->ldap_filter));
                $entries = ldap_get_entries($conn, $search_results);
                $this->info('In 10 kết quả đầu tiên: ');
                $pretty_data = array_slice($this->ldap_results_cleaner($entries), 0, 10);
                // print_r($data);
                $headers = [];
                foreach ($pretty_data as $row) {
                    // populate headers
                    foreach ($row as $key => $value) {
                        // skip objectsid and objectguid because it junks up output
                        if ($key == 'objectsid' || $key == 'objectguid') {
                            continue;
                        }
                        if (! in_array($key, $headers)) {
                            $headers[] = $key;
                        }
                    }
                }
                $table = [];
                // repeat again to populate table
                foreach ($pretty_data as $row) {
                    $newrow = [];
                    foreach ($headers as $header) {
                        if (is_array(@$row[$header])) {
                            $newrow[] = '['.implode(', ', $row[$header]).']';
                        } else {
                            $newrow[] = @$row[$header];
                        }
                    }
                    $table[] = $newrow;
                }

                $this->table($headers, $table);
            } catch (Exception $e) {
                $this->error("CẢNH BÁO: Bắt được ngoại lệ khi bind xác thực tới $username - ".$e->getMessage());

                return false;
            } finally {
                ldap_close($conn);
            }
        });
    }

    /***********************************************
     *
     * This function executes $function - which is expected to be some kind of executable function -
     * with a timeout set. It respects the timeout by forking execution and setting a strict timer
     * for which to get back a SIGUSR1 or SIGUSR2 signal from the forked process.
     *
     ***********************************************/
    private function timed_boolean_execute($function)
    {
        if (! (function_exists('pcntl_sigtimedwait') && function_exists('posix_getpid') && function_exists('pcntl_fork') && function_exists('posix_kill') && function_exists('pcntl_wifsignaled'))) {
            // POSIX functions needed for forking aren't present, just run the function inline (ignoring timeout)
            $this->line('CẢNH BÁO: Không thể chạy lệnh POSIX fork(), timeout có thể không được áp dụng');

            return $function();
        } else {
            $parent_pid = posix_getpid();
            $pid = pcntl_fork();
            switch ($pid) {
                case 0:
                    // we're the 'child'
                    if ($function()) {
                        // SUCCESS = SIGUSR1
                        posix_kill($parent_pid, SIGUSR1);
                    } else {
                        // FAILURE = SIGUSR2
                        posix_kill($parent_pid, SIGUSR2);
                    }
                    exit();
                    break; // yes I know we don't need it.
                case -1:
                    // couldn't fork
                    $this->error('COULD NOT FORK - assuming failure');

                    return false;
                    break; // I still know that we don't need it
                default:
                    // we remain the 'parent', $pid is the PID of the forked process.
                    $siginfo = [];
                    $exit_status = pcntl_sigtimedwait([SIGUSR1, SIGUSR2], $siginfo, $this->option('timeout'));
                    if ($exit_status == SIGUSR1) {
                        return true;
                    } else {
                        posix_kill($pid, SIGKILL); // make sure we don't have processes hanging around that might try and send signals during later executions, confusing us

                        return false;
                    }
                    break; // Yeah I get it already, shush.
            }
        }

    }
}
