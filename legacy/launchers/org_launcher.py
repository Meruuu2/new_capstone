"""
UA Facility Management System - Organization Staff Portal Launcher
Uses pywebview for lightweight embedded browser.
"""

import os
import sys
import asyncio
import webview


# Role-specific configuration
ROLE = "org_staff"
SECRET = os.getenv("FMS_ORG_SECRET", "UA-ORG-2025")
LOGIN_BASE_URL = os.getenv(
    "FMS_LOGIN_URL", "http://127.0.0.1:8000/fms-portal-entry"
)
ACCESS_TOKEN = os.getenv("FMS_ACCESS_TOKEN", "UA-FMS-ACCESS-2025")
LOGIN_URL = f"{LOGIN_BASE_URL}?access_token={ACCESS_TOKEN}&role={ROLE}"


class Api:
    """API methods exposed to the webview.
    
    Clean API class with no circular references to window objects.
    """

    def __init__(self):
        # No window reference to avoid circular references
        self._login_window = None

    def set_login_window(self, window):
        """Set the login window reference (called after window creation)."""
        self._login_window = window

    def validate_key(self, key: str) -> bool:
        """Validate the access key."""
        return key.strip() == SECRET

    def open_portal(self):
        """
        Open the main portal in a new window instead of reusing the login window.
        Returns success status to JavaScript caller.
        """
        print("[launcher] open_portal called with URL:", LOGIN_URL)
        try:
            # Create new portal window
            webview.create_window(
                title="UA Facility Management - Organization Staff Portal",
                url=LOGIN_URL,
                width=1200,
                height=800,
                resizable=True,
                fullscreen=False,
                min_size=(800, 600),
            )
            
            # Destroy login window if it exists
            if self._login_window:
                try:
                    self._login_window.destroy()
                except Exception as e:
                    print(f"[launcher] Warning: Could not destroy login window: {e}")
            
            print("[launcher] Portal window created successfully.")
            return True
        except Exception as e:
            import traceback
            print("[launcher] Failed to open portal:", e)
            traceback.print_exc()
            return False


def create_login_html():
    """Create the login page HTML."""
    return """
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                background: #f4f4f4;
                height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #111;
            }
            .login-container {
                background: #ffffff;
                padding: 32px 28px;
                border-radius: 8px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.15);
                width: 100%;
                max-width: 360px;
                text-align: left;
                border: 1px solid #ddd;
            }
            .app-title {
                font-size: 18px;
                font-weight: 600;
                margin-bottom: 4px;
                letter-spacing: 0.03em;
            }
            .app-subtitle {
                font-size: 12px;
                color: #555;
                text-transform: uppercase;
                letter-spacing: 0.08em;
                margin-bottom: 18px;
            }
            .role-badge {
                display: inline-block;
                border: 1px solid #111;
                color: #111;
                padding: 4px 10px;
                border-radius: 999px;
                font-size: 11px;
                font-weight: 500;
                text-transform: uppercase;
                letter-spacing: 0.08em;
                margin-bottom: 24px;
            }
            label {
                display: block;
                font-size: 12px;
                margin-bottom: 6px;
                color: #333;
            }
            input[type="password"] {
                width: 100%;
                padding: 10px 12px;
                border: 1px solid #b0b0b0;
                border-radius: 4px;
                font-size: 14px;
                margin-bottom: 14px;
                transition: border-color 0.2s, box-shadow 0.2s;
                background: #fafafa;
            }
            input[type="password"]:focus {
                outline: none;
                border-color: #111;
                box-shadow: 0 0 0 1px #111;
                background: #ffffff;
            }
            button {
                width: 100%;
                padding: 10px;
                background: #111;
                color: #ffffff;
                border: none;
                border-radius: 4px;
                font-size: 14px;
                font-weight: 500;
                cursor: pointer;
                transition: background 0.15s, transform 0.05s;
            }
            button:hover {
                background: #222;
            }
            button:active {
                transform: translateY(1px);
            }
            .error {
                color: #b00020;
                font-size: 12px;
                margin-top: 8px;
                display: none;
            }
            .footer {
                margin-top: 18px;
                color: #777;
                font-size: 11px;
                border-top: 1px solid #eee;
                padding-top: 10px;
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <div class="app-title">University of Antique</div>
            <div class="app-subtitle">Facility & Equipment Management</div>
            <div class="role-badge">Organization Staff Portal</div>

            <label for="keyInput">Organization staff access key</label>
            <input type="password" id="keyInput" placeholder="Enter key" autofocus>
            <button onclick="handleLogin()">Continue</button>

            <div class="error" id="errorMsg">Access denied. Invalid key.</div>

            <div class="footer">Authorized personnel only</div>
        </div>

        <script>
            function handleLogin() {
                const key = document.getElementById('keyInput').value;

                // pywebview 6.x exposes js_api as window.pywebview.api
                const api = window.pywebview && window.pywebview.api;

                if (!api) {
                    console.error('pywebview API not available');
                    return;
                }

                api.validate_key(key).then(async (isValid) => {
                    if (isValid) {
                        // Await the portal opening to prevent callback destruction
                        const result = await api.open_portal();
                        if (!result) {
                            console.error('Failed to open portal');
                            document.getElementById('errorMsg').style.display = 'block';
                            document.getElementById('errorMsg').textContent = 'Failed to open portal. Please try again.';
                        }
                    } else {
                        document.getElementById('errorMsg').style.display = 'block';
                        document.getElementById('keyInput').value = '';
                        document.getElementById('keyInput').focus();
                    }
                }).catch(err => {
                    console.error('Error calling validate_key:', err);
                });
            }

            // Allow Enter key to submit
            document.getElementById('keyInput').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    handleLogin();
                }
            });
        </script>
    </body>
    </html>
    """


def main():
    # Create the API first (no window reference to avoid circular references)
    api = Api()

    # Local HTML key gate; portal opens in a separate window after validation
    login_window = webview.create_window(
        title="UA Facility Management - Organization Staff Portal",
        html=create_login_html(),
        width=400,
        height=500,
        resizable=False,
        fullscreen=False,
        js_api=api,
    )

    # Now that the window exists, set the login window reference
    api.set_login_window(login_window)

    # Start the application (only once, in main thread)
    # Use CEF backend on Windows to avoid WebView2 threading issues
    # Falls back to edgechromium if CEF is not available
    try:
        webview.start(gui='cef')
    except Exception:
        print("[launcher] CEF backend not available, falling back to default")
        webview.start()


if __name__ == "__main__":
    main()