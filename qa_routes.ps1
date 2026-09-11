$urls = @('http://127.0.0.1:8123/dashboard', 'http://127.0.0.1:8123/login', 'http://127.0.0.1:8123/register', 'http://127.0.0.1:8123/auth', 'http://127.0.0.1:8123/')

foreach ($u in $urls) {
    try {
        $r = Invoke-WebRequest -Uri $u -UseBasicParsing -TimeoutSec 8 -MaximumRedirection 0 -ErrorAction Stop
        Write-Output ($u + "  -> STATUS " + [int]$r.StatusCode)
    } catch {
        $resp = $_.Exception.Response
        if ($resp -ne $null) {
            $code = [int]$resp.StatusCode
            $loc = $resp.Headers['Location']
            Write-Output ($u + "  -> STATUS " + $code + "  LOCATION " + $loc)
        } else {
            Write-Output ($u + "  -> ERR " + $_.Exception.Message)
        }
    }
}
