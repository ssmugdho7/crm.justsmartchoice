param(
    [int]$Port = 8099,
    [string]$Token = "CHANGE_THIS_TOKEN",
    [string]$BindAddress = "http://+:8099/"
)

# Smart Network Manager Windows Agent v1.1.0
# Run as Administrator on Windows Server 2019.
# Do not expose this listener to the public internet.

$listener = New-Object System.Net.HttpListener
$listener.Prefixes.Add($BindAddress)
$listener.Start()
Write-Host "Smart Network Agent listening at $BindAddress"
Write-Host "Security: LAN/VPN only. Do not port-forward this service."

function Send-Json($context, $object) {
    $json = $object | ConvertTo-Json -Depth 8
    $buffer = [System.Text.Encoding]::UTF8.GetBytes($json)
    $context.Response.ContentType = "application/json"
    $context.Response.ContentLength64 = $buffer.Length
    $context.Response.OutputStream.Write($buffer, 0, $buffer.Length)
    $context.Response.OutputStream.Close()
}

function Get-JsonBody($request) {
    $reader = New-Object System.IO.StreamReader($request.InputStream, $request.ContentEncoding)
    $body = $reader.ReadToEnd()
    if ([string]::IsNullOrWhiteSpace($body)) { return @{} }
    return $body | ConvertFrom-Json
}

function Get-Devices {
    $arp = arp -a
    $devices = @()
    foreach ($line in $arp) {
        if ($line -match "(\d+\.\d+\.\d+\.\d+)\s+([a-fA-F0-9\-]{17})") {
            $ip = $matches[1]
            $mac = $matches[2]
            $name = "Unknown Device"
            try { $dns = [System.Net.Dns]::GetHostEntry($ip); if ($dns.HostName) { $name = $dns.HostName } } catch {}
            $devices += [PSCustomObject]@{
                device_name = $name
                device_type = "network"
                ip_address = $ip
                mac_address = $mac
                manufacturer = ""
                group_name = ""
                status = "online"
            }
        }
    }
    return $devices
}

while ($listener.IsListening) {
    try {
        $context = $listener.GetContext()
        $request = $context.Request
        $tokenHeader = $request.Headers["X-SNM-Token"]
        if ($tokenHeader -ne $Token) {
            Send-Json $context @{ success = $false; message = "Unauthorized" }
            continue
        }
        $path = $request.Url.AbsolutePath.Trim("/").ToLower()
        if ($request.HttpMethod -ne "POST") {
            Send-Json $context @{ success = $false; message = "POST required" }
            continue
        }
        $body = Get-JsonBody $request
        if ($path -eq "scan") {
            Send-Json $context @{ success = $true; devices = @(Get-Devices); message = "Scan completed" }
        } elseif ($path -eq "device-action") {
            $action = [string]$body.action
            $device = $body.device
            # Safe default: log/intake only. Real blocking requires router/firewall adapter.
            Send-Json $context @{ success = $true; message = "Action received by agent in safe mode: $action. Configure router adapter for real blocking."; action = $action; device = $device }
        } else {
            Send-Json $context @{ success = $false; message = "Unknown endpoint" }
        }
    } catch {
        Write-Host $_.Exception.Message
    }
}
