#!/bin/bash
set -e

# Change to the script's directory
cd "$(dirname "$0")"

echo "Checking execution environment..."

# Check if python3.11 is available
if ! command -v python3.11 &> /dev/null; then
    echo "python3.11 could not be found. Please install Python 3.11."
    exit 1
fi

# Create virtual environment if it doesn't exist
if [ ! -d "venv" ]; then
    echo "Creating virtual environment with python3.11..."
    python3.11 -m venv venv
else
    # Check if venv is python 3.11
    if [ ! -f "venv/bin/python3.11" ]; then
        echo "Existing venv is not Python 3.11. Recreating..."
        rm -rf venv
        python3.11 -m venv venv
    fi
fi

# Activate virtual environment
source venv/bin/activate

# Upgrade pip
pip install --upgrade pip

# Install requirements
if [ -f "requirements.txt" ]; then
    echo "Installing requirements..."
    pip install -r requirements.txt
else
    echo "requirements.txt not found!"
    exit 1
fi

# Run the application
echo "Starting Face Recognition Client..."
python app.py
