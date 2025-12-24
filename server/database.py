from sqlalchemy import create_engine
from sqlalchemy.orm import sessionmaker, declarative_base
import os
from dotenv import load_dotenv

# Load .env from current directory (server/)
load_dotenv(os.path.join(os.path.dirname(__file__), '.env'))

DB_CONNECTION = os.getenv("DB_CONNECTION", "mysql")
DB_HOST = os.getenv("DB_HOST", "127.0.0.1")
DB_PORT = os.getenv("DB_PORT", "3306")
DB_DATABASE = os.getenv("DB_DATABASE", "sentinel")
DB_USERNAME = os.getenv("DB_USERNAME", "root")
DB_PASSWORD = os.getenv("DB_PASSWORD", "")

# SQLAlchemy Connection URL
if DB_CONNECTION == "sqlite":
    # For SQLite, use the same database as Laravel (web/)
    # so both FastAPI and Laravel share the same data
    if DB_DATABASE.startswith("/"):
        # Absolute path specified
        SQLALCHEMY_DATABASE_URL = f"sqlite:///{DB_DATABASE}"
    else:
        # Relative path: use web/database/ folder (shared with Laravel)
        server_dir = os.path.dirname(__file__)
        project_root = os.path.dirname(server_dir)
        db_path = os.path.join(project_root, "web", "database", DB_DATABASE)
        SQLALCHEMY_DATABASE_URL = f"sqlite:///{db_path}"
    
    # SQLite requires strict thread management usually, but for dev checks:
    connect_args = {"check_same_thread": False}
else:
    # MySQL
    SQLALCHEMY_DATABASE_URL = f"mysql+pymysql://{DB_USERNAME}:{DB_PASSWORD}@{DB_HOST}:{DB_PORT}/{DB_DATABASE}"
    connect_args = {}

engine = create_engine(SQLALCHEMY_DATABASE_URL, connect_args=connect_args)
SessionLocal = sessionmaker(autocommit=False, autoflush=False, bind=engine)

Base = declarative_base()

def get_db():
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()
