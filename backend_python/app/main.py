from fastapi import FastAPI
from fastapi.exceptions import RequestValidationError, HTTPException

# from app.db.session import engine, Base
from app.middlewares.loggingMiddleware import LoggingMiddleware
from app.handlers.exceptionHandler import (
    http_exception_handler,
    unhandled_exception_handler,
    validation_exception_handler,
)
from app.api import api_router
import logging

# Base.metadata.create_all(bind=engine)

app = FastAPI()

logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s - %(levelname)s - %(name)s - %(message)s",
)

app.add_exception_handler(HTTPException, http_exception_handler)
app.add_exception_handler(RequestValidationError, validation_exception_handler)
app.add_exception_handler(Exception, unhandled_exception_handler)


app.add_middleware(LoggingMiddleware)

app.include_router(api_router)

@app.get("/")
def root():
    return {"message": "Welcome to NetSentinel!"}