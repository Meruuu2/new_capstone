import getpass
import webbrowser
import sys
import os

# Read from environment variables with defaults
SECRET = os.getenv('FMS_LAUNCHER_SECRET', 'UA-FMS-2025')
LOGIN_BASE_URL = os.getenv('FMS_LOGIN_URL', 'http://127.0.0.1:8000/fms-portal-entry')
ACCESS_TOKEN = os.getenv('FMS_ACCESS_TOKEN', 'UA-FMS-ACCESS-2025')
ROLE = os.getenv('FMS_LAUNCHER_ROLE', 'admin')
LOGIN_URL = f"{LOGIN_BASE_URL}?access_token={ACCESS_TOKEN}&role={ROLE}"

def main():
    print("University of Antique - Facility Management System")
    print("Staff/Authorized Personnel Only")
    print()

    # Use getpass so the key isn't shown as you type
    key = getpass.getpass("Enter access key: ")

    if key != SECRET:
        print("Invalid key. Exiting.")
        input("Press Enter to close...")
        sys.exit(1)

    print("Access granted. Opening secure portal...")
    webbrowser.open(LOGIN_URL)
    print("Portal opened in your default browser.")
    input("Press Enter to close...")
    sys.exit(0)

if __name__ == "__main__":
    main()
 