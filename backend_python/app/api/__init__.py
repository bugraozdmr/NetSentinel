from fastapi import APIRouter
from app.api.v1 import scanner

api_router = APIRouter()
api_router.include_router(scanner.router)