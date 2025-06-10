from pydantic import BaseModel
from typing import List, Literal

class PortStatus(BaseModel):
    port: int
    status: Literal["open", "closed"]

class ScanResult(BaseModel):
    ip: str
    ports: List[PortStatus]

class ScannerResponse(BaseModel):
    local_ip: str
    network: str
    scanned_ports: List[int]
    results: List[ScanResult]
