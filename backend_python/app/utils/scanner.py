import socket
import psutil

def get_local_ip():
    for interface_addrs in psutil.net_if_addrs().values():
        for addr in interface_addrs:
            if addr.family == socket.AF_INET and not addr.address.startswith("127."):
                return addr.address
    return None

def scan_port(ip, port=80, timeout=0.5):
    try:
        with socket.create_connection((ip, port), timeout=timeout):
            return True
    except Exception:
        return False
