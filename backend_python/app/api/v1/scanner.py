from fastapi import APIRouter, Query, HTTPException
from app.utils.scanner import get_local_ip, scan_port
from app.schemas.scanner import ScannerResponse, ScanResult, PortStatus
import ipaddress
from concurrent.futures import ThreadPoolExecutor, as_completed

router = APIRouter(prefix="/scanner", tags=["scanner"])

@router.get("/scan", response_model=ScannerResponse)
def scan_network(
    ports: str = Query("80", description="Ports to scan (separate with commas, e.g. 22,80,443)")
):
    try:
        port_list = [int(p.strip()) for p in ports.split(",") if p.strip().isdigit()]
    except ValueError:
        raise HTTPException(status_code=400, detail="Invalid port format")

    if not port_list:
        raise HTTPException(status_code=400, detail="At least one valid port must be entered.")

    local_ip = get_local_ip()
    if not local_ip:
        raise HTTPException(status_code=400, detail="Local IP address not found.")

    network = ipaddress.IPv4Network(local_ip + '/24', strict=False)
    ip_list = [str(ip) for ip in network.hosts() if str(ip) != local_ip]

    results_by_ip: dict[str, list[PortStatus]] = {}
    targets = [(ip, port) for ip in ip_list for port in port_list]

    with ThreadPoolExecutor(max_workers=100) as executor:
        future_to_target = {
            executor.submit(scan_port, ip, port): (ip, port)
            for ip, port in targets
        }

        for future in as_completed(future_to_target):
            ip, port = future_to_target[future]
            try:
                is_open = future.result()
            except Exception:
                is_open = False

            if ip not in results_by_ip:
                results_by_ip[ip] = []

            results_by_ip[ip].append(PortStatus(
                port=port,
                status="open" if is_open else "closed"
            ))

    results: list[ScanResult] = [
        ScanResult(ip=ip, ports=port_statuses)
        for ip, port_statuses in results_by_ip.items()
    ]

    return ScannerResponse(
        local_ip=local_ip,
        network=str(network),
        scanned_ports=port_list,
        results=results
    )
